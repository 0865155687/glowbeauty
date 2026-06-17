<?php
$total = 0;
foreach (($items ?? []) as $item) {
    $total += (float) ($item['price'] ?? 0);
}
$count = count($items ?? []);
function gb_like_count($p)
{
    if (isset($p['favorite_count']))
        return (int) $p['favorite_count'];
    if (isset($p['likes']))
        return (int) $p['likes'];
    $id = (int) ($p['id'] ?? 1);
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
            <div class="wishlist-title-line-final"><span class="wishlist-title-heart">♥</span>
                <h1>Sản phẩm đã lưu</h1>
            </div>
            <p class="js-wishlist-subtitle">Các sản phẩm yêu thích của bạn
                <?= $count ? '(' . $count . ' sản phẩm)' : '' ?>
            </p>
        </div>

        <?php if (!empty($items)): ?>
            <div class="wishlist-summary-final">
                <?php if (!empty($_SESSION['success'])): ?>
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

    <?php if (empty($items)): ?>
        <div class="pro-empty wishlist-empty-final">
            <b>Chưa có sản phẩm nào được lưu.</b>
            <p>Hãy chọn biểu tượng trái tim ở trang sản phẩm để thêm vào mục yêu thích.</p>
            <a class="btn" href="<?= BASE_URL ?>products">Xem sản phẩm</a>
        </div>
    <?php else: ?>
        <div class="wishlist-grid-final">
            <?php foreach ($items as $p): ?>
                <article class="wishlist-card-final" data-price="<?= (float) ($p['price'] ?? 0) ?>"
                    data-stock="<?= (int) ($p['stock'] ?? 0) ?>" data-product-id="<?= (int) ($p['id'] ?? 0) ?>">
                    <a class="wishlist-img-final" href="<?= BASE_URL ?>product?id=<?= (int) $p['id'] ?>">
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
                        <span><?= ((int) ($p['stock'] ?? 0) > 0) ? 'Còn hàng: ' . (int) $p['stock'] . ' sản phẩm' : 'Hết hàng' ?></span>
                    </div>

                    <div class="wishlist-qty-final" aria-label="Số lượng">
                        <button type="button" onclick="gbWishQty(this,-1)">−</button>
                        <input type="number" value="1" min="1" max="<?= max(1, (int) ($p['stock'] ?? 1)) ?>"
                            inputmode="numeric">
                        <button type="button" onclick="gbWishQty(this,1)">+</button>
                    </div>

                    <div class="wishlist-actions-final">
                        <a class="remove-wish-final js-remove-wish"
                            href="<?= BASE_URL ?>wishlist/remove?id=<?= (int) $p['id'] ?>">Xóa khỏi danh sách</a>
                        <a class="pay-wish-final js-pay-wish"
                            href="<?= BASE_URL ?>cart/add-checkout?id=<?= (int) $p['id'] ?>">Thanh toán</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<style>
    .wishlist-qty-final input.wish-qty-error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, .12) !important;
        color: #b91c1c !important;
    }

    .wishlist-toast-final {
        position: fixed !important;
        right: 28px !important;
        bottom: 96px !important;
        top: auto !important;
        left: auto !important;
        z-index: 999999 !important;
        max-width: 430px !important;
        min-width: 320px !important;
        padding: 14px 44px 14px 16px !important;
        border-radius: 16px !important;
        background: #ecfdf5 !important;
        color: #047857 !important;
        border: 1px solid #86efac !important;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .16) !important;
        font-weight: 700 !important;
        line-height: 1.45 !important;
    }

    .wishlist-toast-final span {
        display: inline-flex;
        width: 24px;
        height: 24px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #22c55e;
        color: #fff;
        margin-right: 8px;
    }

    .wishlist-toast-final button {
        position: absolute;
        right: 12px;
        top: 10px;
        border: 0;
        background: transparent;
        color: inherit;
        font-size: 20px;
        cursor: pointer;
        font-weight: 800;
    }

    .wishlist-toast-final.is-hide {
        opacity: 0;
        transform: translateY(12px);
        transition: .25s ease;
    }

    .stock-error-box {
        background: #fff7ed;
        color: #9a3412;
        padding: 12px 14px;
        margin: 12px 0 10px;
        border-radius: 14px;
        font-weight: 700;
        border: 1px solid #fed7aa;
        line-height: 1.45;
    }

    .pay-wish-final.is-disabled {
        opacity: .72;
        cursor: not-allowed;
        background: #f43f5e !important;
    }
