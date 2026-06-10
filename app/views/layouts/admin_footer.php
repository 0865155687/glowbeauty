</main>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Giữ nguyên vị trí cuộn sau khi cập nhật form trong admin
    var params = new URLSearchParams(window.location.search);
    if (params.has('_scroll_y')) {
        var y = parseInt(params.get('_scroll_y') || '0', 10);
        if (!isNaN(y) && y > 0) {
            setTimeout(function(){ window.scrollTo(0, y); }, 0);
            setTimeout(function(){ window.scrollTo(0, y); }, 80);
        }
    }
    document.querySelectorAll('form').forEach(function(form){
        form.addEventListener('submit', function(){
            var field = form.querySelector('[name="_scroll_y"]');
            if (!field) {
                field = document.createElement('input');
                field.type = 'hidden';
                field.name = '_scroll_y';
                form.appendChild(field);
            }
            field.value = String(window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0);
        }, true);
    });
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
/* Nút loa Admin: chỉ giữ 1 icon tròn ở góc phải dưới */
.gb-voice-toggle{
    position:fixed;
    right:20px;
    bottom:20px;
    z-index:99999;
    width:58px;
    height:58px;
    border:0;
    border-radius:50%;
    background:linear-gradient(135deg,#8b4f31,#c9864b);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:25px;
    line-height:1;
    box-shadow:0 12px 34px rgba(75,39,20,.28);
    cursor:pointer;
    transition:.18s ease;
}
.gb-voice-toggle:hover{transform:translateY(-2px) scale(1.04);}
.gb-voice-toggle.is-off{background:linear-gradient(135deg,#7c7c7c,#4b5563);}
.gb-voice-toggle.has-new::after{
    content:'';
    position:absolute;
    right:6px;
    top:5px;
    width:13px;
    height:13px;
    background:#ef4444;
    border:2px solid #fff;
    border-radius:50%;
    animation:gbVoicePing 1.15s infinite;
}
@keyframes gbVoicePing{0%{box-shadow:0 0 0 0 rgba(239,68,68,.6)}70%{box-shadow:0 0 0 10px rgba(239,68,68,0)}100%{box-shadow:0 0 0 0 rgba(239,68,68,0)}}
@media(max-width:700px){.gb-voice-toggle{right:14px;bottom:14px;width:52px;height:52px;font-size:23px;}}
</style>

<button type="button" id="gbVoiceToggle" class="gb-voice-toggle" title="Bật/tắt báo giọng nói" aria-label="Bật/tắt báo giọng nói">🔇</button>

<script>
(function(){
    var statusUrl = '<?= BASE_URL ?>admin/notification-status';
    var voiceEnabledKey = 'gb_admin_voice_enabled';
    var lastOrderKey = 'gb_admin_last_order_id';
    var lastContactKey = 'gb_admin_last_contact_id';
    var firstRunKey = 'gb_admin_first_poll_done';
    var pollMs = 10000;
    var toggle = document.getElementById('gbVoiceToggle');

    function isVoiceEnabled(){ return localStorage.getItem(voiceEnabledKey) === '1'; }
    function updateToggle(){
        if(!toggle) return;
        var on = isVoiceEnabled();
        toggle.textContent = on ? '🔊' : '🔇';
        toggle.classList.toggle('is-off', !on);
        toggle.title = on ? 'Đang bật báo giọng nói - bấm để tắt' : 'Đang tắt báo giọng nói - bấm để bật';
        toggle.setAttribute('aria-label', toggle.title);
    }
    function speak(msg){
        if(!isVoiceEnabled()) return;
        if(!('speechSynthesis' in window)) return;
        try{
            window.speechSynthesis.cancel();
            var u = new SpeechSynthesisUtterance(msg);
            u.lang = 'vi-VN';
            u.rate = 1;
            u.pitch = 1;
            window.speechSynthesis.speak(u);
        }catch(e){}
    }
    function markNew(){
        if(!toggle) return;
        toggle.classList.add('has-new');
        clearTimeout(window.__gbVoiceDotTimer);
        window.__gbVoiceDotTimer = setTimeout(function(){ toggle.classList.remove('has-new'); }, 14000);
    }
    function parseIntSafe(v){ v=parseInt(v||'0',10); return isNaN(v)?0:v; }
    function check(){
        fetch(statusUrl, {cache:'no-store', credentials:'same-origin'})
            .then(function(r){ return r.json(); })
            .then(function(data){
                if(!data || !data.ok) return;
                var latestOrder = parseIntSafe(data.latest_order_id);
                var latestContact = parseIntSafe(data.latest_contact_id);
                var oldOrder = parseIntSafe(localStorage.getItem(lastOrderKey));
                var oldContact = parseIntSafe(localStorage.getItem(lastContactKey));
                var firstDone = localStorage.getItem(firstRunKey) === '1';

                if(!firstDone){
                    localStorage.setItem(lastOrderKey, String(latestOrder));
                    localStorage.setItem(lastContactKey, String(latestContact));
                    localStorage.setItem(firstRunKey, '1');
                    return;
                }

                if(latestOrder > oldOrder){
                    localStorage.setItem(lastOrderKey, String(latestOrder));
                    markNew();
                    speak('Bạn có 1 đơn hàng mới cần xử lý.');
                }
                if(latestContact > oldContact){
                    localStorage.setItem(lastContactKey, String(latestContact));
                    markNew();
                    speak('Bạn có 1 yêu cầu tư vấn mới từ khách hàng.');
                }
            })
            .catch(function(){});
    }

    if(toggle){
        updateToggle();
        toggle.addEventListener('click', function(){
            var next = isVoiceEnabled() ? '0' : '1';
            localStorage.setItem(voiceEnabledKey, next);
            toggle.classList.remove('has-new');
            updateToggle();
            if(next === '1') speak('Đã bật báo giọng nói cho GlowBeauty.');
        });
    }
    check();
    setInterval(check, pollMs);
})();
</script></script>

</body></html>
