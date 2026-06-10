<?php
require_once __DIR__ . '/../models/Wishlist.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';

class AccountController extends Controller {
    private function guard() {
        if (empty($_SESSION['user'])) {
            $_SESSION['success']='Vui lòng đăng nhập để tiếp tục thanh toán.';
            $_SESSION['redirect_after_login'] = $_GET['url'] ?? 'checkout';
            $this->redirect('login');
        }
    }

    private function guestWishlistIds() {
        if (!isset($_SESSION['guest_wishlist']) || !is_array($_SESSION['guest_wishlist'])) {
            $_SESSION['guest_wishlist'] = [];
        }
        return array_values(array_unique(array_map('intval', $_SESSION['guest_wishlist'])));
    }

    private function setGuestWishlistIds($ids) {
        $_SESSION['guest_wishlist'] = array_values(array_unique(array_filter(array_map('intval', (array)$ids))));
    }

    private function wishlistCount() {
        if (!empty($_SESSION['user']['id'])) {
            return Wishlist::countByUser((int)$_SESSION['user']['id']);
        }
        return count($this->guestWishlistIds());
    }

    public function wishlist() {
        if (!empty($_SESSION['user']['id'])) {
            $items = Wishlist::allByUser((int)$_SESSION['user']['id']);
        } else {
            $items = Product::findMany($this->guestWishlistIds());
        }
        $this->view('account/wishlist', ['items'=>$items, 'title'=>'Sản phẩm đã lưu - GlowBeauty']);
    }

    public function orders() {
        $this->guard();
        $orders = Order::byUser($_SESSION['user']['id']);
        $this->view('account/orders', ['orders'=>$orders, 'title'=>'Lịch sử mua hàng - GlowBeauty']);
    }

    private function isAjax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_GET['ajax']) && $_GET['ajax'] == '1');
    }

    private function json($data) {
        while (ob_get_level() > 0) { @ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function addWishlist() {
        $productId = (int)($_GET['id'] ?? 0);
        if ($productId <= 0 || !Product::find($productId)) {
            if ($this->isAjax()) $this->json(['ok'=>false, 'message'=>'Sản phẩm không hợp lệ.']);
            $this->redirect('products');
        }

        if (!empty($_SESSION['user']['id'])) {
            $userId = (int)$_SESSION['user']['id'];
            $alreadySaved = Wishlist::exists($userId, $productId);
            if ($alreadySaved) {
                Wishlist::remove($userId, $productId);
                $count = Wishlist::countByUser($userId);
                $payload = ['ok'=>true,'saved'=>false,'count'=>$count,'message'=>'Đã bỏ sản phẩm khỏi mục yêu thích của bạn'];
                if ($this->isAjax()) $this->json($payload);
                $_SESSION['success'] = $payload['message'];
            } else {
                Wishlist::add($userId, $productId);
                $count = Wishlist::countByUser($userId);
                $payload = ['ok'=>true,'saved'=>true,'count'=>$count,'message'=>'Đã thêm vào mục yêu thích của bạn'];
                if ($this->isAjax()) $this->json($payload);
                $_SESSION['success'] = $payload['message'];
            }
        } else {
            $ids = $this->guestWishlistIds();
            $alreadySaved = in_array($productId, $ids, true);
            if ($alreadySaved) {
                $ids = array_values(array_diff($ids, [$productId]));
                $this->setGuestWishlistIds($ids);
                $payload = ['ok'=>true,'saved'=>false,'count'=>count($ids),'message'=>'Đã bỏ sản phẩm khỏi mục yêu thích của bạn'];
                if ($this->isAjax()) $this->json($payload);
                $_SESSION['success'] = $payload['message'];
            } else {
                $ids[] = $productId;
                $this->setGuestWishlistIds($ids);
                $payload = ['ok'=>true,'saved'=>true,'count'=>count($ids),'message'=>'Đã thêm vào mục yêu thích của bạn'];
                if ($this->isAjax()) $this->json($payload);
                $_SESSION['success'] = $payload['message'];
            }
        }

        $back = $_SERVER['HTTP_REFERER'] ?? BASE_URL.'products';
        header('Location: '.$back); exit;
    }

    public function checkoutWishlist() {
        $this->guard();
        $items = Wishlist::allByUser($_SESSION['user']['id']);
        if (empty($items)) {
            $_SESSION['success'] = 'Danh sách yêu thích của bạn chưa có sản phẩm.';
            $this->redirect('account/wishlist');
        }
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        foreach ($items as $p) {
            $id = (int)($p['id'] ?? 0);
            $stock = (int)($p['stock'] ?? 0);
            if ($id > 0 && $stock > 0) {
                $_SESSION['cart'][$id] = min(max(($_SESSION['cart'][$id] ?? 0), 1), $stock);
            }
        }
        $this->redirect('checkout');
    }

    public function removeWishlist() {
        $productId = (int)($_GET['id'] ?? 0);
        if (!empty($_SESSION['user']['id'])) {
            Wishlist::remove((int)$_SESSION['user']['id'], $productId);
            $count = Wishlist::countByUser((int)$_SESSION['user']['id']);
        } else {
            $ids = array_values(array_diff($this->guestWishlistIds(), [$productId]));
            $this->setGuestWishlistIds($ids);
            $count = count($ids);
        }
        if ($this->isAjax()) {
            $this->json(['ok'=>true,'removed'=>true,'count'=>$count,'message'=>'Đã xóa sản phẩm khỏi danh sách.']);
        }
        $_SESSION['success']='Đã xóa sản phẩm khỏi danh sách.';
        $this->redirect('account/wishlist');
    }
}
