<?php
function gbOrderStatusClass($status) {
    if ($status === 'Hoàn thành') return 'is-success';
    if ($status === 'Đã hủy') return 'is-cancel';
    if ($status === 'Đang giao') return 'is-info';
    if ($status === 'Đã xác nhận') return 'is-warning';
    return 'is-pending';
}
function gbPaymentClass($status) {
    return ($status === 'Đã thanh toán') ? 'is-paid' : 'is-unpaid';
}
function gbCleanPaymentStatus($status) {
    return in_array($status, ['Chưa thanh toán','Đã thanh toán'], true) ? $status : 'Chưa thanh toán';
}
$statusLabels = ['Chờ xác nhận'=>'Chờ xác nhận','Đã xác nhận'=>'Đã xác nhận','Đang giao'=>'Đang giao','Hoàn thành'=>'Hoàn thành','Đã hủy'=>'Đã hủy'];
$paymentLabels = ['Chưa thanh toán'=>'Chưa thanh toán','Đã thanh toán'=>'Đã thanh toán'];
?>

<section class="admin-hero-v34 compact admin-hero-tight-clean">
    <div>
        <span class="admin-kicker-v34">Order manager</span>
        <h1>Quản lý đơn hàng</h1>
        <p>Theo dõi đơn hàng, cập nhật giao hàng và thanh toán.</p>
    </div>
    <a class="btn admin-primary-v34 admin-link-fix" href="<?= BASE_URL ?>admin/order-create">+ Thêm đơn hàng</a>
</section>

<div class="order-tools-sample">
    <div class="admin-panel-v34 order-search-card-sample">
        <h3>Tìm kiếm đơn hàng</h3>
        <div class="order-search-box-sample">
            <span>⌕</span>
            <input class="order-search-input-final" type="text" id="orderSearchInput" placeholder="Nhập mã đơn, tên khách, số điện thoại hoặc địa chỉ" autocomplete="off">
        </div>
    </div>

    <div class="admin-panel-v34 order-filter-card-sample">
        <h3>Trạng thái đơn hàng</h3>
        <div class="status-filter-list-sample">
            <button type="button" class="status-filter-btn all active" data-filter="">▣ Tất cả</button>
            <button type="button" class="status-filter-btn pending" data-filter="chờ xác nhận">☆ Chờ xác nhận</button>
            <button type="button" class="status-filter-btn blue" data-filter="đang giao">☆ Đang giao</button>
            <button type="button" class="status-filter-btn green" data-filter="hoàn thành">✓ Hoàn thành</button>
            <button type="button" class="status-filter-btn red" data-filter="đã hủy">✕ Đã hủy</button>
        </div>
    </div>
</div>

