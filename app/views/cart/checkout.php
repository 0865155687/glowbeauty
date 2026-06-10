<?php
$checkoutItems = $items ?? [];
$checkoutTotal = $total ?? 0;
$error = $error ?? '';
if (!function_exists('checkout_product_image_url')) {
    function checkout_product_image_url($image) {
        if (function_exists('gb_image_url')) {
            return gb_image_url($image);
        }
        $image = trim((string)$image);
        if ($image === '') {
            return (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/public/assets/images/glowbeauty-logo-small.png';
        }
        if (preg_match('~^https?://~i', $image)) {
            return $image;
        }
        $image = ltrim($image, '/');
        if (strpos($image, 'public/') === 0) {
            return (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/' . $image;
        }
        if (strpos($image, 'assets/') === 0) {
            return (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/public/' . $image;
        }
        return (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/public/assets/images/' . $image;
    }
}
$shippingFee = $checkoutTotal >= 500000 || $checkoutTotal == 0 ? 0 : 30000;
$grandTotal = $checkoutTotal + $shippingFee;
?>
<section class="checkout-pro-hero">
  <div class="container checkout-pro-hero-inner">
    <span>GLOWBEAUTY CHECKOUT</span>
    <h1>Thanh toán</h1>
    <p>Kiểm tra đơn hàng và nhập thông tin nhận hàng để shop xác nhận nhanh nhất.</p>
  </div>
</section>

<section class="container checkout-pro-page">
  <?php if($error): ?>
    <div class="checkout-pro-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="checkout-pro-grid">
    <form method="post" class="checkout-pro-form" novalidate>
      <div class="checkout-pro-card-title">
        <div class="checkout-pro-icon">📦</div>
        <div>
          <h2>Thông tin nhận hàng</h2>
          <p>Shop chỉ dùng thông tin này để giao hàng và xác nhận đơn.</p>
        </div>
      </div>

      <div class="checkout-pro-two-cols">
        <div class="checkout-pro-field">
          <label>Họ và tên khách hàng</label>
          <input name="customer_name" required value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>" data-name-input placeholder="Ví dụ: Nguyễn Thị Ngoan">
        </div>

        <div class="checkout-pro-field">
          <label>Số điện thoại</label>
          <input name="phone" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" data-phone-input inputmode="numeric" maxlength="10" pattern="[0-9]{10}" placeholder="Ví dụ: 0865155687">
        </div>
      </div>

      <div class="checkout-pro-field">
        <label>Địa chỉ nhận hàng</label>
        <input name="address" required value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" placeholder="Số nhà, phường/xã, quận/huyện, tỉnh/thành">
      </div>

      <div class="checkout-pro-field checkout-pro-note-field">
        <label>Yêu cầu giao hàng <span>(không bắt buộc)</span></label>
        <input name="note" value="<?= htmlspecialchars($_POST['note'] ?? '') ?>" maxlength="255" placeholder="Ví dụ: Gọi trước khi giao, giao buổi chiều...">
      </div>

      <div class="checkout-pro-policy">
        <div><strong>🚚 Giao hàng nhanh</strong><span>Shop liên hệ xác nhận trước khi giao.</span></div>
        <div><strong>💄 Chính hãng</strong><span>Sản phẩm rõ nguồn gốc, tư vấn tận tâm.</span></div>
      </div>

      <button class="checkout-pro-submit" type="submit">Xác nhận đặt hàng</button>
    </form>

    <aside class="checkout-pro-summary">
      <div class="checkout-pro-card-title small">
        <div class="checkout-pro-icon">🧾</div>
        <div>
          <h2>Đơn hàng của bạn</h2>
          <p><?= count($checkoutItems) ?> sản phẩm trong giỏ</p>
        </div>
      </div>

      <div class="checkout-pro-items">
        <?php if(empty($checkoutItems)): ?>
          <div class="checkout-pro-empty">Giỏ hàng đang trống.</div>
        <?php else: ?>
          <?php foreach($checkoutItems as $item): ?>
            <div class="checkout-pro-item">
              <img src="<?= htmlspecialchars(checkout_product_image_url($item['image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <div class="checkout-pro-item-info">
                <strong><?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                <span>Số lượng: <?= (int)($item['qty'] ?? 1) ?></span>
              </div>
              <b><?= number_format((float)($item['line'] ?? 0), 0, ',', '.') ?>đ</b>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="checkout-pro-total-row"><span>Tạm tính</span><b><?= number_format($checkoutTotal, 0, ',', '.') ?>đ</b></div>
      <div class="checkout-pro-total-row"><span>Phí vận chuyển</span><b><?= $shippingFee ? number_format($shippingFee, 0, ',', '.') . 'đ' : 'Miễn phí' ?></b></div>
      <div class="checkout-pro-grand"><span>Tổng thanh toán</span><b><?= number_format($grandTotal, 0, ',', '.') ?>đ</b></div>
    </aside>
  </div>
</section>

<style>
.checkout-pro-hero{display:none!important}.checkout-pro-page{padding:20px 14px 34px!important}.checkout-pro-grid{max-width:1100px!important;margin:0 auto!important;display:grid!important;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr)!important;gap:18px!important;align-items:start!important}.checkout-pro-form,.checkout-pro-summary{background:rgba(255,255,255,.94)!important;border:1px solid #efcdbd!important;border-radius:24px!important;box-shadow:0 14px 42px rgba(140,75,45,.09)!important;padding:28px!important}.checkout-pro-card-title{display:flex!important;flex-direction:column!important;align-items:center!important;justify-content:center!important;text-align:center!important;gap:10px!important;margin-bottom:26px!important}.checkout-pro-card-title.small{margin-bottom:22px!important}.checkout-pro-icon{width:50px!important;height:50px!important;border-radius:17px!important;background:linear-gradient(135deg,#fff1e9,#ffe2d7)!important;display:flex!important;align-items:center!important;justify-content:center!important;font-size:22px!important;box-shadow:inset 0 0 0 1px #f5cfc0!important}.checkout-pro-card-title h2{margin:0!important;color:#3c170e!important;font-size:27px!important;line-height:1.15!important;text-align:center!important}.checkout-pro-card-title p{margin:0!important;color:#8b6253!important;font-size:14.5px!important;line-height:1.45!important;text-align:center!important}.checkout-pro-two-cols{display:grid!important;grid-template-columns:1fr 1fr!important;gap:16px!important}.checkout-pro-field{margin-bottom:14px!important;text-align:left!important}.checkout-pro-field label{display:block!important;color:#4b2015!important;font-weight:800!important;text-transform:none!important;font-size:14px!important;letter-spacing:0!important;margin:0 0 7px!important;text-align:left!important;line-height:1.3!important}.checkout-pro-field label span{font-weight:700!important;color:#a57969!important}.checkout-pro-field label:before{content:none!important;display:none!important}.checkout-pro-field input{width:100%!important;height:50px!important;border:1px solid #e8bda8!important;border-radius:15px!important;background:#fffdfb!important;padding:0 16px!important;font-size:15px!important;outline:none!important;transition:.2s!important;box-sizing:border-box!important}.checkout-pro-field input:focus{border-color:#c9754a!important;box-shadow:0 0 0 4px rgba(201,117,74,.12)!important}.checkout-pro-policy{display:grid!important;grid-template-columns:1fr 1fr!important;gap:14px!important;margin:8px 0 18px!important}.checkout-pro-policy div{background:#fff7f2!important;border:1px solid #f1d1c2!important;border-radius:16px!important;padding:13px 15px!important;text-align:left!important}.checkout-pro-policy strong{display:block!important;color:#552519!important;margin-bottom:4px!important;font-size:15px!important}.checkout-pro-policy span{display:block!important;color:#8a6254!important;font-size:13.5px!important;line-height:1.45!important}.checkout-pro-submit{width:100%!important;border:0!important;border-radius:16px!important;padding:15px 24px!important;background:linear-gradient(135deg,#a95d2f,#df9d42)!important;color:#fff!important;text-transform:uppercase!important;font-weight:900!important;letter-spacing:.3px!important;font-size:15px!important;box-shadow:0 12px 26px rgba(153,89,47,.22)!important;cursor:pointer!important;transition:.2s!important}.checkout-pro-submit:hover{transform:translateY(-2px)!important;box-shadow:0 16px 34px rgba(153,89,47,.30)!important}.checkout-pro-summary{position:sticky!important;top:86px!important}.checkout-pro-items{display:flex!important;flex-direction:column!important;gap:12px!important;margin:4px 0 18px!important;max-height:270px!important;overflow:auto!important;padding-right:4px!important}.checkout-pro-item{display:grid!important;grid-template-columns:58px 1fr auto!important;gap:10px!important;align-items:center!important;border:1px solid #f1d5c9!important;border-radius:17px!important;padding:10px!important;background:#fffaf7!important}.checkout-pro-item img{width:58px!important;height:58px!important;border-radius:13px!important;object-fit:cover!important;background:#fff!important}.checkout-pro-item-info strong{display:block!important;color:#3b170f!important;font-size:14px!important;line-height:1.25!important}.checkout-pro-item-info span{display:block!important;color:#9a6c5b!important;font-size:13px!important;margin-top:3px!important}.checkout-pro-item b{color:#e91e63!important;white-space:nowrap!important;font-size:15px!important}.checkout-pro-empty{padding:16px!important;border-radius:16px!important;background:#fff7f2!important;color:#8b6253!important;text-align:center!important}.checkout-pro-total-row{display:flex!important;justify-content:space-between!important;padding:11px 0!important;border-top:1px dashed #edcbbb!important;color:#795242!important;font-size:15px!important}.checkout-pro-total-row b{color:#3c170e!important}.checkout-pro-grand{display:flex!important;justify-content:space-between!important;align-items:center!important;margin-top:10px!important;padding:16px 18px!important;border-radius:18px!important;background:linear-gradient(135deg,#fff1e8,#fff8f5)!important;border:1px solid #efcdbd!important;text-transform:uppercase!important;font-weight:900!important;color:#9c4d2c!important;gap:14px!important}.checkout-pro-grand b{font-size:25px!important;color:#e91e63!important;white-space:nowrap!important}@media(max-width:980px){.checkout-pro-grid{grid-template-columns:1fr!important;max-width:680px!important}.checkout-pro-summary{position:static!important}.checkout-pro-page{padding:16px 12px 32px!important}}@media(max-width:640px){.checkout-pro-two-cols,.checkout-pro-policy{grid-template-columns:1fr!important}.checkout-pro-form,.checkout-pro-summary{padding:18px!important;border-radius:20px!important}.checkout-pro-card-title{gap:9px!important;margin-bottom:22px!important}.checkout-pro-icon{width:44px!important;height:44px!important;font-size:20px!important}.checkout-pro-card-title h2{font-size:23px!important}.checkout-pro-card-title p{font-size:13.5px!important}.checkout-pro-field input{height:48px!important;font-size:14px!important}.checkout-pro-field label{font-size:13.5px!important}.checkout-pro-item{grid-template-columns:52px 1fr!important}.checkout-pro-item img{width:52px!important;height:52px!important}.checkout-pro-item b{grid-column:2!important}.checkout-pro-grand{align-items:flex-start!important;flex-direction:column!important}.checkout-pro-grand b{font-size:23px!important}}
</style>
