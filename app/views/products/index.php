<?php
$savedIds = array_map('intval', $savedIds ?? []);
$grouped = [];
foreach ($products as $p) {
    $grouped[$p['category']][] = $p;
}
uasort($grouped, function($a, $b){ return count($b) <=> count($a); });
$categoryNames = array_keys($grouped);
$tabIndex = 0;
?>

<section class="container product-filter-clean product-filter-top-clean">
    <form method="get" action="<?= BASE_URL ?>products">
        <input type="hidden" name="url" value="products">

        <div class="filter-field-clean">
            <label>Tìm sản phẩm</label>
            <input id="productLiveSearch" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nhập tên, danh mục hoặc công dụng" autocomplete="off">
        </div>

        <div class="filter-field-clean">
            <label>Danh mục</label>
            <select name="cat" id="productCategorySelect">
                <option value="">Tất cả danh mục</option>
                <?php foreach($categories as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= $cat === $c ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="btn filter-submit-clean" type="submit">Lọc sản phẩm</button>
    </form>
</section>


<section class="container category-panels product-section-clean">
<?php $tabIndex = 0; foreach($grouped as $category => $items): ?>
    <div class="category-panel <?= $tabIndex === 0 ? 'active' : '' ?>" id="cat-<?= $tabIndex ?>">
        <div class="category-title-row category-title-clean">
            <div>
                <span>Danh mục</span>
                <h2><?= htmlspecialchars($category) ?></h2>
            </div>
            <p><?= count($items) ?> sản phẩm phù hợp</p>
        </div>

        <div class="product-carousel-block product-grid-clean" data-carousel>
            <button class="real-arrow left" type="button" data-prev aria-label="Sản phẩm trước">‹</button>

            <div class="product-rail">
                <?php foreach($items as $p): ?>
                    <article class="product-card carousel-card" data-product-text="<?= htmlspecialchars(mb_strtolower(($p['name'] ?? '').' '.($p['category'] ?? '').' '.($p['brand'] ?? '').' '.($p['benefit'] ?? '').' '.($p['description'] ?? ''), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>">
                        <a href="<?= BASE_URL ?>product?id=<?= $p['id'] ?>">
                            <div class="img-box">
                                <img src="<?= gb_image_url($p['image'] ?? '') ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            </div>
                            <span class="product-category-badge"><?= htmlspecialchars($p['category']) ?></span>
                            <h3><?= htmlspecialchars($p['name']) ?></h3>
                        </a>

                        <div class="card-actions product-card-simple-actions">
                            <div class="product-meta-line"><div class="stock-badge">
                                <?= $p['stock'] <= 0 ? 'Đã hết hàng' : ((int)$p['stock'] < 3 ? 'Sắp hết hàng: còn '.$p['stock'] : 'Còn hàng: '.$p['stock']) ?>
                            </div><div class="price"><?= number_format($p['price'], 0, ',', '.') ?>đ</div></div>
                            <div class="product-action-row">
                                <a class="mini-btn add-cart-btn <?= $p['stock'] <= 0 ? 'disabled' : '' ?>" href="<?= $p['stock'] > 0 ? BASE_URL.'cart/add?id='.$p['id'] : '#' ?>"><span class="gb-cart-icon" aria-hidden="true">🛒</span><span class="gb-cart-text">Thêm vào giỏ</span></a>
                                <?php $isSaved = in_array((int)$p['id'], $savedIds, true); ?>
                                <a class="save-heart js-save-heart <?= $isSaved ? 'is-saved' : '' ?> <?= $p['stock'] <= 0 ? 'disabled' : '' ?>"
                                   href="<?= BASE_URL ?>wishlist/add?id=<?= (int)$p['id'] ?>"
                                   onclick="return gbToggleWishlist(event, this)"
                                   data-url="<?= BASE_URL ?>wishlist/add?id=<?= (int)$p['id'] ?>"
                                   data-product-id="<?= (int)$p['id'] ?>"
                                   title="<?= $isSaved ? 'Đã lưu sản phẩm' : 'Lưu sản phẩm' ?>"
                                   aria-label="<?= $isSaved ? 'Đã lưu sản phẩm' : 'Lưu sản phẩm yêu thích' ?>">
                                    <span class="heart-outline">♡</span>
                                    <span class="heart-filled">♥</span>
                                </a>
                            </div>
                            <div class="favorite-inline-message" aria-live="polite"></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <button class="real-arrow right" type="button" data-next aria-label="Sản phẩm tiếp theo">›</button>
        </div>
    </div>
<?php $tabIndex++; endforeach; ?>

<?php if (empty($products)): ?>
    <div class="empty-state-clean">Không tìm thấy sản phẩm phù hợp.</div>
<?php endif; ?>
</section>

