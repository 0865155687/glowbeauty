<div class="admin-title"><div><h1>Quản lý khách hàng</h1><p>Thêm, sửa, xoá tài khoản khách hàng đăng ký trên website.</p></div><a class="btn" href="<?= BASE_URL ?>admin/customer-form">+ Thêm khách hàng</a></div>
<table class="admin-table">
  <tr><th>Mã</th><th>Khách hàng</th><th>Email</th><th>Vai trò</th><th>Ngày tạo</th><th>Thao tác</th></tr>
  <?php foreach($customers as $c): ?>
  <tr>
    <td>#<?= $c['id'] ?></td>
    <td><b><?= htmlspecialchars($c['name']) ?></b></td>
    <td><?= htmlspecialchars($c['email']) ?></td>
    <td><?= htmlspecialchars($c['role']) ?></td>
    <td><?= $c['created_at'] ?></td>
    <td class="actions"><a href="<?= BASE_URL ?>admin/customer-form?id=<?= $c['id'] ?>">Sửa</a><a class="danger" onclick="return confirm('Xoá khách hàng này?')" href="<?= BASE_URL ?>admin/customer-delete?id=<?= $c['id'] ?>">Xoá</a></td>
  </tr>
  <?php endforeach; ?>
</table>
