<div class="admin-title admin-title-products">
  <div>
    <h1>Quản lý sản phẩm</h1>
    <p>Danh sách sản phẩm, tồn kho, giá bán và trạng thái hiển thị.</p>
  </div>
  <a class="btn admin-primary-v34 admin-link-fix" href="<?= BASE_URL ?>admin/product-form">+ Thêm sản phẩm</a>
</div>

<div class="admin-search-box">
  <div>
    <label for="adminProductSearch">Tìm kiếm sản phẩm</label>
    <p>Tìm theo tên, mã sản phẩm, thương hiệu hoặc danh mục.</p>
  </div>
  <input id="adminProductSearch" type="search" placeholder="🔎 Nhập từ khóa tìm kiếm">
</div>

<table class="admin-table product-admin" id="adminProductTable">
  <thead>
    <tr><th>Ảnh</th><th>Tên sản phẩm</th><th>Thương hiệu</th><th>Danh mục</th><th>Giá</th><th>Kho</th><th>Trạng thái</th><th>Thao tác</th></tr>
  </thead>
  <tbody>
    <?php foreach($products as $p): ?>
      <?php
        $stock = (int)$p['stock'];
        $stockRowClass = $stock <= 0 ? 'stock-row-out-v40' : ($stock <= 3 ? 'stock-row-low-v40' : '');
      ?>
      <tr class="<?= $stockRowClass ?>" data-search="<?= htmlspecialchars(mb_strtolower($p['name'].' '.$p['category'].' '.$p['brand'].' '.$p['benefit'], 'UTF-8')) ?>">
        <td><img src="<?= gb_image_url($p['image'] ?? '') ?>" alt="<?= htmlspecialchars($p['name'] ?? 'Sản phẩm') ?>" onerror="this.style.display='none'"></td>
        <td><b><?= htmlspecialchars($p['name']) ?></b><small><?= htmlspecialchars(excerpt($p['benefit'],70)) ?></small></td>
        <td><?= htmlspecialchars($p['brand']) ?></td>
        <td><?= htmlspecialchars($p['category']) ?></td>
        <td class="price-cell"><?= number_format((float)$p['price'],0,',','.') ?>đ</td>
        <td><span class="stock-number-v40">Kho: <?= $stock ?></span></td>
        <td><?= $p['status']?'Hiển thị':'Ẩn' ?></td>
        <td class="actions"><a class="edit" href="<?= BASE_URL ?>admin/product-form?id=<?= $p['id'] ?>">Sửa</a><a class="delete" onclick="return confirm('Xóa sản phẩm này?')" href="<?= BASE_URL ?>admin/product-delete?id=<?= $p['id'] ?>">Xóa</a></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<p id="adminSearchEmpty" class="admin-empty-note" style="display:none">Không tìm thấy sản phẩm phù hợp.</p>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const input = document.getElementById('adminProductSearch');
  const rows = Array.from(document.querySelectorAll('#adminProductTable tbody tr'));
  const empty = document.getElementById('adminSearchEmpty');
  if(!input) return;
  input.addEventListener('input', function(){
    const keyword = this.value.trim().toLowerCase();
    let count = 0;
    rows.forEach(row => {
      const text = (row.dataset.search || '').toLowerCase();
      const show = !keyword || text.includes(keyword);
      row.style.display = show ? '' : 'none';
      if(show) count++;
    });
    empty.style.display = count ? 'none' : 'block';
  });
});
</script>
