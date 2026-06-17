<?php
$orders = $orders ?? [];
$itemsByOrder = $itemsByOrder ?? [];
$reviewsByOrder = $reviewsByOrder ?? [];
$updatedOrders = $updatedOrders ?? [];
$statusCounts = [
    'Chờ xác nhận' => 0,
    'Đã xác nhận' => 0,
    'Đang giao' => 0,
    'Hoàn thành' => 0,
    'Đã hủy' => 0,
];
$needReviewCount = 0;
$updatedStatusCounts = [
    'Chờ xác nhận' => 0,
    'Đã xác nhận' => 0,
    'Đang giao' => 0,
    'Hoàn thành' => 0,
    'Đã hủy' => 0,
];
foreach ($updatedOrders as $uo) {
    $us = $uo['status'] ?? '';
    if (isset($updatedStatusCounts[$us])) $updatedStatusCounts[$us]++;
}
foreach ($orders as $o) {
    $oid = (int)($o['id'] ?? 0);
    $status = $o['status'] ?? 'Chờ xác nhận';
    if (isset($statusCounts[$status])) $statusCounts[$status]++;
    if ($status === 'Hoàn thành') {
        $items = $itemsByOrder[$oid] ?? [];
        $reviews = $reviewsByOrder[$oid] ?? [];
        foreach ($items as $it) {
            $pid = (int)($it['product_id'] ?? 0);
            if ($pid > 0 && empty($reviews[$pid])) $needReviewCount++;
        }
    }
}
function gb_order_status_class($status) {
    return 'st-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower(strtr($status, ['Đ'=>'D','đ'=>'d','á'=>'a','à'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a','ă'=>'a','ắ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a','ặ'=>'a','â'=>'a','ấ'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a','é'=>'e','è'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e','ê'=>'e','ế'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e','í'=>'i','ì'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i','ó'=>'o','ò'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o','ô'=>'o','ố'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o','ơ'=>'o','ớ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o','ú'=>'u','ù'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u','ư'=>'u','ứ'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u','ý'=>'y','ỳ'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y'])));
}
?>
<section class="container gb-account-orders-pro">
    <div class="gb-orders-hero-pro gb-orders-hero-compact">
        <div class="gb-orders-hero-copy">
            <span class="gb-kicker">Tài khoản GlowBeauty</span>
            <h1>Đơn hàng của tôi</h1>
            <p>Theo dõi đơn mua, vận chuyển, trạng thái và đánh giá sau khi nhận hàng.</p>
        </div>
    </div>

    <?php if(!empty($_SESSION['review_success']) || !empty($_GET['reviewed'])): ?>
        <div class="gb-customer-toast-success show" id="gbReviewSuccessToast">
            <strong>✅ Đánh giá thành công</strong>
            <span><?= htmlspecialchars($_SESSION['review_success'] ?? 'Cảm ơn bạn đã phản hồi cho GlowBeauty. Đánh giá đã được gửi về admin và cập nhật lên sản phẩm.') ?></span>
        </div>
        <?php unset($_SESSION['review_success']); ?>
    <?php endif; ?>

    <?php if(empty($orders)): ?>
        <div class="gb-orders-empty-pro">
            <div>🛍️</div>
            <h2>Bạn chưa có đơn hàng nào</h2>
            <p>Hãy chọn sản phẩm yêu thích và đặt hàng tại GlowBeauty nhé.</p>
            <a class="btn" href="<?= BASE_URL ?>products">Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <div class="gb-order-status-board-pro">
            <div class="gb-status-board-head-pro">
                <b>Đơn mua</b>
                <a href="#" data-filter="all">Xem lịch sử mua hàng ›</a>
            </div>
            <div class="gb-order-tabs-pro gb-order-tabs-icons-pro">
                <a href="#" data-filter="Chờ xác nhận" data-badge-type="notify" class="active">
                    <i>💳</i><span>Chờ xác nhận</span><b><?= (int)$updatedStatusCounts['Chờ xác nhận'] ?></b>
                </a>
                <a href="#" data-filter="Đã xác nhận" data-badge-type="notify">
                    <i>📦</i><span>Đã xác nhận</span><b><?= (int)$updatedStatusCounts['Đã xác nhận'] ?></b>
                </a>
                <a href="#" data-filter="Đang giao" data-badge-type="notify">
                    <i>🚚</i><span>Chờ giao hàng</span><b><?= (int)$updatedStatusCounts['Đang giao'] ?></b>
                </a>
                <a href="#" data-filter="Hoàn thành" data-badge-type="notify">
                    <i>✅</i><span>Đã giao</span><b><?= (int)$updatedStatusCounts['Hoàn thành'] ?></b>
                </a>
                <a href="#" data-filter="need-review" data-badge-type="review">
                    <i>⭐</i><span>Đánh giá</span><b><?= (int)$needReviewCount ?></b>
                </a>
            </div>
        </div>

        <div id="gb-filter-empty" class="gb-filter-empty-pro" style="display:none;">
            <div>📦</div>
            <h3>Đơn đang trống</h3>
            <p>Hiện chưa có đơn nào ở mục này.</p>
        </div>

        <div class="gb-order-list-pro">
            <?php foreach($orders as $o):
                $oid=(int)$o['id'];
                $items=$itemsByOrder[$oid] ?? [];
                $reviews=$reviewsByOrder[$oid] ?? [];
                $status=$o['status'] ?? 'Chờ xác nhận';
                $unreviewed = 0;
                if ($status === 'Hoàn thành') {
                    foreach($items as $it) {
                        $pid=(int)($it['product_id'] ?? 0);
                        if($pid > 0 && empty($reviews[$pid])) $unreviewed++;
                    }
                }
            ?>
            <article class="gb-order-card-pro" data-status="<?= htmlspecialchars($status) ?>" data-need-review="<?= $unreviewed > 0 ? '1' : '0' ?>">
                <div class="gb-order-top-pro">
                    <div>
                        <span class="gb-order-code-pro">Đơn hàng #<?= $oid ?></span>
                        <h2><?= htmlspecialchars($items[0]['product_name'] ?? 'Đơn hàng GlowBeauty') ?><?= count($items) > 1 ? ' +' . (count($items)-1) . ' sản phẩm khác' : '' ?></h2>
                        <p>Ngày đặt: <?= htmlspecialchars($o['created_at'] ?? '') ?></p>
                    </div>
                    <span class="gb-status-pill-pro <?= gb_order_status_class($status) ?>"><?= htmlspecialchars($status === 'Hoàn thành' ? 'Đã giao' : $status) ?></span>
                </div>

                <div class="gb-order-body-pro">
                    <div class="gb-order-main-pro">
                        <div class="gb-section-title-pro">🛒 Sản phẩm trong đơn</div>
                        <div class="gb-order-products-pro">
                            <?php foreach($items as $it):
                                $img = $it['product_image'] ?? '';
                            ?>
                            <div class="gb-order-product-pro">
                                <div class="gb-product-thumb-pro">
                                    <?php if(!empty($img)): ?>
                                        <img src="<?= gb_image_url($img) ?>" alt="<?= htmlspecialchars($it['product_name'] ?? '') ?>">
                                    <?php else: ?>💄
