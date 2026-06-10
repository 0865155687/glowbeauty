-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 25, 2026 lúc 11:31 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `beauty_makeup_shop`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contact_requests`
--

CREATE TABLE `contact_requests` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(120) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `need` varchar(120) NOT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Mới gửi',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `contact_requests`
--

INSERT INTO `contact_requests` (`id`, `customer_name`, `phone`, `need`, `message`, `status`, `created_at`) VALUES
(4, 'Nguyễn Thanh Ngoan', '0865155687', 'Chăm sóc da', 'da hỗn hợp dùng sản phẩm nào?', 'Đã tư vấn', '2026-05-22 13:58:23'),
(7, 'Nguyễn Thị Ngoan', '0865155687', 'Trang điểm nền', 'da hỗn hợp', 'Mới gửi', '2026-05-22 15:05:58'),
(8, 'Nguyễn Thanh Ngoan', '0815659860', 'Trang điểm nền', 'okok', 'Mới gửi', '2026-05-22 15:25:27'),
(9, 'Nguyễn Thanh Ngoan', '0865155687', 'Chăm sóc da', 'iukuky', 'Mới gửi', '2026-05-22 15:33:12'),
(10, 'Nguyễn Thanh Ngoan', '0865155687', 'Chăm sóc da', 'ouykytjg', 'Mới gửi', '2026-05-22 15:37:05'),
(11, 'Nguyễn Thanh Ngoan', '0865155687', 'Chăm sóc da', 'tưeewgh', 'Mới gửi', '2026-05-22 15:43:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(160) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `note` text DEFAULT NULL,
  `total` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'Chờ xác nhận',
  `payment_status` varchar(50) NOT NULL DEFAULT 'Chưa thanh toán',
  `payment_code` varchar(80) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `phone`, `address`, `note`, `total`, `status`, `payment_status`, `payment_code`, `created_at`) VALUES
(1, 'Nguyễn Thu Hà', '0912345678', 'Hoàn Kiếm, Hà Nội', 'Khách mua combo đầu năm', 1848000, 'Hoàn thành', 'Chưa thanh toán', 'GBPAY26011000001', '2026-01-10 09:18:00'),
(2, 'Trần Minh Anh', '0987654321', 'Cầu Giấy, Hà Nội', 'Giao giờ hành chính', 1326000, 'Hoàn thành', 'Chưa thanh toán', 'GBPAY26012200002', '2026-01-22 14:35:00'),
(3, 'Lê Phương Linh', '0901122334', 'Đống Đa, Hà Nội', 'Tư vấn thêm toner', 2609000, 'Hoàn thành', 'Chưa thanh toán', 'GBPAY26020600003', '2026-02-06 10:12:00'),
(4, 'Phạm Ngọc Mai', '0933445566', 'Hai Bà Trưng, Hà Nội', 'Khách đặt online', 1085000, 'Hoàn thành', 'Chưa thanh toán', 'GBPAY26022000004', '2026-02-20 16:40:00'),
(5, 'Hoàng Bảo Ngọc', '0977889900', 'Thanh Xuân, Hà Nội', 'Khách quen', 2719000, 'Hoàn thành', 'Chưa thanh toán', 'GBPAY26030800005', '2026-03-08 11:05:00'),
(6, 'Vũ Khánh Ly', '0966112233', 'Long Biên, Hà Nội', 'Gói quà tặng', 1697000, 'Hoàn thành', 'Chưa thanh toán', 'GBPAY26032700006', '2026-03-27 15:22:00'),
(7, 'Đỗ Minh Châu', '0944556677', 'Nam Từ Liêm, Hà Nội', 'Thanh toán khi nhận hàng', 2649000, 'Hoàn thành', 'Đã thanh toán', 'GBPAY26040500007', '2026-04-05 08:58:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_code`, `product_name`, `price`, `quantity`) VALUES
(1, 1, 7, 'GB-0007', 'GlowBeauty Premium Combo 7 sản phẩm', 1499000, 1),
(2, 1, 1, 'GB-0001', 'GlowBeauty 4 Colors Eyeshadow', 349000, 1),
(3, 2, 3, 'GB-0003', 'GlowBeauty Radiant Foundation', 389000, 2),
(4, 2, 5, 'GB-0005', 'GlowBeauty Glow Velvet Lipstick', 279000, 1),
(5, 2, 2, 'GB-0002', 'GlowBeauty Soft Rose Blush', 259000, 1),
(6, 2, 10, 'GB-0010', 'Simple Refreshing Facial Wash', 125000, 1),
(7, 3, 8, 'GB-0008', 'GlowBeauty Luxury Makeup Banner Collection', 2350000, 1),
(8, 3, 6, 'GB-0006', 'GlowBeauty Rose Hydrating Toner', 259000, 1),
(9, 4, 26, 'GB-0026', 'Makeup By Mario SurrealSkin Foundation', 620000, 1),
(10, 4, 17, 'GB-0017', 'Rare Beauty Soft Pinch Tinted Lip Oil', 395000, 1),
(11, 4, 9, 'GB-0009', 'Biore Sạch Dịu Nhẹ Sáng Mịn & Dưỡng Ẩm', 89000, 1),
(12, 5, 7, 'GB-0007', 'GlowBeauty Premium Combo 7 sản phẩm', 1499000, 1),
(13, 5, 22, 'GB-0022', 'YSL Make Me Blush Bold Blurring Blush', 950000, 1),
(14, 5, 5, 'GB-0005', 'GlowBeauty Glow Velvet Lipstick', 279000, 1),
(15, 6, 27, 'GB-0027', 'Make Up For Ever HD Skin Foundation', 890000, 1),
(16, 6, 31, 'GB-0031', '3CE Stylenanda Waterful Foundation', 420000, 1),
(17, 6, 3, 'GB-0003', 'GlowBeauty Radiant Foundation', 389000, 1),
(18, 7, 8, 'GB-0008', 'GlowBeauty Luxury Makeup Banner Collection', 2350000, 1),
(19, 7, 4, 'GB-0004', 'GlowBeauty Soft Veil Loose Powder', 299000, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(120) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 10,
  `image` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `benefit` text NOT NULL,
  `ingredients` text NOT NULL,
  `usage_text` text NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 99,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `product_code`, `name`, `brand`, `category`, `price`, `stock`, `image`, `description`, `benefit`, `ingredients`, `usage_text`, `status`, `sort_order`, `created_at`) VALUES
