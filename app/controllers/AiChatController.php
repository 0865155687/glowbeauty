<?php
require_once __DIR__ . '/../core/Database.php';

class AiChatController extends Controller
{
    public function message()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            echo json_encode(['ok' => false, 'reply' => 'Yêu cầu không hợp lệ.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) $data = $_POST;

        $message = trim((string)($data['message'] ?? ''));
        if ($message === '') {
            echo json_encode(['ok' => true, 'reply' => 'Bạn nhắn nội dung cần hỏi giúp mình nhé.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $reply = $this->buildReply($message);
        } catch (Exception $e) {
            $reply = 'Mình đang lỗi kết nối dữ liệu. Bạn thử lại sau vài giây hoặc gọi/Zalo 0865155687 để shop kiểm tra ngay nhé.';
        }

        echo json_encode(['ok' => true, 'reply' => $reply], JSON_UNESCAPED_UNICODE);
    }

    private function buildReply($message)
    {
        $text = $this->normalize($message);

        if ($this->isGreeting($text)) {
            return "GlowBeauty xin chào bạn 👋\nMình là trợ lý của GlowBeauty. Mình có thể giúp gì được cho bạn?";
        }

        if ($this->isThanks($text)) {
            return 'Không có gì nha ❤️ Cần mình xem đơn hay tư vấn sản phẩm thì cứ nhắn tiếp.';
        }

        // Chỉ cần khách gửi số điện thoại/mã đơn/mã thanh toán là tự hiểu đang muốn kiểm tra đơn.
        if ($this->looksLikeOrderLookup($message, $text)) {
            return $this->answerOrder($message, $text);
        }

        if ($this->hasAny($text, ['da dau', 'da nhon', 'mun']) && $this->hasAny($text, ['kem nen', 'nen', 'foundation'])) {
            return 'Da dầu/mụn nên chọn kem nền kiềm dầu, mỏng nhẹ, không bí da. Khi đánh nền, bạn phủ thêm phấn nhẹ vùng chữ T để lâu trôi hơn nhé.';
        }

        if ($this->hasAny($text, ['da kho', 'nhay cam']) && $this->hasAny($text, ['kem nen', 'nen', 'foundation'])) {
            return 'Da khô/nhạy cảm nên chọn nền mỏng ẩm, finish căng nhẹ. Trước khi makeup nhớ dưỡng ẩm kỹ để nền không mốc nhé.';
        }

        if ($this->hasAny($text, ['da dau', 'da nhon', 'mun', 'da kho', 'nhay cam', 'skincare', 'routine'])) {
            return $this->answerSkincare($text);
        }

        if ($this->hasAny($text, ['son','kem nen','foundation','phan ma','blush','phan phu','sua rua mat','toner','serum','mascara','che khuyet diem','combo','san pham','gia','bao nhieu','con hang','loai nao','nen chon','mua gi'])) {
            return $this->answerProduct($message, $text);
        }

        if ($this->hasAny($text, ['doi tra','tra hang','hoan tien','loi san pham','san pham loi','giao nham','vo hang'])) {
            return 'Được bạn nhé. Nếu sản phẩm lỗi/giao nhầm/vỡ hỏng, bạn gửi ảnh hoặc video mở hàng trong 24 giờ để shop hỗ trợ đổi/trả.';
        }

        if ($this->hasAny($text, ['ship','van chuyen','giao hang','bao lau','may ngay','phi ship'])) {
            return 'Shop giao hàng toàn quốc. Thường đơn sẽ được xử lý sau khi xác nhận. Muốn mình xem đơn cụ thể thì gửi số điện thoại hoặc mã đơn nha.';
        }

        if ($this->hasAny($text, ['thanh toan','chuyen khoan','qr','cod','tra tien'])) {
            return 'Shop hỗ trợ COD và chuyển khoản. Nếu đã chuyển khoản, bạn gửi mã thanh toán để mình kiểm tra nhanh hơn.';
        }

        if ($this->hasAny($text, ['dia chi','showroom','cua hang','o dau'])) {
            return 'Shop ở Số 3 Vũ Công Đán, phường Tứ Minh, Thành phố Hải Phòng. Mở cửa 8:00 - 21:00 hằng ngày.';
        }

        if ($this->hasAny($text, ['hotline','lien he','zalo','facebook','sdt','so dien thoai'])) {
            return 'Bạn liên hệ shop qua hotline/Zalo 0865155687 hoặc 0394807683 nhé.';
        }

        return $this->shortFriendlyAnswer($message, $text);
    }

    private function answerOrder($message, $text)
    {
        $pdo = Database::connect();
        $order = null;

        $phone = $this->extractPhone($message);
        $orderId = $this->extractOrderId($message, $text);
        $paymentCode = $this->extractPaymentCode($message);

        if ($paymentCode) {
            $st = $pdo->prepare("SELECT * FROM orders WHERE payment_code = ? ORDER BY id DESC LIMIT 1");
            $st->execute([$paymentCode]);
            $order = $st->fetch();
        }

        if (!$order && $orderId) {
            $st = $pdo->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
            $st->execute([$orderId]);
            $order = $st->fetch();
        }

        if (!$order && $phone) {
            $st = $pdo->prepare("SELECT * FROM orders WHERE phone = ? ORDER BY id DESC LIMIT 1");
            $st->execute([$phone]);
            $order = $st->fetch();
        }

        if (!$order && !empty($_SESSION['user']['id'])) {
            try {
                $st = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 1");
                $st->execute([(int)$_SESSION['user']['id']]);
                $order = $st->fetch();
            } catch (Exception $e) {}
        }

        if (!$order) {
            if ($phone || $orderId || $paymentCode) {
                return 'Mình chưa thấy đơn này trong hệ thống. Bạn kiểm tra lại mã đơn/số điện thoại giúp mình, hoặc nhắn Zalo 0865155687 để shop kiểm tra thủ công.';
            }
            return 'Bạn gửi mình mã đơn hoặc số điện thoại đặt hàng, mình kiểm tra ngay.';
        }

        $items = $this->orderItems((int)$order['id']);
        $status = $order['status'] ?? 'Chờ xác nhận';
        $payment = $order['payment_status'] ?? 'Chưa thanh toán';
        $total = number_format((int)($order['total'] ?? 0), 0, ',', '.').'đ';

        $reply = 'Đơn #'.(int)$order['id'].' đang: '.$status.'.';
        $reply .= "\nThanh toán: ".$payment.'. Tổng: '.$total.'.';
        if ($items) $reply .= "\nSản phẩm: ".$items.'.';
        $reply .= "\n".$this->statusGuide($status);
        return $reply;
    }

    private function answerProduct($message, $text)
    {
        $products = $this->findProducts($message, $text);

        if ($products) {
            $lines = [];
            foreach ($products as $p) {
                $stockText = ((int)$p['stock'] > 0) ? 'còn hàng' : 'tạm hết';
                $lines[] = $p['name'].' — '.number_format((int)$p['price'],0,',','.').'đ, '.$stockText;
            }
            return "Mình thấy mấy món này hợp nè:\n".implode("\n", $lines)."\nBạn muốn mình chọn 1 món hợp nhất theo loại da thì nhắn loại da của bạn nhé.";
        }

        if ($this->hasAny($text, ['kem nen', 'foundation'])) {
            return 'Bạn chọn kem nền theo da nhé: da dầu chọn nền kiềm dầu, da khô chọn nền ẩm mịn, da mụn chọn nền mỏng nhẹ ít bí da.';
        }
        if (strpos($text, 'son') !== false) {
            return 'Son dùng hằng ngày nên chọn màu hồng đất, cam đất hoặc đỏ nâu nhẹ. Dễ dùng và không kén da.';
        }
        if (strpos($text, 'che khuyet diem') !== false) {
            return 'Che khuyết điểm nên chọn đúng tông da để che mụn/thâm, còn che quầng mắt thì chọn sáng hơn da nửa tông.';
        }

        return 'Bạn muốn tìm son, kem nền, che khuyết điểm, phấn phủ hay skincare? Nhắn tên loại sản phẩm, mình gợi ý nhanh cho.';
    }

    private function findProducts($message, $text)
    {
        $pdo = Database::connect();
        $category = $this->categoryFromText($text);

        if ($category) {
            $like = '%' . $category . '%';
            $st = $pdo->prepare("SELECT id,name,category,price,stock FROM products WHERE status=1 AND category LIKE ? ORDER BY stock DESC, sort_order ASC LIMIT 3");
            $st->execute([$like]);
            $rows = $st->fetchAll();
            if ($rows) return $rows;
        }

        $keyword = trim(preg_replace('/(gia|bao nhieu|con hang|san pham|tu van|mua|shop|co|khong|nao|dep|tot|cho|minh|toi|em|anh|chi|ban|ạ|a|nhe|nhé|thì|thi|nên|nen|chọn|chon|loại|loai)/iu', ' ', $message));
        $keyword = trim(preg_replace('/\s+/', ' ', $keyword));

        if ($keyword !== '' && mb_strlen($keyword, 'UTF-8') >= 2) {
            $like = '%' . $keyword . '%';
            $st = $pdo->prepare("SELECT id,name,category,price,stock FROM products WHERE status=1 AND (name LIKE ? OR category LIKE ? OR brand LIKE ?) ORDER BY stock DESC, sort_order ASC LIMIT 3");
            $st->execute([$like, $like, $like]);
            return $st->fetchAll();
        }

        return [];
    }

    private function answerSkincare($text)
    {
        if (strpos($text, 'da dau') !== false || strpos($text, 'da nhon') !== false || strpos($text, 'mun') !== false) {
            return 'Da dầu/mụn nên ưu tiên làm sạch dịu nhẹ, dưỡng mỏng nhẹ và chống nắng không bí da. Makeup thì chọn nền kiềm dầu + phủ phấn vùng chữ T.';
        }
        if (strpos($text, 'da kho') !== false || strpos($text, 'nhay cam') !== false) {
            return 'Da khô/nhạy cảm nên chọn toner cấp ẩm, serum/kem dưỡng phục hồi và nền mỏng ẩm. Tránh sản phẩm quá nặng mùi hoặc quá lì.';
        }
        return 'Routine cơ bản: sữa rửa mặt → toner → serum/kem dưỡng → kem chống nắng. Tối thì tẩy trang kỹ trước khi rửa mặt.';
    }

    private function shortFriendlyAnswer($message, $text)
    {
        if ($this->hasAny($text, ['ok','oke','uh','um','vang','da','duoc'])) {
            return 'Oki bạn nha ❤️';
        }

        if ($this->hasAny($text, ['dep khong','tot khong','nen mua khong'])) {
            return 'Mình cần tên sản phẩm hoặc loại da của bạn để trả lời chuẩn hơn nha.';
        }

        if ($this->hasAny($text, ['khuyen mai','giam gia','sale','ma giam'])) {
            return 'Bạn xem giá đang hiển thị trên website nhé. Nếu có mã giảm giá, shop sẽ áp dụng ở bước thanh toán.';
        }

        return 'Mình chưa hiểu đúng ý bạn. Bạn nhắn rõ hơn một chút, ví dụ: “da dầu nên dùng kem nền nào” hoặc “kiểm tra đơn 0865...” nhé.';
    }

    private function orderItems($orderId)
    {
        $st = Database::connect()->prepare("SELECT product_name, quantity FROM order_items WHERE order_id=? ORDER BY id ASC LIMIT 5");
        $st->execute([$orderId]);
        $items = [];
        foreach ($st->fetchAll() as $row) {
            $items[] = $row['product_name'].' x'.(int)$row['quantity'];
        }
        return implode(', ', $items);
    }

    private function statusGuide($status)
    {
        switch ($status) {
            case 'Chờ xác nhận': return 'Shop đang tiếp nhận đơn, chưa giao đâu bạn nhé.';
            case 'Đã xác nhận': return 'Shop đã xác nhận và đang chuẩn bị hàng.';
            case 'Đang giao': return 'Đơn đang trên đường giao. Bạn chú ý điện thoại để shipper liên hệ nhé.';
            case 'Hoàn thành': return 'Đơn đã giao hoàn thành rồi nha.';
            case 'Đã hủy': return 'Đơn này đã bị hủy. Nếu muốn, bạn đặt lại trên website giúp shop nhé.';
            default: return 'Cần xử lý gấp thì bạn gọi/Zalo 0865155687 nha.';
        }
    }

    private function looksLikeOrderLookup($message, $text)
    {
        if ($this->isOrderQuestion($text)) return true;
        if ($this->extractPhone($message)) return true;
        if ($this->extractPaymentCode($message)) return true;
        if (preg_match('/#\s*\d{1,7}/', $message)) return true;
        if (preg_match('/^\s*\d{1,7}\s*$/', $message)) return true;
        return false;
    }

    private function isOrderQuestion($text)
    {
        return $this->hasAny($text, [
            'don hang','ma don','kiem tra don','trang thai don','giao chua','dang giao','ship chua',
            'da giao','van don','thanh toan don','den dau','toi dau','don cua toi','don minh','don em','don anh','don chi'
        ]);
    }

    private function isGreeting($text)
    {
        $clean = trim($text);
        return in_array($clean, ['hi','hello','helo','chao','xin chao','alo','a lo','shop oi','ban oi','em oi','tu van'], true);
    }

    private function isThanks($text)
    {
        return $this->hasAny($text, ['cam on','thank','thanks','ok cam on','oke cam on','duoc roi','cam on ban']);
    }

    private function extractPhone($message)
    {
        if (preg_match('/0\d{9}/', $message, $m)) return $m[0];
        return null;
    }

    private function extractPaymentCode($message)
    {
        if (preg_match('/GBPAY\d{11}/i', $message, $m)) return strtoupper($m[0]);
        return null;
    }

    private function extractOrderId($message, $text)
    {
        if (preg_match('/(?:don|đơn|#)\s*(?:hang|hàng|so|số|ma|mã|cua toi|của tôi|cua minh|của mình)?\s*#?\s*(\d{1,7})/iu', $message, $m)) {
            return (int)$m[1];
        }
        if (preg_match('/^\s*#?\s*(\d{1,7})\s*$/', $message, $m)) return (int)$m[1];
        return null;
    }

    private function categoryFromText($text)
    {
        $map = [
            'son' => 'Son',
            'kem nen' => 'Kem nền',
            'foundation' => 'Kem nền',
            'phan ma' => 'Phấn má',
            'blush' => 'Phấn má',
            'phan phu' => 'Phấn phủ',
            'sua rua mat' => 'Sữa rửa mặt',
            'toner' => 'Toner',
            'serum' => 'Serum',
            'mascara' => 'Mascara',
            'che khuyet diem' => 'Che khuyết điểm',
            'combo' => 'Combo',
            'skincare' => 'Skincare'
        ];
        foreach ($map as $k => $v) if (strpos($text, $k) !== false) return $v;
        return null;
    }

    private function hasAny($text, $needles)
    {
        foreach ($needles as $n) if (strpos($text, $n) !== false) return true;
        return false;
    }

    private function normalize($str)
    {
        $str = mb_strtolower($str, 'UTF-8');
        $from = ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ','è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ','ì','í','ị','ỉ','ĩ','ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ','ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ','ỳ','ý','ỵ','ỷ','ỹ','đ'];
        $to   = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','d'];
        return str_replace($from, $to, $str);
    }
}