<?php endif; ?>
                                </div>
                                <div class="gb-product-info-pro">
                                    <b><?= htmlspecialchars($it['product_name'] ?? '') ?></b>
                                    <span>Mã SP: <?= htmlspecialchars($it['product_code'] ?? ('SP'.$it['product_id'])) ?></span>
                                    <em>Số lượng: <?= (int)$it['quantity'] ?></em>
                                </div>
                                <strong><?= number_format((int)($it['price'] ?? 0),0,',','.') ?>đ</strong>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="gb-timeline-pro">
                            <div class="gb-section-title-pro">🚚 Tiến trình đơn hàng</div>
                            <div class="gb-timeline-steps-pro">
                                <?php $flow=['Chờ xác nhận'=>'Tạo đơn','Đã xác nhận'=>'Đã xác nhận','Đang giao'=>'Chờ giao hàng','Hoàn thành'=>'Đã giao']; $keys=array_keys($flow); foreach($flow as $key=>$label): ?>
                                    <?php $done = array_search($key,$keys) <= array_search($status,$keys) && $status !== 'Đã hủy'; ?>
                                    <span class="<?= $done ? 'done' : '' ?>"><i><?= $done ? '✓' : '•' ?></i><?= $label ?></span>
                                <?php endforeach; ?>
                                <?php if($status==='Đã hủy'): ?><span class="cancel"><i>×</i>Đã hủy</span>
