<?php
$productReviews = $productReviews ?? [];
$reviewSummary = $reviewSummary ?? ['total_reviews'=>0,'avg_rating'=>0,'star5'=>0,'star4'=>0,'star3'=>0,'star2'=>0,'star1'=>0];
$avgRating = (float)($reviewSummary['avg_rating'] ?? 0);
$totalReviews = (int)($reviewSummary['total_reviews'] ?? 0);
$displayRating = $totalReviews > 0 ? number_format($avgRating, 1) : '0.0';
function gb_format_sold($n) {
    $n = max(0, (int)$n);
    if ($n >= 1000000) return rtrim(rtrim(number_format($n/1000000, 1, ',', '.'), '0'), ',') . 'tr';
    if ($n >= 1000) return rtrim(rtrim(number_format($n/1000, 1, ',', '.'), '0'), ',') . 'k';
    return (string)$n;
}
$displaySold = Product::soldQty((int)($p['id'] ?? 0));
function gb_product_star_text($rating) {
    $rating = (int)round((float)$rating);
    if ($rating <= 0) return '☆☆☆☆☆';
    $rating = max(1, min(5, $rating));
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}
?>
<section class="product-page-new">

  <div class="product-breadcrumb-new">
    <a href="<?= gb_url() ?>">Trang chủ</a>
    <span>›</span>
    <a href="<?= gb_url('products') ?>">Sản phẩm</a>
    <span>›</span>
    <span><?= htmlspecialchars($p['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
  </div>

  <div class="product-main-new">

    <div class="product-gallery-new">
      <div class="discount-badge-new">-20%</div>

      <img class="product-main-img-new" src="<?= gb_image_url($p['image'] ?? '') ?>"
        alt="<?= htmlspecialchars($p['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

      <div class="product-thumbs-new">
        <img src="<?= gb_image_url($p['image'] ?? '') ?>" alt="">
      </div>
    </div>

    <div class="product-info-new">

      <span class="category-pill-new">
        <?= htmlspecialchars($p['category'] ?? 'Mỹ phẩm', ENT_QUOTES, 'UTF-8') ?>
      </span>

      <h1><?= htmlspecialchars($p['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>

      <div class="rating-new">
        <span><?= gb_product_star_text($avgRating) ?></span>
        <b><?= htmlspecialchars($displayRating) ?></b>
        <em>(<?= (int)$totalReviews ?> đánh giá)</em>
        <i></i>
        <em>Đã bán <?= htmlspecialchars(gb_format_sold($displaySold)) ?></em>
      </div>

      <div class="price-box-new">
        <strong><?= number_format($p['price'], 0, ',', '.') ?>đ</strong>
        <del><?= number_format($p['price'] * 1.2, 0, ',', '.') ?>đ</del>
        <span>-20%</span>
      </div>

      <div class="stock-new">
        ✓ <?= $p['stock'] <= 0 ? 'Đã hết hàng' : ((int)$p['stock'] < 3 ? 'Sắp hết hàng: còn ' . $p['stock'] . ' sản phẩm' : 'Còn hàng: ' . $p['stock'] . ' sản phẩm') ?>
      </div>

      <div class="offer-new">
        <div>🎁</div>
        <div>
          <b>Ưu đãi đặc biệt</b>
          <p>Freeship cho đơn hàng từ 1.000.000đ</p>
        </div>
      </div>

      <div class="quantity-new">
        <label>Số lượng</label>

        <div class="qty-line-new">
          <button type="button" onclick="changeQty(-1)">−</button>

          <input id="qtyInput" type="number" value="1" min="1" data-stock="<?= (int)$p['stock'] ?>">

          <button type="button" onclick="changeQty(1)">+</button>

          <span>Còn <?= $p['stock'] ?> sản phẩm</span>
        </div>
        <div id="qtyWarningBox" class="qty-warning-new" style="display:none"></div>
      </div>

      <div class="product-actions-new">
        <a id="addCartBtn" class="btn-add-cart-new <?= $p['stock'] <= 0 ? 'disabled' : '' ?>"
          href="<?= $p['stock'] > 0 ? gb_url('cart/add?id=' . ($p['id'] ?? 0)) : '#' ?>" data-base-url="<?= gb_url('cart/add?id=' . ($p['id'] ?? 0)) ?>">
          🛒 Thêm vào giỏ hàng
        </a>

        <a id="buyNowBtn" class="btn-buy-now-new <?= $p['stock'] <= 0 ? 'disabled' : '' ?>"
          href="<?= $p['stock'] > 0 ? gb_url('cart/add-checkout?id=' . ($p['id'] ?? 0)) : '#' ?>" data-base-url="<?= gb_url('cart/add-checkout?id=' . ($p['id'] ?? 0)) ?>">
          ⚡ Mua ngay
        </a>
      </div>

      <div class="service-box-new">
        <div>🚚 <b>Giao hàng</b><span>Toàn quốc</span></div>
        <div>🔄 <b>Đổi trả</b><span>Trong 7 ngày</span></div>
        <div>🛡️ <b>Chính hãng</b><span>Cam kết 100%</span></div>
        <div>☎️ <b>Hotline</b><span>0865155687</span></div>
      </div>

    </div>

  </div>

  <div class="product-bottom-new">

    <div class="product-desc-new">

      <div class="tab-title-new">
        <span class="tab-btn active" onclick="openTab(event, 'desc')">📝 MÔ TẢ</span>
        <span class="tab-btn" onclick="openTab(event, 'ingredient')">🌿 THÀNH PHẦN</span>
        <span class="tab-btn" onclick="openTab(event, 'usage')">💄 CÁCH DÙNG</span>
        <span class="tab-btn" onclick="openTab(event, 'review')">⭐ ĐÁNH GIÁ</span>
      </div>

      <div class="desc-content-new">

        <div id="desc" class="tab-content active">
          <p><?= nl2br(htmlspecialchars($p['benefit'] ?? $p['description'] ?? 'Sản phẩm được chọn lọc tại GlowBeauty.', ENT_QUOTES, 'UTF-8')) ?></p>
          <ul>
            <li>Lên màu chuẩn, bám màu tốt và lâu trôi.</li>
            <li>Phù hợp trang điểm đi học, đi làm và dự tiệc.</li>
            <li>Thiết kế nhỏ gọn, dễ mang theo trong túi.</li>
          </ul>
        </div>

        <div id="ingredient" class="tab-content">
          <p><?= nl2br(htmlspecialchars($p['ingredients'] ?? 'Thông tin thành phần đang được cập nhật.', ENT_QUOTES, 'UTF-8')) ?></p>
        </div>

        <div id="usage" class="tab-content">
          <p><?= nl2br(htmlspecialchars($p['usage_text'] ?? 'Sử dụng theo hướng dẫn trên bao bì sản phẩm.', ENT_QUOTES, 'UTF-8')) ?></p>
        </div>

        <div id="review" class="tab-content">

          <div class="review-summary-new">
            <div>
              <strong><?= htmlspecialchars($displayRating) ?>/5</strong>
              <span><?= gb_product_star_text($avgRating) ?></span>
              <p>Dựa trên <?= (int)$totalReviews ?> đánh giá của khách hàng</p>
            </div>

            <div class="review-bars-new">
              <?php for($s=5;$s>=1;$s--):
                $count = (int)($reviewSummary['star'.$s] ?? 0);
                $percent = $totalReviews > 0 ? round($count * 100 / $totalReviews) : 0;
              ?>
                <p><?= $s ?> sao <span><b style="width:<?= $percent ?>%"></b></span> <?= $percent ?>%</p>
              <?php endfor; ?>
            </div>
          </div>

          <div class="review-list-new">
            <?php if(empty($productReviews)): ?>
              <div class="review-empty-new">Chưa có đánh giá thực tế cho sản phẩm này.</div>
            <?php else: ?>
              <?php foreach($productReviews as $rv):
                $name = trim((string)($rv['customer_name'] ?? '')); if($name==='') $name='Khách hàng GlowBeauty';
                $initial = mb_substr($name, 0, 1, 'UTF-8');
              ?>
                <div class="review-item-new">
                  <div class="review-avatar-new"><?= htmlspecialchars($initial) ?></div>
                  <div>
                    <b><?= htmlspecialchars($name) ?></b>
                    <span><?= gb_product_star_text($rv['rating'] ?? 5) ?></span>
                    <p><?= nl2br(htmlspecialchars($rv['comment'] ?? '')) ?></p>
                    <?php if(!empty($rv['image'])): ?>
                      <img class="product-review-photo" src="<?= BASE_URL ?>public/uploads/reviews/<?= htmlspecialchars($rv['image']) ?>" alt="Ảnh đánh giá">
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        </div>

      </div>
    </div>

    <?php
    $suggestProducts = Product::all();
    ?>

    <div class="suggest-box-new">
      <h3>Bạn có thể thích</h3>

      <?php foreach ($suggestProducts as $sp): ?>
        <?php if ($sp['id'] == $p['id'])
          continue; ?>

        <a href="<?= gb_url('product?id='.($sp['id'] ?? 0)) ?>" class="suggest-item-new">
          <div class="suggest-img-new">
            <img src="<?= gb_image_url($sp['image'] ?? '') ?>"
              alt="<?= htmlspecialchars($sp['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <b><?= htmlspecialchars($sp['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></b>
          <span><?= number_format($sp['price'], 0, ',', '.') ?>đ</span>
        </a>

      <?php endforeach; ?>
    </div>

  </div>

</section>

<script>
  function openTab(event, tabId) {
    document.querySelectorAll('.tab-content').forEach(function (tab) {
      tab.classList.remove('active');
    });

    document.querySelectorAll('.tab-btn').forEach(function (btn) {
      btn.classList.remove('active');
    });

    document.getElementById(tabId).classList.add('active');
    event.currentTarget.classList.add('active');
  }

  function gbShowStockToast(message) {
    let toast = document.getElementById('gbStockToast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'gbStockToast';
      toast.className = 'gb-ajax-toast gb-stock-toast';
      document.body.appendChild(toast);
    }
    toast.innerHTML = '<span class="gb-toast-check">!</span><span>' + message + '</span><button type="button" class="gb-toast-close" aria-label="Đóng">×</button>';
    toast.style.borderColor = '#f3b08b';
    toast.style.background = '#fff4ec';
    toast.style.color = '#b84216';
    toast.classList.add('show');
    const closeBtn = toast.querySelector('.gb-toast-close');
    if (closeBtn) closeBtn.onclick = function(){ toast.classList.remove('show'); };
    clearTimeout(toast._timer);
    toast._timer = setTimeout(function(){ toast.classList.remove('show'); }, 4200);
  }

  function getStockQty() {
    const input = document.getElementById('qtyInput');
    return Math.max(0, parseInt((input && input.dataset.stock) || 0));
  }

  function setQtyWarning(message) {
    const box = document.getElementById('qtyWarningBox');
    if (!box) return;
    if (message) {
      box.innerHTML = '⚠️ ' + message;
      box.style.display = 'flex';
    } else {
      box.innerHTML = '';
      box.style.display = 'none';
    }
  }

  function getSelectedQty() {
    const input = document.getElementById('qtyInput');
    const raw = parseInt(input && input.value !== '' ? input.value : 0);
    return isNaN(raw) ? 0 : raw;
  }

  function validateQty(showMessage) {
    const input = document.getElementById('qtyInput');
    const stock = getStockQty();
    const qty = getSelectedQty();

    if (qty < 1) {
      const msg = 'Vui lòng nhập số lượng từ 1 sản phẩm trở lên.';
      setQtyWarning(msg);
      if (showMessage) gbShowStockToast(msg);
      if (input) input.classList.add('qty-error-new');
      return false;
    }

    if (stock <= 0) {
      const msg = 'Sản phẩm đã hết hàng, không thể mua hoặc thêm vào giỏ.';
      setQtyWarning(msg);
      if (showMessage) gbShowStockToast(msg);
      return false;
    }

    if (qty > stock) {
      const msg = 'Sản phẩm không đủ số lượng bạn cần. Kho hiện chỉ còn ' + stock + ' sản phẩm, vui lòng giảm số lượng trước khi mua.';
      setQtyWarning(msg);
      if (showMessage) gbShowStockToast(msg);
      if (input) input.classList.add('qty-error-new');
      return false;
    }

    setQtyWarning('');
    if (input) input.classList.remove('qty-error-new');
    return true;
  }

  function changeQty(change) {
    const input = document.getElementById('qtyInput');
    const max = getStockQty();
    let value = getSelectedQty() + change;

    if (value < 1) value = 1;
    if (max > 0 && value > max) {
      value = max;
      gbShowStockToast('Sản phẩm không đủ số lượng bạn cần. Kho hiện chỉ còn ' + max + ' sản phẩm.');
    }

    input.value = value;
    validateQty(false);
    updateQtyLinks();
  }

  function updateQtyLinks() {
    const qty = getSelectedQty();
    ['addCartBtn','buyNowBtn'].forEach(function(id){
      const a = document.getElementById(id);
      if (!a || a.classList.contains('disabled')) return;
      const base = a.getAttribute('data-base-url') || a.getAttribute('href');
      try {
        const u = new URL(base, window.location.origin);
        u.searchParams.set('qty', String(qty));
        a.setAttribute('href', u.pathname + u.search);
      } catch(err) {
        const cleanBase = String(base).replace(/([?&])qty=[^&]*&?/g, '$1').replace(/[?&]$/, '');
        a.setAttribute('href', cleanBase + (cleanBase.indexOf('?') >= 0 ? '&' : '?') + 'qty=' + qty);
      }
    });
  }

  function bindStockGuard(id) {
    const a = document.getElementById(id);
    if (!a) return;
    a.addEventListener('click', function(e){
      if (!validateQty(true)) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
      updateQtyLinks();
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    const input = document.getElementById('qtyInput');
    if (input) {
      input.addEventListener('input', function(){
        const stock = getStockQty();
        const qty = getSelectedQty();
        if (qty > stock) {
          validateQty(false);
          clearTimeout(input._stockToastTimer);
          input._stockToastTimer = setTimeout(function(){
            gbShowStockToast('Sản phẩm không đủ số lượng bạn cần. Kho hiện chỉ còn ' + stock + ' sản phẩm, vui lòng giảm số lượng.');
          }, 120);
        } else {
          validateQty(false);
        }
        updateQtyLinks();
      });
      input.addEventListener('blur', function(){
        validateQty(true);
      });
    }
    bindStockGuard('addCartBtn');
    bindStockGuard('buyNowBtn');
    updateQtyLinks();
  });
</script>
<script>/* GlowBeauty detail page enhanced */</script>

<style>.product-review-photo{max-width:140px;border-radius:12px;margin-top:8px;border:1px solid #f0d2c2}</style>

<style>
.product-review-photo{display:block;margin-top:10px;max-width:180px;max-height:180px;object-fit:cover;border-radius:14px;border:1px solid #f2d4c3}
.review-empty-new{padding:18px;border:1px dashed #efcdbb;border-radius:16px;color:#8a5542;background:#fff8f3;font-weight:700}
</style>
