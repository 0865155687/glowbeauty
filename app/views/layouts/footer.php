</main>
<?php $footerPage = $_GET['url'] ?? 'home'; if(!in_array($footerPage,['about','contact'])): ?>
<section class="service-strip">
  <div class="container service-grid">
    <div><i>🛡️</i><b>Cam kết chính hãng</b><span>Sản phẩm rõ nguồn gốc, thông tin và giá bán.</span></div>
    <div><i>🚚</i><b>Giao hàng toàn quốc</b><span>Hỗ trợ giao hàng và tư vấn trước khi đặt.</span></div>
    <div><i>💄</i><b>Tư vấn makeup</b><span>Tư vấn son, nền, phấn má phù hợp.</span></div>
    <div><i>🌸</i><b>Chăm sóc da</b><span>Tư vấn routine làm sạch, cấp ẩm và phục hồi.</span></div>
  </div>
</section>
<?php endif; ?>
<footer class="soft-footer elegant-footer">
  <div class="container soft-footer-grid">
    <div>
      <h3 class="footer-brand"><img src="<?= BASE_URL ?>public/assets/images/glowbeauty-logo-small.png" alt="GlowBeauty"> GlowBeauty</h3>
      <p>Mỹ phẩm & Makeup cao cấp phong cách rose-gold, mua sắm dễ dàng như một showroom online.</p>
    </div>
    <div>
      <h4>☎️ Hotline</h4>
      <p><a href="tel:0865155687">📞 0865155687 (Ngoan)</a><br><a href="tel:0394807683">📞 0394807683 (Ánh)</a></p>
    </div>
    <div>
      <h4>📍 Showroom</h4>
      <p>Số 3 Vũ Công Đán, phường Tứ Minh, Thành phố Hải Phòng<br>🕘 8:00 - 21:00 hằng ngày</p>
    </div>
    <div>
      <h4>🔗 Kết nối</h4>
      <p class="footer-actions">
        <a class="footer-pill" target="_blank" href="https://www.facebook.com/ngoanss.pess?mibextid=wwXIfr"><span class="fb-icon-real">f</span> Facebook</a>
        <a class="footer-pill" target="_blank" href="https://www.google.com/maps?q=Tr%C6%B0%E1%BB%9Dng+%C4%90%E1%BA%A1i+h%E1%BB%8Dc+Th%C3%A0nh+%C4%90%C3%B4ng,+S%E1%BB%91+03+V%C5%A9+C%C3%B4ng+%C4%90%C3%A1n,+T%E1%BB%A9+Minh,+H%E1%BA%A3i+Ph%C3%B2ng+171967">🗺️ Google Map</a>
      </p>
    </div>
  </div>
</footer>
<div class="floating-social" aria-label="Liên hệ nhanh GlowBeauty">
  <a class="float-btn fb" target="_blank" href="https://www.facebook.com/ngoanss.pess?mibextid=wwXIfr" title="Facebook">f</a>
  <a class="float-btn zalo" target="_blank" href="https://zalo.me/0865155687" title="Zalo">Zalo</a>
</div>


<button class="ai-chat-toggle" type="button" onclick="toggleGlowAi()" title="Trợ lý GlowBeauty">🤖</button>
<div class="ai-chatbox" id="glowAiBox">
  <div class="ai-chat-head">
    <div><strong>AI GlowBeauty</strong><span>Tư vấn sản phẩm • Tra cứu đơn hàng</span></div>
    <button class="ai-chat-close" type="button" onclick="toggleGlowAi()">×</button>
  </div>
  <div class="ai-chat-body" id="glowAiBody">
    <div class="ai-msg bot">Hellooo bạn đẹp ơi 👋
Mình là AI GlowBeauty — tư vấn mỹ phẩm, makeup, skincare và tra cứu đơn hàng cho bạn nè ✨</div>
    <div class="ai-quick-row">
      <button type="button" onclick="glowAiQuick('Da dầu nên dùng kem nền nào?')">Da dầu</button>
      <button type="button" onclick="glowAiQuick('Da ngăm hợp son màu gì?')">Da ngăm</button>
      <button type="button" onclick="glowAiQuick('Tôi muốn xem phấn má')">Phấn má</button>
      <button type="button" onclick="glowAiQuick('Kiểm tra đơn hàng của tôi')">Tra đơn</button>
    </div>
  </div>
  <div class="ai-chat-foot">
    <input id="glowAiInput" type="text" placeholder="Nhập câu hỏi hoặc mã đơn/số điện thoại..." onkeydown="if(event.key==='Enter') glowAiSend()">
    <button type="button" id="glowAiSendBtn" onclick="glowAiSend()">Gửi</button>
  </div>
