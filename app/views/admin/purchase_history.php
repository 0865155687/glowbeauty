<div class="admin-title gb-page-hero">
  <div>
    <span class="gb-page-kicker">LỊCH SỬ KHÁCH ĐẶT HÀNG</span>
    <h1>Lịch sử mua hàng</h1>
    <p>Xem chi tiết từng khách đã mua đơn nào, sản phẩm gì, tổng chi tiêu và trạng thái đơn.</p>
  </div>
</div>

<?php if(empty($customers)): ?>
  <div class="admin-card gb-empty-state">Chưa có lịch sử mua hàng.</div>
<?php else: ?>
  <div class="gb-history-wrap">
    <?php foreach($customers as $c): ?>
      <section class="gb-history-card">
        <div class="gb-history-customer-head">
          <div class="gb-history-customer-info">
            <div class="gb-avatar-bag">🛍️</div>
            <div>
              <h2><?= htmlspecialchars($c['customer_name'] ?: 'Khách hàng') ?></h2>
              <p>☎ <?= htmlspecialchars($c['phone'] ?? 'Chưa có SĐT') ?> <span>•</span> <?= htmlspecialchars($c['address'] ?? 'Chưa có địa chỉ') ?></p>
            </div>
          </div>
          <div class="gb-history-summary">
            <span><?= (int)$c['total_orders'] ?> đơn</span>
            <strong><?= number_format((int)$c['total_spent'],0,',','.') ?>đ</strong>
          </div>
        </div>

        <div class="gb-history-orders">
          <?php foreach(($c['orders'] ?? []) as $o):
            $items = $c['items_by_order'][(int)$o['id']] ?? [];
            $shippingFee = (int)($o['shipping_fee'] ?? 0);
            $itemsSubtotal = 0;
            foreach($items as $calcItem) {
              $itemsSubtotal += (int)($calcItem['price'] ?? 0) * (int)($calcItem['quantity'] ?? 0);
            }
            $orderTotalRaw = (int)($o['total'] ?? 0);
            // Nếu đơn cũ trên host chưa lưu shipping_fee nhưng total đã cộng ship,
            // suy ra phí ship = total - tiền hàng để không hiển thị mâu thuẫn "Miễn phí".
            if ($shippingFee <= 0 && $itemsSubtotal > 0 && $orderTotalRaw > $itemsSubtotal) {
              $shippingFee = $orderTotalRaw - $itemsSubtotal;
            }
            // total trong orders là tổng cuối cùng khách trả, đã bao gồm phí ship.
            $orderGrandTotal = $orderTotalRaw > 0 ? $orderTotalRaw : ($itemsSubtotal + $shippingFee);
            $status = trim((string)($o['status'] ?? 'Chưa rõ'));
            $statusClass = 'pending';
            if (mb_stripos($status, 'hoàn') !== false) $statusClass = 'done';
            elseif (mb_stripos($status, 'hủy') !== false || mb_stripos($status, 'huỷ') !== false) $statusClass = 'cancel';
            elseif (mb_stripos($status, 'giao') !== false) $statusClass = 'shipping';
            elseif (mb_stripos($status, 'xác') !== false) $statusClass = 'confirmed';
          ?>
            <article class="gb-history-order-card">
              <div class="gb-history-order-main">
                <div>
                  <h3>Đơn #<?= (int)$o['id'] ?></h3>
                  <p class="gb-history-date">Ngày đặt: <?php $orderTime = !empty($o['created_at']) ? strtotime($o['created_at']) : false; ?><?php if($orderTime): ?><span><?= date('d/m/Y', $orderTime) ?></span><small><?= date('H:i:s', $orderTime) ?></small><?php else: ?>-<?php endif; ?></p>
                </div>
                <span class="gb-order-status <?= $statusClass ?>"><?= htmlspecialchars($status) ?></span>
                <strong><?= number_format($orderGrandTotal,0,',','.') ?>đ</strong>
              </div>

              <div class="gb-history-products">
                <?php foreach($items as $it): ?>
                  <div class="gb-history-product-row has-product-image">
                    <?php $productImg = trim((string)($it['product_image'] ?? '')); ?>
                    <?php if ($productImg !== ''): ?>
                      <img class="gb-history-product-img" src="<?= function_exists('gb_image_url') ? gb_image_url($productImg) : BASE_URL . 'public/assets/images/' . htmlspecialchars($productImg) ?>" alt="<?= htmlspecialchars($it['product_name'] ?? 'Sản phẩm') ?>" onerror="this.style.display='none'">
                    <?php else: ?>
                      <div class="gb-history-product-img gb-history-product-img-empty">📦</div>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($it['product_name'] ?? 'Sản phẩm') ?></span>
                    <em>SL: <?= (int)($it['quantity'] ?? 0) ?></em>
                    <b><?= number_format((int)($it['price'] ?? 0) * (int)($it['quantity'] ?? 0),0,',','.') ?>đ</b>
                  </div>
                <?php endforeach; ?>
                <div class="gb-history-ship-row">
                  <span>🚚 Phí ship</span>
                  <b><?= $shippingFee > 0 ? number_format($shippingFee,0,',','.') . 'đ' : 'Miễn phí' ?></b>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<style>
.gb-history-product-row.has-product-image{display:grid;grid-template-columns:54px minmax(0,1fr) auto auto;align-items:center;gap:12px}
.gb-history-product-img{width:48px;height:48px;border-radius:12px;object-fit:cover;border:1px solid #f0d2c2;background:#fff}
.gb-history-ship-row{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:12px;margin-top:10px;padding:12px 14px;border:1px dashed #efcdbd;border-radius:14px;background:#fff7f1;color:#6f3a23;font-weight:800}
.gb-history-ship-row em{font-style:normal;color:#9a6a55;font-size:13px;font-weight:700;white-space:nowrap}.gb-history-ship-row b{color:#d45a1c;white-space:nowrap}
@media(max-width:768px){.gb-history-product-row.has-product-image{grid-template-columns:46px 1fr;gap:8px}.gb-history-product-row.has-product-image em,.gb-history-product-row.has-product-image b{margin-left:54px}.gb-history-product-img{width:42px;height:42px}.gb-history-ship-row{grid-template-columns:1fr}.gb-history-ship-row em,.gb-history-ship-row b{margin-left:0}}
</style>

<style>
.gb-history-product-img-empty{display:flex;align-items:center;justify-content:center;font-size:22px;color:#c26939}
</style>

<style>
.gb-history-date{display:flex!important;align-items:center!important;gap:6px!important;flex-wrap:wrap!important}
.gb-history-date span{font-weight:700!important;color:#4a2418!important}
.gb-history-date small{font-size:13px!important;color:#8a6758!important;font-weight:700!important}
.gb-history-product-row.has-product-image{display:grid!important;grid-template-columns:58px minmax(0,1fr) 80px 130px!important;align-items:center!important;gap:12px!important}
.gb-history-product-img{width:52px!important;height:52px!important;border-radius:14px!important;object-fit:cover!important;border:1px solid #efcdbd!important;background:#fff!important;box-shadow:0 8px 18px rgba(105,54,28,.08)!important}
.gb-history-product-img-empty{display:flex!important;align-items:center!important;justify-content:center!important;font-size:22px!important;color:#c26939!important}
@media(max-width:768px){.gb-history-product-row.has-product-image{grid-template-columns:52px 1fr!important}.gb-history-product-row.has-product-image em,.gb-history-product-row.has-product-image b{grid-column:2!important;margin-left:0!important;text-align:left!important}.gb-history-product-img{width:46px!important;height:46px!important}}
</style>
