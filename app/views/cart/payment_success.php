<?php
$orderCode = '#GB' . date('Ymd', strtotime($order['created_at'])) . str_pad((string)$order['id'], 4, '0', STR_PAD_LEFT);
$payCode = $order['payment_code'] ?? ('GBPAY' . date('Ymd', strtotime($order['created_at'])) . str_pad((string)$order['id'], 4, '0', STR_PAD_LEFT));
$shippingFee = (int)($order['shipping_fee'] ?? 0);
$grandTotal = (int)($order['total'] ?? 0);
$subTotal = max(0, $grandTotal - $shippingFee);
?>
<section class="invoice-page gb-payment-page success-invoice-page">
    <div class="invoice-shell gb-bill-shell">
        <div class="payment-success-box no-print gb-success-hero">
            <div class="success-icon">✓</div>
            <span class="invoice-kicker">GlowBeauty</span>
            <h1>Thanh toán thành công</h1>
            <p>Đơn hàng đã được ghi nhận là <b>Đã thanh toán</b>.</p>
        </div>

        <div id="customer-bill" class="customer-bill gb-bill-card gb-bill-paid">
            <div class="gb-bill-header">
                <div class="gb-brand-mini">
                    <img src="<?= BASE_URL ?>public/assets/images/glowbeauty-logo-small.png" alt="GlowBeauty">
                    <div>
                        <h2>GlowBeauty</h2>
                        <p>Mỹ phẩm chính hãng • Tư vấn tận tâm</p>
                    </div>
                </div>
                <div class="gb-status paid">ĐÃ THANH TOÁN</div>
            </div>

            <div class="gb-bill-title gb-success-title">
                <div>
                    <small>PHIẾU XÁC NHẬN THANH TOÁN</small>
                    <h3><?= $orderCode ?></h3>
                    <span>Mã thanh toán: <?= htmlspecialchars($payCode) ?></span>
                </div>
                <div class="gb-thank-card">
                    <b>Thank you!</b>
                    <span>GlowBeauty cảm ơn bạn đã lựa chọn shop.</span>
                </div>
            </div>

            <div class="gb-bill-info-2col">
                <div class="gb-info-box">
                    <h4>Thông tin khách hàng</h4>
                    <p><span>Khách hàng</span><b><?= htmlspecialchars($order['customer_name']) ?></b></p>
                    <p><span>Số điện thoại</span><b><?= htmlspecialchars($order['phone']) ?></b></p>
                    <p><span>Địa chỉ</span><b><?= htmlspecialchars($order['address']) ?></b></p>
                </div>
                <div class="gb-info-box pay-info">
                    <h4>Thông tin hóa đơn</h4>
                    <p><span>Phương thức</span><b>Chuyển khoản/QR</b></p>
                    <p><span>Trạng thái</span><b>Đã thanh toán</b></p>
                    <p><span>Ngày tạo</span><b><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></b></p>
                </div>
            </div>

            <div class="gb-bill-table-wrap">
                <h4>Chi tiết đơn hàng</h4>
                <table class="customer-bill-table gb-bill-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>SL</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $it): ?>
                            <tr>
                                <td>
                                    <b><?= htmlspecialchars($it['product_name']) ?></b>
                                    <small><?= htmlspecialchars($it['product_code'] ?? ('SP'.$it['product_id'])) ?></small>
                                </td>
                                <td><?= (int)$it['quantity'] ?></td>
                                <td><?= number_format($it['price'],0,',','.') ?>đ</td>
                                <td><b><?= number_format($it['price']*$it['quantity'],0,',','.') ?>đ</b></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="gb-bill-bottom-grid">
                <div class="gb-note-box">
                    <h4>Ghi chú giao hàng</h4>
                    <?php
                    $deliveryNoteText = trim((string)($order['note'] ?? ''));
                    if ($deliveryNoteText === '' && !empty($_SESSION['order_delivery_notes'][(int)($order['id'] ?? 0)])) {
                        $deliveryNoteText = trim((string)$_SESSION['order_delivery_notes'][(int)$order['id']]);
                    }
                    ?>
                    <p><?= htmlspecialchars($deliveryNoteText !== '' ? $deliveryNoteText : 'Không có ghi chú giao hàng') ?></p>
                    <small>Shop sẽ liên hệ xác nhận và giao hàng trong thời gian sớm nhất.</small>
                </div>
                <div class="gb-total-box">
                    <p><span>Tạm tính</span><b><?= number_format($subTotal,0,',','.') ?>đ</b></p>
                    <p><span>Phí vận chuyển</span><b><?= number_format($shippingFee,0,',','.') ?>đ</b></p>
                    <p class="grand"><span>Tổng đã thanh toán</span><b><?= number_format($grandTotal,0,',','.') ?>đ</b></p>
                </div>
            </div>

            <div class="gb-bill-footerline">
                <span>Hotline: 0865155687</span>
                <span>GlowBeauty cảm ơn quý khách đã tin tưởng 🌸</span>
            </div>
        </div>

        <div class="invoice-actions no-print">
            <button class="btn" onclick="window.print()">🧾 In hóa đơn</button>
            <a class="btn outline" href="<?= BASE_URL ?>products">Tiếp tục mua hàng</a>
        </div>
    </div>
</section>



