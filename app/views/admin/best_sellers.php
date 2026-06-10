<section class="admin-hero-v35 admin-hero-tight-clean">
  <div><span class="admin-kicker-v35">Báo cáo bán hàng</span><h1>Thống kê sản phẩm bán chạy</h1></div>
</section>
<div class="admin-panel-v35">
  <div class="admin-panel-head-v35"><h2>🏆 Top sản phẩm bán chạy</h2></div>
  <table class="admin-table admin-table-v35">
    <tr><th>Ảnh</th><th>Sản phẩm</th><th>Danh mục</th><th>Đã bán</th><th>Doanh thu</th><th>Tồn kho</th></tr>
    <?php foreach($products as $p): ?>
    <tr>
      <td><img class="admin-mini-img" src="<?= gb_image_url($p['image'] ?? '') ?>" alt=""></td>
      <td><b><?= htmlspecialchars($p['name']) ?></b></td>
      <td><?= htmlspecialchars($p['category']) ?></td>
      <td><b><?= (int)$p['sold_qty'] ?></b></td>
      <td><?= number_format($p['revenue'],0,',','.') ?>đ</td>
      <td><?= (int)$p['stock'] ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
