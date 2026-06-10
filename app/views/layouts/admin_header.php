<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin GlowBeauty</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css?v=20260610-final-mobile-responsive">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/admin-modern.css?v=20260610-final-mobile-responsive">
</head>
<body class="admin-body admin-body-v36 gb-admin-body">
<?php
  $currentAdminUrl = trim($_GET['url'] ?? 'admin/dashboard', '/');
  function gbAdminActive($keys, $currentAdminUrl) {
    foreach ((array)$keys as $key) {
      if ($currentAdminUrl === $key || ($key !== 'admin' && strpos($currentAdminUrl, $key . '/') === 0)) {
        return ' active';
      }
    }
    return '';
  }
?>
<aside class="admin-sidebar admin-sidebar-v36 gb-admin-sidebar">
  <div class="admin-logo admin-logo-v36 gb-admin-brand">
    <img src="<?= BASE_URL ?>public/assets/images/glowbeauty-logo-small.png" alt="Logo">
    <div><strong>GlowBeauty</strong><span>Admin Panel</span></div>
  </div>
  <div class="gb-admin-mini-title">Quản trị hệ thống</div>
  <nav class="admin-nav-v36 gb-admin-nav">
    <a class="<?= gbAdminActive(['admin','admin/dashboard'], $currentAdminUrl) ?>" href="<?= BASE_URL ?>admin/dashboard"><span class="nav-ico">📊</span><span>Dashboard</span></a>
    <a class="<?= gbAdminActive(['admin/products','admin/product-form','admin/product-delete','admin/low-stock'], $currentAdminUrl) ?>" href="<?= BASE_URL ?>admin/products"><span class="nav-ico">💄</span><span>Sản phẩm</span></a>
    <a class="<?= gbAdminActive(['admin/orders','admin/order-detail','admin/order-update','admin/order-create'], $currentAdminUrl) ?>" href="<?= BASE_URL ?>admin/orders"><span class="nav-ico">🧾</span><span>Đơn hàng</span></a>
    <a class="<?= gbAdminActive(['admin/contacts','admin/contact-update'], $currentAdminUrl) ?>" href="<?= BASE_URL ?>admin/contacts"><span class="nav-ico">💬</span><span>Tư vấn</span></a>
    <a class="<?= gbAdminActive(['admin/customers','admin/customer-form'], $currentAdminUrl) ?>" href="<?= BASE_URL ?>admin/customers"><span class="nav-ico">👥</span><span>Khách hàng</span></a>
    <a href="<?= BASE_URL ?>home"><span class="nav-ico">🏠</span><span>Xem website</span></a>
    <a class="gb-admin-logout" href="<?= BASE_URL ?>logout" onclick="return confirm('Bạn muốn đăng xuất khỏi trang quản trị?')"><span class="nav-ico">🚪</span><span>Đăng xuất</span></a>
  </nav>
</aside>
<main class="admin-main admin-main-v36 gb-admin-main">
