<?php
function gbDashStatusClass($status) {
    if ($status === 'Hoàn thành') return 'status-done';
    if ($status === 'Đã hủy') return 'status-cancel';
    if ($status === 'Đang giao') return 'status-shipping';
    if ($status === 'Đã xác nhận') return 'status-confirmed';
    return 'status-pending';
}
?>
<section class="admin-hero-v35 admin-hero-tight-clean">
  <div>
    <span class="admin-kicker-v35">GlowBeauty Admin</span>
    <h1>Dashboard quản trị</h1>
  </div>
</section>

<div class="stat-grid-v35 stat-grid-v36">
  <a class="stat-card-v35" href="<?= BASE_URL ?>admin/products"><i>💄</i><span>Sản phẩm</span><b><?= $stats['products'] ?></b><em>Xem chi tiết →</em></a>
  <a class="stat-card-v35" href="<?= BASE_URL ?>admin/orders"><i>🧾</i><span>Đơn hàng</span><b><?= $stats['orders'] ?></b><em>Xem chi tiết →</em></a>
  <a class="stat-card-v35" href="<?= BASE_URL ?>admin/revenue-today"><i>💰</i><span>Doanh thu hôm nay</span><b><?= number_format($stats['today'],0,',','.') ?>đ</b><em>Xem chi tiết →</em></a>
  <a class="stat-card-v35" href="<?= BASE_URL ?>admin/revenue-month"><i>📈</i><span>Doanh thu tháng</span><b><?= number_format($stats['month'],0,',','.') ?>đ</b><em>Xem biểu đồ →</em></a>
  <a class="stat-card-v35" href="<?= BASE_URL ?>admin/low-stock"><i>⚠️</i><span>Sắp hết / Hết hàng</span><b><?= $stats['low'] ?></b><em><?= (int)($stats['out_stock'] ?? 0) ?> hết · <?= (int)($stats['low_stock_only'] ?? 0) ?> sắp hết →</em></a>
  <a class="stat-card-v35" href="<?= BASE_URL ?>admin/contacts"><i>💬</i><span>Tư vấn mới</span><b><?= (int)$contactNew ?></b><em>Xem chi tiết →</em></a>
</div>

<div class="admin-panel-v35">
  <div class="admin-panel-head-v35"><h2>🛍️ Đơn hàng mới</h2></div>
  <table class="admin-table admin-table-v35">
  <tr><th>Mã</th><th>Khách hàng</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày tạo</th><th>Chi tiết</th></tr>
  <?php foreach($orders as $o): ?>
  <tr>
  <td>#<?= $o['id'] ?></td><td class="customer-cell-final"><b><?= htmlspecialchars($o['customer_name']) ?></b><span>☎ <?= htmlspecialchars($o['phone']) ?></span></td><td><b><?= number_format($o['total'],0,',','.') ?>đ</b></td><td><span class="status-pill <?= gbDashStatusClass($o['status'] ?? 'Chờ xác nhận') ?>"><?= htmlspecialchars($o['status']) ?></span></td><td><?= $o['created_at'] ?></td><td><a class="order-detail-btn" href="<?= BASE_URL ?>admin/order-detail?id=<?= $o['id'] ?>">Xem</a></td>
  </tr>
  <?php endforeach; ?>
  <?php if(empty($orders)): ?><tr><td colspan="6" class="empty-admin">Chưa có đơn hàng mới.</td></tr><?php endif; ?>
  </table>
</div>