(1, 'GB-0001', 'GlowBeauty 4 Colors Eyeshadow', 'GlowBeauty', 'Phấn mắt', 349000, 10, '5d9f1105-752c-4481-97f4-6f676c7c684e.png', 'Bảng phấn mắt 4 ô tông hồng đào, ánh nhũ nhẹ và chất phấn mịn, phù hợp trang điểm hằng ngày hoặc dự tiệc.', 'Giúp đôi mắt sáng hơn, dễ phối màu, lên màu chuẩn và tạo hiệu ứng mắt có chiều sâu.', '4 tông màu gồm lì, nhũ mịn, nhũ ánh kim và nhũ glitter; hạt phấn mịn, dễ tán.', 'Dùng cọ lấy lượng phấn vừa đủ, tán từ màu nhạt đến màu đậm ở bầu mắt và đuôi mắt.', 1, 1, '2026-05-22 08:26:11'),
(2, 'GB-0002', 'GlowBeauty Soft Rose Blush', 'GlowBeauty', 'Phấn má', 259000, 10, '186f6edf-cd4a-4993-ae3b-fd382410e02a.png', 'Phấn má GlowBeauty tông hồng tự nhiên, thiết kế nữ tính, giúp gương mặt tươi tắn và mềm mại.', 'Tạo má hồng rạng rỡ, làm khuôn mặt có sức sống, phù hợp makeup tự nhiên.', 'Bột phấn mịn, sắc hồng đào dễ dùng, độ bám ổn định.', 'Dùng cọ tán nhẹ lên gò má, có thể chồng nhiều lớp để tăng sắc độ.', 1, 2, '2026-05-22 08:26:11'),
(3, 'GB-0003', 'GlowBeauty Radiant Foundation', 'GlowBeauty', 'Kem nền', 389000, 10, '7e22a2c4-47cb-40f1-9bea-ddcc3af173c6.png', 'Kem nền GlowBeauty tạo lớp nền mỏng nhẹ, sáng mịn, phù hợp phong cách makeup cao cấp.', 'Che phủ nhẹ đến vừa, giúp da đều màu, mịn hơn và giữ nền tự nhiên.', 'Kết cấu lỏng mịn, có độ ẩm, phù hợp trang điểm hằng ngày.', 'Lấy 1-2 pump, tán đều bằng mút hoặc cọ từ giữa mặt ra ngoài.', 1, 3, '2026-05-22 08:26:11'),
(4, 'GB-0004', 'GlowBeauty Soft Veil Loose Powder', 'GlowBeauty', 'Phấn phủ', 299000, 10, 'b999eddd-f76a-4dc3-82a7-a5ea08c5e78e.png', 'Phấn phủ GlowBeauty giúp cố định lớp nền, tạo hiệu ứng mịn lì nhưng không làm mặt bị dày.', 'Kiềm dầu nhẹ, giảm bóng nhờn, giúp lớp nền bền và mịn hơn.', 'Hạt phấn siêu mịn, finish tự nhiên, phù hợp nhiều tông da.', 'Dặm nhẹ vùng chữ T, dưới mắt và vùng dễ đổ dầu sau bước kem nền.', 1, 4, '2026-05-22 08:26:11'),
(5, 'GB-0005', 'GlowBeauty Glow Velvet Lipstick', 'GlowBeauty', 'Son môi', 279000, 10, 'e3994ca6-7672-4e2d-9dc0-63a1c99cfbbb.png', 'Son GlowBeauty tông đỏ hồng nữ tính, chất son mịn, lên màu rõ và phù hợp nhiều phong cách.', 'Giúp môi nổi bật, làm sáng khuôn mặt, phù hợp đi học, đi làm và dự tiệc.', 'Kết cấu velvet mềm, sắc tố rõ, hạn chế khô môi khi dùng đúng cách.', 'Thoa trực tiếp lên môi hoặc tán lòng môi để tạo hiệu ứng nhẹ nhàng.', 1, 5, '2026-05-22 08:26:11'),
(6, 'GB-0006', 'GlowBeauty Rose Hydrating Toner', 'GlowBeauty', 'Toner & Essence', 259000, 10, 'd766a7f3-1211-40d7-b144-7c50b8323349.png', 'Toner hoa hồng GlowBeauty hỗ trợ cân bằng da sau rửa mặt và chuẩn bị da cho các bước dưỡng tiếp theo.', 'Cấp ẩm nhẹ, làm dịu da, giúp da mềm và dễ hấp thu serum hơn.', 'Chiết xuất hoa hồng, thành phần cấp ẩm, kết cấu nước nhẹ.', 'Đổ ra bông tẩy trang hoặc lòng bàn tay, vỗ nhẹ sau bước làm sạch.', 1, 6, '2026-05-22 08:26:11'),
(7, 'GB-0007', 'GlowBeauty Premium Combo 7 sản phẩm', 'GlowBeauty', 'Combo', 1499000, 10, '80c93bee-84bb-4c67-8b4d-c1672ef08fcd.png', 'Combo chăm sóc và trang điểm toàn diện gồm sữa rửa mặt, toner, serum, kem dưỡng, cushion, son và bảng mắt.', 'Tiết kiệm chi phí, đầy đủ sản phẩm cơ bản cho routine làm đẹp hằng ngày.', 'Bộ sản phẩm được phối theo tông hồng rose-gold, phù hợp làm quà tặng.', 'Dùng theo thứ tự: làm sạch, cân bằng, serum, kem dưỡng, nền, mắt và son.', 1, 7, '2026-05-22 08:26:11'),
(8, 'GB-0008', 'GlowBeauty Luxury Makeup Banner Collection', 'GlowBeauty', 'Combo', 2350000, 10, '3d7437b9-c6c3-4326-a9df-ca2ee66a36d6.png', 'Bộ sưu tập makeup cao cấp của GlowBeauty gồm sản phẩm nền, son, phấn mắt và phấn má tông rose-gold.', 'Mang lại phong cách trang điểm sang trọng, đồng bộ và nổi bật.', 'Các sản phẩm trong bộ có tông màu dễ dùng, phù hợp nhiều dịp.', 'Sử dụng từng sản phẩm theo nhu cầu trang điểm cá nhân.', 1, 8, '2026-05-22 08:26:11'),
(9, 'GB-0009', 'Biore Sạch Dịu Nhẹ Sáng Mịn & Dưỡng Ẩm', 'Biore', 'Sữa rửa mặt', 89000, 10, '0001.jpg', 'Sữa rửa mặt Biore giúp làm sạch da nhẹ nhàng, phù hợp sử dụng hằng ngày.', 'Làm sạch bụi bẩn, dầu thừa, hỗ trợ da sáng mịn và mềm hơn sau khi rửa.', 'Chiết xuất nước vo gạo, công nghệ thanh lọc da, kết cấu tạo bọt nhẹ.', 'Làm ướt mặt, lấy lượng vừa đủ tạo bọt, massage 30-60 giây rồi rửa sạch.', 1, 9, '2026-05-22 08:26:11'),
(10, 'GB-0010', 'Simple Refreshing Facial Wash', 'Simple', 'Sữa rửa mặt', 125000, 10, 'rua_mat_simple-3.jpg', 'Sữa rửa mặt Simple nổi bật với công thức dịu nhẹ, phù hợp da nhạy cảm.', 'Làm sạch da mà không gây cảm giác khô căng, giúp da thông thoáng.', 'Không hương liệu, không màu nhân tạo, công thức lành tính.', 'Dùng sáng và tối, massage nhẹ trên da ẩm rồi rửa sạch.', 1, 10, '2026-05-22 08:26:11'),
(11, 'GB-0011', 'CeraVe Foaming Facial Cleanser', 'CeraVe', 'Sữa rửa mặt', 365000, 10, 'SRM-Cerave-Foaming-Facial-For-Normal-To-Oily-Skin-.jpeg', 'Sữa rửa mặt tạo bọt CeraVe dành cho da thường đến da dầu.', 'Làm sạch dầu thừa, hỗ trợ bảo vệ hàng rào ẩm tự nhiên của da.', 'Ceramides, Niacinamide và Hyaluronic Acid.', 'Dùng 1-2 lần/ngày, tránh vùng mắt và rửa lại bằng nước sạch.', 1, 11, '2026-05-22 08:26:11'),
(12, 'GB-0012', 'Cetaphil Gentle Skin Cleanser', 'Cetaphil', 'Sữa rửa mặt', 345000, 10, 'sua-rua-mat-diu-nhe-cetaphil-gentle-skin-cleanser-473ml-070323-050017-600x600.jpg', 'Sữa rửa mặt Cetaphil Gentle Skin Cleanser phù hợp da khô, da nhạy cảm và dùng hằng ngày.', 'Làm sạch dịu nhẹ, hỗ trợ duy trì độ ẩm và giảm cảm giác căng da.', 'Công thức dịu nhẹ, không xà phòng mạnh, phù hợp nhiều loại da.', 'Massage trên da ẩm, rửa sạch hoặc lau bằng bông mềm tùy nhu cầu.', 1, 12, '2026-05-22 08:26:11'),
(13, 'GB-0013', 'Proactiv Revitalizing Toner', 'Proactiv', 'Toner & Essence', 320000, 10, '61545kOb3bL._SL1500_.jpg', 'Toner Proactiv hỗ trợ chăm sóc da dầu mụn, giúp da thông thoáng hơn sau bước rửa mặt.', 'Hỗ trợ cân bằng da, làm sạch cặn thừa và tạo cảm giác tươi mát.', 'Công thức toner dành cho da dễ nổi mụn.', 'Dùng bông tẩy trang lau nhẹ sau khi rửa mặt, tránh vùng mắt.', 1, 13, '2026-05-22 08:26:11'),
(14, 'GB-0014', 'Su:m37 Skin Saver Essential Cleansing Foam', 'Su:m37', 'Sữa rửa mặt', 260000, 10, '62575e2ebc921ca7dd4b49efd9e67e01.jpg', 'Sữa rửa mặt Su:m37 Skin Saver tạo bọt mịn, phù hợp làm sạch dịu nhẹ.', 'Loại bỏ bụi bẩn, giúp da mềm và sạch thoáng sau khi dùng.', 'Công thức bọt mịn, cảm giác dịu trên da.', 'Tạo bọt với nước, massage nhẹ rồi rửa sạch.', 1, 14, '2026-05-22 08:26:11'),
(15, 'GB-0015', 'Quiym Aqua Vitalize Skincare Set', 'Quiym', 'Skincare', 499000, 10, 'a97d766d-62a5-420b-b225-68e695629534.__CR0-0-970-600_PT0_SX970_V1___.jpg', 'Bộ chăm sóc da cấp nước gồm serum, toner, kem dưỡng và sữa rửa mặt tông xanh aqua.', 'Cấp ẩm, hỗ trợ da căng mướt, phù hợp da thiếu nước.', 'Hyaluronic Acid, chiết xuất biển và thành phần cấp ẩm.', 'Dùng theo routine: rửa mặt, toner, serum, kem dưỡng.', 1, 15, '2026-05-22 08:26:11'),
(16, 'GB-0016', 'CNP Laboratory Propolis Treatment Essence', 'CNP Laboratory', 'Toner & Essence', 420000, 10, 'vn-11134207-7ras8-m2vwo5c0s3ra9e.jfif', 'Tinh chất CNP Propolis Treatment Ampule Essence hỗ trợ làm dịu và phục hồi da.', 'Giúp da mềm, sáng khỏe và có độ bóng tự nhiên.', 'Chiết xuất keo ong, thành phần cấp ẩm và làm dịu.', 'Dùng sau toner, vỗ nhẹ đến khi thấm.', 1, 16, '2026-05-22 08:26:11'),
(17, 'GB-0017', 'Rare Beauty Soft Pinch Tinted Lip Oil', 'Rare Beauty', 'Son môi', 395000, 10, 'son-duong-rare-beauty-soft-pinch-tinted-lip-oil-mau-hong-kho-2-jpg-1680486361-03042023084601.webp', 'Son dầu Rare Beauty tạo sắc môi hồng nhẹ, bóng khỏe và mềm mại.', 'Dưỡng môi, tạo màu tự nhiên, phù hợp makeup nhẹ.', 'Kết cấu lip oil mỏng nhẹ, sắc hồng khô dễ dùng.', 'Thoa trực tiếp lên môi, dặm lại khi cần.', 1, 17, '2026-05-22 08:26:11'),
(18, 'GB-0018', 'Merzy The Watery Dew Tint V6 Siren', 'Merzy', 'Son môi', 179000, 10, 'f7973ec129c17238ddfc48d109b88448.png', 'Son tint Merzy V6 Siren màu đỏ nổi bật, hiệu ứng môi căng mọng.', 'Lên màu rõ, tạo hiệu ứng full môi hoặc lòng môi quyến rũ.', 'Chất tint bóng nhẹ, màu đỏ cam dễ nổi bật.', 'Thoa một lớp mỏng và tán đều, có thể chồng lớp để đậm hơn.', 1, 18, '2026-05-22 08:26:11'),
(19, 'GB-0019', 'Merzy New Watery Dew Tint WD21', 'Merzy', 'Son môi', 169000, 10, 'website_nd-10_db148e2cda6c4858af668121aebc5230_master.png', 'Son Merzy WD21 tông đỏ hồng khô, chất tint nước bóng nhẹ.', 'Tạo môi tươi tắn, mọng nhẹ, phù hợp đi học đi làm.', 'Kết cấu watery tint, sắc đỏ hồng dễ dùng.', 'Thoa lòng môi hoặc full môi tùy phong cách.', 1, 19, '2026-05-22 08:26:11'),
(20, 'GB-0020', 'Glamrr Q Long Wear Lip Cream', 'Glamrr Q', 'Son môi', 199000, 10, 'bang-mau-son-glamrr-q-moi-nhat-glamrr-q-long-wear-lip-cream-1-fa5f7a0e.jpg', 'Son kem Glamrr Q Long Wear Lip Cream có độ bám màu tốt và finish hiện đại.', 'Lên màu đậm, giúp môi sắc nét, phù hợp makeup cá tính.', 'Chất son kem lì, công thức bền màu.', 'Gạt bớt son trên cọ, thoa từ lòng môi ra viền môi.', 1, 20, '2026-05-22 08:26:11'),
(21, 'GB-0021', 'Romand Better Than Cheek Blush', 'Romand', 'Phấn má', 235000, 10, '18w401_-_2024-01-17t163224.025_279a2f7231824c3cbcb25e25009bf52d_large.png', 'Phấn má Romand tông tự nhiên, chất phấn mịn dễ tán.', 'Tạo hiệu ứng má hồng nhẹ nhàng, giúp gương mặt tươi sáng.', 'Bột phấn mịn, tông màu nude hồng dễ phối.', 'Dùng cọ lấy phấn, tán nhẹ lên vùng gò má.', 1, 21, '2026-05-22 08:26:11'),
(22, 'GB-0022', 'YSL Make Me Blush Bold Blurring Blush', 'YSL', 'Phấn má', 950000, 10, 'phan-ma-hong-yves-saint-laurent-ysl-make-me-blush-bold-blurring-blush-87-pink-voltage-mau-hong-dao-6g-68e389735f282-06102025161843.webp', 'Phấn má YSL tông hồng đào cao cấp, thiết kế sang trọng.', 'Tạo hiệu ứng ửng hồng nổi bật, làm mềm đường nét khuôn mặt.', 'Chất phấn mịn, màu hồng đào dễ nổi bật.', 'Tán nhẹ từng lớp để đạt độ hồng mong muốn.', 1, 22, '2026-05-22 08:26:11'),
(23, 'GB-0023', 'Dasique Blending Mood Cheek Palette', 'Dasique', 'Phấn má', 315000, 10, 'vn-11134207-7r98o-lw1fkikj774pc0.jfif', 'Bảng phấn má Dasique 4 màu tông hồng dễ thương, phù hợp makeup Hàn Quốc.', 'Tạo má hồng nhiều sắc độ, dễ phối màu theo phong cách.', '4 ô màu hồng đào, hồng sữa và hồng tươi.', 'Dùng riêng từng màu hoặc phối các ô để tạo màu má hài hòa.', 1, 23, '2026-05-22 08:26:11'),
(24, 'GB-0024', '3CE Face Blush Nude Peach', '3CE', 'Phấn má', 295000, 10, 'phan-ma-hong-3ce-face-blush-5g-nude-peach_2c394fba64e14cb7b11074ede798e806_large.jpg', 'Phấn má 3CE màu nude peach cho hiệu ứng má tự nhiên.', 'Giúp khuôn mặt tươi hơn mà vẫn giữ phong cách nhẹ nhàng.', 'Phấn nén mịn, tông nude đào dễ dùng.', 'Tán lên gò má bằng cọ mềm.', 1, 24, '2026-05-22 08:26:11'),
(25, 'GB-0025', 'Veecci 4 ô Blush & Highlight', 'Veecci', 'Phấn mắt', 189000, 10, 'bang_phan_ma___highlight_4_o_veecci__4__608fc3d99ce44793a75e3ddd53f74fc0.jpg', 'Bảng Veecci 4 ô kết hợp phấn má và highlight tông hồng đào.', 'Tạo điểm nhấn bắt sáng, làm gương mặt rạng rỡ hơn.', 'Gồm màu lì, nhũ nhẹ và highlight.', 'Dùng cọ nhỏ tán lên mắt, má hoặc vùng cần bắt sáng.', 1, 25, '2026-05-22 08:26:11'),
(26, 'GB-0026', 'Makeup By Mario SurrealSkin Foundation', 'Makeup By Mario', 'Kem nền', 620000, 10, 'vn-11134207-7r98o-luvji4fa7imq9b.jfif', 'Kem nền Makeup By Mario cho lớp nền mỏng mịn, phù hợp phong cách da tự nhiên.', 'Giúp da đều màu, tạo hiệu ứng mềm mịn và dễ chồng lớp ở vùng cần che phủ.', 'Kết cấu liquid foundation, finish tự nhiên, dễ tán.', 'Lắc đều, lấy lượng nhỏ và tán từ trung tâm gương mặt ra ngoài.', 1, 26, '2026-05-22 08:26:11'),
(27, 'GB-0027', 'Make Up For Ever HD Skin Foundation', 'Make Up For Ever', 'Kem nền', 890000, 10, 'vn-11134207-7ras8-mc08sotjs22a9d.jfif', 'Kem nền HD Skin tạo lớp nền chuyên nghiệp, phù hợp chụp ảnh và makeup lâu giờ.', 'Che phủ ổn, giữ nền mịn và tự nhiên trên da.', 'Kết cấu liquid foundation, nhiều tông màu.', 'Tán đều bằng bông mút ẩm hoặc cọ nền.', 1, 27, '2026-05-22 08:26:11'),
(28, 'GB-0028', 'Maybelline Super Stay Lumi Matte Foundation', 'Maybelline', 'Kem nền', 298000, 10, 'kem-nen-maybelline-super-stay-up-to-30h-lumi-matte-foundation-spf16-pa35ml-5.jpg', 'Kem nền Maybelline Super Stay Lumi Matte hỗ trợ nền lâu trôi và sáng mịn.', 'Giúp da đều màu, che phủ khuyết điểm nhẹ đến vừa.', 'Kết cấu nền mỏng nhẹ, SPF16 PA+++.', 'Lắc đều trước khi dùng, tán từng lớp mỏng.', 1, 28, '2026-05-22 08:26:11'),
(29, 'GB-0029', 'Carslan Lasting Moisture Foundation', 'Carslan', 'Kem nền', 250000, 10, 'kem_nen_carslan_lasting_moisture_foundation__5__776a71f9d377405c85df5d4e7861343e.jpg', 'Kem nền Carslan Lasting Moisture tạo lớp nền ẩm mịn, phù hợp makeup tự nhiên.', 'Che phủ nhẹ, hỗ trợ da căng bóng và đều màu.', 'Kết cấu dưỡng ẩm, finish tự nhiên.', 'Tán đều sau bước dưỡng và kem lót.', 1, 29, '2026-05-22 08:26:11'),
(30, 'GB-0030', 'ColorKey Airy Longwear Foundation', 'ColorKey', 'Kem nền', 269000, 10, 'combo-colorkey-kem-nen-w2-30g-xit-co-dinh-lop-trang-diem-nam-cham-den-100ml-2-1708597625_img_800x800_eb97c5_fit_center.jpg', 'Kem nền ColorKey Airy Longwear có lớp nền thoáng khí, mềm mại và lâu trôi.', 'Tạo nền mỏng nhẹ, hỗ trợ đều màu và bền lớp trang điểm.', 'Kết cấu lỏng nhẹ, độ cấp ẩm ổn, phù hợp da hỗn hợp.', 'Lấy lượng nhỏ, tán đều và chồng lớp ở vùng cần che phủ.', 1, 30, '2026-05-22 08:26:11'),
(31, 'GB-0031', '3CE Stylenanda Waterful Foundation', '3CE', 'Kem nền', 420000, 10, 'figure-1651051253314.png', 'Kem nền 3CE Waterful Foundation tạo lớp nền mềm mịn và hiệu ứng da trong trẻo.', 'Che khuyết điểm nhẹ, cải thiện vẻ ngoài nền da và nếp nhăn nhỏ.', 'Kết cấu waterful foundation, finish mỏng nhẹ.', 'Tán bằng mút ẩm để lớp nền tự nhiên hơn.', 1, 31, '2026-05-22 08:26:11'),
(32, 'GB-0032', 'NARS Radiant Creamy Concealer', 'NARS', 'Che khuyết điểm', 720000, 10, 'z4985812587158_338a830ffecc2773109521584ea295a8_8c6fcb18c4a84156a620a335763f49af_master.jpg', 'Che khuyết điểm NARS nổi tiếng với độ che phủ tốt và finish tự nhiên.', 'Che quầng thâm, vết mụn, vùng da không đều màu mà không quá dày.', 'Kết cấu creamy, nhiều tông màu, dễ tán.', 'Chấm lượng nhỏ lên vùng cần che, vỗ nhẹ bằng tay hoặc mút.', 1, 32, '2026-05-22 08:26:11'),
(33, 'GB-0033', 'The Saem Cover Perfection Tip Concealer', 'The Saem', 'Che khuyết điểm', 89000, 10, '9dfe421af7c9119748d8_2dbb5be78ad643ea852c39241508b15a_master.jpg', 'Che khuyết điểm The Saem có giá tốt, phù hợp che mụn và thâm nhẹ.', 'Giúp vùng da đều màu hơn, dễ dùng cho makeup hằng ngày.', 'Kết cấu lỏng, đầu cọ tiện lợi.', 'Chấm lên vùng cần che, tán viền để hòa vào nền.', 1, 33, '2026-05-22 08:26:11'),
(34, 'GB-0034', 'SVMY FT Color Concealer Palette', 'SVMY', 'Che khuyết điểm', 139000, 10, 'cn-11134207-7r98o-lxwj3rmd7gatac.jfif', 'Bảng che khuyết điểm SVMY 3 màu hỗ trợ hiệu chỉnh nhiều vùng trên khuôn mặt.', 'Che quầng thâm, vùng xỉn màu, làm sáng sống mũi và vùng dưới mắt.', '3 tông hiệu chỉnh: natural, lifting và salmon.', 'Dùng cọ nhỏ lấy màu phù hợp, tán trước hoặc sau nền tùy nhu cầu.', 1, 34, '2026-05-22 08:26:11'),
(35, 'GB-0035', 'Merzy Volume Curl Mascara', 'Merzy', 'Mascara', 169000, 10, '458386683_931178342387753_2192065793230772382_n_f8729475db074a35a004d6fa342d739a_1024x1024.jpg', 'Mascara Merzy giúp hàng mi rõ nét, phù hợp makeup mắt tự nhiên lẫn cá tính.', 'Tạo hiệu ứng mi cong, dày hơn và nổi bật hơn.', 'Đầu cọ dễ chải, chất mascara bám mi tốt.', 'Chải từ chân mi lên ngọn theo chuyển động ziczac nhẹ.', 1, 35, '2026-05-22 08:26:11');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Quản trị viên', 'admin@gmail.com', '$2y$12$osim3.pR9yKQReEenQmV/.LCinnNcGLmNPxpSGpnixN7e2azPkaOu', 'admin', '2026-05-22 08:26:11'),
(2, 'Nguyễn Thu Hà', 'thuhaglow@example.com', '$2y$12$osim3.pR9yKQReEenQmV/.LCinnNcGLmNPxpSGpnixN7e2azPkaOu', 'customer', '2026-05-22 08:26:11'),
(3, 'Trần Minh Anh', 'minhanhglow@example.com', '$2y$12$osim3.pR9yKQReEenQmV/.LCinnNcGLmNPxpSGpnixN7e2azPkaOu', 'customer', '2026-05-22 08:26:11'),
(4, 'Lê Phương Linh', 'phuonglinhglow@example.com', '$2y$12$osim3.pR9yKQReEenQmV/.LCinnNcGLmNPxpSGpnixN7e2azPkaOu', 'customer', '2026-05-22 08:26:11'),
(5, 'Phạm Ngọc Mai', 'ngocmaiglow@example.com', '$2y$12$osim3.pR9yKQReEenQmV/.LCinnNcGLmNPxpSGpnixN7e2azPkaOu', 'customer', '2026-05-22 08:26:11'),
(6, 'Nguyễn Thị Ngoan', 'nn9499008@gmail.com', '$2y$10$Q73j1LRSpzzXkoLNKGIp6uDEKPwZbvQLA7gc1kkFBGdtjS2R.0J2C', 'customer', '2026-05-22 13:09:22');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `contact_requests`
--
ALTER TABLE `contact_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
