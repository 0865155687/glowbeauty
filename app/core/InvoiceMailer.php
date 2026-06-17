<?php
require_once __DIR__ . '/Database.php';

class InvoiceMailer
{
    private static function ensureLogTable()
    {
        try {
            $pdo = Database::connect();
            $pdo->exec("CREATE TABLE IF NOT EXISTS invoice_email_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                email VARCHAR(190) NOT NULL,
                status VARCHAR(30) NOT NULL,
                message TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_invoice_email_order (order_id, email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {}
    }

    private static function alreadySent($orderId, $email = '')
    {
        self::ensureLogTable();
        try {
            if ($email !== '') {
                $st = Database::connect()->prepare("SELECT status FROM invoice_email_logs WHERE order_id=? AND email=? LIMIT 1");
                $st->execute([(int)$orderId, $email]);
            } else {
                $st = Database::connect()->prepare("SELECT status FROM invoice_email_logs WHERE order_id=? LIMIT 1");
                $st->execute([(int)$orderId]);
            }
            $row = $st->fetch();
            return $row && ($row['status'] ?? '') === 'sent';
        } catch (Exception $e) {
            return false;
        }
    }

    private static function log($orderId, $email, $status, $message)
    {
        self::ensureLogTable();
        try {
            $pdo = Database::connect();
            $st = $pdo->prepare("INSERT INTO invoice_email_logs(order_id,email,status,message,created_at)
                VALUES(?,?,?,?,NOW())
                ON DUPLICATE KEY UPDATE status=VALUES(status), message=VALUES(message), created_at=NOW()");
            $st->execute([(int)$orderId, $email, $status, $message]);
        } catch (Exception $e) {}

        self::debug('order=' . (int)$orderId . ' | to=' . $email . ' | status=' . $status . ' | ' . $message);
    }

    private static function debug($message)
    {
        $dir = dirname(__DIR__, 2) . '/storage/email_outbox';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        @file_put_contents($dir . '/mail_debug.log', date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND);
    }

    private static function money($n)
    {
        return number_format((float)$n, 0, ',', '.') . 'đ';
    }

    private static function config()
    {
        $file = dirname(__DIR__, 2) . '/config/mail.php';
        $cfg = is_file($file) ? require $file : [];

        $defaultDomain = preg_replace('/[^a-zA-Z0-9\.\-]/', '', $_SERVER['HTTP_HOST'] ?? 'glowbeauty.id.vn');
        if ($defaultDomain === '') $defaultDomain = 'glowbeauty.id.vn';

        return array_merge([
            'admin_email' => 'nn9499008@gmail.com',
            'brevo_api_key' => '',
            'brevo_api_url' => 'https://api.brevo.com/v3/smtp/email',
            'smtp_host' => 'smtp-relay.brevo.com',
            'smtp_port' => 587,
            'smtp_secure' => 'tls',
            'smtp_username' => 'aed627001@smtp-brevo.com',
            'smtp_password' => '',
            'from_email' => 'no-reply@glowbeauty.id.vn',
            'from_name' => 'GlowBeauty Website',
            // Dùng cho fallback mail() trên hosting. Nên để email theo tên miền để host dễ chấp nhận hơn.
            'fallback_from_email' => 'no-reply@' . $defaultDomain,
            'reply_to' => 'nn9499008@gmail.com',
        ], is_array($cfg) ? $cfg : []);
    }

    private static function encSubject($subject)
    {
        return '=?UTF-8?B?' . base64_encode((string)$subject) . '?=';
    }

    private static function encHeaderName($name)
    {
        return '=?UTF-8?B?' . base64_encode((string)$name) . '?=';
    }

    private static function smtpRead($fp)
    {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    }

    private static function smtpCmd($fp, $cmd, $expect)
    {
        fwrite($fp, $cmd . "\r\n");
        $res = self::smtpRead($fp);
        $code = (int)substr($res, 0, 3);
        $expects = (array)$expect;
        if (!in_array($code, $expects, true)) {
            throw new Exception('SMTP lỗi sau lệnh ' . strtok($cmd, ' ') . ': ' . trim($res));
        }
        return $res;
    }

    private static function cryptoMethod()
    {
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            return STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        }
        return STREAM_CRYPTO_METHOD_TLS_CLIENT;
    }

    private static function smtpRemote($host, $port, $isSsl)
    {
        // Nhiều host/local bị lỗi IPv6 "Network is unreachable" khi mở smtp.gmail.com.
        // Ép lấy IPv4 trước để Gmail SMTP kết nối ổn định hơn, nhưng vẫn giữ peer_name là smtp.gmail.com cho SSL/TLS.
        $ips = @gethostbynamel($host);
        if (is_array($ips) && !empty($ips[0])) {
            return ($isSsl ? 'ssl://' : 'tcp://') . $ips[0] . ':' . (int)$port;
        }
        return ($isSsl ? 'ssl://' : 'tcp://') . $host . ':' . (int)$port;
    }

    private static function smtpSession($to, $subject, $html, $host, $port, $secure)
    {
        $cfg = self::config();
        $username = trim((string)$cfg['smtp_username']);
        $password = str_replace(' ', '', trim((string)$cfg['smtp_password']));
        $fromEmail = trim((string)$cfg['from_email']);
        $fromName = trim((string)$cfg['from_name']);

        if ($host === '' || $username === '' || $password === '' || $fromEmail === '') {
            throw new Exception('Chưa cấu hình đủ SMTP trong config/mail.php.');
        }

        $isSsl = ((int)$port === 465 || strtolower((string)$secure) === 'ssl');
        $remote = self::smtpRemote($host, $port, $isSsl);
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                'SNI_enabled' => true,
                'SNI_server_name' => $host,
                'peer_name' => $host,
            ]
        ]);

        $fp = @stream_socket_client($remote, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        if (!$fp) {
            $last = error_get_last();
            $detail = trim((string)$errstr);
            if ($detail === '' && !empty($last['message'])) $detail = $last['message'];
            throw new Exception('Không kết nối được SMTP ' . $host . ':' . $port . ' qua ' . $remote . ' - ' . $detail . ' (' . $errno . ')');
        }
        stream_set_timeout($fp, 30);

        $hello = self::smtpRead($fp);
        if ((int)substr($hello, 0, 3) !== 220) {
            throw new Exception('SMTP không phản hồi 220: ' . trim($hello));
        }

        $ehloHost = preg_replace('/[^a-zA-Z0-9\.\-]/', '', $_SERVER['HTTP_HOST'] ?? 'glowbeauty.id.vn');
        if ($ehloHost === '') $ehloHost = 'glowbeauty.id.vn';

        self::smtpCmd($fp, 'EHLO ' . $ehloHost, 250);

        if (!$isSsl && ((int)$port === 587 || strtolower((string)$secure) === 'tls')) {
            self::smtpCmd($fp, 'STARTTLS', 220);
            $cryptoOk = @stream_socket_enable_crypto($fp, true, self::cryptoMethod());
            if (!$cryptoOk) {
                throw new Exception('Không bật được TLS cho SMTP ' . $host . ':' . $port . '.');
            }
            self::smtpCmd($fp, 'EHLO ' . $ehloHost, 250);
        }

        self::smtpCmd($fp, 'AUTH LOGIN', 334);
        self::smtpCmd($fp, base64_encode($username), 334);
        self::smtpCmd($fp, base64_encode($password), 235);
        self::smtpCmd($fp, 'MAIL FROM:<' . $fromEmail . '>', 250);
        self::smtpCmd($fp, 'RCPT TO:<' . $to . '>', [250, 251]);
        self::smtpCmd($fp, 'DATA', 354);

        $headers = [];
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'Message-ID: <gb-' . uniqid('', true) . '@' . $ehloHost . '>';
        $headers[] = 'From: ' . self::encHeaderName($fromName) . ' <' . $fromEmail . '>';
        $headers[] = 'To: <' . $to . '>';
        $headers[] = 'Subject: ' . self::encSubject($subject);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: 8bit';
        $headers[] = 'X-Mailer: GlowBeauty SMTP';

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $html;
        $message = preg_replace('/^\./m', '..', $message);
        fwrite($fp, $message . "\r\n.\r\n");

        $res = self::smtpRead($fp);
        if ((int)substr($res, 0, 3) !== 250) {
            throw new Exception('SMTP không nhận DATA: ' . trim($res));
        }

        @fwrite($fp, "QUIT\r\n");
        @fclose($fp);
        return true;
    }

    private static function sendSmtp($to, $subject, $html)
    {
        $cfg = self::config();
        $host = trim((string)$cfg['smtp_host']);
        $primaryPort = (int)$cfg['smtp_port'];
        $primarySecure = strtolower((string)$cfg['smtp_secure']);

        $attempts = [];
        $attempts[] = [$host, $primaryPort ?: 587, $primarySecure ?: 'tls'];
        if (($primaryPort ?: 587) !== 465) $attempts[] = [$host, 465, 'ssl'];
        if (($primaryPort ?: 587) !== 587) $attempts[] = [$host, 587, 'tls'];

        $errors = [];
        foreach ($attempts as $a) {
            try {
                self::debug('Thử SMTP ' . $a[0] . ':' . $a[1] . '/' . $a[2] . ' -> ' . $to);
                return self::smtpSession($to, $subject, $html, $a[0], $a[1], $a[2]);
            } catch (Exception $e) {
                $errors[] = $a[0] . ':' . $a[1] . '/' . $a[2] . ' => ' . $e->getMessage();
                self::debug('SMTP fail: ' . end($errors));
            }
        }

        throw new Exception('SMTP thất bại: ' . implode(' || ', $errors));
    }

    private static function sendByHostMail($to, $subject, $html)
    {
        $cfg = self::config();
        $domain = preg_replace('/[^a-zA-Z0-9\.\-]/', '', $_SERVER['HTTP_HOST'] ?? 'glowbeauty.id.vn');
        if ($domain === '') $domain = 'glowbeauty.id.vn';

        $fromEmail = trim((string)($cfg['fallback_from_email'] ?? ''));
        if ($fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $fromEmail = 'no-reply@' . $domain;
        }

        $replyTo = trim((string)($cfg['reply_to'] ?? ($cfg['smtp_username'] ?? 'nn9499008@gmail.com')));
        if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $replyTo = 'nn9499008@gmail.com';

        $fromName = trim((string)($cfg['from_name'] ?? 'GlowBeauty Website'));

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: 8bit';
        $headers[] = 'From: ' . self::encHeaderName($fromName) . ' <' . $fromEmail . '>';
        $headers[] = 'Reply-To: ' . $replyTo;
        $headers[] = 'X-Mailer: GlowBeauty Host Mail';

        $ok = @mail($to, self::encSubject($subject), $html, implode("\r\n", $headers), '-f' . $fromEmail);
        if (!$ok) {
            $ok = @mail($to, self::encSubject($subject), $html, implode("\r\n", $headers));
        }
        if (!$ok && $fromEmail !== 'info@glowbeauty.id.vn') {
            $headers2 = $headers;
            foreach ($headers2 as $k => $h) {
                if (stripos($h, 'From:') === 0) $headers2[$k] = 'From: ' . self::encHeaderName($fromName) . ' <info@glowbeauty.id.vn>';
            }
            $ok = @mail($to, self::encSubject($subject), $html, implode("\r\n", $headers2), '-finfo@glowbeauty.id.vn');
            if (!$ok) $ok = @mail($to, self::encSubject($subject), $html, implode("\r\n", $headers2));
        }
        if (!$ok) {
            throw new Exception('Hàm mail() của hosting trả về false.');
        }
        return true;
    }

    private static function brevoApiKey()
    {
        $cfg = self::config();

        // Ưu tiên API key riêng. Nếu chưa có, thử dùng smtp_password cũ để log rõ hơn.
        $key = trim((string)($cfg['brevo_api_key'] ?? ''));
        if ($key === '' || stripos($key, 'PASTE_') === 0 || stripos($key, 'DAN_') === 0) {
            $key = trim((string)($cfg['api_key'] ?? ''));
        }
        if ($key === '' || stripos($key, 'PASTE_') === 0 || stripos($key, 'DAN_') === 0) {
            $key = trim((string)($cfg['smtp_password'] ?? ''));
        }

        return $key;
    }

    private static function brevoApiUrl()
    {
        $cfg = self::config();
        $url = trim((string)($cfg['brevo_api_url'] ?? ''));
        return $url !== '' ? $url : 'https://api.brevo.com/v3/smtp/email';
    }

    private static function sendBrevoApi($to, $subject, $html)
    {
        $cfg = self::config();
        $apiKey = self::brevoApiKey();
        $fromEmail = trim((string)($cfg['from_email'] ?? 'no-reply@glowbeauty.id.vn'));
        $fromName = trim((string)($cfg['from_name'] ?? 'GlowBeauty Website'));
        $replyTo = trim((string)($cfg['reply_to'] ?? $cfg['admin_email'] ?? 'nn9499008@gmail.com'));

        if ($apiKey === '' || stripos($apiKey, 'PASTE_') === 0 || stripos($apiKey, 'DAN_') === 0 || $apiKey === 'xxxxxxxxxxxxxxxxxxxxxxxx') {
            throw new Exception('Chưa cấu hình Brevo API key trong config/mail.php. Cần dùng API Key, không dùng SMTP Key.');
        }
        if (stripos($apiKey, 'xsmtpsib-') === 0) {
            throw new Exception('Bạn đang dán SMTP Key. Brevo API cần API Key ở tab API keys & MCP, thường bắt đầu bằng xkeysib-.');
        }
        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('from_email không hợp lệ trong config/mail.php.');
        }
        if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $replyTo = $cfg['admin_email'] ?? 'nn9499008@gmail.com';
        }

