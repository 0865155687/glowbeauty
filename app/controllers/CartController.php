<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/InvoiceMailer.php';

class CartController extends Controller
{

    private function addressHasProvinceOrCity($address)
    {
        $addr = mb_strtolower(trim((string)$address), 'UTF-8');
        if ($addr === '') return false;
        $keywords = [
            'hà nội','ha noi','hải phòng','hai phong','đà nẵng','da nang','hồ chí minh','ho chi minh','sài gòn','sai gon','cần thơ','can tho',
            'an giang','bà rịa','ba ria','vũng tàu','vung tau','bắc giang','bac giang','bắc kạn','bac kan','bạc liêu','bac lieu','bắc ninh','bac ninh','bến tre','ben tre','bình định','binh dinh','bình dương','binh duong','bình phước','binh phuoc','bình thuận','binh thuan','cà mau','ca mau','cao bằng','cao bang','đắk lắk','dak lak','đắk nông','dak nong','điện biên','dien bien','đồng nai','dong nai','đồng tháp','dong thap','gia lai','hà giang','ha giang','hà nam','ha nam','hà tĩnh','ha tinh','hải dương','hai duong','hậu giang','hau giang','hòa bình','hoa binh','hưng yên','hung yen','khánh hòa','khanh hoa','kiên giang','kien giang','kon tum','lai châu','lai chau','lâm đồng','lam dong','lạng sơn','lang son','lào cai','lao cai','long an','nam định','nam dinh','nghệ an','nghe an','ninh bình','ninh binh','ninh thuận','ninh thuan','phú thọ','phu tho','phú yên','phu yen','quảng bình','quang binh','quảng nam','quang nam','quảng ngãi','quang ngai','quảng ninh','quang ninh','quảng trị','quang tri','sóc trăng','soc trang','sơn la','son la','tây ninh','tay ninh','thái bình','thai binh','thái nguyên','thai nguyen','thanh hóa','thanh hoa','thừa thiên huế','thua thien hue','huế','hue','tiền giang','tien giang','trà vinh','tra vinh','tuyên quang','tuyen quang','vĩnh long','vinh long','vĩnh phúc','vinh phuc','yên bái','yen bai'
        ];
        foreach ($keywords as $kw) {
            if (strpos($addr, $kw) !== false) return true;
        }
        return (strpos($addr, 'tỉnh ') !== false || strpos($addr, 'thành phố ') !== false || strpos($addr, 'tp ') !== false || strpos($addr, 'tp.') !== false);
    }

    private function currentCheckoutCart()
    {
        if (isset($_SESSION['direct_checkout']) && is_array($_SESSION['direct_checkout'])) {
            return $_SESSION['direct_checkout'];
        }
        return $_SESSION['cart'] ?? [];
    }

    private function clearCheckoutCartAfterOrder()
    {
        if (isset($_SESSION['direct_checkout'])) {
            unset($_SESSION['direct_checkout']);
            return;
        }
        $_SESSION['cart'] = [];
    }


    public function index()
    {
        $items = [];
        $total = 0;
        foreach (($_SESSION['cart'] ?? []) as $id => $qty) {
            $p = Product::find($id);
            if ($p) {
                $p['qty'] = $qty;
                $p['line'] = $qty * $p['price'];
                $total += $p['line'];
                $items[] = $p;
            }
        }
        $this->view('cart/index', ['items' => $items, 'total' => $total, 'title' => 'Giỏ hàng']);
    }

    public function add()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $qty = (int)($_GET['qty'] ?? 1);
        $p = Product::find($id);
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $ok = false;
        $message = 'Sản phẩm không hợp lệ.';
        if ($qty < 1) {
            $message = 'Vui lòng nhập số lượng từ 1 sản phẩm trở lên.';
        } elseif ($p && (int)$p['stock'] > 0) {
            $stock = (int)$p['stock'];
            $current = (int)($_SESSION['cart'][$id] ?? 0);
            if ($qty > $stock || ($current + $qty) > $stock) {
                $message = 'Kho chỉ còn ' . $stock . ' sản phẩm. Bạn đang chọn quá số lượng tồn kho.';
            } else {
                $_SESSION['cart'][$id] = $current + $qty;
                $ok = true;
                $message = 'Đã thêm vào giỏ hàng.';
            }
        } elseif ($p && (int)$p['stock'] <= 0) {
            $message = 'Sản phẩm đã hết hàng, không thể thêm vào giỏ.';
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => $ok,
                'message' => $message,
                'cart_count' => array_sum(array_map('intval', $_SESSION['cart'] ?? [])),
                'item_qty' => (int)($_SESSION['cart'][$id] ?? 0)
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $_SESSION['success'] = $message;
        $this->redirect($ok ? 'cart' : 'products');
    }


