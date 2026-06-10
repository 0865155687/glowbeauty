<?php
$total = 0;
foreach (($items ?? []) as $item) {
    $total += (float)($item['price'] ?? 0);
}
$count = count($items ?? []);
function gb_like_count($p) {
    if (isset($p['favorite_count'])) return (int)$p['favorite_count'];
    if (isset($p['likes'])) return (int)$p['likes'];
    $id = (int)($p['id'] ?? 1);
    return 96 + (($id * 37) % 260);
}
?>
<section class="container wishlist-page-final">
    <div class="wishlist-breadcrumb-final">
        <a href="<?= BASE_URL ?>">Trang chủ</a><span>›</span>
        <a href="<?= BASE_URL ?>account/wishlist">Tài khoản</a><span>›</span>
        <b>Sản phẩm đã lưu</b>
    </div>

    <div class="wishlist-head-final">
        <div>
            <div class="wishlist-title-line-final"><span class="wishlist-title-heart">♥</span><h1>Sản phẩm đã lưu</h1></div>
            <p class="js-wishlist-subtitle">Các sản phẩm yêu thích của bạn <?= $count ? '(' . $count . ' sản phẩm)' : '' ?></p>
        </div>

        <?php if(!empty($items)): ?>
            <div class="wishlist-summary-final">
                <?php if(!empty($_SESSION['success'])): ?>
                    <div class="wishlist-toast-final" id="wishlistAutoToast">
                        <span>✓</span> <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" onclick="this.parentElement.remove()">×</button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                <a class="wishlist-checkout-final js-wishlist-checkout-all" href="<?= BASE_URL ?>wishlist/checkout">
                    🛒 Thanh toán<?= $count ? ' (' . $count . ' sản phẩm)' : '' ?>
                </a>
                <small class="js-wishlist-total">Tổng tiền: <?= number_format($total, 0, ',', '.') ?>đ</small>
            </div>
        <?php endif; ?>
    </div>

    <?php if(empty($items)): ?>
        <div class="pro-empty wishlist-empty-final">
            <b>Chưa có sản phẩm nào được lưu.</b>
            <p>Hãy chọn biểu tượng trái tim ở trang sản phẩm để thêm vào mục yêu thích.</p>
            <a class="btn" href="<?= BASE_URL ?>products">Xem sản phẩm</a>
        </div>
    <?php else: ?>
        <div class="wishlist-grid-final">
            <?php foreach($items as $p): ?>
                <article class="wishlist-card-final" data-price="<?= (float)($p['price'] ?? 0) ?>">
                    <a class="wishlist-img-final" href="<?= BASE_URL ?>product?id=<?= (int)$p['id'] ?>">
                        <img src="<?= gb_image_url($p['image'] ?? '') ?>" alt="<?= htmlspecialchars($p['name'] ?? '') ?>">
                        <span class="wishlist-heart-final">♥</span>
                    </a>

                    <div class="wish-meta-final">
                        <span class="wish-badge-final"><?= htmlspecialchars($p['category'] ?? 'Mỹ phẩm') ?></span>
                        <span class="wish-like-count-final">♥ <?= gb_like_count($p) ?> lượt yêu thích</span>
                    </div>

                    <h3><?= htmlspecialchars($p['name'] ?? '') ?></h3>

                    <div class="wishlist-price-row-final">
                        <b><?= number_format($p['price'] ?? 0, 0, ',', '.') ?>đ</b>
                        <span><?= ((int)($p['stock'] ?? 0) > 0) ? 'Còn hàng: '.(int)$p['stock'].' sản phẩm' : 'Hết hàng' ?></span>
                    </div>

                    <div class="wishlist-qty-final" aria-label="Số lượng">
                        <button type="button" onclick="gbWishQty(this,-1)">−</button>
                        <input type="number" value="1" min="1" max="<?= max(1, (int)($p['stock'] ?? 1)) ?>" inputmode="numeric">
                        <button type="button" onclick="gbWishQty(this,1)">+</button>
                    </div>

                    <div class="wishlist-actions-final">
                        <a class="remove-wish-final js-remove-wish" href="<?= BASE_URL ?>wishlist/remove?id=<?= (int)$p['id'] ?>">Xóa khỏi danh sách</a>
                        <a class="pay-wish-final" href="<?= BASE_URL ?>cart/add-checkout?id=<?= (int)$p['id'] ?>">Thanh toán</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