<?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <aside class="gb-order-side-pro">
                        <div class="gb-info-box-pro">
                            <b>Người nhận</b>
                            <span><?= htmlspecialchars($o['customer_name'] ?? '') ?></span>
                            <small><?= htmlspecialchars($o['phone'] ?? '') ?></small>
                        </div>
                        <div class="gb-info-box-pro">
                            <b>Địa chỉ nhận hàng</b>
                            <span><?= htmlspecialchars($o['address'] ?? '') ?></span>
                        </div>
                        <div class="gb-pay-summary-pro">
                            <div><span>Thanh toán</span><b><?= htmlspecialchars($o['payment_status'] ?? 'Chưa thanh toán') ?></b></div>
                            <div><span>Phí ship</span><b><?= number_format((int)($o['shipping_fee'] ?? 0),0,',','.') ?>đ</b></div>
                            <div class="total"><span>Tổng thanh toán</span><strong><?= number_format((int)($o['total'] ?? 0),0,',','.') ?>đ</strong></div>
                        </div>
                    </aside>
                </div>

                <?php if(!empty($o['warranty_note'])): ?>
                    <div class="gb-warranty-pro"><b>🛡️ Cam kết bảo hành:</b> <?= htmlspecialchars($o['warranty_note']) ?></div>
<?php endif; ?>

                <?php if(($o['status'] ?? '') === 'Hoàn thành'): ?>
                    <div class="gb-review-zone-pro">
                        <div class="gb-section-title-pro">⭐ Đánh giá sản phẩm sau khi nhận hàng</div>
                        <?php foreach($items as $it):
                            $pid=(int)$it['product_id'];
                            $rv=$reviews[$pid] ?? null;
                            $img = $it['product_image'] ?? '';
                        ?>
                            <form method="post" action="<?= BASE_URL ?>account/review" enctype="multipart/form-data" class="gb-review-form-pro <?= $rv ? 'is-reviewed' : 'is-unreviewed' ?>" data-reviewed="<?= $rv ? '1' : '0' ?>">
                                <input type="hidden" name="order_id" value="<?= $oid ?>">
                                <input type="hidden" name="product_id" value="<?= $pid ?>">
                                <div class="gb-review-product-head-pro">
                                    <div class="gb-review-thumb-pro">
                                        <?php if(!empty($img)): ?>
                                            <img src="<?= gb_image_url($img) ?>" alt="<?= htmlspecialchars($it['product_name'] ?? '') ?>">
                                        <?php else: ?>💄
<?php endif; ?>
                                    </div>
                                    <div>
                                        <b><?= htmlspecialchars($it['product_name'] ?? '') ?></b>
                                        <span>Mã đơn #<?= $oid ?> • SL: <?= (int)($it['quantity'] ?? 1) ?></span>
                                        <?php if($rv): ?><em>Đã đánh giá</em><?php else: ?><em>Chưa đánh giá</em>
