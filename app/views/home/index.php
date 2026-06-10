<?php
$savedIds = array_map('intval', $savedIds ?? []);
?>
<section class="home-full-hero gb-banner-slider clean-banner-only" data-hero-slider>
  <div class="gb-hero-track">
    <div class="gb-hero-slide active"><img src="<?= BASE_URL ?>public/assets/images/glowbeauty-banner-1.png" alt="Banner GlowBeauty 1"></div>
    <div class="gb-hero-slide"><img src="<?= BASE_URL ?>public/assets/images/glowbeauty-banner-2.png" alt="Banner GlowBeauty 2"></div>
    <div class="gb-hero-slide"><img src="<?= BASE_URL ?>public/assets/images/glowbeauty-banner-3.png" alt="Banner GlowBeauty 3"></div>
  </div>
  <div class="gb-hero-dots" aria-label="Banner GlowBeauty">
    <button type="button" class="active" data-hero-dot="0"></button>
    <button type="button" data-hero-dot="1"></button>
    <button type="button" data-hero-dot="2"></button>
  </div>
  <a class="down-arrow" href="#home-products" aria-label="Xem sản phẩm">⌄</a>
</section>

<section id="home-products" class="container section-head compact-head">
  <span>Sản phẩm nổi bật</span>
  <h2>Xem nhanh sản phẩm GlowBeauty</h2>
</section>

<section class="container product-carousel-block" data-carousel>
  <button class="real-arrow left" type="button" data-prev aria-label="Sản phẩm trước">‹</button>
  <div class="product-rail">
    <?php foreach($products as $p): ?>
      <article class="product-card carousel-card">
        <a href="<?= BASE_URL ?>product?id=<?= $p['id'] ?>">
          <div class="img-box"><img src="<?= BASE_URL ?>public/assets/images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"></div>
          <span class="product-category-badge"><?= htmlspecialchars($p['category']) ?></span>
          <h3><?= htmlspecialchars($p['name']) ?></h3>
          <p><?= htmlspecialchars(excerpt($p['benefit'],88)) ?></p>
        </a>
        <div class="card-actions home-card-actions">
          <div>
            <div class="product-meta-line"><div class="stock-badge">Kho: <?= $p['stock']>0?$p['stock']:'Đã hết hàng' ?></div><div class="price"><?= number_format($p['price'],0,',','.') ?>đ</div></div>
          </div>
          <div class="product-action-row">
            <a class="mini-btn add-cart-btn <?= $p['stock']<=0?'disabled':'' ?>" href="<?= $p['stock']>0?BASE_URL.'cart/add?id='.$p['id']:'#' ?>">
              <?= $p['stock']>0?'<span class="gb-cart-icon" aria-hidden="true">🛒</span><span class="gb-cart-text">Thêm vào giỏ</span>':'Đã hết hàng' ?>
            </a>
            <?php $isSaved = in_array((int)$p['id'], $savedIds, true); ?>
            <a class="save-heart js-save-heart <?= $isSaved ? 'is-saved' : '' ?>"
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
</section>

<section class="container beauty-story home-story"><div><span>GlowBeauty Experience</span><h2>Đẹp hơn mỗi ngày với routine phù hợp</h2><p>GlowBeauty kết hợp skincare và makeup để khách dễ chọn: làm sạch, dưỡng ẩm, nền mịn, điểm màu và hoàn thiện phong cách cá nhân.</p><a class="btn" href="<?= BASE_URL ?>about">Tìm hiểu thương hiệu</a></div><div class="story-cards"><article><i>01</i><b>Làm sạch</b><p>Sữa rửa mặt, toner, essence hỗ trợ nền da thông thoáng.</p></article><article><i>02</i><b>Dưỡng & phục hồi</b><p>Serum, kem dưỡng, bộ skincare cấp ẩm cho da mịn hơn.</p></article><article><i>03</i><b>Trang điểm</b><p>Kem nền, phấn mắt, phấn má và son giúp hoàn thiện vẻ ngoài.</p></article></div></section>
