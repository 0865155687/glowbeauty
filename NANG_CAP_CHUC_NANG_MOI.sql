-- GlowBeauty - SQL nâng cấp an toàn, có thể chạy lại nhiều lần
-- Chạy trong database: beauty_makeup_shop

CREATE TABLE IF NOT EXISTS wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    INDEX idx_wishlist_user (user_id),
    INDEX idx_wishlist_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- XAMPP/MariaDB hỗ trợ ADD COLUMN IF NOT EXISTS, giúp import lại không bị lỗi Duplicate column.
ALTER TABLE orders ADD COLUMN IF NOT EXISTS user_id INT NULL AFTER total;
ALTER TABLE products ADD COLUMN IF NOT EXISTS sold_count INT DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS points INT DEFAULT 0;

-- Đồng bộ lại user_id cho đơn hàng cũ nếu số điện thoại khớp với tài khoản khách hàng.
UPDATE orders o
JOIN users u ON u.phone = o.phone
SET o.user_id = u.id
WHERE o.user_id IS NULL;

-- Cập nhật sold_count từ dữ liệu chi tiết đơn hàng hiện có.
UPDATE products p
LEFT JOIN (
    SELECT product_id, COALESCE(SUM(quantity), 0) AS total_sold
    FROM order_items
    GROUP BY product_id
) s ON s.product_id = p.id
SET p.sold_count = COALESCE(s.total_sold, 0);

-- Them yeu cau giao hang cho don hang
ALTER TABLE orders ADD COLUMN IF NOT EXISTS note TEXT NULL;
