<?php
require_once __DIR__ . '/../core/Database.php';

class Review {
    public static function ensureTable() {
        $pdo = Database::connect();
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS product_reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                user_id INT NULL,
                rating INT NOT NULL DEFAULT 5,
                comment TEXT NULL,
                image VARCHAR(255) NULL,
                seller_service TINYINT NOT NULL DEFAULT 5,
                shipping_service TINYINT NOT NULL DEFAULT 5,
                package_service TINYINT NOT NULL DEFAULT 5,
                status TINYINT NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_order_product_user (order_id, product_id, user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch(Exception $e) {}

        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS review_hidden_deletes (
                review_id INT NOT NULL PRIMARY KEY,
                product_id INT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch(Exception $e) {}

        foreach ([
            'seller_service' => "ALTER TABLE product_reviews ADD seller_service TINYINT NOT NULL DEFAULT 5",
            'shipping_service' => "ALTER TABLE product_reviews ADD shipping_service TINYINT NOT NULL DEFAULT 5",
            'package_service' => "ALTER TABLE product_reviews ADD package_service TINYINT NOT NULL DEFAULT 5",
            'status' => "ALTER TABLE product_reviews ADD status TINYINT NOT NULL DEFAULT 1",
            'admin_seen' => "ALTER TABLE product_reviews ADD admin_seen TINYINT NOT NULL DEFAULT 0",
            'user_id' => "ALTER TABLE product_reviews ADD user_id INT NULL",
            'image' => "ALTER TABLE product_reviews ADD image VARCHAR(255) NULL"
        ] as $col => $sql) {
            try {
                $check = $pdo->prepare("SHOW COLUMNS FROM product_reviews LIKE ?");
                $check->execute([$col]);
                if (!$check->fetch()) $pdo->exec($sql);
            } catch(Exception $e) {}
        }
    }


    private static function demoNames() {
        return ['Lan Anh','Minh Trang','Hoàng Mai','Thu Hà','Ngọc Linh','Thanh Huyền','Phương Anh','Bảo Ngọc','Hồng Nhung','Mai Chi','Khánh Ly','Yến Nhi','Ngọc Ánh','Thùy Dương'];
    }

    private static function demoComments() {
        return [
            'Sản phẩm giống ảnh, chất mịn, dùng lên da rất tự nhiên.',
            'Đóng gói cẩn thận, giao nhanh, màu lên đẹp hơn mình mong đợi.',
            'Dùng đi học và đi làm đều hợp, không bị nặng mặt.',
            'Thiết kế xinh, cầm chắc tay, chất lượng ổn so với giá.',
            'Mình đã mua lại lần hai, shop tư vấn nhiệt tình.',
            'Màu nhẹ nhàng, dễ dùng hằng ngày, rất đáng tiền.',
            'Sản phẩm đẹp, lớp finish mịn và không bị bột nhiều.',
            'Giao đúng mẫu, hộp còn nguyên vẹn, dùng khá thích.'
        ];
    }

    private static function hiddenFakeIds($productId = null) {
        self::ensureTable();
        try {
            if ($productId !== null) {
                $st = Database::connect()->prepare("SELECT review_id FROM review_hidden_deletes WHERE review_id < 0 AND product_id=?");
                $st->execute([(int)$productId]);
            } else {
                $st = Database::connect()->query("SELECT review_id FROM review_hidden_deletes WHERE review_id < 0");
            }
            $ids = [];
            foreach ($st->fetchAll() as $r) $ids[(int)$r['review_id']] = true;
            return $ids;
        } catch(Exception $e) {
            return [];
        }
    }

    private static function fakeReviewRowsForProduct($productId, $limit=20) {
        $productId = (int)$productId;
        $limit = max(0, (int)$limit);
        if ($productId <= 0 || $limit === 0) return [];
        try {
            $st = Database::connect()->prepare("SELECT id, name, image FROM products WHERE id=?");
            $st->execute([$productId]);
            $p = $st->fetch();
            if (!$p) return [];
        } catch(Exception $e) { return []; }
        $names = self::demoNames();
        $comments = self::demoComments();
        $count = min($limit, 10 + ($productId % 8));
        $rows = [];
        for ($i=0; $i<$count; $i++) {
            $rating = (($productId + $i) % 10 === 0) ? 4 : 5;
            $rows[] = [
                'id' => -($productId * 1000 + $i + 1),
                'order_id' => 31 + (($productId * 3 + $i) % 60),
                'product_id' => $productId,
                'user_id' => null,
                'rating' => $rating,
                'comment' => $comments[($productId + $i) % count($comments)],
                'image' => null,
                'seller_service' => 5,
                'shipping_service' => (($productId + $i) % 7 === 0) ? 4 : 5,
                'package_service' => 5,
                'status' => 1,
                'admin_seen' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-'.($i + ($productId % 6)).' days')),
                'product_name' => $p['name'],
                'product_image' => $p['image'],
                'customer_name' => $names[($productId + $i) % count($names)],
                'phone' => '09' . str_pad((string)(($productId*7919 + $i*137) % 100000000), 8, '0', STR_PAD_LEFT)
            ];
        }
        $hidden = self::hiddenFakeIds($productId);
        if (!empty($hidden)) {
            $rows = array_values(array_filter($rows, function($r) use ($hidden) {
                return empty($hidden[(int)$r['id']]);
            }));
        }
        return $rows;
    }

    private static function fakeReviewRowsAll($perProduct=2) {
        try {
            $products = Database::connect()->query("SELECT id FROM products WHERE status=1 ORDER BY sort_order ASC, id ASC LIMIT 36")->fetchAll();
        } catch(Exception $e) { return []; }
        $rows = [];
        foreach ($products as $p) {
            $rows = array_merge($rows, self::fakeReviewRowsForProduct((int)$p['id'], $perProduct));
        }
        usort($rows, function($a,$b){ return strcmp($b['created_at'], $a['created_at']); });
        return $rows;
    }

    private static function clampStar($value) {
        return max(1, min(5, (int)$value));
    }

    public static function create($data) {
        self::ensureTable();
        $rating = self::clampStar($data['rating'] ?? 5);
        $seller = self::clampStar($data['seller_service'] ?? 5);
        $shipping = self::clampStar($data['shipping_service'] ?? 5);
        $package = self::clampStar($data['package_service'] ?? 5);
        $comment = trim((string)($data['comment'] ?? ''));
        if ($comment === '') throw new Exception('Vui lòng nhập nội dung đánh giá.');

        $params = [
            (int)$data['order_id'],
            (int)$data['product_id'],
            $data['user_id'] ?: null,
            $rating,
            $comment,
            trim((string)($data['image'] ?? '')),
            $seller,
            $shipping,
            $package
        ];

        $pdo = Database::connect();
        try {
            $st = $pdo->prepare("INSERT INTO product_reviews(order_id, product_id, user_id, rating, comment, image, seller_service, shipping_service, package_service, status, admin_seen, created_at)
                VALUES(?,?,?,?,?,?,?,?,?,1,0,NOW())
                ON DUPLICATE KEY UPDATE 
                    rating=VALUES(rating),
                    comment=VALUES(comment),
                    image=VALUES(image),
                    seller_service=VALUES(seller_service),
                    shipping_service=VALUES(shipping_service),
                    package_service=VALUES(package_service),
                    status=1,
                    admin_seen=0,
                    created_at=NOW()");
            return $st->execute($params);
        } catch(Exception $e) {
            try {
                $st = $pdo->prepare("INSERT INTO product_reviews(order_id, product_id, user_id, rating, comment, image, seller_service, shipping_service, package_service, admin_seen, created_at)
                    VALUES(?,?,?,?,?,?,?,?,?,0,NOW())
                    ON DUPLICATE KEY UPDATE 
                        rating=VALUES(rating),
                        comment=VALUES(comment),
                        image=VALUES(image),
                        seller_service=VALUES(seller_service),
                        shipping_service=VALUES(shipping_service),
                        package_service=VALUES(package_service),
                        admin_seen=0,
                        created_at=NOW()");
                return $st->execute($params);
            } catch(Exception $e2) {
                $st = $pdo->prepare("INSERT INTO product_reviews(order_id, product_id, user_id, rating, comment, image, seller_service, shipping_service, package_service, created_at)
                    VALUES(?,?,?,?,?,?,?,?,?,NOW())
                    ON DUPLICATE KEY UPDATE 
                        rating=VALUES(rating),
                        comment=VALUES(comment),
                        image=VALUES(image),
                        seller_service=VALUES(seller_service),
                        shipping_service=VALUES(shipping_service),
                        package_service=VALUES(package_service),
                        created_at=NOW()");
                return $st->execute($params);
            }
        }
    }

    public static function all() {
        self::ensureTable();
        $sql = "SELECT r.*, p.name product_name, p.image product_image, COALESCE(NULLIF(u.name,''), NULLIF(o.customer_name,''), 'Khách hàng') AS customer_name, o.phone
                FROM product_reviews r
                LEFT JOIN products p ON p.id=r.product_id
                LEFT JOIN orders o ON o.id=r.order_id
                LEFT JOIN users u ON u.id=r.user_id
                ORDER BY r.created_at DESC, r.id DESC";
        $real = Database::connect()->query($sql)->fetchAll();
        $fake = self::fakeReviewRowsAll(2);
        // Ưu tiên đánh giá thật của khách mới nhất lên đầu; đánh giá mẫu chỉ nằm phía sau.
        usort($real, function($a, $b) {
            $ta = strtotime($a['created_at'] ?? '') ?: 0;
            $tb = strtotime($b['created_at'] ?? '') ?: 0;
            if ($tb === $ta) return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
            return $tb <=> $ta;
        });
        usort($fake, function($a, $b) {
            $ta = strtotime($a['created_at'] ?? '') ?: 0;
            $tb = strtotime($b['created_at'] ?? '') ?: 0;
            return $tb <=> $ta;
        });
        return array_merge($real, $fake);
    }

    public static function byOrder($orderId) {
        self::ensureTable();
        $st = Database::connect()->prepare("SELECT * FROM product_reviews WHERE order_id=?");
        $st->execute([(int)$orderId]);
        $map=[];
        foreach($st->fetchAll() as $r) $map[(int)$r['product_id']]=$r;
        return $map;
    }

    public static function byProduct($productId, $limit=20) {
        self::ensureTable();
        $st = Database::connect()->prepare("SELECT r.*, COALESCE(u.name, o.customer_name, 'Khách hàng') customer_name
                FROM product_reviews r
                LEFT JOIN users u ON u.id=r.user_id
                LEFT JOIN orders o ON o.id=r.order_id
                WHERE r.product_id=? AND COALESCE(r.status,1)=1
                ORDER BY r.created_at DESC, r.id DESC
                LIMIT ?");
        $st->bindValue(1, (int)$productId, PDO::PARAM_INT);
        $st->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $st->execute();
        $real = $st->fetchAll();
        $fake = self::fakeReviewRowsForProduct((int)$productId, max(0, (int)$limit - count($real)));
        $rows = array_merge($real, $fake);
        return array_slice($rows, 0, (int)$limit);
    }

    public static function summaryByProduct($productId) {
        self::ensureTable();
        $st = Database::connect()->prepare("SELECT COUNT(*) total_reviews, COALESCE(AVG(rating),0) avg_rating,
            SUM(CASE WHEN rating=5 THEN 1 ELSE 0 END) star5,
            SUM(CASE WHEN rating=4 THEN 1 ELSE 0 END) star4,
            SUM(CASE WHEN rating=3 THEN 1 ELSE 0 END) star3,
            SUM(CASE WHEN rating=2 THEN 1 ELSE 0 END) star2,
            SUM(CASE WHEN rating=1 THEN 1 ELSE 0 END) star1
            FROM product_reviews WHERE product_id=?");
        $st->execute([(int)$productId]);
        $real = $st->fetch() ?: ['total_reviews'=>0,'avg_rating'=>0,'star5'=>0,'star4'=>0,'star3'=>0,'star2'=>0,'star1'=>0];
        $fakeTotal = 22 + (((int)$productId * 7) % 28);
        $fake4 = max(3, (int)floor($fakeTotal * 0.07));
        $fake3 = max(1, (int)floor($fakeTotal * 0.02));
        $fake5 = $fakeTotal - $fake4 - $fake3;
        $hidden = self::hiddenFakeIds((int)$productId);
        if (!empty($hidden)) {
            $visibleFakeTotal = 0; $visibleFake5 = 0; $visibleFake4 = 0; $visibleFake3 = 0;
            $fakeRowCount = min(20, 10 + (((int)$productId) % 8));
            for ($i=0; $i<$fakeRowCount; $i++) {
                $fakeId = -(((int)$productId) * 1000 + $i + 1);
                if (!empty($hidden[$fakeId])) continue;
                $rating = ((((int)$productId) + $i) % 10 === 0) ? 4 : 5;
                $visibleFakeTotal++;
                if ($rating >= 5) $visibleFake5++;
                elseif ($rating == 4) $visibleFake4++;
                else $visibleFake3++;
            }
            if ($visibleFakeTotal < $fakeTotal) {
                $fakeTotal = $visibleFakeTotal;
                $fake5 = $visibleFake5;
                $fake4 = $visibleFake4;
                $fake3 = $visibleFake3;
            }
        }
        $realTotal = (int)($real['total_reviews'] ?? 0);
        $star5 = (int)($real['star5'] ?? 0) + $fake5;
        $star4 = (int)($real['star4'] ?? 0) + $fake4;
        $star3 = (int)($real['star3'] ?? 0) + $fake3;
        $star2 = (int)($real['star2'] ?? 0);
        $star1 = (int)($real['star1'] ?? 0);
        $total = $realTotal + $fakeTotal;
        $sum = $star5*5 + $star4*4 + $star3*3 + $star2*2 + $star1;
        return ['total_reviews'=>$total,'avg_rating'=>$total ? $sum/$total : 0,'star5'=>$star5,'star4'=>$star4,'star3'=>$star3,'star2'=>$star2,'star1'=>$star1];
    }



    public static function countAdminUnseen() {
        self::ensureTable();
        try {
            $row = Database::connect()->query("SELECT COUNT(*) c FROM product_reviews WHERE COALESCE(admin_seen,0)=0")->fetch();
            return (int)($row['c'] ?? 0);
        } catch(Exception $e) {
            return self::countAfterId($_SESSION['seen_review_id'] ?? 0);
        }
    }

    public static function markAdminSeen() {
        self::ensureTable();
        try {
            Database::connect()->exec("UPDATE product_reviews SET admin_seen=1 WHERE COALESCE(admin_seen,0)=0");
        } catch(Exception $e) {}
    }

    public static function latestId() {
        self::ensureTable();
        try {
            $row = Database::connect()->query("SELECT COALESCE(MAX(id),0) latest_id FROM product_reviews")->fetch();
            return (int)($row['latest_id'] ?? 0);
        } catch(Exception $e) {
            return 0;
        }
    }

    public static function countAfterId($id) {
        self::ensureTable();
        try {
            $st = Database::connect()->prepare("SELECT COUNT(*) c FROM product_reviews WHERE id > ?");
            $st->execute([(int)$id]);
            $row = $st->fetch();
            return (int)($row['c'] ?? 0);
        } catch(Exception $e) {
            return 0;
        }
    }

    public static function setStatus($id,$status) {
        self::ensureTable();
        try {
            return Database::connect()->prepare("UPDATE product_reviews SET status=? WHERE id=?")->execute([(int)$status,(int)$id]);
        } catch(Exception $e) {
            return false;
        }
    }

    public static function delete($id) {
        self::ensureTable();
        $id = (int)$id;
        if ($id < 0) {
            $productId = (int)floor(abs($id) / 1000);
            try {
                $st = Database::connect()->prepare("INSERT IGNORE INTO review_hidden_deletes(review_id, product_id, created_at) VALUES(?,?,NOW())");
                return $st->execute([$id, $productId]);
            } catch(Exception $e) {
                return false;
            }
        }
        return Database::connect()->prepare("DELETE FROM product_reviews WHERE id=?")->execute([$id]);
    }
}
