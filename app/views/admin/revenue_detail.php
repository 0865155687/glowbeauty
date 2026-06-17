<?php
function gbRevenueStatusClass($status) {
    if ($status === 'Hoàn thành') return 'status-done';
    if ($status === 'Đã hủy') return 'status-cancel';
    if ($status === 'Đang giao') return 'status-shipping';
    if ($status === 'Đã xác nhận') return 'status-confirmed';
    return 'status-pending';
}
?>
<section class="admin-hero-v35 revenue-head-v35 admin-hero-tight-clean">
  <div>
    <span class="admin-kicker-v35">Báo cáo bán hàng</span>
    <h1><?= htmlspecialchars($heading) ?></h1>
    <?php if(($mode ?? '')==='month'): ?><p>Tháng <?= (int)$month ?>/<?= (int)$year ?> · Doanh thu chỉ tính đơn đã thanh toán.</p><?php endif; ?>
    <?php if(($mode ?? '')==='today'): ?><p>Ngày <?= date('d/m/Y') ?> · Doanh thu chỉ tính đơn đã thanh toán.</p><?php endif; ?>
    <?php if(($mode ?? '')==='all'): ?><p>Năm <?= (int)$year ?> · Tổng hợp doanh thu theo đơn đã thanh toán.</p><?php endif; ?>
  </div>
  <div class="revenue-actions-v36">
    <a class="btn admin-export-v36" href="<?= BASE_URL ?>admin/revenue-export-excel?mode=<?= urlencode($mode ?? 'month') ?><?= (($mode ?? '')==='month') ? '&month='.(int)$month.'&year='.(int)$year : ((($mode ?? '')==='all') ? '&year='.(int)$year : '') ?>">📥 Xuất Excel</a>
    <a class="btn admin-secondary-v34" href="<?= BASE_URL ?>admin/dashboard">← Dashboard</a>
  </div>
</section>

<div class="admin-tabs-v35">
  <a class="<?= ($mode ?? '')==='today'?'active':'' ?>" href="<?= BASE_URL ?>admin/revenue-today">💰 Hôm nay</a>
  <a class="<?= ($mode ?? '')==='month'?'active':'' ?>" href="<?= BASE_URL ?>admin/revenue-month">📈 Theo tháng</a>
  <a class="<?= ($mode ?? '')==='all'?'active':'' ?>" href="<?= BASE_URL ?>admin/revenue-all?year=<?= (int)($year ?? date('Y')) ?>">📚 Tất cả tháng</a>
  <a href="<?= BASE_URL ?>admin/low-stock">⚠️ Sắp hết hàng</a>
  <a href="<?= BASE_URL ?>admin/orders">🧾 Đơn hàng</a>
</div>

<?php if(($mode ?? '')==='month' || ($mode ?? '')==='all'): ?>
<div class="admin-panel-v35 month-chart-panel-v35">
  <div class="admin-panel-head-v35"><h2>📊 Biểu đồ doanh thu năm <?= (int)$year ?></h2></div>
  <div class="month-chart-v35">
    <?php $max=max(array_column($chart,'revenue')); if($max<=0) $max=1; foreach($chart as $c): $h=max(8, round(($c['revenue']/$max)*170)); ?>
      <a class="bar-item-v35 <?= (($mode ?? '')==='month' && (int)$c['month']===(int)$month)?'active':'' ?>" href="<?= BASE_URL ?>admin/revenue-month?month=<?= $c['month'] ?>&year=<?= (int)$year ?>" title="Tháng <?= $c['month'] ?>: <?= number_format($c['revenue'],0,',','.') ?>đ">
        <span class="bar-v35" style="height:<?= $h ?>px"></span>
        <b>T<?= $c['month'] ?></b>
        <small><?= number_format($c['revenue'],0,',','.') ?>đ</small>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<?php if(($mode ?? '')==='month'): ?>
<div class="admin-tabs-v35 month-tabs-v35">
  <?php foreach($months as $m): ?>
    <a class="<?= ((int)$m['m']===(int)$month && (int)$m['y']===(int)$year)?'active':'' ?>" href="<?= BASE_URL ?>admin/revenue-month?month=<?= (int)$m['m'] ?>&year=<?= (int)$m['y'] ?>">Tháng <?= (int)$m['m'] ?>/<?= (int)$m['y'] ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="revenue-summary-box-v35"><span>Doanh thu đã thanh toán</span><b><?= number_format($total,0,',','.') ?>đ</b><small><?= count($orders) ?> đơn đã ghi nhận</small></div>
<div class="revenue-table-panel-final"><table class="admin-table admin-table-v35 order-admin-table revenue-table-final">
  <tr><th>Mã</th><th>Khách hàng</th><th>Địa chỉ</th><th>Tổng tiền</th><th>Trạng thái</th><th>Thanh toán</th><th>Ngày tạo</th><th>Chi tiết</th></tr>
  <?php if(empty($orders)): ?><tr><td colspan="8" class="empty-admin">Chưa có đơn hàng phù hợp.</td></tr><?php endif; ?>
  <?php foreach($orders as $o): ?>
  <tr>
    <td>#<?= $o['id'] ?></td>
    <td class="customer-cell-final"><b><?= htmlspecialchars($o['customer_name']) ?></b><span>☎ <?= htmlspecialchars($o['phone']) ?></span></td>
    <td><?= htmlspecialchars($o['address']) ?></td>
    <td><b><?= number_format($o['total'],0,',','.') ?>đ</b></td>
    <td><span class="status-pill <?= gbRevenueStatusClass($o['status'] ?? 'Chờ xác nhận') ?>"><?= htmlspecialchars($o['status']) ?></span></td>
    <td><span class="payment-pill <?= (($o['payment_status'] ?? '') === 'Đã thanh toán') ? 'paid' : 'unpaid' ?>"><?= htmlspecialchars(in_array(($o['payment_status'] ?? ''), ['Chưa thanh toán','Đã thanh toán'], true) ? $o['payment_status'] : 'Chưa thanh toán') ?></span></td>
    <td class="revenue-date-cell"><?php $dt = !empty($o['created_at']) ? strtotime($o['created_at']) : false; ?><?php if($dt): ?><span><?= date('Y-m-d', $dt) ?></span><small><?= date('H:i:s', $dt) ?></small><?php else: ?>-<?php endif; ?></td>
    <td><a class="order-detail-btn" href="<?= BASE_URL ?>admin/order-detail?id=<?= $o['id'] ?>">Xem chi tiết</a></td>
  </tr>
  <?php endforeach; ?>
</table></div>
