<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Wishlist.php';

class HomeController extends Controller  {

    public function index() {
        $savedIds = [];
        if (!empty($_SESSION['user']['id'])) {
            $savedIds = Wishlist::idsByUser((int)$_SESSION['user']['id']);
        } elseif (!empty($_SESSION['guest_wishlist']) && is_array($_SESSION['guest_wishlist'])) {
            $savedIds = array_values(array_unique(array_map('intval', $_SESSION['guest_wishlist'])));
        }
        $this->view('home/index',[
            'products'=>Product::featured(8),
            'savedIds'=>$savedIds,
            'title'=>'GlowBeauty - Mỹ phẩm & Makeup cao cấp'
        ]);
    }
}
