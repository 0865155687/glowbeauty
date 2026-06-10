-- RESET DỮ LIỆU MẪU GLOWBEAUTY
-- Giữ tài khoản admin, xoá dữ liệu thử và reset ID đơn hàng/tư vấn từ 1.
-- Doanh thu chỉ cộng các đơn có payment_status = 'Đã thanh toán' và status != 'Đã hủy'.

SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE order_items;
TRUNCATE TABLE orders;
TRUNCATE TABLE contact_requests;
DELETE FROM users WHERE role <> 'admin';
SET FOREIGN_KEY_CHECKS=1;

INSERT INTO users(name,email,password,role) VALUES
('Nguyễn Mai Anh','maianh@example.com','$2y$12$osim3.pR9yKQReEenQmV/.LCinnNcGLmNPxpSGpnixN7e2azPkaOu','customer'),
('Trần Bảo Ngọc','baongoc@example.com','$2y$12$osim3.pR9yKQReEenQmV/.LCinnNcGLmNPxpSGpnixN7e2azPkaOu','customer'),
('Lê Thùy Dương','thuyduong@example.com','$2y$12$osim3.pR9yKQReEenQmV/.LCinnNcGLmNPxpSGpnixN7e2azPkaOu','customer'),
('Phạm Khánh Ly','khanhly@example.com','$2y$12$osim3.pR9yKQReEenQmV/.LCinnNcGLmNPxpSGpnixN7e2azPkaOu','customer'),
('Hoàng Minh Châu','minhchau@example.com','$2y$12$osim3.pR9yKQReEenQmV/.LCinnNcGLmNPxpSGpnixN7e2azPkaOu','customer'),
('Vũ Phương Linh','phuonglinh@example.com','$2y$12$osim3.pR9yKQReEenQmV/.LCinnNcGLmNPxpSGpnixN7e2azPkaOu','customer');

INSERT INTO orders(id,customer_name,phone,address,note,total,status,payment_status,payment_code,created_at) VALUES
(1,'Nguyễn Mai Anh','0912345678','Hoàn Kiếm, Hà Nội','Khách đã thanh toán đơn chăm sóc da',648000,'Hoàn thành','Đã thanh toán','GBPAY26011000001','2026-01-10 09:15:00'),
(2,'Trần Bảo Ngọc','0987654321','Cầu Giấy, Hà Nội','Đơn son môi đã thanh toán',558000,'Hoàn thành','Đã thanh toán','GBPAY26020500002','2026-02-05 14:20:00'),
(3,'Lê Thùy Dương','0901122334','Đống Đa, Hà Nội','Đơn chưa thanh toán nên chưa tính doanh thu',738000,'Đang giao','Chưa thanh toán','GBPAY26021800003','2026-02-18 10:40:00'),
(4,'Phạm Khánh Ly','0933445566','Long Biên, Hà Nội','Đã thanh toán tại quầy',299000,'Đã xác nhận','Đã thanh toán','GBPAY26031200004','2026-03-12 16:05:00'),
(5,'Hoàng Minh Châu','0977889900','Nam Từ Liêm, Hà Nội','Đơn đã thanh toán',608000,'Hoàn thành','Đã thanh toán','GBPAY26042100005','2026-04-21 11:10:00'),
(6,'Vũ Phương Linh','0966112233','Đông Đa, Hà Nội','Khách mới đặt online',349000,'Chờ xác nhận','Chưa thanh toán','GBPAY26050100006','2026-05-01 15:35:00'),
(7,'Bùi Hồng Nhung','0922334455','Hà Đông, Hà Nội','Đã thanh toán chuyển khoản',1504000,'Hoàn thành','Đã thanh toán','GBPAY26050800007','2026-05-08 19:10:00'),
(8,'Đỗ Minh Châu','0944556677','Nam Từ Liêm, Hà Nội','Đơn đã hủy không tính doanh thu',2649000,'Đã hủy','Đã thanh toán','GBPAY26051600008','2026-05-16 08:58:00'),
(9,'Nguyễn Hạ Vy','0977112233','Hai Bà Trưng, Hà Nội','Đơn chưa thanh toán',867000,'Đã xác nhận','Chưa thanh toán','GBPAY26052200009','2026-05-22 17:30:00'),
(10,'Mai Phương Anh','0909887766','Thanh Xuân, Hà Nội','Đã thanh toán khi nhận hàng',997000,'Hoàn thành','Đã thanh toán','GBPAY26052500010','2026-05-25 10:05:00');

INSERT INTO order_items(order_id,product_id,product_code,product_name,price,quantity) VALUES
(1,6,'GB-0006','GlowBeauty Rose Hydrating Toner',259000,1),
(1,3,'GB-0003','GlowBeauty Radiant Foundation',389000,1),
(2,5,'GB-0005','GlowBeauty Glow Velvet Lipstick',279000,2),
(3,1,'GB-0001','GlowBeauty 4 Colors Eyeshadow',349000,1),
(3,2,'GB-0002','GlowBeauty Soft Rose Blush',259000,1),
(3,17,'GB-0017','Rare Beauty Soft Pinch Tinted Lip Oil',130000,1),
(4,4,'GB-0004','GlowBeauty Soft Veil Loose Powder',299000,1),
(5,2,'GB-0002','GlowBeauty Soft Rose Blush',259000,1),
(5,1,'GB-0001','GlowBeauty 4 Colors Eyeshadow',349000,1),
(6,1,'GB-0001','GlowBeauty 4 Colors Eyeshadow',349000,1),
(7,3,'GB-0003','GlowBeauty Radiant Foundation',389000,2),
(7,5,'GB-0005','GlowBeauty Glow Velvet Lipstick',279000,2),
(7,4,'GB-0004','GlowBeauty Soft Veil Loose Powder',299000,1),
(8,12,'GB-0012','Combo GlowBeauty Gift Set',2649000,1),
(9,2,'GB-0002','GlowBeauty Soft Rose Blush',259000,1),
(9,1,'GB-0001','GlowBeauty 4 Colors Eyeshadow',349000,1),
(9,6,'GB-0006','GlowBeauty Rose Hydrating Toner',259000,1),
(10,1,'GB-0001','GlowBeauty 4 Colors Eyeshadow',349000,1),
(10,3,'GB-0003','GlowBeauty Radiant Foundation',389000,1),
(10,2,'GB-0002','GlowBeauty Soft Rose Blush',259000,1);

INSERT INTO contact_requests(id,customer_name,phone,need,message,status,created_at) VALUES
(1,'Nguyễn Mai Anh','0912345678','Chăm sóc da','Da hỗn hợp thiên dầu, cần tư vấn routine cơ bản.','Đã tư vấn','2026-05-20 09:10:00'),
(2,'Trần Bảo Ngọc','0987654321','Trang điểm nền','Muốn chọn kem nền mỏng nhẹ đi làm.','Đang tư vấn','2026-05-21 14:25:00'),
(3,'Lê Thùy Dương','0901122334','Son môi / màu son','Cần màu son hợp da ngăm.','Mới gửi','2026-05-22 10:30:00'),
(4,'Phạm Khánh Ly','0933445566','Combo quà tặng','Tư vấn combo tặng sinh nhật.','Mới gửi','2026-05-22 16:45:00');

ALTER TABLE orders AUTO_INCREMENT = 11;
ALTER TABLE order_items AUTO_INCREMENT = 21;
ALTER TABLE contact_requests AUTO_INCREMENT = 5;