</div>
<script>
function toggleGlowAi(){
  var box=document.getElementById('glowAiBox');
  if(box) box.classList.toggle('open');
}
function glowAiQuick(text){
  var input=document.getElementById('glowAiInput');
  if(input){ input.value=text; glowAiSend(); }
}
function glowAiAddMessage(type, text){
  var body=document.getElementById('glowAiBody');
  if(!body) return null;
  var div=document.createElement('div');
  div.className='ai-msg '+type;
  div.textContent=text;
  body.appendChild(div);
  body.scrollTop=body.scrollHeight;
  return div;
}
function glowAiSend(){
  var input=document.getElementById('glowAiInput');
  var btn=document.getElementById('glowAiSendBtn');
  if(!input) return;
  var text=input.value.trim();
  if(!text) return;
  glowAiAddMessage('user', text);
  input.value='';
  if(btn) btn.disabled=true;
  var loading=glowAiAddMessage('bot ai-loading','Mình xem nhanh cho bạn...');
  fetch('<?= BASE_URL ?>ai-chat/message', {
    method:'POST',
    headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body:JSON.stringify({message:text})
  })
  .then(function(res){ return res.json(); })
  .then(function(data){
    if(loading) loading.textContent=(data && data.reply) ? data.reply : 'Mình chưa hiểu đúng ý bạn. Bạn nhắn rõ hơn một chút nhé.';
  })
  .catch(function(){
    if(loading) loading.textContent='Chatbox đang mất kết nối. Bạn vui lòng thử lại hoặc gọi hotline 0865155687 nhé.';
  })
  .finally(function(){
    if(btn) btn.disabled=false;
    if(input) input.focus();
    var body=document.getElementById('glowAiBody');
    if(body) body.scrollTop=body.scrollHeight;
  });
}
</script>

<script src="<?= BASE_URL ?>public/assets/js/main.js?v=20260609-mobile-speaker-v1"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-phone-input]').forEach(function (input) {
        input.setAttribute('maxlength', '10');
        input.setAttribute('inputmode', 'numeric');
        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '').slice(0, 10);
        });
    });

    document.querySelectorAll('[data-name-input]').forEach(function (input) {
        input.addEventListener('input', function () {
            input.value = input.value.replace(/[0-9]/g, '');
        });
    });

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var phone = form.querySelector('[data-phone-input]');
            var name = form.querySelector('[data-name-input]');

            if (name && /[0-9]/.test(name.value)) {
                event.preventDefault();
                alert('Họ và tên không được chứa số.');
                name.focus();
                return;
            }

            if (name && name.value.trim().split(/\s+/).length < 2) {
                event.preventDefault();
                alert('Vui lòng nhập đầy đủ họ và tên.');
                name.focus();
                return;
            }

            if (phone && !/^[0-9]{10}$/.test(phone.value)) {
                event.preventDefault();
                alert('Số điện thoại phải đúng 10 số.');
                phone.focus();
            }
        });
    });
});
</script>

<script src="<?= BASE_URL ?>public/assets/js/ajax-cart.js?v=fix-cart-20260609-mobile-v1"></script>
<script src="<?= BASE_URL ?>public/assets/js/fix-no-favorite-tabs-ajax-cart.js?v=fix-cart-20260609-mobile-v1"></script>

<style>
.ai-chatbox{border-radius:24px!important;box-shadow:0 18px 45px rgba(86,42,22,.28)!important;overflow:hidden!important}
.ai-chat-head{background:linear-gradient(135deg,#7b4226,#a8693f)!important}
.ai-msg.bot{background:#fff3ec!important;border:1px solid #f4d4c5!important;line-height:1.55!important}
.ai-msg.user{background:linear-gradient(135deg,#9b623a,#7b4226)!important;color:#fff!important;border-radius:18px 18px 4px 18px!important}
.ai-quick-row{display:flex;gap:8px;flex-wrap:wrap;margin:8px 0 4px}
.ai-quick-row button{border:1px solid #efc8b7;background:#fff8f4;color:#7b4226;border-radius:999px;padding:7px 10px;font-weight:700;cursor:pointer}
.ai-quick-row button:hover{background:#ffe7dc}
.ai-chat-foot input{border-radius:999px!important}
.ai-chat-foot button{border-radius:999px!important;font-weight:800!important}
</style>

</body></html>
