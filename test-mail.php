<?php
// File kiểm tra gửi mail trên host AwardSpace cho GlowBeauty.
// Mở: https://glowbeauty.id.vn/test-mail.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$cfgFile = __DIR__ . '/config/mail.php';
$cfg = is_file($cfgFile) ? require $cfgFile : [];
$to = $cfg['admin_email'] ?? 'nn9499008@gmail.com';
$domain = preg_replace('/[^a-zA-Z0-9\.\-]/', '', $_SERVER['HTTP_HOST'] ?? 'glowbeauty.id.vn');
if ($domain === '') $domain = 'glowbeauty.id.vn';
$from = $cfg['fallback_from_email'] ?? ('no-reply@' . $domain);
$reply = $cfg['reply_to'] ?? ($cfg['smtp_username'] ?? $to);
$subject = 'GlowBeauty - Test mail hosting ' . date('Y-m-d H:i:s');
$body = '<h2>GlowBeauty test mail</h2><p>Nếu bạn nhận được email này thì host đã gửi mail được.</p><p>Thời gian: ' . date('Y-m-d H:i:s') . '</p>';

function enc_subject($s) { return '=?UTF-8?B?' . base64_encode($s) . '?='; }
function enc_name($s) { return '=?UTF-8?B?' . base64_encode($s) . '?='; }

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/html; charset=UTF-8';
$headers[] = 'Content-Transfer-Encoding: 8bit';
$headers[] = 'From: ' . enc_name('GlowBeauty Website') . ' <' . $from . '>';
$headers[] = 'Reply-To: ' . $reply;
$headers[] = 'X-Mailer: GlowBeauty Host Mail Test';

$logDir = __DIR__ . '/storage/email_outbox';
if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
$logFile = $logDir . '/test_mail_result.log';

$ok = @mail($to, enc_subject($subject), $body, implode("\r\n", $headers), '-f' . $from);
if (!$ok) {
    $ok = @mail($to, enc_subject($subject), $body, implode("\r\n", $headers));
}

$msg = date('Y-m-d H:i:s') . ' | to=' . $to . ' | from=' . $from . ' | result=' . ($ok ? 'OK' : 'FAILED') . "\n";
@file_put_contents($logFile, $msg, FILE_APPEND);

header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html><html><head><meta charset="UTF-8"><title>GlowBeauty Test Mail</title></head><body style="font-family:Arial;padding:24px">';
echo '<h2>GlowBeauty - Kiểm tra gửi mail</h2>';
echo '<p><b>Gửi tới:</b> ' . htmlspecialchars($to) . '</p>';
echo '<p><b>Gửi từ:</b> ' . htmlspecialchars($from) . '</p>';
if ($ok) {
    echo '<h3 style="color:green">ĐÃ GỌI mail() THÀNH CÔNG</h3>';
    echo '<p>Hãy kiểm tra Gmail: Hộp thư đến, Spam, Quảng cáo.</p>';
} else {
    echo '<h3 style="color:red">mail() CỦA HOSTING CŨNG KHÔNG GỬI ĐƯỢC</h3>';
    echo '<p>Khi đó AwardSpace đang chặn cả SMTP và hàm mail(). Cần dùng mail server/API bên ngoài hoặc nâng gói host.</p>';
}
echo '<p>Log đã ghi tại: <code>storage/email_outbox/test_mail_result.log</code></p>';
echo '</body></html>';