<?php endif; ?>
                                    </div>
                                </div>

                                <label class="gb-review-label-pro">Đánh giá sản phẩm</label>
                                <div class="gb-star-picker-pro">
                                    <?php for($i=5;$i>=1;$i--): ?>
                                        <input type="radio" id="star-<?= $oid ?>-<?= $pid ?>-<?= $i ?>" name="rating" value="<?= $i ?>" <?= ((int)($rv['rating'] ?? 5)==$i?'checked':'') ?>>
                                        <label for="star-<?= $oid ?>-<?= $pid ?>-<?= $i ?>">★</label>
                                    <?php endfor; ?>
                                </div>

                                <label class="gb-upload-box-pro">
                                    <input type="file" name="review_image" accept="image/*" class="gb-review-file-input">
                                    <span>📷</span>
                                    <b>Thêm hình ảnh sản phẩm</b>
                                    <small class="gb-file-name">Có thể bỏ qua nếu chưa có ảnh thực tế</small>
                                </label>
                                <?php if(!empty($rv['image'])): ?><input type="hidden" name="old_image" value="<?= htmlspecialchars($rv['image']) ?>">
<?php endif; ?>

                                <label class="gb-review-label-pro">Viết đánh giá</label>
                                <textarea name="comment" minlength="10" placeholder="Ví dụ: Sản phẩm đóng gói đẹp, màu lên nhẹ, chất phấn mịn. Hãy chia sẻ nhận xét của bạn nhé..." required><?= htmlspecialchars($rv['comment'] ?? '') ?></textarea>

                               <div class="gb-service-ratings-pro">
    <?php
      $serviceRows = [
        ['name'=>'seller_service', 'label'=>'Dịch vụ của người bán', 'value'=>(int)($rv['seller_service'] ?? 5)],
        ['name'=>'shipping_service', 'label'=>'Tốc độ giao hàng', 'value'=>(int)($rv['shipping_service'] ?? 5)],
        ['name'=>'package_service', 'label'=>'Đóng gói sản phẩm', 'value'=>(int)($rv['package_service'] ?? 5)],
      ];
      foreach($serviceRows as $sr):
        $val = max(1, min(5, (int)$sr['value']));
    ?>
    <div class="service-row">
        <span><?= htmlspecialchars($sr['label']) ?></span>
        <div class="rating-stars">
            <input type="hidden" name="<?= htmlspecialchars($sr['name']) ?>" value="<?= $val ?>">
            <?php for($si=1;$si<=5;$si++): ?>
                <i class="star <?= $si <= $val ? 'active' : '' ?>" data-value="<?= $si ?>">★</i>
            <?php endfor; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

                                <button class="btn gb-review-submit-pro" type="submit"><?= $rv ? 'Cập nhật đánh giá' : 'Gửi đánh giá' ?></button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
<?php endif; ?>

<style>
.gb-service-ratings-pro{
    display:grid;
    gap:12px;
    margin:16px 0;
    padding:14px;
    border:1px solid #f3d7df;
    border-radius:14px;
    background:#fff7fa;
}
.gb-service-ratings-pro .service-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
}
.gb-service-ratings-pro .service-row span{
    font-weight:600;
    color:#5b2134;
}
.rating-stars{
    display:flex;
    gap:5px;
    user-select:none;
}
.rating-stars .star{
    font-style:normal;
    font-size:24px;
    line-height:1;
    color:#d1d5db;
    cursor:pointer;
    transition:.15s ease;
}
.rating-stars .star.active{
    color:#f59e0b;
    text-shadow:0 2px 8px rgba(245,158,11,.25);
}
.rating-stars .star:hover{
    transform:scale(1.12);
}
</style>