(function(){
    function fmtVnd(value){
        value = Math.max(0, Math.round(Number(value) || 0));
        return value.toLocaleString('vi-VN') + 'đ';
    }
    function showWishToast(message){
        var box = document.querySelector('.wishlist-summary-final');
        if(!box) return;
        var toast = document.getElementById('wishlistAutoToast');
        if(!toast){
            toast = document.createElement('div');
            toast.className = 'wishlist-toast-final';
            toast.id = 'wishlistAutoToast';
            box.insertBefore(toast, box.firstChild);
        }
        toast.innerHTML = '<span>✓</span> ' + (message || 'Đã xóa sản phẩm khỏi danh sách.') + '<button type="button" aria-label="Đóng">×</button>';
        toast.classList.remove('is-hide');
        var close = toast.querySelector('button');
        if(close) close.onclick = function(){ toast.remove(); };
        clearTimeout(toast._timer);
        toast._timer = setTimeout(function(){
            toast.classList.add('is-hide');
            setTimeout(function(){ if(toast && toast.parentNode) toast.remove(); }, 350);
        }, 3000);
    }
    function refreshSummary(count){
        var cards = Array.from(document.querySelectorAll('.wishlist-card-final'));
        if(count === undefined || count === null) count = cards.length;
        document.querySelectorAll('.wishlist-count, .wish-count-badge').forEach(function(el){ el.textContent = count; });
        var sub = document.querySelector('.js-wishlist-subtitle');
        if(sub) sub.textContent = 'Các sản phẩm yêu thích của bạn ' + (count ? '(' + count + ' sản phẩm)' : '');
        var checkout = document.querySelector('.js-wishlist-checkout-all');
        if(checkout) checkout.innerHTML = '🛒 Thanh toán' + (count ? ' (' + count + ' sản phẩm)' : '');
        var total = cards.reduce(function(sum, card){ return sum + (Number(card.dataset.price) || 0); }, 0);
        var totalEl = document.querySelector('.js-wishlist-total');
        if(totalEl) totalEl.textContent = 'Tổng tiền: ' + fmtVnd(total);
    }
    window.gbWishQty = function(btn, step){
        const wrap = btn.closest('.wishlist-qty-final');
        const input = wrap ? wrap.querySelector('input') : null;
        if(!input) return;
        const min = parseInt(input.min || '1', 10);
        const max = parseInt(input.max || '99', 10);
        let value = parseInt(input.value || '1', 10) + step;
        if(value < min) value = min;
        if(value > max) value = max;
        input.value = value;
    };
    document.addEventListener('click', function(e){
        var remove = e.target.closest('.js-remove-wish');
        if(!remove) return;
        e.preventDefault();
        var card = remove.closest('.wishlist-card-final');
        var url = remove.href + (remove.href.indexOf('?') >= 0 ? '&' : '?') + 'ajax=1&_=' + Date.now();
        remove.classList.add('is-loading');
        fetch(url, {method:'GET', credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, cache:'no-store'})
            .then(function(res){ return res.json(); })
            .then(function(data){
                if(data && data.ok){
                    if(card){
                        card.classList.add('is-removing');
                        setTimeout(function(){ card.remove(); refreshSummary(data.count); }, 260);
                    } else { refreshSummary(data.count); }
                    showWishToast(data.message || 'Đã xóa sản phẩm khỏi danh sách.');
                } else {
                    showWishToast((data && data.message) || 'Chưa xóa được sản phẩm, vui lòng thử lại.');
                }
            })
            .catch(function(){ showWishToast('Chưa xóa được sản phẩm, vui lòng thử lại.'); })
            .finally(function(){ remove.classList.remove('is-loading'); });
    });
    refreshSummary(<?= (int)$count ?>);
    var firstToast = document.getElementById('wishlistAutoToast');
    if(firstToast){
        clearTimeout(firstToast._timer);
        firstToast._timer = setTimeout(function(){
            firstToast.classList.add('is-hide');
            setTimeout(function(){ if(firstToast && firstToast.parentNode) firstToast.remove(); }, 350);
        }, 3000);
    }
})();
</script>