<div class="admin-panel-v34 admin-table-panel-final">
    <table class="admin-table admin-table-v34 order-table-final">
        <thead>
            <tr>
                <th>Mã</th>
                <th>Khách hàng</th>
                <th>Địa chỉ</th>
                <th>Tổng tiền</th>
                <th>Đơn hàng</th>
                <th>Thanh toán</th>
                <th>Chi tiết</th>
                <th>Xoá</th>
            </tr>
        </thead>
        <tbody id="ordersTableBody">
            <?php foreach ($orders as $o): ?>
                <?php
                    $currentOrderStatus = $o['status'] ?? 'Chờ xác nhận';
                    $currentPayment = gbCleanPaymentStatus($o['payment_status'] ?? 'Chưa thanh toán');
                    $searchText = mb_strtolower(trim('#'.$o['id'].' '.($o['customer_name'] ?? '').' '.($o['phone'] ?? '').' '.($o['address'] ?? '').' '.$currentOrderStatus.' '.$currentPayment), 'UTF-8');
                    $orderStatusKey = mb_strtolower($currentOrderStatus, 'UTF-8');
                ?>
                <tr data-search="<?= htmlspecialchars($searchText, ENT_QUOTES, 'UTF-8') ?>" data-order-status="<?= htmlspecialchars($orderStatusKey, ENT_QUOTES, 'UTF-8') ?>">
                    <td class="cell-center">#<?= (int)$o['id'] ?></td>
                    <td class="customer-cell-final">
                        <b><?= htmlspecialchars($o['customer_name']) ?></b>
                        <span>☎ <?= htmlspecialchars($o['phone']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($o['address']) ?></td>
                    <td class="money-cell-final"><b><?= number_format($o['total'], 0, ',', '.') ?>đ</b></td>
                    <td>
                        <form class="status-pill-select <?= gbOrderStatusClass($currentOrderStatus) ?>" method="post" action="<?= BASE_URL ?>admin/order-update">
                            <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                            <input type="hidden" name="_scroll_y" value="0" data-scroll-y>
                            <span class="status-label-final"><?= htmlspecialchars($currentOrderStatus) ?></span>
                            <span class="status-dots-final">...</span>
                            <select name="status" aria-label="Cập nhật trạng thái đơn" onchange="gbPreserveScrollAndSubmit(this.form)">
                                <option value="" selected disabled>...</option>
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($statusLabels[$s] ?? $s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td>
                        <form class="status-pill-select <?= gbPaymentClass($currentPayment) ?>" method="post" action="<?= BASE_URL ?>admin/order-update">
                            <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                            <input type="hidden" name="_scroll_y" value="0" data-scroll-y>
                            <input type="hidden" name="status" value="<?= htmlspecialchars($currentOrderStatus, ENT_QUOTES, 'UTF-8') ?>">
                            <span class="status-label-final"><?= htmlspecialchars($currentPayment) ?></span>
                            <span class="status-dots-final">...</span>
                            <select name="payment_status" aria-label="Cập nhật thanh toán" onchange="gbPreserveScrollAndSubmit(this.form)">
                                <option value="" selected disabled>...</option>
                                <?php foreach ($paymentStatuses as $ps): ?>
                                    <option value="<?= htmlspecialchars($ps) ?>"><?= htmlspecialchars($paymentLabels[$ps] ?? $ps) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td class="cell-center"><a class="order-detail-btn" href="<?= BASE_URL ?>admin/order-detail?id=<?= (int)$o['id'] ?>">👁️ Xem</a></td>
                    <td class="cell-center"><a class="delete-btn-v62" onclick="return confirm('Xoá đơn hàng này?')" href="<?= BASE_URL ?>admin/order-delete?id=<?= (int)$o['id'] ?>" title="Xoá đơn hàng">🗑️</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
                <tr><td colspan="8" class="empty-admin">Chưa có đơn hàng nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function gbPreserveScrollAndSubmit(form){
    var field = form ? form.querySelector('[data-scroll-y]') : null;
    if(field){ field.value = String(window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0); }
    form.submit();
}
document.addEventListener('DOMContentLoaded', function(){
    const input = document.getElementById('orderSearchInput');
    const body = document.getElementById('ordersTableBody');
    if (!input || !body) return;
    function normalize(str){return (str||'').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/đ/g,'d');}
    const filterButtons = Array.from(document.querySelectorAll('.status-filter-btn'));
    let currentStatus = '';

    function applyOrderFilters(){
        const key = normalize(input.value.trim());
        const statusKey = normalize(currentStatus);
        body.querySelectorAll('tr[data-search]').forEach(row=>{
            const text = normalize(row.dataset.search || '');
            const orderStatus = normalize(row.dataset.orderStatus || '');
            const okSearch = !key || text.includes(key);
            const okStatus = !statusKey || orderStatus === statusKey;
            row.style.display = (okSearch && okStatus) ? '' : 'none';
        });
    }

    input.addEventListener('input', applyOrderFilters);
    filterButtons.forEach(btn=>{
        btn.addEventListener('click', function(){
            filterButtons.forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');
            currentStatus = btn.dataset.filter || '';
            applyOrderFilters();
        });
    });
});
</script>
