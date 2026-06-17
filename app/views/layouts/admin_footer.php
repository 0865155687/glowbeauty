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
/* Nút chuông Admin: không dùng giọng đọc, chỉ chuông + popup */
.gb-voice-toggle{
    position:fixed;
    right:20px;
    bottom:22px;
    width:58px;
    height:58px;
    border:0;
    border-radius:50%;
    background:linear-gradient(135deg,#c07a3a,#a85b2a);
    color:#fff;
    font-size:24px;
    cursor:pointer;
    z-index:99999;
    box-shadow:0 18px 38px rgba(75,32,14,.28);
    display:flex;
    align-items:center;
    justify-content:center;
    transition:.2s ease;
}
.gb-voice-toggle:hover{transform:translateY(-2px) scale(1.04);}
.gb-voice-toggle.is-off{background:linear-gradient(135deg,#7c7c7c,#4b5563);}
.gb-voice-toggle.has-new::after{
    content:"";
    position:absolute;
    top:6px;
    right:6px;
    width:13px;
    height:13px;
    background:#ef4444;
    border:2px solid #fff;
    border-radius:50%;
    animation:gbNotifyPing 1.15s infinite;
}
@keyframes gbNotifyPing{0%{box-shadow:0 0 0 0 rgba(239,68,68,.6)}70%{box-shadow:0 0 0 10px rgba(239,68,68,0)}100%{box-shadow:0 0 0 0 rgba(239,68,68,0)}}

.gb-admin-toast-wrap{
    position:fixed;
    right:22px;
    bottom:92px;
    width:min(360px,calc(100vw - 32px));
    display:grid;
    gap:12px;
    z-index:99998;
    pointer-events:none;
}
.gb-admin-toast{
    pointer-events:auto;
    background:#fffaf7;
    border:1px solid #efcfbf;
    border-radius:22px;
    box-shadow:0 22px 48px rgba(65,28,15,.22);
    padding:16px 17px;
    color:#35180f;
    animation:gbToastIn .25s ease both;
}
.gb-admin-toast-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:9px;
}
.gb-admin-toast-title{
    font-weight:800;
    font-size:17px;
    color:#8c421f;
}
.gb-admin-toast-close{
    border:0;
    background:#fff0e6;
    color:#8c421f;
    width:30px;
    height:30px;
    border-radius:50%;
    cursor:pointer;
    font-weight:800;
}
.gb-admin-toast p{
    margin:4px 0;
    font-size:14px;
    line-height:1.45;
}
.gb-admin-toast b{color:#35180f;}
.gb-admin-toast-actions{
    margin-top:12px;
    display:flex;
    gap:8px;
    justify-content:flex-end;
}
.gb-admin-toast-actions a{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:36px;
    padding:0 14px;
    border-radius:999px;
    background:#c07a3a;
    color:white;
    font-weight:700;
    font-size:13px;
    text-decoration:none;
}
@keyframes gbToastIn{from{opacity:0;transform:translateY(12px) scale(.98)}to{opacity:1;transform:none}}
@media(max-width:700px){
    .gb-voice-toggle{right:14px;bottom:14px;width:52px;height:52px;font-size:23px;}
    .gb-admin-toast-wrap{right:14px;bottom:76px;}
}
</style>

<button type="button" id="gbVoiceToggle" class="gb-voice-toggle" title="Bật/tắt chuông thông báo đơn hàng" aria-label="Bật/tắt chuông thông báo đơn hàng">🔕</button>
<div id="gbAdminToastWrap" class="gb-admin-toast-wrap" aria-live="polite"></div>

<script>
(function(){
    var statusUrl = '<?= BASE_URL ?>admin/notification-status';

    // Đổi sang key mới để loại bỏ lỗi lưu ID cũ trong localStorage.
    var notifyEnabledKey = 'gb_admin_notify_enabled_v2';
    var lastOrderKey = 'gb_admin_last_order_id_v2';
    var lastContactKey = 'gb_admin_last_contact_id_v2';

    var pollMs = 15000;
    var toggle = document.getElementById('gbVoiceToggle');
    var toastWrap = document.getElementById('gbAdminToastWrap');
    var audioCtx = null;
    var checking = false;

    function isNotifyEnabled(){
        return localStorage.getItem(notifyEnabledKey) === '1';
    }

    function unlockAudio(){
        try{
            var Ctx = window.AudioContext || window.webkitAudioContext;
            if(!Ctx) return;
            if(!audioCtx) audioCtx = new Ctx();
            if(audioCtx.state === 'suspended') audioCtx.resume();
        }catch(e){}
    }

    function playOneTing(offset){
        try{
            unlockAudio();
            if(!audioCtx) return;
            var now = audioCtx.currentTime + (offset || 0);

            // Âm "ting" lớn, rõ, không phụ thuộc file mp3.
            [
                {freq:880, start:0.00, dur:0.16, vol:0.58},
                {freq:1320, start:0.03, dur:0.20, vol:0.38},
                {freq:1760, start:0.08, dur:0.12, vol:0.22}
            ].forEach(function(note){
                var osc = audioCtx.createOscillator();
                var gain = audioCtx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(note.freq, now + note.start);

                gain.gain.setValueAtTime(0.0001, now + note.start);
                gain.gain.exponentialRampToValueAtTime(note.vol, now + note.start + 0.025);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + note.start + note.dur);

                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now + note.start);
                osc.stop(now + note.start + note.dur + 0.05);
            });
        }catch(e){}
    }

    function playNotifySound(){
        if(!isNotifyEnabled()) return;

        // Ting ting thật rõ: 3 tiếng liên tiếp.
        playOneTing(0);
        playOneTing(0.55);
        playOneTing(1.10);
    }

    function updateToggle(){
        if(!toggle) return;
        var on = isNotifyEnabled();
        toggle.textContent = on ? '🔔' : '🔕';
        toggle.classList.toggle('is-off', !on);
        toggle.title = on ? 'Đang bật chuông + popup đơn hàng mới - bấm để tắt' : 'Đang tắt chuông + popup đơn hàng mới - bấm để bật';
        toggle.setAttribute('aria-label', toggle.title);
    }

    function markNew(){
        if(!toggle) return;
        toggle.classList.add('has-new');
        clearTimeout(window.__gbNotifyDotTimer);
        window.__gbNotifyDotTimer = setTimeout(function(){
            toggle.classList.remove('has-new');
        }, 16000);
    }

    function money(v){
        v = parseInt(v || 0, 10);
        if(isNaN(v)) v = 0;
        return v.toLocaleString('vi-VN') + 'đ';
    }

    function htmlEscape(s){
        return String(s == null ? '' : '').replace(/[&<>"']/g, function(c){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
        });
    }

    function showToast(type, data){
        if(!toastWrap || !isNotifyEnabled()) return;

        var isOrder = type === 'order';
        var title = isOrder ? '🔔 Đơn hàng mới' : '💬 Yêu cầu tư vấn mới';
        var body = '';

        if(isOrder && data){
            // Popup đơn hàng mới: chỉ hiện thông báo + nút xem đơn hàng cho gọn.
            body = '<p>Bạn vừa có đơn hàng mới cần kiểm tra.</p>';
        }else if(data){
            body =
                '<p>Khách hàng: <b>' + htmlEscape(data.name || data.customer_name || 'Khách hàng') + '</b></p>' +
                '<p>SĐT: <b>' + htmlEscape(data.phone || '') + '</b></p>';
        }else{
            body = '<p>Bạn có thông báo mới cần kiểm tra.</p>';
        }

        var div = document.createElement('div');
        div.className = 'gb-admin-toast';
        div.innerHTML =
            '<div class="gb-admin-toast-head">' +
                '<div class="gb-admin-toast-title">' + title + '</div>' +
                '<button type="button" class="gb-admin-toast-close" aria-label="Đóng">×</button>' +
            '</div>' +
            body +
            '<div class="gb-admin-toast-actions">' +
                '<a href="<?= BASE_URL ?>admin/orders">Xem đơn hàng</a>' +
            '</div>';

        toastWrap.prepend(div);
        div.querySelector('.gb-admin-toast-close').addEventListener('click', function(){
            div.remove();
        });
        setTimeout(function(){
            if(div && div.parentNode) div.remove();
        }, 18000);
    }

    function parseIntSafe(v){
        v = parseInt(v || '0', 10);
        return isNaN(v) ? 0 : v;
    }

    function notifyOrder(order){
        markNew();
        playNotifySound();
        showToast('order', order || null);
    }

    function notifyContact(contact){
        markNew();
        playNotifySound();
        showToast('contact', contact || null);
    }

    function baselineCurrent(data){
        localStorage.setItem(lastOrderKey, String(parseIntSafe(data && data.latest_order_id)));
        localStorage.setItem(lastContactKey, String(parseIntSafe(data && data.latest_contact_id)));
    }

    function check(options){
        options = options || {};
        if(checking) return;
        checking = true;

        fetch(statusUrl + '?_=' + Date.now(), {cache:'no-store', credentials:'same-origin'})
            .then(function(r){ return r.json(); })
            .then(function(data){
                if(!data || !data.ok) return;

                var latestOrder = parseIntSafe(data.latest_order_id);
                var latestContact = parseIntSafe(data.latest_contact_id);
                var oldOrderRaw = localStorage.getItem(lastOrderKey);
                var oldContactRaw = localStorage.getItem(lastContactKey);
                var oldOrder = parseIntSafe(oldOrderRaw);
                var oldContact = parseIntSafe(oldContactRaw);

                // Lần đầu mở admin: chỉ lấy mốc hiện tại, không kêu với đơn cũ.
                // Sau đó khách đặt đơn mới ID lớn hơn mốc này thì sẽ tự kêu.
                if(options.baselineOnly || oldOrderRaw === null || oldContactRaw === null){
                    baselineCurrent(data);
                    return;
                }

                if(latestOrder > oldOrder){
                    localStorage.setItem(lastOrderKey, String(latestOrder));
                    notifyOrder(data.latest_order || {id: latestOrder});
                }

                if(latestContact > oldContact){
                    localStorage.setItem(lastContactKey, String(latestContact));
                    notifyContact(data.latest_contact || null);
                }
            })
            .catch(function(){})
            .finally(function(){ checking = false; });
    }

    if(toggle){
        updateToggle();

        toggle.addEventListener('click', function(){
            unlockAudio();

            var next = isNotifyEnabled() ? '0' : '1';
            localStorage.setItem(notifyEnabledKey, next);
            toggle.classList.remove('has-new');
            updateToggle();

            if(next === '1'){
                // Khi bật chuông: phát thử 1 lần để Chrome mở quyền âm thanh,
                // đồng thời lấy mốc đơn hiện tại để đơn cũ không báo lại.
                playNotifySound();
                check({baselineOnly:true});
            }
        });
    }

    // Bất kỳ click đầu tiên trong admin cũng mở khóa âm thanh cho Chrome.
    document.addEventListener('click', unlockAudio, {once:true});

    // Lấy mốc hiện tại khi mới vào trang, sau đó tự kiểm tra đơn mới mỗi 3 giây.
    check({baselineOnly:false});
    setInterval(function(){
        if(isNotifyEnabled()) check();
    }, pollMs);
})();
</script>

</body></html>
