<section class="admin-hero-v35">
  <div>
    <span class="admin-kicker-v35">Kho hàng</span>
    <h1>Sản phẩm sắp hết hàng</h1>
    <p>Hiển thị các sản phẩm có tồn kho từ 3 trở xuống để nhập thêm kịp thời.</p>
  </div>
  <a class="btn admin-secondary-v34" href="<?= BASE_URL ?>admin/dashboard">← Dashboard</a>
</section>

<div class="admin-tabs-v35">
  <a href="<?= BASE_URL ?>admin/dashboard">📊 Dashboard</a>
  <a href="<?= BASE_URL ?>admin/products">💄 Sản phẩm</a>
  <a class="active" href="<?= BASE_URL ?>admin/low-stock">⚠️ Sắp hết hàng</a>
</div>

<div class="admin-panel-v35">
<table class="admin-table admin-table-v35 product-admin">
  <tr><th>Ảnh</th><th>Sản phẩm</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th><th>Sửa</th></tr>
  <?php if(empty($products)): ?><tr><td colspan="6" class="empty-admin">Chưa có sản phẩm nào sắp hết hàng.</td></tr><?php endif; ?>
  <?php foreach($products as $p): ?>
    <tr>
      <td><img src="<?= BASE_URL ?>public/assets/images/<?= htmlspecialchars($p['image']) ?>" alt=""></td>
      <td><b><?= htmlspecialchars($p['name']) ?></b><small><?= htmlspecialchars($p['brand']) ?></small></td>
      <td><?= htmlspecialchars($p['category']) ?></td>
      <td><b><?= number_format($p['price'],0,',','.') ?>đ</b></td>
      <td><span class="stock-warn-v35"><?= (int)$p['stock'] ?></span></td>
      <td><a class="order-detail-btn" href="<?= BASE_URL ?>admin/product-form?id=<?= $p['id'] ?>">Sửa kho</a></td>
    </tr>
  <?php endforeach; ?>
</table>
</div>
