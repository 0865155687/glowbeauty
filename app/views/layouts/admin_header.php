<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin GlowBeauty</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css?v=20260613-admin-scale90-statusfix-reviewdelete">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/admin-modern.css?v=orders-compact-100-20260613-3">

<style>.gb-admin-nav a{position:relative;padding-right:52px!important}.gb-chat-nav-badge{position:absolute;right:14px;top:50%;transform:translateY(-50%);z-index:5;min-width:22px;height:22px;border-radius:999px;background:#ef3f68;color:#fff;display:inline-grid;place-items:center;font-size:12px;font-weight:800;box-shadow:0 8px 18px rgba(239,63,104,.25);padding:0 6px}</style>
</head>
<body class="admin-body admin-body-v36 gb-admin-body">
<?php
  require_once __DIR__ . '/../../models/ChatMessage.php';
  require_once __DIR__ . '/../../models/Order.php';
  require_once __DIR__ . '/../../models/User.php';
  require_once __DIR__ . '/../../models/ContactRequest.php';
  require_once __DIR__ . '/../../models/Review.php';
  $gbUnreadChats = ChatMessage::unreadTotal();
  $gbNewOrders = Order::countAdminUnseen();
  $gbNewCustomers = User::countAdminUnseen();
  $gbNewContacts = ContactRequest::countAfterId($_SESSION['seen_contact_id'] ?? 0);
  $gbNewReviews = Review::countAdminUnseen();
  $currentAdminUrl = trim($_GET['url'] ?? 'admin/dashboard', '/');
  function gbAdminActive($keys, $currentAdminUrl) {
    foreach ((array)$keys as $key) {
      if ($currentAdminUrl === $key || ($key !== 'admin' && strpos($currentAdminUrl, $key . '/') === 0)) {
        return ' active';
      }
    }
    return '';
  }
  function gbAdminBadge($n) {
    $n = (int)$n;
    return $n > 0 ? '<b class="gb-chat-nav-badge">'.$n.'</b>' : '';
  }
?>

<?php
if (!function_exists('gb_url')) {
  function gb_url($path = '') {
    $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
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
    if (strpos($image, 'public/') === 0) {
      $rel = $image;
    } elseif (strpos($image, 'assets/') === 0) {
      $rel = 'public/' . $image;
    } else {
      $rel = 'public/assets/images/' . $image;
    }

    $docRoot = dirname(__DIR__, 3);
    $abs = $docRoot . '/' . $rel;
    if (is_file($abs)) return gb_url($rel);

    $dir = dirname($abs);
    $base = pathinfo($abs, PATHINFO_FILENAME);
    foreach (['.jpg','.jpeg','.png','.webp','.jfif','.JPG','.JPEG','.PNG','.WEBP','.JFIF'] as $ext) {
      $try = $dir . '/' . $base . $ext;
      if (is_file($try)) {
        $tryRel = trim(str_replace('\\', '/', substr($try, strlen($docRoot))), '/');
        return gb_url($tryRel);
      }
    }
    return gb_url($rel);
  }
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
    <a class="<?= gbAdminActive(['admin/orders','admin/order-detail','admin/order-update','admin/order-create'], $currentAdminUrl) ?>" href="<?= BASE_URL ?>admin/orders"><span class="nav-ico">🧾</span><span>Đơn hàng</span><?= gbAdminBadge($gbNewOrders) ?></a>
    <a class="<?= gbAdminActive(['admin/purchase-history'], $currentAdminUrl) ?>" href="<?= BASE_URL ?>admin/purchase-history"><span class="nav-ico">🛍️</span><span>Lịch sử mua hàng</span></a>
    <a class="<?= gbAdminActive(['admin/reviews'], $currentAdminUrl) ?>" href="<?= BASE_URL ?>admin/reviews"><span class="nav-ico">⭐</span><span>Đánh giá khách hàng</span><?= gbAdminBadge($gbNewReviews) ?></a>
    <a class="<?= gbAdminActive(['admin/chats'], $currentAdminUrl) ?>" href="<?= BASE_URL ?>admin/chats"><span class="nav-ico">💬</span><span>Chat khách hàng</span><?= gbAdminBadge($gbUnreadChats) ?></a>
    <a class="<?= gbAdminActive(['admin/contacts','admin/contact-update'], $currentAdminUrl) ?>" href="<?= BASE_URL ?>admin/contacts"><span class="nav-ico">☎️</span><span>Tư vấn</span><?= gbAdminBadge($gbNewContacts) ?></a>
    <a class="<?= gbAdminActive(['admin/customers','admin/customer-form'], $currentAdminUrl) ?>" href="<?= BASE_URL ?>admin/customers"><span class="nav-ico">👥</span><span>Khách hàng</span><?= gbAdminBadge($gbNewCustomers) ?></a>
    <a href="<?= BASE_URL ?>home"><span class="nav-ico">🏠</span><span>Xem website</span></a>
    <a class="gb-admin-logout" href="<?= BASE_URL ?>logout" onclick="return confirm('Bạn muốn đăng xuất khỏi trang quản trị?')"><span class="nav-ico">🚪</span><span>Đăng xuất</span></a>
  </nav>
</aside>
<main class="admin-main admin-main-v36 gb-admin-main">
