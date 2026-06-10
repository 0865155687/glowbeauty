<?php
function gbConsultStatusClass($status) {
    if ($status === 'Đã tư vấn') return 'is-success';
    if ($status === 'Đang tư vấn') return 'is-warning';
    if ($status === 'Huỷ') return 'is-cancel';
    return 'is-pending';
}
?>

<section class="admin-hero-v36 admin-contacts-hero-clean admin-hero-tight-clean">
  <div>
    <span class="admin-kicker-v35">GlowBeauty Admin</span>
    <h1>Khách hàng đăng ký tư vấn</h1>
    <p>Theo dõi yêu cầu tư vấn và cập nhật tình trạng chăm sóc khách hàng.</p>
  </div>
</section>

<div class="admin-tabs-v35 admin-tabs-v36 admin-one-tab-clean">
  <a class="active" href="<?= BASE_URL ?>admin/contacts">💬 Tư vấn</a>
</div>

<div class="admin-panel-v35 admin-table-panel-final consult-panel-final">
  <div class="admin-panel-head-v35"><h2>💬 Danh sách yêu cầu tư vấn</h2></div>
  <table class="admin-table admin-table-v35 consult-table-final">
    <thead>
      <tr>
        <th>Mã</th>
        <th>Khách hàng</th>
        <th>Nhu cầu</th>
        <th>Nội dung</th>
        <th>Trạng thái</th>
        <th>Ngày gửi</th>
        <th>Xoá</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($contacts)): ?>
        <tr><td colspan="7" class="empty-admin">Chưa có khách hàng đăng ký tư vấn.</td></tr>
      <?php endif; ?>

      <?php foreach($contacts as $c): ?>
        <tr>
          <td class="cell-center">#<?= (int)$c['id'] ?></td>
          <td class="customer-cell-final">
            <b><?= htmlspecialchars($c['customer_name']) ?></b>
            <span>☎ <?= htmlspecialchars($c['phone']) ?></span>
          </td>
          <td><?= htmlspecialchars($c['need']) ?></td>
          <td class="consult-message-final"><?= nl2br(htmlspecialchars($c['message'])) ?></td>
          <td>
            <form method="post" action="<?= BASE_URL ?>admin/contact-update" class="status-pill-select <?= gbConsultStatusClass($c['status'] ?? 'Mới gửi') ?>">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <span class="status-label-final"><?= htmlspecialchars($c['status']) ?></span>
              <span class="status-dots-final">...</span>
              <select name="status" onchange="this.form.submit()" aria-label="Cập nhật trạng thái tư vấn">
                <option value="" selected disabled>...</option>
                <?php foreach($statuses as $st): ?>
                  <option value="<?= htmlspecialchars($st) ?>"><?= htmlspecialchars($st) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td><?= htmlspecialchars($c['created_at']) ?></td>
          <td class="cell-center"><a class="delete-soft-v36" onclick="return confirm('Xoá yêu cầu tư vấn này?')" href="<?= BASE_URL ?>admin/contact-delete?id=<?= (int)$c['id'] ?>">Xoá</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