<style>
.gb-order-tabs-icons-pro a{position:relative!important;padding-right:34px!important;overflow:visible!important}
.gb-order-tabs-icons-pro a b{
    position:absolute!important;
    right:12px!important;
    top:10px!important;
    min-width:22px!important;
    height:22px!important;
    border-radius:999px!important;
    background:#ef3f68!important;
    color:#fff!important;
    display:grid!important;
    place-items:center!important;
    font-size:12px!important;
    font-weight:900!important;
    box-shadow:0 8px 18px rgba(239,63,104,.28)!important;
    z-index:3!important;
}
.gb-order-tabs-icons-pro a b:empty,
.gb-order-tabs-icons-pro a b[data-zero="1"]{display:none!important}
.gb-customer-toast-success{
    position:fixed!important;
    left:50%!important;
    top:50%!important;
    bottom:auto!important;
    transform:translate(-50%,-50%)!important;
    z-index:99999!important;
    max-width:430px!important;
    width:calc(100% - 36px)!important;
    padding:20px 22px!important;
    border-radius:22px!important;
    background:#ecfff3!important;
    border:1px solid #86efac!important;
    box-shadow:0 22px 60px rgba(22,101,52,.22)!important;
    color:#166534!important;
    text-align:center!important;
    font-weight:800!important;
}
.gb-customer-toast-success strong{display:block;font-size:20px;margin-bottom:6px}
.gb-customer-toast-success span{display:block;font-weight:700;line-height:1.45}
@media(max-width:768px){
    .gb-order-tabs-icons-pro a{padding-right:28px!important}
    .gb-order-tabs-icons-pro a b{right:6px!important;top:6px!important}
}
</style>


<style>
/* FIX: bảng trạng thái đơn gọn, không tràn chữ/badge */
.gb-order-status-board-pro{
    overflow:visible!important;
}
.gb-order-tabs-icons-pro{
    display:grid!important;
    grid-template-columns:repeat(5,minmax(120px,1fr))!important;
    gap:14px!important;
    align-items:stretch!important;
}
.gb-order-tabs-icons-pro a{
    min-height:78px!important;
    padding:13px 30px 12px 14px!important;
    border-radius:18px!important;
    display:grid!important;
    place-items:center!important;
    gap:5px!important;
    text-align:center!important;
    white-space:normal!important;
    line-height:1.2!important;
}
.gb-order-tabs-icons-pro a i{
    font-size:24px!important;
    line-height:1!important;
}
.gb-order-tabs-icons-pro a span{
    display:block!important;
    max-width:100%!important;
    font-size:15px!important;
    font-weight:800!important;
    word-break:keep-all!important;
}
.gb-order-tabs-icons-pro a b{
    right:10px!important;
    top:9px!important;
    transform:none!important;
}
@media(max-width:900px){
    .gb-order-tabs-icons-pro{grid-template-columns:repeat(2,minmax(0,1fr))!important}
    .gb-order-tabs-icons-pro a{min-height:72px!important}
}
@media(max-width:520px){
    .gb-order-tabs-icons-pro{grid-template-columns:1fr!important}
    .gb-order-tabs-icons-pro a{grid-template-columns:36px 1fr!important;place-items:center start!important;text-align:left!important}
}
</style>

</section>

<?php
// Sau khi khách đã mở trang Đơn hàng của tôi thì đánh dấu các cập nhật admin là đã xem.
if (!empty($_SESSION['user']['id'])) {
    require_once __DIR__ . '/../../models/Order.php';
    Order::markStatusSeenByUser((int)$_SESSION['user']['id']);
}
?>

