<div class="admin-title gb-page-hero">
  <div>
    <span class="gb-page-kicker">PHẢN HỒI KHÁCH HÀNG</span>
    <h1>Đánh giá khách hàng</h1>
    <p>Xem đầy đủ đánh giá sản phẩm, dịch vụ, tốc độ giao hàng và đóng gói.</p>
  </div>
</div>

<?php
function gb_review_stars_admin($n) {
    $n = max(1, min(5, (int)$n));
    return str_repeat('★', $n) . '<span>' . str_repeat('☆', 5 - $n) . '</span>';
}

function gb_review_image_src_admin($file) {
    $file = trim((string)$file);
    if ($file === '') return '';
    $file = basename($file);
    $paths = [
        ['public/uploads/reviews/', BASE_URL . 'public/uploads/reviews/'],
        ['public/assets/images/', BASE_URL . 'public/assets/images/'],
        ['uploads/reviews/', BASE_URL . 'uploads/reviews/'],
        ['uploads/', BASE_URL . 'uploads/']
    ];
    foreach ($paths as $pair) {
        $abs = dirname(__DIR__, 3) . '/' . $pair[0] . $file;
        if (is_file($abs)) return $pair[1] . rawurlencode($file);
    }
    return '';
}

function gb_review_initial_admin($name) {
    $name = trim((string)$name);
    if ($name === '') return 'K';
    $parts = preg_split('/\s+/u', $name);
    $last = end($parts);
    return mb_strtoupper(mb_substr($last ?: $name, 0, 1, 'UTF-8'), 'UTF-8');
}
?>

<?php if(empty($reviews)): ?>
  <div class="admin-card gb-empty-state">Chưa có đánh giá nào từ khách hàng.</div>
<?php else: ?>
  <div class="gb-review-grid">
    <?php foreach($reviews as $r):
      $status = isset($r['status']) ? (int)$r['status'] : 1;
      $rating = max(1, min(5, (int)($r['rating'] ?? 5)));
      $created = !empty($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '-';
      $seller = max(1, min(5, (int)($r['seller_service'] ?? 5)));
      $shipping = max(1, min(5, (int)($r['shipping_service'] ?? 5)));
      $package = max(1, min(5, (int)($r['package_service'] ?? 5)));
    ?>
      <article class="gb-review-card">
        <div class="gb-review-top">
          <div class="gb-review-customer">
            <div class="gb-avatar-star"><?= htmlspecialchars(gb_review_initial_admin($r['customer_name'] ?? 'Khách hàng')) ?></div>
            <div>
              <h3><?= htmlspecialchars($r['customer_name'] ?: 'Khách hàng') ?></h3>
              <p>☎ <?= htmlspecialchars($r['phone'] ?? 'Chưa có SĐT') ?></p>
            </div>
          </div>
        </div>

        <div class="gb-review-product">
          <?php if(!empty($r['product_image'])): ?>
            <img src="<?= BASE_URL ?>public/assets/images/<?= htmlspecialchars($r['product_image']) ?>" alt="">
          <?php endif; ?>
          <div>
            <b><?= htmlspecialchars($r['product_name'] ?? 'Sản phẩm') ?></b>
            <small>Đơn #<?= (int)($r['order_id'] ?? 0) ?> • <?= htmlspecialchars($created) ?></small>
          </div>
        </div>

        <div class="gb-stars" aria-label="<?= $rating ?> sao">
          <?= gb_review_stars_admin($rating) ?>
        </div>

        <p class="gb-review-comment"><?= nl2br(htmlspecialchars($r['comment'] ?? '')) ?></p>

        <?php $reviewPhoto = gb_review_image_src_admin($r['image'] ?? ''); ?>
        <?php if($reviewPhoto !== ''): ?>
          <div class="gb-review-photo gb-review-photo-before-service">
            <img src="<?= htmlspecialchars($reviewPhoto, ENT_QUOTES, 'UTF-8') ?>" alt="Ảnh đánh giá">
          </div>
        <?php endif; ?>

        <div class="gb-review-service-box">
          <div><span>Dịch vụ người bán</span><b><?= gb_review_stars_admin($seller) ?></b></div>
          <div><span>Tốc độ giao hàng</span><b><?= gb_review_stars_admin($shipping) ?></b></div>
          <div><span>Đóng gói sản phẩm</span><b><?= gb_review_stars_admin($package) ?></b></div>
        </div>

        <?php if((int)($r['id'] ?? 0) > 0): ?>
        <div class="gb-review-actions">
          <a class="gb-btn-danger" onclick="return confirm('Xoá đánh giá này?')" href="<?= BASE_URL ?>admin/review-delete?id=<?= (int)$r['id'] ?>">Xoá</a>
        </div>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<style>
.gb-review-service-box{display:grid;gap:8px;background:#fff7f2;border:1px solid #f0d2c2;border-radius:14px;padding:12px;margin:12px 0}
.gb-review-service-box div{display:flex;justify-content:space-between;align-items:center;gap:14px}
.gb-review-service-box span{font-weight:600;color:#5b2a1d}
.gb-review-service-box b{color:#f59e0b;letter-spacing:1px;white-space:nowrap}
.gb-review-service-box b span,.gb-stars span{color:#d6c4bd}
.gb-review-photo{margin:12px 0 10px!important}.gb-review-photo img{width:128px!important;height:128px!important;object-fit:cover!important;border-radius:14px;border:1px solid #f0d2c2;display:block!important}
.gb-avatar-star{width:54px;height:54px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#fb7b43,#f8b37f);color:#fff!important;font-weight:900;font-size:22px;box-shadow:0 10px 24px rgba(202,94,45,.18)}
@media(max-width:768px){
  .gb-review-grid{display:grid;grid-template-columns:1fr!important;gap:12px}
  .gb-review-card{padding:12px!important}
  .gb-review-top,.gb-review-product,.gb-review-service-box div{align-items:flex-start}
  .gb-review-service-box div{display:grid;gap:4px}
}
</style>
