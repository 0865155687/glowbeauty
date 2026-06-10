<section class="about-store-hero about-clean">
  <div class="container about-store-grid">
    <div class="about-store-copy">
      <span>Về cửa hàng GlowBeauty</span>
      <h1>Showroom mỹ phẩm & makeup cao cấp tại Hải Phòng</h1>
      <p><b>GlowBeauty</b> là cửa hàng mỹ phẩm và makeup theo phong cách rose-gold nữ tính, chuyên các dòng sản phẩm
        chăm sóc da, trang điểm nền, son môi, phấn má, phấn mắt và combo làm đẹp.</p>
      <p>Khách hàng có thể xem thông tin sản phẩm, giá bán, tình trạng còn hàng và đặt mua thuận tiện ngay trên website.</p>
      <a class="btn" href="<?= BASE_URL ?>products">Xem sản phẩm</a>
    </div>
    <div class="about-store-image about-slider-final" id="aboutSliderFinal">

      <div class="about-final-track">

        <div class="about-final-slide">
          <img src="<?= gb_image_url('about-banner-glowbeauty.png') ?>" alt="Banner GlowBeauty">
        </div>

        <div class="about-final-slide">
          <img src="<?= gb_image_url('about_showroom.png') ?>" alt="Showroom GlowBeauty">
        </div>

      </div>

      <button class="about-final-arrow about-final-prev">‹</button>
      <button class="about-final-arrow about-final-next">›</button>

      <div class="about-final-dots">
        <span class="active"></span>
        <span></span>
      </div>

    </div>
  </div>
</section>
<section class="container about-info-section">
  <div class="section-head compact"><span>GlowBeauty Store</span>
    <h2>Vì sao khách hàng chọn GlowBeauty?</h2>
    <p>Không chỉ bán mỹ phẩm, GlowBeauty muốn mang đến trải nghiệm mua sắm nhẹ nhàng, rõ ràng và đáng tin như một
      showroom thật.</p>
  </div>
  <div class="about-value-grid about-story-grid">
    <article><i>💎</i>
      <h3>Không gian rose-gold cao cấp</h3>
      <p>Không gian và hình ảnh thương hiệu được xây dựng theo phong cách nữ tính, sang trọng và chỉn chu.</p>
    </article>
    <article><i>🧴</i>
      <h3>Sản phẩm có thông tin minh bạch</h3>
      <p>Mỗi sản phẩm đều có hình ảnh, giá bán, công dụng, thành phần và hướng dẫn sử dụng để khách dễ chọn đúng nhu
        cầu.</p>
    </article>
    <article><i>🛒</i>
      <h3>Mua hàng nhanh, tư vấn thật</h3>
      <p>Khách có thể xem chi tiết, thêm giỏ hàng, đặt mua và liên hệ Zalo/Facebook để được tư vấn tone da, routine hoặc
        combo phù hợp.</p>
    </article>
    <article><i>📦</i>
      <h3>Quản trị bán hàng thực tế</h3>
      <p>Hệ thống quản trị hỗ trợ theo dõi sản phẩm, tồn kho, đơn hàng, doanh thu và chi tiết đơn hàng.</p>
    </article>
  </div>
</section>



<script>
  document.addEventListener('DOMContentLoaded', function () {
    const slider = document.getElementById('aboutSliderFinal');
    if (!slider) return;

    const track = slider.querySelector('.about-final-track');
    const slides = slider.querySelectorAll('.about-final-slide');
    const dots = slider.querySelectorAll('.about-final-dots span');
    const next = slider.querySelector('.about-final-next');
    const prev = slider.querySelector('.about-final-prev');
    let index = 0;
    let timer = null;

    function render() {
      track.style.transform = 'translateX(-' + (index * 100) + '%)';
      dots.forEach(function (dot, i) {
        dot.classList.toggle('active', i === index);
      });
    }

    function go(step) {
      index = (index + step + slides.length) % slides.length;
      render();
    }

    function start() {
      stop();
      timer = setInterval(function () { go(1); }, 6000);
    }

    function stop() {
      if (timer) { clearInterval(timer); timer = null; }
    }

    if (next) { next.addEventListener('click', function () { go(1); start(); }); }
    if (prev) { prev.addEventListener('click', function () { go(-1); start(); }); }
    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () { index = i; render(); start(); });
    });

    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);

    render();
    start();
  });
</script>