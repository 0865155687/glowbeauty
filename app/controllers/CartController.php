<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';

class CartController extends Controller
{

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
        $p = Product::find($id);
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if ($p && $p['stock'] > 0) {
            // Mỗi lần bấm chỉ cộng đúng 1 sản phẩm, không bị cộng đôi.
            $_SESSION['cart'][$id] = min((int)($_SESSION['cart'][$id] ?? 0) + 1, (int)$p['stock']);
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'cart_count' => array_sum(array_map('intval', $_SESSION['cart'] ?? [])),
                'item_qty' => (int)($_SESSION['cart'][$id] ?? 0)
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $this->redirect('cart');
    }


    public function addCheckout()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $p = Product::find($id);
        if ($p && $p['stock'] > 0) {
            if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            $_SESSION['cart'][$id] = min(max(($_SESSION['cart'][$id] ?? 0), 1), $p['stock']);
        }
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
                $orderId = Order::create($_POST['customer_name'], $_POST['phone'], $_POST['address'], $_POST['note'] ?? '', $_SESSION['cart'] ?? []);
                $_SESSION['cart'] = [];
                $this->redirect('payment?id=' . $orderId);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        $items = [];
        $total = 0;
        foreach (($_SESSION['cart'] ?? []) as $id => $qty) {
            $product = Product::find($id);
            if ($product) {
                $product['qty'] = (int)$qty;
                $product['line'] = (int)$qty * (float)$product['price'];
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
        $order = Order::find($id);
        $items = Order::items($id);

        $this->view('cart/payment_success', [
            'order' => $order,
            'items' => $items,
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
