document.addEventListener('DOMContentLoaded', function() {
    const chat = document.querySelector('#chatWidget');
    if(chat) {
        const toggle = chat.querySelector('.chat-toggle');
        if(toggle) toggle.addEventListener('click',()=>chat.classList.toggle('open'));
        chat.querySelectorAll('.quick-questions button').forEach(btn=>btn.addEventListener('click',()=> {
            const text=encodeURIComponent(btn.dataset.text||btn.textContent.trim());
            window.open('https://zalo.me/0865155687?text='+text,'_blank');
        }
        ));
    }
    function visibleCount(box) {
        if (!box) return 4;
        const rail = box.querySelector('.product-rail');
        if (!rail) return 4;
        const containerWidth = rail.getBoundingClientRect().width;
        const cards = box.querySelectorAll('.carousel-card');
        if (!cards.length) return 4;
        const cardWidth = cards[0].getBoundingClientRect().width || 330;
        const gap = parseFloat(getComputedStyle(rail).columnGap || getComputedStyle(rail).gap || 0) || 26;
        const n = Math.floor((containerWidth + gap) / (cardWidth + gap));
        return Math.max(1, n);
    }
    function setupCarousel(box) {
        const rail = box.querySelector('.product-rail');
        const cards = Array.from(box.querySelectorAll('.carousel-card'));
        const prev = box.querySelector('[data-prev]');
        const next = box.querySelector('[data-next]');
        if(!rail || !cards.length || !prev || !next) return;
        let index = 0;
        let dotsWrap = box.nextElementSibling;
        if(!dotsWrap || !dotsWrap.classList || !dotsWrap.classList.contains('carousel-dots')) {
            dotsWrap = document.createElement('div');
            dotsWrap.className = 'carousel-dots';
            box.insertAdjacentElement('afterend', dotsWrap);
        }
        function step() {
            const gap = parseFloat(getComputedStyle(rail).columnGap || getComputedStyle(rail).gap || 0) || 0;
            return cards[0].getBoundingClientRect().width + gap;
        }
        function maxIndex() {
            return Math.max(0, cards.length - Math.min(visibleCount(box), cards.length));
        }
        function buildDots() {
            dotsWrap.innerHTML = '';
            const max = maxIndex();
            for(let i=0;
            i<=max;
            i++) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'carousel-dot';
                dot.addEventListener('click', function() {
                    index = i;
                    render(true);
                }
                );
                dotsWrap.appendChild(dot);
            }
        }
        function render(smooth) {
            const max = maxIndex();
            if(index < 0) index = 0;
            if(index > max) index = max;
            rail.style.transform = 'none';
            rail.scrollTo( {
                left: index * step(), behavior: smooth ? 'smooth' : 'auto'
            }
            );
            prev.disabled = index === 0;
            next.disabled = index === max;
            prev.classList.toggle('is-disabled', prev.disabled);
            next.classList.toggle('is-disabled', next.disabled);
            const dots = dotsWrap.querySelectorAll('.carousel-dot');
            dots.forEach((d,i)=>d.classList.toggle('active', i === index));
            dotsWrap.style.display = max > 0 ? 'flex' : 'none';
        }
        prev.addEventListener('click', function(e) {
            e.preventDefault();
            if(index > 0) {
                index--;
                render(true);
            }
        }
        );
        next.addEventListener('click', function(e) {
            e.preventDefault();
            if(index < maxIndex()) {
                index++;
                render(true);
            }
        }
        );
        box._gbRefresh = function() {
            index = Math.min(index, maxIndex());
            buildDots();
            render(false);
        }
        ;
        buildDots();
        render(false);
    }
    document.querySelectorAll('[data-carousel]').forEach(setupCarousel);
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            document.querySelectorAll('[data-carousel]').forEach(box=>box._gbRefresh && box._gbRefresh());
        }
        , 120);
    }
    );
    const tabs = Array.from(document.querySelectorAll('.category-tab'));
    const panels = Array.from(document.querySelectorAll('.category-panel'));
    tabs.forEach(tab=> {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const id = tab.getAttribute('data-tab');
            tabs.forEach(t=>t.classList.remove('active'));
            panels.forEach(p=>p.classList.remove('active'));
            tab.classList.add('active');
            const panel = document.getElementById(id);
            if(panel) {
                panel.classList.add('active');
                const carousel = panel.querySelector('[data-carousel]');
                if(carousel && carousel._gbRefresh) carousel._gbRefresh();
            }
        }
        );
    }
    );
}
);
// GlowBeauty v53: banner trượt từ PHẢI sang TRÁI, mỗi ảnh 3s
(function() {
    function initGlowHero() {
        const hero = document.querySelector('[data-hero-slider]');
        if(!hero) return;
        const slides = Array.from(hero.querySelectorAll('.gb-hero-slide'));
        const dots = Array.from(hero.querySelectorAll('[data-hero-dot]'));
        if(slides.length < 2) return;
        let current = 0;
        let timer = null;
        let locked = false;
        const DURATION = 3000;
        const ANIM_TIME = 860;
        function prepare() {
            slides.forEach((s,i)=> {
                s.classList.remove('active','slide-out');
                s.style.visibility = i === current ? 'visible' : 'hidden';
                s.style.opacity = i === current ? '1' : '0';
                s.style.transform = i === current ? 'translateX(0)' : 'translateX(100%)';
                if(i === current) s.classList.add('active');
            }
            );
            dots.forEach((d,i)=>d.classList.toggle('active', i === current));
        }
        function go(nextIndex) {
            if(locked || nextIndex === current) return;
            locked = true;
            const old = current;
            current = (nextIndex + slides.length) % slides.length;
            slides.forEach((s,i)=> {
                if(i !== old && i !== current) {
                    s.classList.remove('active','slide-out');
                    s.style.visibility = 'hidden';
                    s.style.opacity = '0';
                    s.style.transform = 'translateX(100%)';
                }
            }
            );
            const outgoing = slides[old];
            const incoming = slides[current];
            incoming.classList.remove('slide-out');
            incoming.style.visibility = 'visible';
            incoming.style.opacity = '1';
            incoming.style.transform = 'translateX(100%)';
            requestAnimationFrame(()=> {
                requestAnimationFrame(()=> {
                    outgoing.classList.remove('active');
                    outgoing.classList.add('slide-out');
                    outgoing.style.visibility = 'visible';
                    outgoing.style.opacity = '1';
                    outgoing.style.transform = 'translateX(-100%)';
                    incoming.classList.add('active');
                    incoming.style.transform = 'translateX(0)';
                    dots.forEach((d,i)=>d.classList.toggle('active', i === current));
                }
                );
            }
            );
            setTimeout(()=> {
                outgoing.classList.remove('active','slide-out');
                outgoing.style.visibility = 'hidden';
                outgoing.style.opacity = '0';
                outgoing.style.transform = 'translateX(100%)';
                locked = false;
            }
            , ANIM_TIME);
        }
        function start() {
            clearInterval(timer);
            timer = setInterval(()=>go(current + 1), DURATION);
        }
        dots.forEach((dot,i)=>dot.addEventListener('click', function() {
            go(i);
            start();
        }
        ));
        prepare();
        start();
    }
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initGlowHero);
    else initGlowHero();
}
)();




