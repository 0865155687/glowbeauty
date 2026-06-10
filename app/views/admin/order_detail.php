<?php
function gbDetailStatusClass($status) {
    if ($status === 'Hoàn thành') return 'status-done';
    if ($status === 'Đã hủy') return 'status-cancel';
    if ($status === 'Đang giao') return 'status-shipping';
    if ($status === 'Đã xác nhận') return 'status-confirmed';
    return 'status-pending';
}
$paymentStatus = in_array(($order['payment_status'] ?? ''), ['Chưa thanh toán','Đã thanh toán'], true) ? $order['payment_status'] : 'Chưa thanh toán';
$paymentClass = ($paymentStatus === 'Đã thanh toán') ? 'paid' : 'unpaid';
$orderNote = trim((string)($order['note'] ?? ''));
?>

<section class="admin-hero-v34 compact admin-hero-tight-clean order-detail-hero-clean">
    <div>
        <span class="admin-kicker-v34">Chi tiết đơn hàng</span>
        <h1>Đơn hàng #<?= (int)$order['id'] ?></h1>
        <p><b><?= htmlspecialchars($order['customer_name']) ?></b> · <?= htmlspecialchars($order['phone']) ?></p>
    </div>
    <div class="detail-actions detail-actions-clean">
        <button class="btn admin-secondary-v34" onclick="window.print()">🧾 In bill</button>
        <a class="btn admin-secondary-v34" href="<?= BASE_URL ?>admin/orders">← Quay lại</a>
    </div>
</section>

<div class="order-detail-summary-clean no-print">
    <div class="detail-card-clean receiver-card-clean">
        <span class="detail-icon-clean">📦</span>
        <div>
            <small>Thông tin nhận hàng</small>
            <h3><?= htmlspecialchars($order['address']) ?></h3>
            <p>Ngày tạo: <?= htmlspecialchars($order['created_at']) ?></p>
        </div>
    </div>

    <div class="detail-card-clean status-card-clean">
        <span class="detail-icon-clean">🚚</span>
        <div>
            <small>Trạng thái đơn</small>
            <span class="status-pill <?= gbDetailStatusClass($order['status'] ?? 'Chờ xác nhận') ?>">
                <?= htmlspecialchars($order['status']) ?>
            </span>
        </div>
    </div>

    <div class="detail-card-clean payment-card-clean">
        <span class="detail-icon-clean">💳</span>
        <div>
            <small>Thanh toán</small>
            <span class="payment-pill <?= $paymentClass ?>">
                <?= htmlspecialchars($paymentStatus) ?>
            </span>
        </div>
    </div>

    <div class="detail-card-clean total-card-clean">
        <span class="detail-icon-clean">💰</span>
        <div>
            <small>Tổng thanh toán</small>
            <h2><?= number_format($order['total'],0,',','.') ?>đ</h2>
        </div>
    </div>
</div>

<?php if($orderNote !== ''): ?>
<div class="admin-panel-v34 order-note-panel-clean no-print">
    <div class="order-note-head-clean">
        <span>📝</span>
        <div>
            <small>Yêu cầu giao hàng của khách</small>
            <p><?= htmlspecialchars($orderNote) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="admin-panel-v34 order-items-panel-clean no-print">
    <div class="admin-panel-head-v35"><h2>🛍️ Sản phẩm trong đơn</h2></div>
    <table class="admin-table order-detail-table-clean">
        <thead>
            <tr>
                <th>Mã SP</th>
                <th>Sản phẩm</th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($items as $it): ?>
            <tr>
                <td><?= htmlspecialchars($it['product_code'] ?? ('SP'.$it['product_id'])) ?></td>
                <td><b><?= htmlspecialchars($it['product_name']) ?></b></td>
                <td><?= number_format($it['price'],0,',','.') ?>đ</td>
                <td><?= (int)$it['quantity'] ?></td>
                <td><b><?= number_format($it['price']*$it['quantity'],0,',','.') ?>đ</b></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="print-bill" class="print-bill">
    <div class="bill-box">
        <div class="bill-head">
            <div><h2>GlowBeauty</h2><p>Hoá đơn bán hàng</p></div>
            <div class="bill-code"><b>#<?= (int)$order['id'] ?></b><span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span></div>
        </div>
        <div class="bill-info">
            <p><b>Khách hàng:</b> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><b>Số điện thoại:</b> <?= htmlspecialchars($order['phone']) ?></p>
            <p><b>Địa chỉ:</b> <?= htmlspecialchars($order['address']) ?></p>
            <?php if($orderNote !== ''): ?><p><b>Yêu cầu giao hàng:</b> <?= htmlspecialchars($orderNote) ?></p><?php endif; ?>
            <p><b>Trạng thái thanh toán:</b> <?= htmlspecialchars($paymentStatus) ?></p>
        </div>
        <table class="bill-table">
            <thead><tr><th>Mã SP</th><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
            <tbody>
            <?php foreach($items as $it): ?>
                <tr>
                    <td><?= htmlspecialchars($it['product_code'] ?? ('SP'.$it['product_id'])) ?></td>
                    <td><?= htmlspecialchars($it['product_name']) ?></td>
                    <td><?= (int)$it['quantity'] ?></td>
                    <td><?= number_format($it['price'],0,',','.') ?>đ</td>
                    <td><?= number_format($it['price']*$it['quantity'],0,',','.') ?>đ</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="bill-total"><span>Tổng thanh toán</span><b><?= number_format($order['total'],0,',','.') ?>đ</b></div>
        <p class="bill-thanks">Cảm ơn quý khách đã mua hàng tại GlowBeauty!</p>
    </div>
</div>

<style>
.order-note-panel-clean{max-width:100%;margin:22px 0;padding:0!important;border:0!important;background:transparent!important;box-shadow:none!important}.order-note-head-clean{display:flex;gap:16px;align-items:flex-start;padding:20px 24px;border:1px solid #efcdbd;border-radius:24px;background:linear-gradient(135deg,#fffaf7,#fff3ec);box-shadow:0 12px 34px rgba(114,58,35,.08)}.order-note-head-clean>span{width:50px;height:50px;border-radius:18px;background:#ffe9dd;display:flex;align-items:center;justify-content:center;font-size:24px;flex:0 0 50px}.order-note-head-clean small{display:block;color:#bd6a3f;text-transform:uppercase;letter-spacing:1.4px;font-weight:900;margin-bottom:7px}.order-note-head-clean p{margin:0;color:#3c170e;font-size:18px;font-weight:700;line-height:1.45}@media print{.order-note-panel-clean{display:none!important}}
</style>