    public function addCheckout()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $qty = (int)($_GET['qty'] ?? 1);
        $p = Product::find($id);
        $back = (($_GET['from'] ?? '') === 'wishlist') ? 'account/wishlist' : 'product?id=' . $id;

        if ($qty < 1) {
            $_SESSION['success'] = 'Vui lòng nhập số lượng từ 1 sản phẩm trở lên.';
            $this->redirect($back);
        }

        if (!$p || (int)($p['status'] ?? 1) !== 1) {
            $_SESSION['success'] = 'Sản phẩm không hợp lệ hoặc đã ngừng bán.';
            $this->redirect($back);
        }

        $stock = (int)($p['stock'] ?? 0);
        if ($stock <= 0) {
            $_SESSION['success'] = 'Sản phẩm đã hết hàng nên không thể thanh toán.';
            $this->redirect($back);
        }

        if ($qty > $stock) {
            $_SESSION['success'] = 'Sản phẩm không đủ số lượng bạn cần. Kho hiện chỉ còn ' . $stock . ' sản phẩm, vui lòng giảm số lượng trước khi thanh toán.';
            $this->redirect($back);
        }

        // Mua ngay / thanh toán từ Đã lưu phải tách khỏi giỏ hàng hiện có.
        // Nếu không tách, checkout sẽ kéo thêm các sản phẩm cũ trong giỏ và gây sai đơn.
        $_SESSION['direct_checkout'] = [$id => $qty];
        $this->redirect('checkout');
    }

    public function remove()
    {
        unset($_SESSION['cart'][(int) ($_GET['id'] ?? 0)]);
        $this->redirect('cart');
    }
    public function update()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $action = $_GET['action'] ?? '';

        $p = Product::find($id);

        if ($p && isset($_SESSION['cart'][$id])) {
            if ($action === 'plus') {
                $_SESSION['cart'][$id] = min($_SESSION['cart'][$id] + 1, $p['stock']);
            }

            if ($action === 'minus') {
                $_SESSION['cart'][$id] = max($_SESSION['cart'][$id] - 1, 1);
            }
        }

        $this->redirect('cart');
    }

    public function checkout()
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['success'] = 'Vui lòng đăng nhập hoặc đăng ký tài khoản trước khi đặt hàng.';
            $_SESSION['redirect_after_login'] = 'checkout';
            $this->redirect('login');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $checkoutCart = $this->currentCheckoutCart();
                foreach ($checkoutCart as $cartProductId => $cartQty) {
                    $cartProduct = Product::find((int)$cartProductId);
                    $stock = (int)($cartProduct['stock'] ?? 0);
                    if ((int)$cartQty < 1) {
                        throw new Exception('Vui lòng nhập số lượng từ 1 sản phẩm trở lên.');
                    }
                    if (!$cartProduct || $stock <= 0 || (int)$cartQty > $stock) {
                        $name = $cartProduct['name'] ?? 'Sản phẩm';
                        if ($stock <= 0) {
                            throw new Exception($name . ' đã hết hàng nên không thể đặt hàng.');
                        }
                        throw new Exception($name . ' không đủ số lượng bạn cần. Kho hiện chỉ còn ' . $stock . ' sản phẩm, vui lòng giảm số lượng trước khi đặt.');
                    }
                }
                $address = trim((string)($_POST['address'] ?? ''));
                if (!$this->addressHasProvinceOrCity($address)) {
                    throw new Exception('Vui lòng nhập đầy đủ địa chỉ có tỉnh/thành phố để shop tính phí vận chuyển chính xác. Ví dụ: Số 3, phường Tứ Minh, thành phố Hải Phòng.');
                }
                $deliveryNote = trim((string)($_POST['note'] ?? ''));
                $allowedDeliveryNotes = ['Giao buổi sáng', 'Giao buổi chiều', 'Giao buổi tối'];
                if (!in_array($deliveryNote, $allowedDeliveryNotes, true)) {
                    throw new Exception('Vui lòng chọn khung giờ giao hàng: giao buổi sáng, giao buổi chiều hoặc giao buổi tối.');
                }
                $customerEmail = trim((string)($_POST['customer_email'] ?? ''));
                if ($customerEmail === '' && !empty($_SESSION['user']['email'])) {
                    $customerEmail = trim((string)$_SESSION['user']['email']);
                }
                if ($customerEmail === '' || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Vui lòng nhập email hợp lệ để nhận hóa đơn sau khi thanh toán.');
                }

                $orderId = Order::create($_POST['customer_name'], $_POST['phone'], $address, $deliveryNote, $checkoutCart, $customerEmail);

                // Lưu chắc chắn đơn vào tài khoản hiện tại và lưu khung giờ giao hàng vào hóa đơn.
                Order::rememberOrderForCurrentSession($orderId);
                Order::forceAttachToCurrentUser($orderId);
                Order::forceUpdateNote($orderId, $deliveryNote);
                $_SESSION['order_delivery_notes'][(int)$orderId] = $deliveryNote;

                try {
                    $newOrder = Order::find($orderId);
                    $newItems = Order::items($orderId);
                    InvoiceMailer::sendAdminNewOrder($newOrder, $newItems);
                } catch (Exception $mailNoticeError) {}
                $this->clearCheckoutCartAfterOrder();
                $this->redirect('payment?id=' . $orderId);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        $items = [];
        $total = 0;
        $checkoutCart = $this->currentCheckoutCart();
        foreach ($checkoutCart as $id => $qty) {
            $product = Product::find($id);
            if ($product) {
                $qty = (int)$qty;
                $stock = (int)($product['stock'] ?? 0);
                if ($stock <= 0 || $qty > $stock) {
                    $product['cart_warning'] = $stock <= 0
                        ? 'Sản phẩm đã hết hàng.'
                        : 'Kho chỉ còn ' . $stock . ' sản phẩm, vui lòng giảm số lượng.';
                }
                $product['qty'] = $qty;
                $product['line'] = $qty * (float)$product['price'];
                $total += $product['line'];
                $items[] = $product;
            }
        }
        $this->view('cart/checkout', ['error' => $error ?? null, 'title' => 'Thanh toán', 'items' => $items, 'total' => $total]);
    }

    public function payment()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $order = Order::find($id);
        if (!$order) {
            $this->redirect('products');
        }

        if (empty($order['note']) && !empty($_SESSION['order_delivery_notes'][$id])) {
            $order['note'] = $_SESSION['order_delivery_notes'][$id];
        }
        Order::rememberOrderForCurrentSession($id);
        Order::forceAttachToCurrentUser($id);
        $items = Order::items($id);
        $this->view('cart/payment', [
            'order' => $order,
            'items' => $items,
            'title' => 'Quét thanh toán'
        ]);
    }

    public function paymentSuccess()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $order = Order::find($id);
        if (!$order) {
            $this->redirect('products');
        }

        Order::markPaid($id);
        Order::rememberOrderForCurrentSession($id);
        Order::forceAttachToCurrentUser($id);
        $order = Order::find($id);
        if (empty($order['note']) && !empty($_SESSION['order_delivery_notes'][$id])) {
            $order['note'] = $_SESSION['order_delivery_notes'][$id];
        }
        $items = Order::items($id);

        $customerEmail = trim((string)($order['customer_email'] ?? ''));
        if (empty($customerEmail)) {
            $customerEmail = $_SESSION['user']['email'] ?? '';
        }
        if (empty($customerEmail) && !empty($order['user_id'])) {
            $u = User::find((int)$order['user_id']);
            $customerEmail = $u['email'] ?? '';
        }
        // Gửi lại email admin khi khách xác nhận đã thanh toán để đảm bảo admin vẫn nhận được nếu lần tạo đơn bị host chặn tạm thời.
        try { InvoiceMailer::sendAdminNewOrder($order, $items); } catch (Exception $e) {}

        $emailStatus = InvoiceMailer::sendPaidInvoice($order, $items, $customerEmail);

        $this->view('cart/payment_success', [
            'order' => $order,
            'items' => $items,
            'emailStatus' => $emailStatus,
            'title' => 'Thanh toán thành công'
        ]);
    }
    public function removeMultiple()
    {

        $data =
            json_decode(
                file_get_contents(
                    "php://input"
                ),
                true
            );

        foreach (
            $data['ids']
            as $id
        ) {

            unset(
                $_SESSION['cart'][$id]
            );

        }

        echo json_encode(
            [
                'success' => true
            ]
        );

        exit;

    }

}
