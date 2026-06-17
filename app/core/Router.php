<?php

class Router
{

    public function dispatch()
    {
        $page = $_GET['url'] ?? 'home';
        $page = trim($page, '/');

        $routes = [

            'home' => ['HomeController', 'index'],
            '' => ['HomeController', 'index'],

            'products' => ['ProductController', 'index'],
            'product' => ['ProductController', 'detail'],

            'cart' => ['CartController', 'index'],
            'cart/add' => ['CartController', 'add'],
            'cart/add-checkout' => ['CartController', 'addCheckout'],
            'cart/remove' => ['CartController', 'remove'],
            'cart/remove-multiple' => ['CartController', 'removeMultiple'],
            'cart/update' => ['CartController', 'update'],

            'checkout' => ['CartController', 'checkout'],
            'payment' => ['CartController', 'payment'],
            'payment-success' => ['CartController', 'paymentSuccess'],

            'login' => ['AuthController', 'login'],
            'register' => ['AuthController', 'register'],
            'logout' => ['AuthController', 'logout'],

            'about' => ['PageController', 'about'],
            'contact' => ['PageController', 'contact'],
            'contact-thank-you' => ['PageController', 'contactThankYou'],

            'ai-chat/message' => ['AiChatController', 'message'],

            'account/wishlist' => ['AccountController', 'wishlist'],
            'account/orders' => ['AccountController', 'orders'],
            'account/review' => ['AccountController', 'review'],
            'wishlist/add' => ['AccountController', 'addWishlist'],
            'wishlist/remove' => ['AccountController', 'removeWishlist'],
            'wishlist/checkout' => ['AccountController', 'checkoutWishlist'],

            'admin' => ['AdminController', 'dashboard'],
            'admin/dashboard' => ['AdminController', 'dashboard'],
            'admin/notification-status' => ['AdminController', 'notificationStatus'],
            'admin/products' => ['AdminController', 'products'],
            'admin/product-form' => ['AdminController', 'productForm'],
            'admin/product-delete' => ['AdminController', 'productDelete'],

            'admin/orders' => ['AdminController', 'orders'],
            'admin/order-detail' => ['AdminController', 'orderDetail'],
            'admin/order-update' => ['AdminController', 'orderUpdate'],
            'admin/order-create' => ['AdminController', 'orderCreate'],
            'admin/order-delete' => ['AdminController', 'orderDelete'],

            'admin/revenue-today' => ['AdminController', 'revenueToday'],
            'admin/revenue-month' => ['AdminController', 'revenueMonth'],
            'admin/revenue-all' => ['AdminController', 'revenueAll'],
            'admin/revenue-export-excel' => ['AdminController', 'revenueExportExcel'],

            'admin/low-stock' => ['AdminController', 'lowStock'],
            'admin/best-sellers' => ['AdminController', 'bestSellers'],
            'admin/contacts' => ['AdminController', 'contacts'],
            'admin/contact-update' => ['AdminController', 'contactUpdate'],
            'admin/contact-delete' => ['AdminController', 'contactDelete'],

            'admin/purchase-history' => ['AdminController', 'purchaseHistory'],
            'admin/reviews' => ['AdminController', 'reviews'],
            'admin/review-status' => ['AdminController', 'reviewStatus'],
            'admin/review-delete' => ['AdminController', 'reviewDelete'],
            'admin/chats' => ['AdminController', 'chats'],
            'admin/chat-reply' => ['AdminController', 'chatReply'],
            'admin/chat-delete' => ['AdminController', 'chatDelete'],

            'admin/customers' => ['AdminController', 'customers'],
            'admin/customer-form' => ['AdminController', 'customerForm'],
            'admin/customer-delete' => ['AdminController', 'customerDelete']
        ];

        if (!isset($routes[$page])) {

            http_response_code(404);
            echo 'Không tìm thấy trang';
            return;

        }

        [$controller, $method] = $routes[$page];

        require_once __DIR__
            . '/../controllers/'
            . $controller
            . '.php';

        (new $controller())->$method();
    }
}