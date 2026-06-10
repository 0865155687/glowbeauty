<section class="container gb-thank-page">
    <div class="gb-thank-card">
        <div class="gb-thank-icon">✓</div>

        <span>GlowBeauty</span>

        <h1>Gửi yêu cầu thành công</h1>

        <p>
            <?= htmlspecialchars($message ?? 'GlowBeauty đã nhận thông tin tư vấn và sẽ phản hồi cho bạn sớm nhất.') ?>
        </p>

        <div class="gb-thank-actions">
            <a href="<?= BASE_URL ?>products">Xem sản phẩm</a>
            <a href="<?= BASE_URL ?>contact">Gửi yêu cầu khác</a>
        </div>
    </div>
</section>
