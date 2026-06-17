<?php
require_once __DIR__ . '/../models/Wishlist.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Review.php';

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
        $updatedOrders = [];
        foreach ($orders as $o) {
            if ((int)($o['status_seen'] ?? 1) === 0) {
                $updatedOrders[] = $o;
            }
        }
        // Không đánh dấu đã xem ở controller vì header cần hiện badge trước.
        // Sẽ đánh dấu đã xem ở cuối view sau khi người dùng mở trang Đơn hàng của tôi.
        $orderIds = array_map(function($o){ return (int)$o['id']; }, $orders);
        $itemsByOrder = Order::itemsByOrderIds($orderIds);
        $reviewsByOrder = [];
        foreach ($orderIds as $oid) { $reviewsByOrder[$oid] = Review::byOrder($oid); }
        $this->view('account/orders', [
            'orders'=>$orders,
            'updatedOrders'=>$updatedOrders,
            'itemsByOrder'=>$itemsByOrder,
            'reviewsByOrder'=>$reviewsByOrder,
            'title'=>'Đơn hàng của tôi - GlowBeauty'
        ]);
    }


    public function review() {
        $this->guard();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('account/orders');
        $orderId = (int)($_POST['order_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $order = Order::find($orderId);
        if (!$order || (int)($order['user_id'] ?? 0) !== (int)$_SESSION['user']['id']) {
            $_SESSION['success'] = 'Không tìm thấy đơn hàng của bạn.';
            $this->redirect('account/orders');
        }
        if (($order['status'] ?? '') !== 'Hoàn thành') {
            $_SESSION['success'] = 'Chỉ có thể đánh giá khi đơn hàng đã hoàn thành.';
            $this->redirect('account/orders');
        }
        try {
            $imageName = trim((string)($_POST['old_image'] ?? ''));
            if (!empty($_FILES['review_image']['name']) && is_uploaded_file($_FILES['review_image']['tmp_name'])) {
                $ext = strtolower(pathinfo($_FILES['review_image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) {
                    $dir = __DIR__ . '/../../public/uploads/reviews';
                    if (!is_dir($dir)) @mkdir($dir, 0777, true);
                    $imageName = 'review_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                    @move_uploaded_file($_FILES['review_image']['tmp_name'], $dir . '/' . $imageName);
                }
            }
            Review::create([
                'order_id'=>$orderId,
                'product_id'=>$productId,
                'user_id'=>(int)$_SESSION['user']['id'],
                'rating'=>(int)($_POST['rating'] ?? 5),
                'seller_service'=>(int)($_POST['seller_service'] ?? 5),
                'shipping_service'=>(int)($_POST['shipping_service'] ?? 5),
                'package_service'=>(int)($_POST['package_service'] ?? 5),
                'comment'=>$_POST['comment'] ?? '',
                'image'=>$imageName
            ]);
            $_SESSION['success'] = 'Đánh giá thành công. Cảm ơn bạn đã phản hồi cho GlowBeauty.';
            $_SESSION['review_success'] = 'Đánh giá thành công. Đánh giá của bạn đã được cập nhật lên sản phẩm và trang admin.';
        } catch(Exception $e) {
            $_SESSION['success'] = $e->getMessage();
        }
        $this->redirect('account/orders?reviewed=1#review-success');
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
        $product = Product::find($productId);
        if ($productId <= 0 || !$product) {
            if ($this->isAjax()) $this->json(['ok'=>false, 'message'=>'Sản phẩm không hợp lệ.']);
            $_SESSION['success'] = 'Sản phẩm không hợp lệ.';
            $this->redirect('products');
        }
        if ((int)($product['stock'] ?? 0) <= 0 || (int)($product['status'] ?? 1) !== 1) {
            if ($this->isAjax()) $this->json(['ok'=>false, 'message'=>'Sản phẩm đã hết hàng, không thể lưu yêu thích.']);
            $_SESSION['success'] = 'Sản phẩm đã hết hàng, không thể lưu yêu thích.';
            $back = $_SERVER['HTTP_REFERER'] ?? BASE_URL.'products';
            header('Location: '.$back); exit;
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
        $checkout = [];
        foreach ($items as $p) {
            $id = (int)($p['id'] ?? 0);
            $stock = (int)($p['stock'] ?? 0);
            if ($id > 0 && $stock > 0) {
                $checkout[$id] = 1;
            }
        }
        if (empty($checkout)) {
            $_SESSION['success'] = 'Các sản phẩm đã lưu hiện đã hết hàng nên không thể thanh toán.';
            $this->redirect('account/wishlist');
        }
        $_SESSION['direct_checkout'] = $checkout;
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
