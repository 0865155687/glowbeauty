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
$shippingFee = class_exists('Order') ? Order::shippingFee($checkoutTotal, $_POST['address'] ?? '') : ($checkoutTotal >= 1000000 || $checkoutTotal == 0 ? 0 : 30000);
$grandTotal = $checkoutTotal + $shippingFee;
$stockWarnings = [];
foreach ($checkoutItems as $ci) {
    if (!empty($ci['cart_warning'])) {
        $stockWarnings[] = trim(($ci['name'] ?? 'Sản phẩm') . ': ' . $ci['cart_warning']);
    }
}
$hasStockWarning = !empty($stockWarnings);
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
    <div class="checkout-pro-error">⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if($hasStockWarning): ?>
    <div class="checkout-pro-error checkout-stock-error">
      ⚠️ Sản phẩm không đủ số lượng trong kho. Vui lòng quay lại giỏ hàng/đã lưu để giảm số lượng trước khi đặt hàng.
      <ul>
        <?php foreach($stockWarnings as $sw): ?>
          <li><?= htmlspecialchars($sw, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
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
        <label>Email nhận hóa đơn <span>(để gửi hóa đơn sau khi thanh toán)</span></label>
        <input type="email" name="customer_email" required value="<?= htmlspecialchars($_POST['customer_email'] ?? ($_SESSION['user']['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ví dụ: khachhang@gmail.com">
      </div>

      <div class="checkout-pro-field">
        <label>Địa chỉ nhận hàng <span>(bắt buộc có tỉnh/thành phố)</span></label>
        <input name="address" required value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" id="checkoutAddress" placeholder="Ví dụ: Số 3, phường Tứ Minh, thành phố Hải Phòng" title="Vui lòng nhập đầy đủ địa chỉ có tỉnh/thành phố để shop tính phí ship đúng.">
      </div>

      <div class="checkout-pro-field checkout-pro-note-field">
        <label>Khung giờ giao hàng <span>(bắt buộc chọn)</span></label>
        <?php $selectedDeliveryNote = $_POST['note'] ?? ''; ?>
        <select name="note" required class="checkout-delivery-select">
          <option value="" <?= $selectedDeliveryNote === '' ? 'selected' : '' ?>>Chọn khung giờ giao hàng</option>
          <option value="Giao buổi sáng" <?= $selectedDeliveryNote === 'Giao buổi sáng' ? 'selected' : '' ?>>Giao buổi sáng</option>
          <option value="Giao buổi chiều" <?= $selectedDeliveryNote === 'Giao buổi chiều' ? 'selected' : '' ?>>Giao buổi chiều</option>
          <option value="Giao buổi tối" <?= $selectedDeliveryNote === 'Giao buổi tối' ? 'selected' : '' ?>>Giao buổi tối</option>
        </select>
      </div>

      <div class="checkout-pro-policy">
        <div><strong>🚚 Phí ship rõ ràng</strong><span>Hải Phòng 10.000đ, tỉnh khác 30.000đ, đơn từ 1.000.000đ freeship.</span></div>
        <div><strong>🛡️ Bảo hành đơn lớn</strong><span>Đơn từ 5.000.000đ được cam kết đổi mới/bảo hành trong 07 ngày nếu lỗi từ shop hoặc vận chuyển.</span></div>
      </div>

      <button class="checkout-pro-submit" type="submit" <?= $hasStockWarning ? 'disabled aria-disabled="true"' : '' ?>><?= $hasStockWarning ? 'Không thể đặt vì vượt tồn kho' : 'Xác nhận đặt hàng' ?></button>
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
            <div class="checkout-pro-item <?= !empty($item['cart_warning']) ? 'has-stock-warning' : '' ?>">
              <img src="<?= htmlspecialchars(checkout_product_image_url($item['image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              <div class="checkout-pro-item-info">
                <strong><?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                <span>Số lượng: <?= (int)($item['qty'] ?? 1) ?></span>
                <?php if(!empty($item['cart_warning'])): ?>
                  <em class="checkout-item-warning">⚠️ <?= htmlspecialchars($item['cart_warning'], ENT_QUOTES, 'UTF-8') ?></em>
                <?php endif; ?>
              </div>
              <b><?= number_format((float)($item['line'] ?? 0), 0, ',', '.') ?>đ</b>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="checkout-pro-total-row"><span>Tạm tính</span><b><?= number_format($checkoutTotal, 0, ',', '.') ?>đ</b></div>
      <div class="checkout-pro-total-row"><span>Phí vận chuyển</span><b id="shippingText"><?= $shippingFee ? number_format($shippingFee, 0, ',', '.') . 'đ' : 'Miễn phí' ?></b></div>
      <div class="checkout-pro-grand"><span>Tổng thanh toán</span><b id="grandText"><?= number_format($grandTotal, 0, ',', '.') ?>đ</b></div>
      <?php if($grandTotal >= 5000000): ?>
        <div class="checkout-pro-warranty">🛡️ Đơn hàng từ 5.000.000đ được cam kết bảo hành/đổi mới trong 07 ngày nếu lỗi từ nhà sản xuất, giao sai hoặc hư hỏng do vận chuyển.</div>
      <?php endif; ?>
    </aside>
  </div>
</section>

<style>
.checkout-pro-error{max-width:1100px!important;margin:0 auto 16px!important;padding:14px 16px!important;border:1px solid #f3b08b!important;border-radius:16px!important;background:#fff4ec!important;color:#b84216!important;font-weight:800!important;line-height:1.45!important}.checkout-stock-error ul{margin:8px 0 0 18px!important;padding:0!important}.checkout-pro-submit:disabled{opacity:.65!important;cursor:not-allowed!important;filter:grayscale(.25)!important;transform:none!important}.checkout-pro-item.has-stock-warning{border-color:#ef4444!important;background:#fff7f7!important}.checkout-item-warning{display:block!important;margin-top:5px!important;color:#b91c1c!important;font-style:normal!important;font-weight:800!important;font-size:12.5px!important;line-height:1.35!important}
.checkout-pro-hero{display:none!important}.checkout-pro-page{padding:20px 14px 34px!important}.checkout-pro-grid{max-width:1100px!important;margin:0 auto!important;display:grid!important;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr)!important;gap:18px!important;align-items:start!important}.checkout-pro-form,.checkout-pro-summary{background:rgba(255,255,255,.94)!important;border:1px solid #efcdbd!important;border-radius:24px!important;box-shadow:0 14px 42px rgba(140,75,45,.09)!important;padding:28px!important}.checkout-pro-card-title{display:flex!important;flex-direction:column!important;align-items:center!important;justify-content:center!important;text-align:center!important;gap:10px!important;margin-bottom:26px!important}.checkout-pro-card-title.small{margin-bottom:22px!important}.checkout-pro-icon{width:50px!important;height:50px!important;border-radius:17px!important;background:linear-gradient(135deg,#fff1e9,#ffe2d7)!important;display:flex!important;align-items:center!important;justify-content:center!important;font-size:22px!important;box-shadow:inset 0 0 0 1px #f5cfc0!important}.checkout-pro-card-title h2{margin:0!important;color:#3c170e!important;font-size:27px!important;line-height:1.15!important;text-align:center!important}.checkout-pro-card-title p{margin:0!important;color:#8b6253!important;font-size:14.5px!important;line-height:1.45!important;text-align:center!important}.checkout-pro-two-cols{display:grid!important;grid-template-columns:1fr 1fr!important;gap:16px!important}.checkout-pro-field{margin-bottom:14px!important;text-align:left!important}.checkout-pro-field label{display:block!important;color:#4b2015!important;font-weight:800!important;text-transform:none!important;font-size:14px!important;letter-spacing:0!important;margin:0 0 7px!important;text-align:left!important;line-height:1.3!important}.checkout-pro-field label span{font-weight:700!important;color:#a57969!important}.checkout-pro-field label:before{content:none!important;display:none!important}.checkout-pro-field input,.checkout-pro-field select{width:100%!important;height:50px!important;border:1px solid #e8bda8!important;border-radius:15px!important;background:#fffdfb!important;padding:0 16px!important;font-size:15px!important;outline:none!important;transition:.2s!important;box-sizing:border-box!important}.checkout-pro-field select{cursor:pointer!important;color:#35180f!important;font-weight:700!important}.checkout-pro-field input:focus,.checkout-pro-field select:focus{border-color:#c9754a!important;box-shadow:0 0 0 4px rgba(201,117,74,.12)!important}.checkout-pro-policy{display:grid!important;grid-template-columns:1fr 1fr!important;gap:14px!important;margin:8px 0 18px!important}.checkout-pro-policy div{background:#fff7f2!important;border:1px solid #f1d1c2!important;border-radius:16px!important;padding:13px 15px!important;text-align:left!important}.checkout-pro-policy strong{display:block!important;color:#552519!important;margin-bottom:4px!important;font-size:15px!important}.checkout-pro-policy span{display:block!important;color:#8a6254!important;font-size:13.5px!important;line-height:1.45!important}.checkout-pro-submit{width:100%!important;border:0!important;border-radius:16px!important;padding:15px 24px!important;background:linear-gradient(135deg,#a95d2f,#df9d42)!important;color:#fff!important;text-transform:uppercase!important;font-weight:900!important;letter-spacing:.3px!important;font-size:15px!important;box-shadow:0 12px 26px rgba(153,89,47,.22)!important;cursor:pointer!important;transition:.2s!important}.checkout-pro-submit:hover{transform:translateY(-2px)!important;box-shadow:0 16px 34px rgba(153,89,47,.30)!important}.checkout-pro-summary{position:sticky!important;top:86px!important}.checkout-pro-items{display:flex!important;flex-direction:column!important;gap:12px!important;margin:4px 0 18px!important;max-height:270px!important;overflow:auto!important;padding-right:4px!important}.checkout-pro-item{display:grid!important;grid-template-columns:58px 1fr auto!important;gap:10px!important;align-items:center!important;border:1px solid #f1d5c9!important;border-radius:17px!important;padding:10px!important;background:#fffaf7!important}.checkout-pro-item img{width:58px!important;height:58px!important;border-radius:13px!important;object-fit:cover!important;background:#fff!important}.checkout-pro-item-info strong{display:block!important;color:#3b170f!important;font-size:14px!important;line-height:1.25!important}.checkout-pro-item-info span{display:block!important;color:#9a6c5b!important;font-size:13px!important;margin-top:3px!important}.checkout-pro-item b{color:#e91e63!important;white-space:nowrap!important;font-size:15px!important}.checkout-pro-empty{padding:16px!important;border-radius:16px!important;background:#fff7f2!important;color:#8b6253!important;text-align:center!important}.checkout-pro-total-row{display:flex!important;justify-content:space-between!important;padding:11px 0!important;border-top:1px dashed #edcbbb!important;color:#795242!important;font-size:15px!important}.checkout-pro-total-row b{color:#3c170e!important}.checkout-pro-grand{display:flex!important;justify-content:space-between!important;align-items:center!important;margin-top:10px!important;padding:16px 18px!important;border-radius:18px!important;background:linear-gradient(135deg,#fff1e8,#fff8f5)!important;border:1px solid #efcdbd!important;text-transform:uppercase!important;font-weight:900!important;color:#9c4d2c!important;gap:14px!important}.checkout-pro-grand b{font-size:25px!important;color:#e91e63!important;white-space:nowrap!important}@media(max-width:980px){.checkout-pro-grid{grid-template-columns:1fr!important;max-width:680px!important}.checkout-pro-summary{position:static!important}.checkout-pro-page{padding:16px 12px 32px!important}}@media(max-width:640px){.checkout-pro-two-cols,.checkout-pro-policy{grid-template-columns:1fr!important}.checkout-pro-form,.checkout-pro-summary{padding:18px!important;border-radius:20px!important}.checkout-pro-card-title{gap:9px!important;margin-bottom:22px!important}.checkout-pro-icon{width:44px!important;height:44px!important;font-size:20px!important}.checkout-pro-card-title h2{font-size:23px!important}.checkout-pro-card-title p{font-size:13.5px!important}.checkout-pro-field input{height:48px!important;font-size:14px!important}.checkout-pro-field label{font-size:13.5px!important}.checkout-pro-item{grid-template-columns:52px 1fr!important}.checkout-pro-item img{width:52px!important;height:52px!important}.checkout-pro-item b{grid-column:2!important}.checkout-pro-grand{align-items:flex-start!important;flex-direction:column!important}.checkout-pro-grand b{font-size:23px!important}}
.checkout-pro-warranty{margin-top:12px!important;padding:13px 15px!important;border-radius:16px!important;background:#fff7f2!important;border:1px solid #f1d1c2!important;color:#7b3d24!important;font-weight:800!important;line-height:1.45!important}
</style>
<script>
(function(){
  <?php if($hasStockWarning): ?>
  setTimeout(function(){
    var toast = document.createElement('div');
    toast.className = 'gb-ajax-toast gb-stock-toast show';
    toast.innerHTML = '<span class="gb-toast-check">!</span><span>Sản phẩm không đủ số lượng trong kho, không thể đặt hàng. Vui lòng giảm số lượng trước.</span><button type="button" class="gb-toast-close">×</button>';
    document.body.appendChild(toast);
    var btn = toast.querySelector('button');
    if(btn) btn.onclick = function(){ toast.classList.remove('show'); };
    setTimeout(function(){ toast.classList.remove('show'); }, 5000);
  }, 200);
  <?php endif; ?>
  var subtotal = <?= (int)$checkoutTotal ?>;
  var address = document.getElementById('checkoutAddress');
  var shipText = document.getElementById('shippingText');
  var grandText = document.getElementById('grandText');
  function money(n){ return n === 0 ? 'Miễn phí' : n.toLocaleString('vi-VN') + 'đ'; }
  function calcShip(){
    if(!shipText || !grandText) return;
    var ship = 0;
    if(subtotal > 0 && subtotal < 1000000){
      var v = (address && address.value ? address.value : '').toLowerCase();
      ship = (v.indexOf('hải phòng') >= 0 || v.indexOf('hai phong') >= 0) ? 10000 : 30000;
    }
    shipText.textContent = money(ship);
    grandText.textContent = (subtotal + ship).toLocaleString('vi-VN') + 'đ';
  }
  if(address) address.addEventListener('input', calcShip);
  calcShip();
})();
</script>
