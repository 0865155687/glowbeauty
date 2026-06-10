(function(){
  if (window.__GlowBeautyAjaxCartBound) return;
  window.__GlowBeautyAjaxCartBound = true;

  function cartLink(){
    return Array.from(document.querySelectorAll('a')).find(function(a){
      var href = (a.getAttribute('href') || '').toLowerCase();
      return href.includes('/cart') || href.endsWith('cart') || href.includes('cart/');
    });
  }

  function badge(){
    var link = cartLink();
    if (!link) return null;
    var b = link.querySelector('.cart-count');
    if (!b){
      b = document.createElement('span');
      b.className = 'cart-count';
      b.textContent = '0';
      link.appendChild(b);
    }
    return b;
  }

  function setBadge(count){
    var n = parseInt(count, 10);
    if (isNaN(n)) return;
    Array.from(document.querySelectorAll('.cart-count')).forEach(function(el){ el.textContent = String(n); });
    if (window.GlowBeautySetCartCount) window.GlowBeautySetCartCount(n);
    var b = badge();
    if (b) b.textContent = String(n);
  }

  function toast(msg){
    var box = document.querySelector('.ajax-cart-mini-toast');
    if (!box){
      box = document.createElement('div');
      box.className = 'ajax-cart-mini-toast';
      document.body.appendChild(box);
    }
    box.textContent = msg;
    box.classList.add('show');
    clearTimeout(window.__cartToastTimer);
    window.__cartToastTimer = setTimeout(function(){ box.classList.remove('show'); }, 1200);
  }

  function isAdd(el){
    return ((el.textContent || '').toLowerCase().includes('thêm vào giỏ'));
  }

  function addByUrl(url, method, body){
    return fetch(url, {
      method: method || 'GET',
      body: (method || 'GET').toUpperCase() === 'GET' ? undefined : body,
      credentials: 'same-origin',
      headers: {'X-Requested-With':'XMLHttpRequest'}
    }).then(function(res){
      return res.json().catch(function(){ return {}; });
    }).then(function(data){
      if (typeof data.cart_count !== 'undefined') setBadge(data.cart_count);
      toast('Đã thêm vào giỏ hàng');
    }).catch(function(){
      toast('Đã thêm vào giỏ hàng');
    });
  }

  document.addEventListener('submit', function(e){
    var form = e.target;
    if (!form || !isAdd(form)) return;
    e.preventDefault();
    e.stopImmediatePropagation();

    var action = form.getAttribute('action') || location.href;
    var method = (form.getAttribute('method') || 'POST').toUpperCase();
    addByUrl(action, method, new FormData(form));
  }, true);

  document.addEventListener('click', function(e){
    var el = e.target.closest('a,button');
    if (!el || !isAdd(el)) return;
    if (el.classList.contains('disabled')) return;

    var form = el.closest('form');
    if (form) return;

    var href = el.getAttribute('href') || '';
    if (!href || href === '#') return;

    e.preventDefault();
    e.stopImmediatePropagation();
    addByUrl(href, 'GET');
  }, true);
})();
