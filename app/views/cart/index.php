<section class="cart-modern-page">
    <div class="container">

        <div class="cart-breadcrumb">
            <a href="<?= BASE_URL ?>">Trang chủ</a>
            <span>›</span>
            <span>Giỏ hàng</span>
        </div>

        <div class="cart-modern-title">
            <div class="cart-title-icon">🛒</div>

            <div>
                <h1>Giỏ hàng của bạn</h1>
                <p>Kiểm tra sản phẩm trước khi tiến hành thanh toán</p>
            </div>
        </div>

        <?php if (empty($items)): ?>

            <div class="empty-cart-modern">
                <div class="empty-cart-icon">🛒</div>
                <h2>Giỏ hàng của bạn đang trống</h2>
                <p>Hãy chọn thêm sản phẩm yêu thích trước khi đặt hàng.</p>

                <a href="<?= BASE_URL ?>products" class="cart-primary-btn">
                    Mua sắm ngay
                </a>
            </div>

        <?php else: ?>

            <div class="cart-modern-layout">

                <div class="cart-items-card">

                    <div class="cart-items-head">
                        <label>
                            <input type="checkbox" id="selectAll" checked>
                            Chọn tất cả (<?= count($items) ?> sản phẩm)
                        </label>

                        <button type="button" id="removeSelected" class="cart-small-danger">
                            🗑️ Xóa đã chọn
                        </button>
                    </div>

                    <?php foreach ($items as $i): ?>
                        <?php
                        $imagePath = BASE_URL . 'public/assets/images/' . rawurlencode($i['image']);
                        ?>

                        <div class="cart-product-row" data-id="<?= $i['id'] ?>" data-price="<?= $i['price'] ?>"
                            data-qty="<?= $i['qty'] ?>">

                            <div class="cart-product-select">
                                <input type="checkbox" class="cart-check" checked>
                            </div>

                            <div class="cart-product-img">
                                <?php if (!empty($i['image'])): ?>
                                    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($i['name']) ?>"
                                        onerror="this.style.display='none'; this.parentElement.innerHTML='💄';">
                                <?php else: ?>
                                    <span>💄</span>
                                <?php endif; ?>
                            </div>

                            <div class="cart-product-info">
                                <h3><?= htmlspecialchars($i['name']) ?></h3>
                                <p>Danh mục: <?= htmlspecialchars($i['category'] ?? 'Mỹ phẩm') ?></p>
                                <span class="cart-stock-ok">✓ Còn hàng</span>
                            </div>

                            <div class="cart-product-price">
                                <?= number_format($i['price'], 0, ',', '.') ?>đ
                            </div>

                            <div class="cart-qty-box">
                                <a href="<?= BASE_URL ?>cart/update?id=<?= $i['id'] ?>&action=minus">−</a>
                                <strong><?= $i['qty'] ?></strong>
                                <a href="<?= BASE_URL ?>cart/update?id=<?= $i['id'] ?>&action=plus">+</a>
                            </div>

                            <div class="cart-product-total">
                                <?= number_format($i['line'], 0, ',', '.') ?>đ
                            </div>

                            <a href="<?= BASE_URL ?>cart/remove?id=<?= $i['id'] ?>" class="cart-remove-btn">
                                ×
                            </a>

                        </div>
                    <?php endforeach; ?>

                    <div class="cart-bottom-actions">
                        <a href="<?= BASE_URL ?>products" class="cart-back-btn">
                            ← Tiếp tục mua sắm
                        </a>
                    </div>
                </div>

                <div class="cart-summary-modern">
                    <h2>Đơn hàng của bạn</h2>

                    <div class="summary-line">
                        <span>Tạm tính (<b id="selectedCount"><?= count($items) ?></b> sản phẩm)</span>
                        <strong id="subtotalText"><?= number_format($total, 0, ',', '.') ?>đ</strong>
                    </div>

                    <div class="summary-line">
                        <span>Giảm giá</span>
                        <strong>-0đ</strong>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-total">
                        <span>Tổng cộng</span>
                        <strong id="totalText"><?= number_format($total, 0, ',', '.') ?>đ</strong>
                    </div>

                    <p class="summary-note">Đã bao gồm VAT nếu có</p>

                    <div class="free-ship-box">
                        <p id="freeShipText"></p>
                        <div class="free-ship-progress">
                            <span id="freeShipBar"></span>
                        </div>
                    </div>

                    <?php if (empty($_SESSION['user'])): ?>
                        <a class="cart-checkout-btn" href="<?= BASE_URL ?>login">
                            🔒 Đăng nhập để đặt hàng
                        </a>

                        <a class="cart-coupon-btn" href="<?= BASE_URL ?>register">
                            👤 Đăng ký tài khoản
                        </a>
                    <?php else: ?>
                        <a class="cart-checkout-btn" href="<?= BASE_URL ?>checkout">
                            🔒 Tiến hành thanh toán
                        </a>
                    <?php endif; ?>
                </div>

            </div>

        <?php endif; ?>

    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formatMoney = number => new Intl.NumberFormat('vi-VN').format(number) + 'đ';

        const selectAll = document.getElementById('selectAll');
        const checks = document.querySelectorAll('.cart-check');
        const selectedCount = document.getElementById('selectedCount');
        const subtotalText = document.getElementById('subtotalText');
        const totalText = document.getElementById('totalText');
        const freeShipText = document.getElementById('freeShipText');
        const freeShipBar = document.getElementById('freeShipBar');
        const removeSelected = document.getElementById('removeSelected');

        function updateSummary() {
            let total = 0;
            let count = 0;

            document.querySelectorAll('.cart-product-row').forEach(row => {
                const check = row.querySelector('.cart-check');

                if (check.checked) {
                    total += Number(row.dataset.price) * Number(row.dataset.qty);
                    count++;
                }
            });

            selectedCount.textContent = count;
            subtotalText.textContent = formatMoney(total);
            totalText.textContent = formatMoney(total);

            const freeShip = 500000;
            const missing = Math.max(0, freeShip - total);
            const percent = Math.min(100, total / freeShip * 100);

            if (missing > 0) {
                freeShipText.innerHTML = '🚚 Bạn còn thiếu <b>' + formatMoney(missing) + '</b> để được miễn phí giao hàng';
            } else {
                freeShipText.innerHTML = '🎉 Đơn hàng đã đủ điều kiện miễn phí giao hàng';
            }

            freeShipBar.style.width = percent + '%';
        }

        checks.forEach(check => {
            check.addEventListener('change', updateSummary);
        });

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checks.forEach(check => check.checked = selectAll.checked);
                updateSummary();
            });
        }

        if (removeSelected) {

            removeSelected.addEventListener(
                'click',
                function () {

                    let selected = [];

                    document
                        .querySelectorAll('.cart-product-row')
                        .forEach(row => {

                            if (
                                row.querySelector('.cart-check')
                                    .checked
                            ) {

                                selected.push(
                                    row.dataset.id
                                );

                            }

                        });

                    if (selected.length === 0) {

                        alert(
                            'Bạn chưa chọn sản phẩm'
                        );

                        return;

                    }

                    if (
                        confirm(
                            'Bạn có chắc muốn xóa các sản phẩm đã chọn không?'
                        )
                    ) {

                        selected.forEach(id => {

                            fetch(
                                '<?= BASE_URL ?>cart/remove?id=' + id
                            );

                        });

                        setTimeout(() => {

                            location.reload();

                        }, 300);
                    }

                });

        }

        updateSummary();
    });
</script>