<section class="admin-hero-v35">
  <div>
    <span class="admin-kicker-v35">Kho hàng</span>
    <h1>Sản phẩm sắp hết / hết hàng</h1>
    <p>Phân biệt rõ sản phẩm đã hết hàng và sản phẩm sắp hết để nhập thêm kịp thời.</p>
  </div>
  <a class="btn admin-secondary-v34" href="<?= BASE_URL ?>admin/dashboard">← Dashboard</a>
</section>

<div class="admin-tabs-v35">
  <a href="<?= BASE_URL ?>admin/dashboard">📊 Dashboard</a>
  <a href="<?= BASE_URL ?>admin/products">💄 Sản phẩm</a>
  <a class="active" href="<?= BASE_URL ?>admin/low-stock">⚠️ Sắp hết / Hết hàng</a>
</div>

<?php
$outCount = 0;
$lowCount = 0;
foreach ($products as $pp) {
    if ((int)$pp['stock'] <= 0) $outCount++;
    elseif ((int)$pp['stock'] <= 3) $lowCount++;
}
?>

<div class="stock-summary-v40">
  <div class="stock-summary-card-v40 stock-summary-red-v40">
    <span>⛔ Hết hàng</span>
    <b><?= $outCount ?></b>
    <small>Kho bằng 0, cần nhập ngay</small>
  </div>
  <div class="stock-summary-card-v40 stock-summary-orange-v40">
    <span>⚠️ Sắp hết hàng</span>
    <b><?= $lowCount ?></b>
    <small>Kho còn từ 1 đến 3</small>
  </div>
</div>

<div class="admin-panel-v35">
<table class="admin-table admin-table-v35 product-admin">
  <tr><th>Ảnh</th><th>Sản phẩm</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th><th>Sửa</th></tr>
  <?php if(empty($products)): ?><tr><td colspan="6" class="empty-admin">Chưa có sản phẩm nào sắp hết hàng.</td></tr><?php endif; ?>
  <?php foreach($products as $p): ?>
    <?php
      $stock = (int)$p['stock'];
      $isOut = $stock <= 0;
      $rowClass = $isOut ? 'stock-row-out-v40' : 'stock-row-low-v40';
      $badgeClass = $isOut ? 'stock-badge-out-v40' : 'stock-badge-low-v40';
      $statusText = $isOut ? 'Hết hàng' : 'Sắp hết hàng';
    ?>
    <tr class="<?= $rowClass ?>">
      <td><img src="<?= BASE_URL ?>public/assets/images/<?= htmlspecialchars($p['image']) ?>" alt=""></td>
      <td><b><?= htmlspecialchars($p['name']) ?></b><small><?= htmlspecialchars($p['brand']) ?></small></td>
      <td><?= htmlspecialchars($p['category']) ?></td>
      <td><b><?= number_format($p['price'],0,',','.') ?>đ</b></td>
      <td>
        <span class="<?= $badgeClass ?>"><?= $statusText ?></span>
        <span class="stock-number-v40">Kho: <?= $stock ?></span>
      </td>
      <td><a class="order-detail-btn" href="<?= BASE_URL ?>admin/product-form?id=<?= $p['id'] ?>">Sửa kho</a></td>
    </tr>
  <?php endforeach; ?>
</table>
</div>
