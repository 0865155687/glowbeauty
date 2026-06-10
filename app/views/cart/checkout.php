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
.checkout-pro-hero{padding:44px 0 24px;background:linear-gradient(180deg,#fff7f3 0%,#fffaf7 100%);border-bottom:1px solid #f4d4c7}.checkout-pro-hero-inner{text-align:center}.checkout-pro-hero span{display:block;color:#bd6a3f;font-weight:800;letter-spacing:5px;font-size:13px;text-transform:uppercase;margin-bottom:10px}.checkout-pro-hero h1{font-size:44px;line-height:1.1;margin:0;color:#3c170e;text-transform:uppercase}.checkout-pro-hero p{margin:14px 0 0;color:#795242;font-size:16px}.checkout-pro-page{padding:34px 18px 56px}.checkout-pro-error{max-width:1180px;margin:0 auto 18px;padding:14px 18px;border-radius:18px;background:#fff0f0;border:1px solid #ffc4c4;color:#b42318;font-weight:700}.checkout-pro-grid{max-width:1180px;margin:0 auto;display:grid;grid-template-columns:minmax(0,1.45fr) minmax(330px,.85fr);gap:24px;align-items:start}.checkout-pro-form,.checkout-pro-summary{background:rgba(255,255,255,.92);border:1px solid #efcdbd;border-radius:28px;box-shadow:0 18px 55px rgba(140,75,45,.10);padding:30px}.checkout-pro-card-title{display:flex;gap:14px;align-items:center;margin-bottom:24px}.checkout-pro-card-title.small{margin-bottom:18px}.checkout-pro-icon{width:52px;height:52px;border-radius:18px;background:linear-gradient(135deg,#fff1e9,#ffe2d7);display:flex;align-items:center;justify-content:center;font-size:24px;box-shadow:inset 0 0 0 1px #f5cfc0}.checkout-pro-card-title h2{margin:0;color:#3c170e;font-size:25px}.checkout-pro-card-title p{margin:5px 0 0;color:#8b6253}.checkout-pro-two-cols{display:grid;grid-template-columns:1fr 1fr;gap:16px}.checkout-pro-field{margin-bottom:17px}.checkout-pro-field label{display:block;color:#7b3e27;font-weight:800;text-transform:uppercase;font-size:13px;letter-spacing:.6px;margin-bottom:9px}.checkout-pro-field label span{font-weight:700;text-transform:none;letter-spacing:0;color:#a57969}.checkout-pro-field label:before{content:'•';color:#d49a55;margin-right:8px;font-size:20px;vertical-align:middle}.checkout-pro-field input{width:100%;height:58px;border:1px solid #e8bda8;border-radius:16px;background:#fffdfb;padding:0 18px;font-size:16px;outline:none;transition:.2s;box-sizing:border-box}.checkout-pro-field input:focus{border-color:#c9754a;box-shadow:0 0 0 4px rgba(201,117,74,.13)}.checkout-pro-policy{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:8px 0 22px}.checkout-pro-policy div{background:#fff7f2;border:1px solid #f1d1c2;border-radius:18px;padding:14px 16px}.checkout-pro-policy strong{display:block;color:#552519;margin-bottom:4px}.checkout-pro-policy span{display:block;color:#8a6254;font-size:14px}.checkout-pro-submit{border:0;border-radius:999px;padding:16px 34px;background:linear-gradient(135deg,#9b5b32,#d69b4d);color:#fff;text-transform:uppercase;font-weight:900;letter-spacing:.4px;box-shadow:0 15px 30px rgba(153,89,47,.25);cursor:pointer;transition:.2s}.checkout-pro-submit:hover{transform:translateY(-2px);box-shadow:0 18px 38px rgba(153,89,47,.34)}.checkout-pro-summary{position:sticky;top:105px}.checkout-pro-items{display:flex;flex-direction:column;gap:12px;margin:4px 0 18px;max-height:310px;overflow:auto;padding-right:4px}.checkout-pro-item{display:grid;grid-template-columns:64px 1fr auto;gap:12px;align-items:center;border:1px solid #f1d5c9;border-radius:18px;padding:10px;background:#fffaf7}.checkout-pro-item img{width:64px;height:64px;border-radius:14px;object-fit:cover;background:#fff}.checkout-pro-item-info strong{display:block;color:#3b170f;font-size:14px;line-height:1.25}.checkout-pro-item-info span{display:block;color:#9a6c5b;font-size:13px;margin-top:4px}.checkout-pro-item b{color:#e91e63;white-space:nowrap}.checkout-pro-empty{padding:18px;border-radius:18px;background:#fff7f2;color:#8b6253;text-align:center}.checkout-pro-total-row{display:flex;justify-content:space-between;padding:12px 0;border-top:1px dashed #edcbbb;color:#795242}.checkout-pro-total-row b{color:#3c170e}.checkout-pro-grand{display:flex;justify-content:space-between;align-items:center;margin-top:10px;padding:18px;border-radius:20px;background:linear-gradient(135deg,#fff1e8,#fff8f5);border:1px solid #efcdbd;text-transform:uppercase;font-weight:900;color:#9c4d2c}.checkout-pro-grand b{font-size:26px;color:#e91e63}@media(max-width:980px){.checkout-pro-grid{grid-template-columns:1fr}.checkout-pro-summary{position:static}.checkout-pro-hero h1{font-size:34px}}@media(max-width:640px){.checkout-pro-two-cols,.checkout-pro-policy{grid-template-columns:1fr}.checkout-pro-form,.checkout-pro-summary{padding:22px;border-radius:22px}.checkout-pro-hero{padding-top:30px}.checkout-pro-hero h1{font-size:30px}.checkout-pro-item{grid-template-columns:56px 1fr}.checkout-pro-item b{grid-column:2}.checkout-pro-submit{width:100%}}
</style>
