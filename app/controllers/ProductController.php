<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Wishlist.php';

class ProductController extends Controller  {

    public function index() {
        $q=$_GET['q']??'';
        $cat=$_GET['cat']??'';
        $savedIds = [];
        if (!empty($_SESSION['user']['id'])) {
            $savedIds = Wishlist::idsByUser($_SESSION['user']['id']);
        } elseif (!empty($_SESSION['guest_wishlist']) && is_array($_SESSION['guest_wishlist'])) {
            $savedIds = array_values(array_unique(array_map('intval', $_SESSION['guest_wishlist'])));
        }
        $this->view('products/index',[
            'products'=>Product::all($q,$cat),
            'categories'=>Product::categories(),
            'q'=>$q,
            'cat'=>$cat,
            'savedIds'=>$savedIds,
            'title'=>'Sản phẩm'
        ]);
    }

    public function detail() {
        $p=Product::find($_GET['id']??0);
        if(!$p) {
            echo 'Không tìm thấy sản phẩm';
            return;
        }
        $this->view('products/detail',['p'=>$p,'title'=>$p['name']]);
    }
}
