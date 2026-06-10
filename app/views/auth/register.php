<section class="container form-page">
  <form method="post" class="pro-form auth" autocomplete="off">
    <h1>Đăng ký tài khoản</h1>

    <?php if(!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="field">
      <label>Họ và tên</label>
      <input name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" autocomplete="off" data-name-input>
    </div>

    <div class="field">
      <label>Email</label>
      <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autocomplete="off">
    </div>

    <div class="field">
      <label>Mật khẩu</label>
      <input type="password" name="password" required value="" autocomplete="new-password">
    </div>

    <button class="btn">Đăng ký</button>

    <div class="auth-extra-v39">
      <p>Đã có tài khoản?</p>
      <a href="<?= BASE_URL ?>login">Đăng nhập</a>
    </div>
  </form>
</section>
