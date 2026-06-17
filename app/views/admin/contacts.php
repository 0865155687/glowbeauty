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


<style>
.consult-panel-final{overflow:hidden!important}
.consult-table-final{
    width:100%!important;
    table-layout:fixed!important;
    font-size:14px!important;
}
.consult-table-final th,
.consult-table-final td{
    padding:14px 12px!important;
    vertical-align:middle!important;
    word-break:break-word!important;
    overflow-wrap:anywhere!important;
}
.consult-table-final th:nth-child(1),.consult-table-final td:nth-child(1){width:58px!important;text-align:center}
.consult-table-final th:nth-child(2),.consult-table-final td:nth-child(2){width:210px!important}
.consult-table-final th:nth-child(3),.consult-table-final td:nth-child(3){width:145px!important}
.consult-table-final th:nth-child(5),.consult-table-final td:nth-child(5){width:210px!important;text-align:center!important}
.consult-table-final th:nth-child(6),.consult-table-final td:nth-child(6){width:150px!important}
.consult-table-final th:nth-child(7),.consult-table-final td:nth-child(7){width:105px!important;text-align:center}
.consult-message-final{
    max-width:100%!important;
    line-height:1.45!important;
    display:-webkit-box!important;
    -webkit-line-clamp:2!important;
    -webkit-box-orient:vertical!important;
    overflow:hidden!important;
}
.customer-cell-final b,
.customer-cell-final span{
    display:block!important;
    white-space:normal!important;
}
.consult-table-final .status-pill-select{
    width:100%!important;
    max-width:100%!important;
    height:38px!important;
    padding:0!important;
    border:0!important;
    background:transparent!important;
    box-shadow:none!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    gap:10px!important;
    position:relative!important;
    overflow:visible!important;
}
.consult-table-final .status-label-final{
    min-width:118px!important;
    height:34px!important;
    padding:0 18px!important;
    border-radius:999px!important;
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    white-space:nowrap!important;
    font-size:14px!important;
    font-weight:800!important;
}
.consult-table-final .status-dots-final{
    width:34px!important;height:34px!important;min-width:34px!important;border-radius:999px!important;
    border:1px solid #efcdbd!important;background:#fffaf7!important;color:#b85b24!important;
    display:inline-flex!important;align-items:center!important;justify-content:center!important;
    font-size:15px!important;font-weight:900!important;line-height:1!important;padding-bottom:5px!important;
}
.consult-table-final .status-pill-select select{position:absolute!important;right:calc(50% - 84px)!important;top:2px!important;width:34px!important;height:34px!important;opacity:0!important;cursor:pointer!important;z-index:5!important}
.delete-soft-v36{padding:10px 16px!important;font-size:13px!important}
@media(max-width:900px){
    .consult-panel-final{overflow-x:auto!important}
    .consult-table-final{min-width:920px!important}
}
</style>