</style>

<script>
    (function () {
        function fmtVnd(value) {
            value = Math.max(0, Math.round(Number(value) || 0));
            return value.toLocaleString('vi-VN') + 'đ';
        }

        function showWishToast(message) {
            var toast = document.getElementById('wishlistAutoToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.className = 'wishlist-toast-final';
                toast.id = 'wishlistAutoToast';
                document.body.appendChild(toast);
            }
            toast.innerHTML = '<span>!</span> ' + (message || 'Đã cập nhật.') + '<button type="button" aria-label="Đóng">×</button>';
            toast.classList.remove('is-hide');
            var close = toast.querySelector('button');
            if (close) close.onclick = function () { toast.remove(); };
            clearTimeout(toast._timer);
            toast._timer = setTimeout(function () {
                toast.classList.add('is-hide');
                setTimeout(function () { if (toast && toast.parentNode) toast.remove(); }, 350);
            }, 3500);
        }

        function refreshSummary(count) {
            var cards = Array.from(document.querySelectorAll('.wishlist-card-final'));
            if (count === undefined || count === null) count = cards.length;
            document.querySelectorAll('.wishlist-count, .wish-count-badge').forEach(function (el) { el.textContent = count; });
            var sub = document.querySelector('.js-wishlist-subtitle');
            if (sub) sub.textContent = 'Các sản phẩm yêu thích của bạn ' + (count ? '(' + count + ' sản phẩm)' : '');
            var checkout = document.querySelector('.js-wishlist-checkout-all');
            if (checkout) checkout.innerHTML = '🛒 Thanh toán' + (count ? ' (' + count + ' sản phẩm)' : '');
            var total = cards.reduce(function (sum, card) { return sum + (Number(card.dataset.price) || 0); }, 0);
            var totalEl = document.querySelector('.js-wishlist-total');
            if (totalEl) totalEl.textContent = 'Tổng tiền: ' + fmtVnd(total);
        }

        function readWishQty(input) {
            if (!input) return 0;
            var raw = String(input.value || '').trim();
            if (raw === '') return 0;
            var value = parseInt(raw, 10);
            if (isNaN(value)) return 0;
            return value;
        }

        function showInlineStockError(card, stock, qty) {
            if (!card) return;
            var oldError = card.querySelector('.stock-error-box');
            if (oldError) oldError.remove();
            var errorBox = document.createElement('div');
            errorBox.className = 'stock-error-box';
            if (qty < 1) {
                errorBox.innerHTML = '⚠️ Số lượng phải lớn hơn 0. Vui lòng chọn ít nhất <b>1</b> sản phẩm để thanh toán.';
            } else {
                errorBox.innerHTML = '⚠️ Sản phẩm không đủ số lượng bạn cần.<br>Kho hiện chỉ còn <b>' + stock + '</b> sản phẩm, bạn đang chọn <b>' + qty + '</b> sản phẩm.';
            }
            var actionArea = card.querySelector('.wishlist-actions-final');
            if (actionArea && actionArea.parentNode) actionArea.parentNode.insertBefore(errorBox, actionArea);
            var pay = card.querySelector('.js-pay-wish');
            if (pay) {
                pay.classList.add('is-disabled');
                pay.textContent = 'Vượt tồn kho';
            }
        }

        function clearInlineStockError(card) {
            if (!card) return;
            var oldError = card.querySelector('.stock-error-box');
            if (oldError) oldError.remove();
            var pay = card.querySelector('.js-pay-wish');
            if (pay) {
                pay.classList.remove('is-disabled');
                pay.textContent = 'Thanh toán';
            }
        }

        function validateCard(card, silent) {
            if (!card) return false;
            var input = card.querySelector('.wishlist-qty-final input');
            var stock = parseInt(card.dataset.stock || (input && input.max) || '0', 10);
            var qty = readWishQty(input);
            if (!input) return false;
            if (qty < 1 || qty > stock || stock <= 0) {
                input.classList.add('wish-qty-error');
                showInlineStockError(card, stock, qty);
                if (!silent) {
                    if (qty < 1) showWishToast('Số lượng phải lớn hơn 0. Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.');
                    else showWishToast('Sản phẩm không đủ số lượng bạn cần. Kho hiện chỉ còn ' + stock + ' sản phẩm, vui lòng giảm số lượng trước khi thanh toán.');
                }
                return false;
            }
            input.classList.remove('wish-qty-error');
            clearInlineStockError(card);
            return true;
        }

        window.gbWishQty = function (btn, step) {
            var card = btn.closest('.wishlist-card-final');
            var wrap = btn.closest('.wishlist-qty-final');
            var input = wrap ? wrap.querySelector('input') : null;
            if (!input) return;
            var value = readWishQty(input) + step;
            if (value < 0) value = 0;
            input.value = value;
            validateCard(card, value <= parseInt((card && card.dataset.stock) || input.max || '0', 10));
        };

        document.addEventListener('input', function (e) {
            var input = e.target.closest('.wishlist-qty-final input');
            if (!input) return;
            var card = input.closest('.wishlist-card-final');
            validateCard(card, true);
        });

        document.addEventListener('change', function (e) {
            var input = e.target.closest('.wishlist-qty-final input');
            if (!input) return;
            var card = input.closest('.wishlist-card-final');
            validateCard(card, true);
        });

        document.addEventListener('click', function (e) {
            var pay = e.target.closest('.js-pay-wish');
            if (!pay) return;
            var card = pay.closest('.wishlist-card-final');
            if (!validateCard(card, false)) {
                e.preventDefault();
                return false;
            }
            var input = card.querySelector('.wishlist-qty-final input');
            var finalQty = readWishQty(input);
            var productId = card.dataset.productId || '';
            if (productId) {
                e.preventDefault();
                window.location.href = '<?= BASE_URL ?>cart/add-checkout?id=' + encodeURIComponent(productId) + '&qty=' + encodeURIComponent(finalQty) + '&from=wishlist';
            }
        });

        document.addEventListener('click', function (e) {
            var checkoutAll = e.target.closest('.js-wishlist-checkout-all');
            if (!checkoutAll) return;
            var bad = Array.from(document.querySelectorAll('.wishlist-card-final')).find(function (card) {
                return !validateCard(card, true);
            });
            if (bad) {
                e.preventDefault();
                bad.scrollIntoView({ behavior: 'smooth', block: 'center' });
                validateCard(bad, false);
                return false;
            }
        });

        document.addEventListener('click', function (e) {
            var remove = e.target.closest('.js-remove-wish');
            if (!remove) return;
            e.preventDefault();
            var card = remove.closest('.wishlist-card-final');
            var url = remove.href + (remove.href.indexOf('?') >= 0 ? '&' : '?') + 'ajax=1&_=' + Date.now();
            remove.classList.add('is-loading');
            fetch(url, { method: 'GET', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, cache: 'no-store' })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data && data.ok) {
                        if (card) {
                            card.classList.add('is-removing');
                            setTimeout(function () { card.remove(); refreshSummary(data.count); }, 260);
                        } else { refreshSummary(data.count); }
                        showWishToast(data.message || 'Đã xóa sản phẩm khỏi danh sách.');
                    } else {
                        showWishToast((data && data.message) || 'Chưa xóa được sản phẩm, vui lòng thử lại.');
                    }
                })
                .catch(function () { showWishToast('Chưa xóa được sản phẩm, vui lòng thử lại.'); })
                .finally(function () { remove.classList.remove('is-loading'); });
        });

        refreshSummary(<?= (int) $count ?>);
        document.querySelectorAll('.wishlist-card-final').forEach(function(card){ validateCard(card, true); });
        var firstToast = document.getElementById('wishlistAutoToast');
        if (firstToast) {
            document.body.appendChild(firstToast);
            clearTimeout(firstToast._timer);
            firstToast._timer = setTimeout(function () {
                firstToast.classList.add('is-hide');
                setTimeout(function () { if (firstToast && firstToast.parentNode) firstToast.remove(); }, 350);
            }, 3500);
        }
    })();
</script>