<script>
function gbApplyOrderFilter(tab){
    document.querySelectorAll('.gb-order-tabs-pro a').forEach(x=>x.classList.remove('active'));
    tab.classList.add('active');
    if(tab.dataset.badgeType === 'notify'){
        var badge = tab.querySelector('b');
        if(badge){ badge.textContent='0'; badge.dataset.zero='1'; }
    }
    var f = tab.dataset.filter;
    var shown = 0;
    document.querySelectorAll('.gb-order-card-pro').forEach(function(card){
        var ok = false;
        if (f === 'all') ok = true;
        else if (f === 'need-review') ok = card.dataset.needReview === '1';
        else ok = card.dataset.status === f;
        card.style.display = ok ? 'block' : 'none';
        if (ok) shown++;
        card.querySelectorAll('.gb-review-form-pro').forEach(function(form){
            if (f === 'need-review') {
                form.style.display = form.dataset.reviewed === '1' ? 'none' : 'grid';
            } else {
                form.style.display = 'grid';
            }
        });
    });
    var empty = document.getElementById('gb-filter-empty');
    if (empty) {
        var title = empty.querySelector('h3');
        var desc = empty.querySelector('p');
        if (title) title.textContent = f === 'need-review' ? 'Không còn sản phẩm cần đánh giá' : (f === 'all' ? 'Bạn chưa có đơn hàng nào.' : 'Đơn ' + f.toLowerCase() + ' đang trống');
        if (desc) desc.textContent = f === 'need-review' ? 'Các sản phẩm đã giao và chưa đánh giá sẽ hiển thị tại đây.' : 'Hiện chưa có đơn nào ở mục này.';
        empty.style.display = shown === 0 ? 'block' : 'none';
    }
}
document.querySelectorAll('.gb-order-tabs-pro a, .gb-status-board-head-pro a').forEach(function(a){
    a.addEventListener('click', function(e){
        e.preventDefault();
        gbApplyOrderFilter(a);
    });
});
document.querySelectorAll('.gb-order-tabs-pro a b').forEach(function(b){
    if((b.textContent || '').trim() === '0') b.dataset.zero='1';
});
var activeTab = document.querySelector('.gb-order-tabs-pro a.active');
var params = new URLSearchParams(window.location.search);
var startFilter = params.has('reviewed') ? 'need-review' : (location.hash === '#review-success' ? 'need-review' : '');
if(!startFilter && document.querySelector('.gb-order-update-alert')){
    var deliveredBadge = document.querySelector('.gb-order-tabs-pro a[data-filter="Hoàn thành"] b');
    if(deliveredBadge && (deliveredBadge.textContent || '').trim() !== '0') startFilter = 'Hoàn thành';
}
if(startFilter){
    var targetTab = document.querySelector('.gb-order-tabs-pro a[data-filter="'+startFilter+'"]');
    if(targetTab) activeTab = targetTab;
}
if (activeTab) gbApplyOrderFilter(activeTab);
</script>

<script>
document.addEventListener('click', function(e){
    var star = e.target.closest('.rating-stars .star');
    if(!star) return;

    var box = star.closest('.rating-stars');
    var input = box ? box.querySelector('input[type="hidden"]') : null;
    var value = parseInt(star.dataset.value || '5', 10);

    if(input) input.value = value;

    box.querySelectorAll('.star').forEach(function(s){
        var current = parseInt(s.dataset.value || '0', 10);
        s.classList.toggle('active', current <= value);
    });
});
</script>



<script>
document.querySelectorAll('.gb-review-file-input').forEach(function(input){
    input.addEventListener('change', function(){
        var label = input.closest('.gb-upload-box-pro');
        var nameBox = label ? label.querySelector('.gb-file-name') : null;
        if(input.files && input.files.length){
            if(nameBox) nameBox.textContent = 'Đã chọn: ' + input.files[0].name;
            if(label) label.classList.add('is-selected');
        }else{
            if(nameBox) nameBox.textContent = 'Có thể bỏ qua nếu chưa có ảnh thực tế';
            if(label) label.classList.remove('is-selected');
        }
    });
});
setTimeout(function(){
    var toast = document.getElementById('gbReviewSuccessToast');
    if(toast) toast.style.display='none';
}, 4500);
</script>


<script>
document.querySelectorAll('.gb-review-form-pro').forEach(function(form){
    form.addEventListener('submit', function(){
        var btn = form.querySelector('.gb-review-submit-pro');
        if(btn){
            btn.dataset.originalText = btn.textContent;
            btn.textContent = 'Đang gửi đánh giá...';
            btn.disabled = true;
        }
        window.gbSubmittingReview = true;
    });
});
</script>
