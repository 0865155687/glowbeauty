<section class="container contact-layout contact-layout-clean contact-no-hero-clean">
    <div class="contact-info-panel contact-info-clean">
        <span>Showroom GlowBeauty</span>
        <h2>Thông tin liên hệ</h2>
        <p class="contact-lead">Đội ngũ GlowBeauty hỗ trợ nhanh về chăm sóc da, trang điểm nền, son môi và tình trạng hàng.</p>

        <div class="shop-info-list shop-info-clean">
            <a href="tel:0865155687"><i>📞</i><div><b>Hotline 24/7</b><small>0865155687</small></div></a>
            <a href="tel:0394807683"><i>💬</i><div><b>Tư vấn nhanh</b><small>0394807683</small></div></a>
            <a href="mailto:nn9499008@gmail.com"><i>✉️</i><div><b>Email hỗ trợ</b><small>nn9499008@gmail.com</small></div></a>
            <a target="_blank" href="https://zalo.me/0865155687"><i>💙</i><div><b>Zalo GlowBeauty</b><small>Nhắn để được tư vấn trực tiếp</small></div></a>
        </div>
    </div>

    <form class="contact-form-modern contact-form-clean" method="post" action="<?= BASE_URL ?>contact">
        <div class="contact-form-title-clean">
            <span>💌</span>
            <div>
                <h2>Gửi yêu cầu tư vấn</h2>
                <p>Thông tin của bạn chỉ dùng để GlowBeauty hỗ trợ tư vấn.</p>
            </div>
        </div>

        <?php if (!empty($success)): ?><div class="success-box-v36"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="field"><label>Họ và tên</label><input name="customer_name" value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>" placeholder="Nhập họ và tên" required data-name-input></div>
        <div class="field"><label>Số điện thoại</label><input name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="Nhập số điện thoại" pattern="[0-9]{10}" maxlength="10" inputmode="numeric" required data-phone-input></div>
        <div class="field"><label>Nhu cầu cần tư vấn</label><select name="need" required><option value="">Chọn nhu cầu</option><?php foreach (['Chăm sóc da','Trang điểm nền','Son môi / màu son','Combo quà tặng'] as $n): ?><option value="<?= htmlspecialchars($n) ?>" <?= (($_POST['need'] ?? '') === $n) ? 'selected' : '' ?>><?= htmlspecialchars($n) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Nội dung</label><textarea name="message" placeholder="Mô tả nhu cầu, loại da hoặc sản phẩm bạn quan tâm"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea></div>
        <div class="privacy">🔒 Thông tin của bạn được bảo mật.</div>
        <button class="btn contact-btn" type="submit">Gửi yêu cầu</button>
    </form>
</section>