/* GlowBeauty FIX CLICK HEART V9: trái tim hoạt động chắc chắn, AJAX toggle, không nhảy đầu trang */
(function(){
    function qsAll(sel){ return Array.prototype.slice.call(document.querySelectorAll(sel)); }
    function setCount(count){
        if(count === undefined || count === null) return;
        qsAll('.wishlist-count, .wish-count-badge').forEach(function(el){ el.textContent = count; });
    }
    function getScrollY(){ return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0; }
    function keepScroll(y){
        try { window.scrollTo({top:y, left:0, behavior:'auto'}); } catch(e){ window.scrollTo(0,y); }
        setTimeout(function(){ try { window.scrollTo({top:y, left:0, behavior:'auto'}); } catch(e){ window.scrollTo(0,y); } }, 20);
        setTimeout(function(){ try { window.scrollTo({top:y, left:0, behavior:'auto'}); } catch(e){ window.scrollTo(0,y); } }, 80);
    }
    function inlineMsg(heart, message, ok){
        var card = heart && heart.closest ? heart.closest('.product-card') : null;
        var msg = card ? card.querySelector('.favorite-inline-message') : null;
        if(!msg){ return toast(message, ok); }
        msg.innerHTML = '<span>' + (ok === false ? '!' : '✓') + '</span> ' + (message || 'Đã thêm vào mục yêu thích của bạn');
        msg.classList.toggle('is-error', ok === false);
        msg.classList.add('show');
        clearTimeout(msg._timer);
        msg._timer = setTimeout(function(){ msg.classList.remove('show'); }, 3000);
    }
    function toast(message, ok){
        var t = document.querySelector('.gb-ajax-toast');
        if(!t){
            t = document.createElement('div');
            t.className = 'gb-ajax-toast';
            document.body.appendChild(t);
        }
        t.innerHTML = '<span class="gb-toast-check">' + (ok === false ? '!' : '✓') + '</span><span>' + (message || '') + '</span>';
        t.classList.toggle('is-error', ok === false);
        t.classList.add('show');
        clearTimeout(t._timer);
        t._timer = setTimeout(function(){ t.classList.remove('show'); }, 3000);
    }
    function setHeart(heart, saved){
        if(!heart) return;
        heart.classList.toggle('is-saved', !!saved);
        if(saved){ heart.classList.add('just-saved'); setTimeout(function(){ heart.classList.remove('just-saved'); }, 450); }
        heart.setAttribute('title', saved ? 'Đã lưu sản phẩm' : 'Lưu sản phẩm');
        heart.setAttribute('aria-label', saved ? 'Đã lưu sản phẩm' : 'Lưu sản phẩm yêu thích');
    }
    window.gbToggleWishlist = function(ev, heart){
        if(ev){ ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation(); }
        heart = heart || (ev && ev.target ? ev.target.closest('.js-save-heart') : null);
        if(!heart) return false;
        var scrollY = getScrollY();
        if(heart.dataset.busy === '1') return false;
        var baseUrl = heart.getAttribute('data-url') || heart.getAttribute('href');
        if(!baseUrl){ inlineMsg(heart, 'Không tìm thấy đường dẫn lưu yêu thích.', false); return false; }
        var url = baseUrl + (baseUrl.indexOf('?') >= 0 ? '&' : '?') + 'ajax=1&_=' + Date.now();
        heart.dataset.busy = '1';
        heart.classList.add('is-loading');
        fetch(url, {
            method:'GET',
            credentials:'same-origin',
            cache:'no-store',
            headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
        })
        .then(function(res){
            return res.text().then(function(text){
                try { return JSON.parse(text); }
                catch(e) { throw new Error(text ? text.slice(0,160) : 'not-json'); }
            });
        })
        .then(function(data){
            if(data && data.login){
                inlineMsg(heart, data.message || 'Vui lòng đăng nhập để lưu sản phẩm.', false);
                return;
            }
            if(data && data.ok){
                setHeart(heart, !!data.saved);
                setCount(data.count);
                inlineMsg(heart, data.message || (data.saved ? 'Đã thêm vào mục yêu thích của bạn' : 'Đã bỏ sản phẩm khỏi mục yêu thích của bạn'), true);
            } else {
                inlineMsg(heart, (data && data.message) || 'Chưa xử lý được, vui lòng thử lại.', false);
            }
        })
        .catch(function(err){
            inlineMsg(heart, 'Chưa lưu được sản phẩm, vui lòng thử lại.', false);
            if(window.console) console.warn('Wishlist AJAX error:', err);
        })
        .finally(function(){
            heart.dataset.busy = '0';
            heart.classList.remove('is-loading');
            keepScroll(scrollY);
        });
        keepScroll(scrollY);
        return false;
    };
    document.addEventListener('click', function(e){
        var heart = e.target.closest && e.target.closest('.js-save-heart');
        if(!heart) return;
        window.gbToggleWishlist(e, heart);
    }, true);
    document.addEventListener('DOMContentLoaded', function(){
        qsAll('.toast, .wishlist-toast-final').forEach(function(t){
            clearTimeout(t._timer);
            t._timer = setTimeout(function(){
                t.classList.add('is-hide');
                setTimeout(function(){ if(t.parentNode) t.remove(); }, 350);
            }, 3000);
        });
    });
})();


