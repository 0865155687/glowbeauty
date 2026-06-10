<div class="admin-title">
  <div>
    <h1><?= $c?'Sửa khách hàng':'Thêm khách hàng' ?></h1>
    <p>Nhập thông tin tài khoản khách hàng.</p>
  </div>
  <a class="outline admin-back" href="<?= BASE_URL ?>admin/customers">← Quay lại</a>
</div>

<?php if(!empty($error)): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="admin-form">
  <input type="hidden" name="id" value="<?= htmlspecialchars($c['id'] ?? '') ?>">

  <div class="form-grid">
    <div class="field">
      <label>Họ và tên khách hàng</label>
      <input name="name" required value="<?= htmlspecialchars($c['name'] ?? '') ?>" placeholder="Nhập họ tên khách hàng" data-name-input>
    </div>

    <div class="field">
      <label>Email đăng nhập</label>
      <input type="email" name="email" required value="<?= htmlspecialchars($c['email'] ?? '') ?>" placeholder="khachhang@email.com">
    </div>

    <div class="field full">
      <label>Mật khẩu</label>
      <input type="password" name="password" placeholder="<?= !empty($c['id'])?'Để trống nếu không đổi mật khẩu':'Mật khẩu đăng nhập' ?>">
    </div>
  </div>

  <button class="btn">Lưu khách hàng</button>
</form>
