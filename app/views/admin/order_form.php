<div class="admin-hero-v34 compact order-create-hero-v34 admin-hero-tight-clean">
  <div>
    <span class="admin-kicker-v34">Tạo đơn hàng</span>
    <h1>Thêm đơn hàng</h1>
    <p>Tạo đơn tại quầy hoặc hỗ trợ khách đặt hàng nhanh.</p>
  </div>
  <a class="btn admin-secondary-v34 admin-link-fix" href="<?= BASE_URL ?>admin/orders">← Quay lại</a>
</div>
<?php if(!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form class="admin-form manual-order-form admin-form-v34 manual-order-form-clean" method="post" action="<?= BASE_URL ?>admin/order-create">
  <div class="form-section-title-v34"><span>👤</span><div><b>Thông tin khách hàng</b><small>Thông tin người nhận hàng</small></div></div>
  <div class="form-grid form-grid-v34 manual-customer-grid-clean">
    <div class="field"><label>Họ và tên khách</label><input required name="customer_name" placeholder="Nhập họ tên khách hàng" data-name-input></div>
    <div class="field"><label>Số điện thoại</label><input required name="phone" pattern="[0-9]{10}" maxlength="10" inputmode="numeric" placeholder="Nhập đúng 10 số" data-phone-input></div>
    <div class="field full"><label>Địa chỉ giao hàng</label><input required name="address" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"></div>
    <input type="hidden" name="note" value="">
  </div>

  <div class="form-section-title-v34"><span>🛒</span><div><b>Sản phẩm trong đơn</b><small>Chọn sản phẩm và nhập số lượng.</small></div></div>

  <div id="manualOrderItems" class="manual-order-items manual-order-items-v34">
    <div class="manual-order-row manual-order-row-v34 manual-order-row-clean">
      <div class="product-search-box-v34 product-search-box-clean">
        <label>Tìm sản phẩm</label>
        <input type="text" class="product-search-input-v34" placeholder="Nhập tên, mã hoặc danh mục sản phẩm" autocomplete="off">
        <input type="hidden" name="product_id[]" class="product-id-input-v34" required>
        <div class="product-suggestions-v34"></div>
        <small class="chosen-product-v34">Chưa chọn sản phẩm</small>
      </div>
      <div class="qty-box-v34 qty-box-clean">
        <label>SL</label>
        <input type="number" name="quantity[]" min="1" value="1" required>
      </div>
    </div>
  </div>

  <div class="manual-order-actions manual-order-actions-v34 manual-order-actions-clean">
    <div class="manual-order-total manual-order-total-v34">Tạm tính: <b id="manualOrderTotal">0đ</b></div>
    <button class="btn admin-primary-v34 save-order-v34" type="submit">Lưu đơn hàng</button>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded',function(){
 const products = <?= json_encode(array_map(function($p){return ['id'=>(int)$p['id'],'name'=>$p['name'],'code'=>$p['product_code'] ?? '','brand'=>$p['brand'] ?? '','category'=>$p['category'] ?? '','price'=>(int)$p['price'],'stock'=>(int)$p['stock']];}, $products), JSON_UNESCAPED_UNICODE) ?>;
 const wrap=document.getElementById('manualOrderItems');
 const total=document.getElementById('manualOrderTotal');
 function money(n){return (n||0).toLocaleString('vi-VN')+'đ';}
 function normalize(str){return (str||'').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/đ/g,'d');}
 function calc(){let sum=0;wrap.querySelectorAll('.manual-order-row').forEach(row=>{const id=parseInt(row.querySelector('.product-id-input-v34').value||0);const product=products.find(p=>p.id===id);const qty=parseInt(row.querySelector('input[name="quantity[]"]').value||0);if(product)sum+=product.price*qty;});total.textContent=money(sum);}
 function optionHtml(p){return `<button type="button" class="product-suggest-item-v34" data-id="${p.id}"><b>${p.name}</b><span>${money(p.price)} · Kho ${p.stock} · ${p.category}</span></button>`;}
 function bind(row){
   const search=row.querySelector('.product-search-input-v34');
   const hidden=row.querySelector('.product-id-input-v34');
   const list=row.querySelector('.product-suggestions-v34');
   const chosen=row.querySelector('.chosen-product-v34');
   const qty=row.querySelector('input[name="quantity[]"]');
   function show(q=''){
     const key=normalize(q.trim());
     const found=products.filter(p=>{const text=normalize([p.name,p.code,p.brand,p.category].join(' ')); return !key || text.includes(key);}).slice(0,12);
     list.innerHTML=found.length?found.map(optionHtml).join(''):'<div class="no-result-v34">Không tìm thấy sản phẩm</div>';
     list.classList.add('show');
   }
   search.addEventListener('focus',()=>show(search.value));
   search.addEventListener('input',()=>{hidden.value='';chosen.textContent='Chưa chọn sản phẩm';show(search.value);calc();});
   list.addEventListener('click',function(e){const btn=e.target.closest('button[data-id]');if(!btn)return;const p=products.find(x=>x.id==btn.dataset.id);hidden.value=p.id;search.value=p.name;chosen.textContent=`Đã chọn: ${p.name} · ${money(p.price)} · Kho ${p.stock}`;list.classList.remove('show');calc();});
   qty.addEventListener('input',calc);
   document.addEventListener('click',function(e){if(!row.contains(e.target))list.classList.remove('show');});
 }
 wrap.querySelectorAll('.manual-order-row').forEach(bind);
 calc();
});
</script>