/* HOTFIX V12 - xử lý tim yêu thích chắc chắn, không reload, không nhảy đầu trang */
(function(){
    function all(sel){ return Array.prototype.slice.call(document.querySelectorAll(sel)); }
    function nearest(el, sel){ return el && el.closest ? el.closest(sel) : null; }
    function setWishlistCount(count){
        if(count === undefined || count === null) return;
        all('.wishlist-count, .wish-count-badge').forEach(function(el){ el.textContent = count; });
    }
    function saveScroll(){ return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0; }
    function restoreScroll(y){
        try{ window.scrollTo({top:y,left:0,behavior:'auto'}); }catch(e){ window.scrollTo(0,y); }
        setTimeout(function(){ try{ window.scrollTo({top:y,left:0,behavior:'auto'}); }catch(e){ window.scrollTo(0,y); } }, 40);
        setTimeout(function(){ try{ window.scrollTo({top:y,left:0,behavior:'auto'}); }catch(e){ window.scrollTo(0,y); } }, 140);
    }
    function showInline(heart, msg, ok){
        var card = nearest(heart, '.product-card, .carousel-card');
        var target = card ? card.querySelector('.favorite-inline-message') : null;
        if(!target){ return showToast(msg, ok); }
        target.innerHTML = '<span>' + (ok === false ? '!' : '✓') + '</span> ' + (msg || 'Đã thêm vào mục yêu thích của bạn');
        target.classList.toggle('is-error', ok === false);
        target.classList.add('show');
        clearTimeout(target._gbTimer);
        target._gbTimer = setTimeout(function(){ target.classList.remove('show'); }, 3000);
    }
    function showToast(msg, ok){
        var t = document.querySelector('.gb-ajax-toast');
        if(!t){
            t = document.createElement('div');
            t.className = 'gb-ajax-toast';
            document.body.appendChild(t);
        }
        t.innerHTML = '<span class="gb-toast-check">' + (ok === false ? '!' : '✓') + '</span><span>' + (msg || '') + '</span>';
        t.classList.toggle('is-error', ok === false);
        t.classList.add('show');
        clearTimeout(t._gbTimer);
        t._gbTimer = setTimeout(function(){ t.classList.remove('show'); }, 3000);
    }
    function setHeartState(heart, saved){
        heart.classList.toggle('is-saved', !!saved);
        heart.setAttribute('aria-label', saved ? 'Đã lưu sản phẩm' : 'Lưu sản phẩm yêu thích');
        heart.setAttribute('title', saved ? 'Đã lưu sản phẩm' : 'Lưu sản phẩm');
    }
    window.gbToggleWishlist = function(ev, heart){
        if(ev){
            ev.preventDefault();
            ev.stopPropagation();
            if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
        }
        heart = heart || nearest(ev && ev.target, '.js-save-heart');
        if(!heart) return false;
        var y = saveScroll();
        if(heart.dataset.busy === '1'){ restoreScroll(y); return false; }
        var href = heart.getAttribute('data-url') || heart.getAttribute('href');
        if(!href){ showInline(heart, 'Không tìm thấy đường dẫn yêu thích.', false); restoreScroll(y); return false; }
        var url = href + (href.indexOf('?') >= 0 ? '&' : '?') + 'ajax=1&_=' + Date.now();
        heart.dataset.busy = '1';
        heart.classList.add('is-loading');
        fetch(url, {
            method:'GET',
            credentials:'same-origin',
            cache:'no-store',
            headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
        }).then(function(res){
            return res.text().then(function(text){
                try{ return JSON.parse(text); }
                catch(e){ throw new Error(text || 'Không nhận được JSON'); }
            });
        }).then(function(data){
            if(data && data.login){
                showInline(heart, data.message || 'Vui lòng đăng nhập để lưu sản phẩm.', false);
                return;
            }
            if(data && data.ok){
                setHeartState(heart, !!data.saved);
                setWishlistCount(data.count);
                showInline(heart, data.message || (data.saved ? 'Đã thêm vào mục yêu thích của bạn' : 'Đã bỏ sản phẩm khỏi mục yêu thích của bạn'), true);
            }else{
                showInline(heart, (data && data.message) || 'Chưa xử lý được yêu thích.', false);
            }
        }).catch(function(err){
            if(window.console) console.warn('Wishlist error', err);
            showInline(heart, 'Chưa lưu được sản phẩm, vui lòng thử lại.', false);
        }).finally(function(){
            heart.dataset.busy = '0';
            heart.classList.remove('is-loading');
            restoreScroll(y);
        });
        restoreScroll(y);
        return false;
    };
    ['click'].forEach(function(type){
        document.addEventListener(type, function(e){
            var heart = nearest(e.target, '.js-save-heart');
            if(!heart) return;
            window.gbToggleWishlist(e, heart);
        }, true);
    });
    document.addEventListener('DOMContentLoaded', function(){
        all('.wishlist-toast-final, .toast').forEach(function(t){
            clearTimeout(t._gbTimer);
            t._gbTimer = setTimeout(function(){
                t.classList.add('is-hide');
                setTimeout(function(){ if(t.parentNode) t.remove(); }, 350);
            }, 3000);
        });
    });
})();
