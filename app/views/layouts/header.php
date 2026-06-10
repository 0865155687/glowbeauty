<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script, '/public/index.php') !== false) {
        $base = str_replace('/public/index.php', '', $script);
    } elseif (strpos($script, '/index.php') !== false) {
        $base = str_replace('/index.php', '', $script);
    } else {
        $base = dirname($script);
    }
    $base = '/' . trim($base, '/') . '/';
    $base = str_replace('//', '/', $base);
    define('BASE_URL', $base);
}

if (!function_exists('gb_url')) {
    function gb_url($path = '') {
        $base = rtrim(BASE_URL, '/');
        $path = ltrim((string)$path, '/');
        return $path === '' ? $base . '/' : $base . '/' . $path;
    }
}

if (!function_exists('gb_image_url')) {
    function gb_image_url($image) {
        $image = trim((string)$image);
        if ($image === '') return gb_url('public/assets/images/glowbeauty-logo-small.png');
        if (preg_match('~^https?://~i', $image)) return $image;
        $image = ltrim($image, '/');
        if (strpos($image, 'public/') === 0) return gb_url($image);
        if (strpos($image, 'assets/') === 0) return gb_url('public/' . $image);
        return gb_url('public/assets/images/' . $image);
    }
}

$cart = $_SESSION['cart'] ?? [];
$cartCount = is_array($cart) ? array_sum(array_map('intval', $cart)) : 0;
$wishlistCount = 0;
$wishlistModelPath = __DIR__ . '/../../models/Wishlist.php';
if (!empty($_SESSION['user']['id']) && file_exists($wishlistModelPath)) {
    require_once $wishlistModelPath;
    try { $wishlistCount = Wishlist::countByUser((int)$_SESSION['user']['id']); } catch (Throwable $e) { $wishlistCount = 0; }
} else {
    $guestWishlist = $_SESSION['guest_wishlist'] ?? [];
    $wishlistCount = is_array($guestWishlist) ? count(array_unique(array_map('intval', $guestWishlist))) : 0;
}
$current = trim($_GET['url'] ?? 'home', '/');
if ($current === '') {
    $current = 'home';
}

$menus = [
    'home' => 'Trang chủ',
    'products' => 'Sản phẩm',
    'about' => 'Giới thiệu',
    'contact' => 'Liên hệ',
];
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'GlowBeauty - Mỹ phẩm chính hãng', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="GlowBeauty - website bán mỹ phẩm chính hãng, quản lý đơn hàng, tồn kho, yêu thích và chăm sóc khách hàng.">
    <meta name="keywords" content="GlowBeauty, mỹ phẩm, skincare, makeup, son môi, kem nền, chăm sóc da">

    <link rel="stylesheet" href="<?= gb_url('public/assets/css/style.css?v=20260610-final-mobile-responsive') ?>">
    <link rel="stylesheet" href="<?= gb_url('public/assets/css/cart-modern.css?v=20260610-final-mobile-responsive') ?>">
</head>

<body>

    <header class="site-header">
        <div class="container nav-wrap">

            <a class="brand" href="<?= gb_url() ?>">
                <img src="<?= gb_url('public/assets/images/glowbeauty-logo-small.png') ?>" alt="GlowBeauty">
                <span>GlowBeauty</span>
            </a>

            <nav class="main-nav">
                <?php foreach ($menus as $key => $label): ?>
                    <a href="<?= gb_url($key === 'home' ? '' : $key) ?>"
                        class="<?= $current === $key ? 'active' : '' ?>">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="nav-actions">

                <a class="cart-btn" href="<?= gb_url('cart') ?>" aria-label="Giỏ hàng">
                    <svg class="mobile-nav-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="9" cy="20" r="1.5" stroke="currentColor" stroke-width="2" />
                        <circle cx="18" cy="20" r="1.5" stroke="currentColor" stroke-width="2" />
                        <path d="M3 4H5.2L7.3 15.1C7.5 16.2 8.4 17 9.5 17H17.8C18.8 17 19.7 16.3 20 15.3L21.2 8H6.1" stroke="currentColor" stroke-width="2.35" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="cart-icon">🛍️</span>
                    <span>Giỏ hàng</span>
                    <span class="cart-count"><?= (int)$cartCount ?></span>
                </a>

                <a class="outline-btn wish-top-btn" href="<?= gb_url('account/wishlist') ?>" title="Danh sách đã lưu" aria-label="Yêu thích">
                    <svg class="mobile-nav-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M20.8 4.9C18.9 3 15.9 3 14 4.9L12 6.9L10 4.9C8.1 3 5.1 3 3.2 4.9C1.3 6.8 1.3 9.9 3.2 11.8L12 20.4L20.8 11.8C22.7 9.9 22.7 6.8 20.8 4.9Z" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="wish-top-icon">♡</span><span>Đã lưu</span><span class="wish-count-badge wishlist-count"><?= (int)$wishlistCount ?></span>
                </a>

                <?php if (!empty($_SESSION['user'])): ?>
                    <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                        <a class="outline-btn admin-top-btn" href="<?= gb_url('admin') ?>" aria-label="Tài khoản">
                            <svg class="mobile-nav-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="7.5" r="4" stroke="currentColor" stroke-width="2.25" />
                                <path d="M4.5 21C5.3 16.8 8.2 14.5 12 14.5C15.8 14.5 18.7 16.8 19.5 21" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" />
                            </svg>
                            <span class="admin-top-icon">👤</span>
                            <span>Admin</span>
                        </a>
                    <?php endif; ?>

                    <a class="outline-btn logout-top-btn" href="<?= gb_url('logout') ?>">
                        <svg class="mobile-nav-svg" width="21" height="21" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M10 17L15 12L10 7" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M15 12H3" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" />
                            <path d="M7 3H18C19.1 3 20 3.9 20 5V19C20 20.1 19.1 21 18 21H7" stroke="currentColor"
                                stroke-width="2.6" stroke-linecap="round" />
                        </svg>
                        <span>Đăng xuất</span>
                    </a>

                <?php else: ?>

                    <a class="outline-btn login-top-btn" href="<?= gb_url('login') ?>" title="Đăng nhập" aria-label="Đăng nhập">
                        <svg class="mobile-nav-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="12" cy="7.5" r="4" stroke="currentColor" stroke-width="2.25" />
                            <path d="M4.5 21C5.3 16.8 8.2 14.5 12 14.5C15.8 14.5 18.7 16.8 19.5 21" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" />
                        </svg>
                        <span class="login-top-icon" aria-hidden="true">👤</span>
                        <span class="login-top-text">Đăng nhập</span>
                    </a>

                <?php endif; ?>

            </div>
        </div>
    </header>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="toast">
            <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8');
            unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <main>
