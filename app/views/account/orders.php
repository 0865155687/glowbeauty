<section class="container pro-page">
    <div class="pro-heading">
        <span>Tài khoản</span>
        <h1>Lịch sử mua hàng</h1>
        <p>Theo dõi các đơn hàng đã đặt, trạng thái xử lý và thanh toán.</p>
    </div>
    <?php if(empty($orders)): ?>
        <div class="pro-empty"><b>Bạn chưa có đơn hàng nào.</b><p>Hãy chọn sản phẩm yêu thích và đặt hàng tại GlowBeauty.</p><a class="btn" href="<?= BASE_URL ?>products">Mua sắm ngay</a></div>
    <?php else: ?>
    <div class="order-history-pro">
        <?php foreach($orders as $o): ?>
        <div class="order-row-pro">
            <div><b>#<?= (int)$o['id'] ?></b><span><?= htmlspecialchars($o['created_at']) ?></span></div>
            <div><strong><?= number_format($o['total'],0,',','.') ?>đ</strong><span><?= htmlspecialchars($o['payment_status'] ?? 'Chưa thanh toán') ?></span></div>
            <div><em><?= htmlspecialchars($o['status'] ?? 'Chờ xác nhận') ?></em></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
