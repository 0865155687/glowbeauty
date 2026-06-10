<?php
$orderCode = '#GB' . date('Ymd', strtotime($order['created_at'])) . str_pad((string)$order['id'], 4, '0', STR_PAD_LEFT);
$payCode = $order['payment_code'] ?? ('GBPAY' . date('Ymd', strtotime($order['created_at'])) . str_pad((string)$order['id'], 4, '0', STR_PAD_LEFT));
$shippingFee = 0;
$subTotal = (int)$order['total'];
$grandTotal = $subTotal + $shippingFee;
$qrFile = __DIR__ . '/../../../public/assets/images/qr-thanh-toan.png';
$qrUrl = BASE_URL . 'public/assets/images/qr-thanh-toan.png';
?>
<section class="invoice-page gb-payment-page">
    <div class="invoice-shell gb-bill-shell">
        <div class="gb-invoice-toolbar no-print">
            <div>
                <span class="invoice-kicker">GlowBeauty Payment Bill</span>
                <h1>Hóa đơn thanh toán</h1>
                <p>Quét mã QR để chuyển khoản, sau đó bấm xác nhận đã gửi bill cho shop.</p>
            </div>
            <button class="btn outline" onclick="window.print()">🖨️ In bill</button>
        </div>

        <div id="customer-bill" class="customer-bill gb-bill-card gb-bill-unpaid">
            <div class="gb-bill-header">
                <div class="gb-brand-mini">
                    <img src="<?= BASE_URL ?>public/assets/images/glowbeauty-logo-small.png" alt="GlowBeauty">
                    <div>
                        <h2>GlowBeauty</h2>
                        <p>Mỹ phẩm chính hãng • Tư vấn tận tâm</p>
                    </div>
                </div>
                <div class="gb-status unpaid">CHƯA THANH TOÁN</div>
            </div>

            <div class="gb-bill-title">
                <div>
                    <small>HÓA ĐƠN THANH TOÁN</small>
                    <h3><?= $orderCode ?></h3>
                    <span>Ngày tạo: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="gb-qr-card">
                    <?php if(file_exists($qrFile)): ?>
                        <img src="<?= $qrUrl ?>" alt="QR thanh toán GlowBeauty">
                    <?php else: ?>
                        <div class="qr-missing-note">Thiếu ảnh QR<br><b>public/assets/images/qr-thanh-toan.png</b></div>
                    <?php endif; ?>
                    <b>Quét QR thanh toán</b>
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
                    <h4>Thông tin chuyển khoản</h4>
                    <p><span>Ngân hàng</span><b>MB Bank</b></p>
                    <p><span>Chủ tài khoản</span><b>NGUYEN THI NGOAN</b></p>
                    <p><span>Số tài khoản</span><b>111129092005</b></p>
                    <p><span>Nội dung</span><b><?= htmlspecialchars($payCode) ?></b></p>
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
                    <h4>Ghi chú đơn hàng</h4>
                    <p><?= htmlspecialchars($order['note'] ?? 'Không có ghi chú') ?></p>
                    <small>Vui lòng chuyển đúng số tiền và nội dung để shop xác nhận nhanh hơn.</small>
                </div>
                <div class="gb-total-box">
                    <p><span>Tạm tính</span><b><?= number_format($subTotal,0,',','.') ?>đ</b></p>
                    <p><span>Phí vận chuyển</span><b><?= number_format($shippingFee,0,',','.') ?>đ</b></p>
                    <p class="grand"><span>Tổng thanh toán</span><b><?= number_format($grandTotal,0,',','.') ?>đ</b></p>
                </div>
            </div>

            <div class="gb-bill-footerline">
                <span>Hotline: 0865155687</span>
                <span>GlowBeauty cảm ơn quý khách đã tin tưởng 🌸</span>
            </div>
        </div>

        <div class="invoice-actions no-print">
            <a class="btn" href="<?= BASE_URL ?>payment-success?id=<?= (int)$order['id'] ?>">✅ Tôi đã chụp/gửi bill thanh toán</a>
            <a class="btn outline" href="<?= BASE_URL ?>products">Tiếp tục mua hàng</a>
        </div>
    </div>
</section>
