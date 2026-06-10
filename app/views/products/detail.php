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
        <span>★★★★★</span>
        <b>4.9</b>
        <em>(125 đánh giá)</em>
        <i></i>
        <em>Đã bán 1.2k+</em>
      </div>

      <div class="price-box-new">
        <strong><?= number_format($p['price'], 0, ',', '.') ?>đ</strong>
        <del><?= number_format($p['price'] * 1.2, 0, ',', '.') ?>đ</del>
        <span>-20%</span>
      </div>

      <div class="stock-new">
        ✓ <?= $p['stock'] > 0 ? 'Còn hàng: ' . $p['stock'] . ' sản phẩm' : 'Đã hết hàng' ?>
      </div>

      <div class="offer-new">
        <div>🎁</div>
        <div>
          <b>Ưu đãi đặc biệt</b>
          <p>Freeship cho đơn hàng từ 500.000đ</p>
        </div>
      </div>

      <div class="quantity-new">
        <label>Số lượng</label>

        <div class="qty-line-new">
          <button type="button" onclick="changeQty(-1)">−</button>

          <input id="qtyInput" type="number" value="1" min="1" max="<?= $p['stock'] ?>">

          <button type="button" onclick="changeQty(1)">+</button>

          <span>Còn <?= $p['stock'] ?> sản phẩm</span>
        </div>
      </div>

      <div class="product-actions-new">
        <a id="addCartBtn" class="btn-add-cart-new <?= $p['stock'] <= 0 ? 'disabled' : '' ?>"
          href="<?= $p['stock'] > 0 ? gb_url('cart/add?id=' . ($p['id'] ?? 0)) : '#' ?>">
          🛒 Thêm vào giỏ hàng
        </a>

        <a id="buyNowBtn" class="btn-buy-now-new <?= $p['stock'] <= 0 ? 'disabled' : '' ?>"
          href="<?= $p['stock'] > 0 ? gb_url('cart/add?id=' . ($p['id'] ?? 0)) : '#' ?>">
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
              <strong>4.9/5</strong>
              <span>★★★★★</span>
              <p>Dựa trên 125 đánh giá của khách hàng</p>
            </div>

            <div class="review-bars-new">
              <p>5 sao <span><b style="width:92%"></b></span> 92%</p>
              <p>4 sao <span><b style="width:6%"></b></span> 6%</p>
              <p>3 sao <span><b style="width:2%"></b></span> 2%</p>
            </div>
          </div>

          <div class="review-list-new">

            <div class="review-item-new">
              <div class="review-avatar-new">L</div>
              <div>
                <b>Lan Anh</b>
                <span>★★★★★</span>
                <p>Màu lên rất đẹp, chất mịn, dùng đi học hay đi làm đều hợp. Đóng gói cũng rất xinh.</p>
              </div>
            </div>

            <div class="review-item-new">
              <div class="review-avatar-new">M</div>
              <div>
                <b>Minh Trang</b>
                <span>★★★★★</span>
                <p>Sản phẩm giống ảnh, màu nhẹ nhàng, dễ tán và không bị bột nhiều. Mình rất thích.</p>
              </div>
            </div>

            <div class="review-item-new">
              <div class="review-avatar-new">H</div>
              <div>
                <b>Hoàng Mai</b>
                <span>★★★★☆</span>
                <p>Thiết kế đẹp, cầm chắc tay. Màu phù hợp makeup tự nhiên, giao hàng nhanh.</p>
              </div>
            </div>

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

  function changeQty(change) {
    const input = document.getElementById('qtyInput');
    const max = parseInt(input.max);
    let value = parseInt(input.value || 1) + change;

    if (value < 1) value = 1;
    if (value > max) value = max;

    input.value = value;
  }
</script>
<script>/* GlowBeauty detail page enhanced */</script>