        $payload = [
            'sender' => [
                'name' => $fromName,
                'email' => $fromEmail,
            ],
            'to' => [[
                'email' => $to,
            ]],
            'replyTo' => [
                'email' => $replyTo,
            ],
            'subject' => $subject,
            'htmlContent' => $html,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new Exception('Không tạo được JSON gửi Brevo API.');
        }

        $urls = array_values(array_unique(array_filter([
            self::brevoApiUrl(),
            'https://api.brevo.com/v3/smtp/email',
            'https://api.sendinblue.com/v3/smtp/email',
        ])));

        if (function_exists('curl_init')) {
            $curlErrors = [];
            foreach ($urls as $url) {
                self::debug('Thử Brevo API ' . $url . ' -> ' . $to);
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_HTTPHEADER => [
                        'accept: application/json',
                        'api-key: ' . $apiKey,
                        'content-type: application/json',
                    ],
                    CURLOPT_POSTFIELDS => $json,
                    CURLOPT_CONNECTTIMEOUT => 20,
                    CURLOPT_TIMEOUT => 45,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ]);
                $response = curl_exec($ch);
                $errno = curl_errno($ch);
                $error = curl_error($ch);
                $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if (!$errno && $status >= 200 && $status < 300) {
                    self::debug('Gửi Brevo API thành công -> ' . $to . ' | HTTP ' . $status);
                    return true;
                }
                $curlErrors[] = $url . ' => ' . ($errno ? ($error . ' (' . $errno . ')') : ('HTTP ' . $status . ': ' . trim((string)$response)));
            }
            throw new Exception('Brevo API cURL lỗi: ' . implode(' || ', $curlErrors));
        }

        // Fallback nếu cURL bị tắt: gọi HTTPS bằng file_get_contents.
        $url = $urls[0] ?? self::brevoApiUrl();
        $headers = "accept: application/json\r\n"
            . "api-key: " . $apiKey . "\r\n"
            . "content-type: application/json\r\n";

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $headers,
                'content' => $json,
                'timeout' => 45,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        $status = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (preg_match('/^HTTP\/\S+\s+(\d+)/', $h, $m)) {
                    $status = (int)$m[1];
                    break;
                }
            }
        }

        if ($response === false) {
            $last = error_get_last();
            throw new Exception('Brevo API file_get_contents lỗi: ' . ($last['message'] ?? 'không rõ lỗi'));
        }
        if ($status < 200 || $status >= 300) {
            throw new Exception('Brevo API HTTP ' . $status . ': ' . trim((string)$response));
        }

        self::debug('Gửi Brevo API thành công -> ' . $to . ' | HTTP ' . $status);
        return true;
    }

    private static function sendHtml($to, $subject, $html)
    {
        $to = trim((string)$to);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email không hợp lệ: ' . $to);
        }

        $errors = [];

        // AwardSpace chặn SMTP 465/587, nên gửi bằng Brevo REST API qua HTTPS.
        try {
            self::sendBrevoApi($to, $subject, $html);
            return true;
        } catch (Exception $apiError) {
            $errors[] = 'Brevo API lỗi: ' . $apiError->getMessage();
            self::debug('Brevo API fail: ' . $apiError->getMessage());
        }

        // Fallback cuối cùng: thử mail() của hosting nếu API không chạy.
        try {
            self::sendByHostMail($to, $subject, $html);
            self::debug('Gửi bằng mail() hosting thành công -> ' . $to);
            return true;
        } catch (Exception $mailError) {
            $errors[] = 'Host mail() lỗi: ' . $mailError->getMessage();
        }

        throw new Exception(implode(' | ', $errors));
    }

    private static function saveOutbox($prefix, $orderId, $html)
    {
        $outbox = dirname(__DIR__, 2) . '/storage/email_outbox';
        if (!is_dir($outbox)) @mkdir($outbox, 0777, true);
        $file = $outbox . '/' . $prefix . '_' . (int)$orderId . '_' . date('Ymd_His') . '.html';
        @file_put_contents($file, $html);
        return $file;
    }

    private static function buildRows($items)
    {
        $rows = '';
        foreach ($items as $it) {
            $name = htmlspecialchars($it['product_name'] ?? ($it['name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $qty = (int)($it['quantity'] ?? ($it['qty'] ?? 0));
            $price = (float)($it['price'] ?? 0);
            $rows .= '<tr>'
                . '<td style="padding:10px;border-bottom:1px solid #f3d8ca"><b>' . $name . '</b></td>'
                . '<td style="padding:10px;border-bottom:1px solid #f3d8ca;text-align:center">' . $qty . '</td>'
                . '<td style="padding:10px;border-bottom:1px solid #f3d8ca;text-align:right">' . self::money($price) . '</td>'
                . '<td style="padding:10px;border-bottom:1px solid #f3d8ca;text-align:right"><b>' . self::money($price * $qty) . '</b></td>'
                . '</tr>';
        }
        return $rows;
    }

    private static function orderNote($order)
    {
        $noteValue = trim((string)($order['note'] ?? ''));
        return htmlspecialchars($noteValue !== '' ? $noteValue : 'Không có ghi chú', ENT_QUOTES, 'UTF-8');
    }

    public static function sendPaidInvoice($order, $items, $customerEmail)
    {
        $orderId = (int)($order['id'] ?? 0);
        $customerEmail = trim((string)$customerEmail);

        if ($orderId <= 0) return ['ok' => false, 'message' => 'Không tìm thấy mã đơn hàng để gửi email.'];
        if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            self::log($orderId, 'customer-email-empty', 'failed', 'Không tìm thấy email khách hàng để gửi hóa đơn.');
            return ['ok' => false, 'message' => 'Không tìm thấy email khách hàng để gửi hóa đơn. Vui lòng kiểm tra tài khoản khách đã có email hợp lệ.'];
        }
        if (self::alreadySent($orderId, $customerEmail)) return ['ok' => true, 'message' => 'Hóa đơn đã được gửi về email ' . $customerEmail . '.'];

        $orderCode = '#GB' . date('Ymd', strtotime($order['created_at'] ?? 'now')) . str_pad((string)$orderId, 4, '0', STR_PAD_LEFT);
        $payCode = $order['payment_code'] ?? ('GBPAY' . date('Ymd', strtotime($order['created_at'] ?? 'now')) . str_pad((string)$orderId, 4, '0', STR_PAD_LEFT));
        $shippingFee = (float)($order['shipping_fee'] ?? 0);
        $grandTotal = (float)($order['total'] ?? 0);
        $subTotal = max(0, $grandTotal - $shippingFee);

        $customer = htmlspecialchars($order['customer_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($order['phone'] ?? '', ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($order['address'] ?? '', ENT_QUOTES, 'UTF-8');
        $note = self::orderNote($order);

        $subject = 'GlowBeauty - Hóa đơn thanh toán ' . $orderCode;
        $html = '<!doctype html><html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;background:#fff7f2;color:#35160f;padding:20px">'
            . '<div style="max-width:720px;margin:auto;background:white;border:1px solid #f3cdbf;border-radius:18px;padding:24px">'
            . '<h2 style="color:#c75b2b;margin:0 0 8px">GlowBeauty</h2>'
            . '<h1 style="margin:0 0 12px">Hóa đơn thanh toán thành công</h1>'
            . '<p>Cảm ơn <b>' . $customer . '</b> đã mua hàng tại GlowBeauty.</p>'
            . '<p><b>Mã đơn:</b> ' . $orderCode . '<br><b>Mã thanh toán:</b> ' . htmlspecialchars($payCode, ENT_QUOTES, 'UTF-8') . '<br><b>Trạng thái:</b> Đã thanh toán</p>'
            . '<div style="background:#fff1e9;border-radius:14px;padding:14px;margin:16px 0"><b>Thông tin nhận hàng</b><br>Khách hàng: ' . $customer . '<br>SĐT: ' . $phone . '<br>Địa chỉ: ' . $address . '<br><b>Ghi chú giao hàng:</b> ' . $note . '</div>'
            . '<table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-top:12px"><thead><tr style="background:#fff1e9"><th style="padding:10px;text-align:left">Sản phẩm</th><th style="padding:10px">SL</th><th style="padding:10px;text-align:right">Đơn giá</th><th style="padding:10px;text-align:right">Thành tiền</th></tr></thead><tbody>' . self::buildRows($items) . '</tbody></table>'
            . '<div style="margin-top:18px;text-align:right;font-size:16px"><p>Tạm tính: <b>' . self::money($subTotal) . '</b></p><p>Phí vận chuyển: <b>' . self::money($shippingFee) . '</b></p><p style="font-size:22px;color:#e91e63">Tổng đã thanh toán: <b>' . self::money($grandTotal) . '</b></p></div>'
            . '<p style="margin-top:20px;color:#875b4c">Hotline: 0865155687. GlowBeauty cảm ơn quý khách đã tin tưởng 🌸</p>'
            . '</div></body></html>';

        try {
            self::sendHtml($customerEmail, $subject, $html);
            self::log($orderId, $customerEmail, 'sent', 'Đã gửi hóa đơn về email khách hàng.');
            return ['ok' => true, 'message' => 'Đã gửi hóa đơn thanh toán về email ' . $customerEmail . '.'];
        } catch (Exception $e) {
            self::saveOutbox('invoice_order', $orderId, $html);
            self::log($orderId, $customerEmail, 'failed', $e->getMessage());
            return ['ok' => false, 'message' => 'Chưa gửi được hóa đơn về Gmail khách: ' . $e->getMessage()];
        }
    }

    public static function sendAdminNewOrder($order, $items, $adminEmail = null)
    {
        $cfg = self::config();
        $adminEmail = trim((string)($adminEmail ?: ($cfg['admin_email'] ?? 'nn9499008@gmail.com')));
        $orderId = (int)($order['id'] ?? 0);
        if ($orderId <= 0 || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Thiếu mã đơn hoặc email admin không hợp lệ.'];
        }
        if (self::alreadySent($orderId, $adminEmail)) {
            return ['ok' => true, 'message' => 'Đã gửi thông báo đơn hàng mới về email admin.'];
        }

        $orderCode = '#GB' . date('Ymd', strtotime($order['created_at'] ?? 'now')) . str_pad((string)$orderId, 4, '0', STR_PAD_LEFT);
        $customer = htmlspecialchars($order['customer_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($order['phone'] ?? '', ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($order['address'] ?? '', ENT_QUOTES, 'UTF-8');
        $note = self::orderNote($order);
        $shippingFee = (float)($order['shipping_fee'] ?? 0);
        $grandTotal = (float)($order['total'] ?? 0);
        $subTotal = max(0, $grandTotal - $shippingFee);

        $subject = 'GlowBeauty - Đơn hàng mới ' . $orderCode;
        $html = '<!doctype html><html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;background:#fff7f2;color:#35160f;padding:20px">'
            . '<div style="max-width:720px;margin:auto;background:white;border:1px solid #f3cdbf;border-radius:18px;padding:24px">'
            . '<h2 style="color:#c75b2b;margin:0 0 8px">🔔 GlowBeauty có đơn hàng mới</h2>'
            . '<p><b>Mã đơn:</b> ' . $orderCode . '<br><b>Thời gian:</b> ' . htmlspecialchars($order['created_at'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>'
            . '<div style="background:#fff1e9;border-radius:14px;padding:14px;margin:16px 0"><b>Thông tin nhận hàng</b><br>Khách hàng: ' . $customer . '<br>SĐT: ' . $phone . '<br>Địa chỉ: ' . $address . '<br><b>Ghi chú giao hàng:</b> ' . $note . '</div>'
            . '<table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-top:12px"><thead><tr style="background:#fff1e9"><th style="padding:10px;text-align:left">Sản phẩm</th><th style="padding:10px">SL</th><th style="padding:10px;text-align:right">Đơn giá</th><th style="padding:10px;text-align:right">Thành tiền</th></tr></thead><tbody>' . self::buildRows($items) . '</tbody></table>'
            . '<div style="margin-top:18px;text-align:right;font-size:16px"><p>Tạm tính: <b>' . self::money($subTotal) . '</b></p><p>Phí vận chuyển: <b>' . self::money($shippingFee) . '</b></p><p style="font-size:22px;color:#e91e63">Tổng đơn hàng: <b>' . self::money($grandTotal) . '</b></p></div>'
            . '<p style="margin-top:20px;color:#875b4c">Vui lòng vào trang admin để xử lý đơn hàng.</p>'
            . '</div></body></html>';

        try {
            self::sendHtml($adminEmail, $subject, $html);
            self::log($orderId, $adminEmail, 'sent', 'Đã gửi thông báo đơn hàng mới về email admin.');
            return ['ok' => true, 'message' => 'Đã gửi thông báo đơn hàng mới về email admin.'];
        } catch (Exception $e) {
            self::saveOutbox('admin_new_order', $orderId, $html);
            self::log($orderId, $adminEmail, 'failed', $e->getMessage());
            return ['ok' => false, 'message' => 'Chưa gửi được email admin qua SMTP/mail(): ' . $e->getMessage()];
        }
    }
}
