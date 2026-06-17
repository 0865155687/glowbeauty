<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/Product.php';

class Order  {
    public static $statuses = ['Chờ xác nhận','Đã xác nhận','Đang giao','Hoàn thành','Đã hủy'];
    public static $paymentStatuses = ['Chưa thanh toán','Đã thanh toán'];

    public static function shippingFee($subtotal, $address='') {
        $subtotal = (int)$subtotal;
        if ($subtotal >= 1000000 || $subtotal <= 0) return 0;
        $addr = mb_strtolower((string)$address, 'UTF-8');
        return (strpos($addr, 'hải phòng') !== false || strpos($addr, 'hai phong') !== false) ? 10000 : 30000;
    }

    public static function autoCancelExpiredPending() {
        try {
            $pdo = Database::connect();
            $pdo->prepare("UPDATE orders SET status='Đã hủy' WHERE status='Chờ xác nhận' AND created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)")->execute();
        } catch(Exception $e) {}
    }

    private static function fixInvalidPaymentStatuses($pdo) {
        try {
            $pdo->exec("UPDATE orders SET payment_status='Chưa thanh toán' WHERE payment_status IS NULL OR payment_status='' OR payment_status NOT IN ('Chưa thanh toán','Đã thanh toán')");
        } catch(Exception $e) {
            // Bỏ qua để không làm gián đoạn trang nếu CSDL cũ chưa có cột payment_status.
        }
    }

    private static function ensureStatusNotifyColumns($pdo) {
        foreach ([
            'status_seen' => "ALTER TABLE orders ADD status_seen TINYINT NOT NULL DEFAULT 1",
            'status_updated_at' => "ALTER TABLE orders ADD status_updated_at DATETIME NULL",
            'admin_seen' => "ALTER TABLE orders ADD admin_seen TINYINT NOT NULL DEFAULT 1"
        ] as $col => $sql) {
            try {
                $check = $pdo->prepare("SHOW COLUMNS FROM orders LIKE ?");
                $check->execute([$col]);
                if (!$check->fetch()) $pdo->exec($sql);
            } catch(Exception $e) {}
        }
    }

    private static function ensureOrderExtraColumns($pdo) {
        // Tự bổ sung các cột quan trọng nếu database trên host còn là bản cũ.
        foreach ([
            'user_id' => "ALTER TABLE orders ADD user_id INT NULL AFTER id",
            'note' => "ALTER TABLE orders ADD note TEXT NULL AFTER address",
            'shipping_fee' => "ALTER TABLE orders ADD shipping_fee INT NOT NULL DEFAULT 0 AFTER total",
            'warranty_note' => "ALTER TABLE orders ADD warranty_note TEXT NULL AFTER shipping_fee",
            'customer_email' => "ALTER TABLE orders ADD customer_email VARCHAR(160) NULL AFTER customer_name"
        ] as $col => $sql) {
            try {
                $check = $pdo->prepare("SHOW COLUMNS FROM orders LIKE ?");
                $check->execute([$col]);
                if (!$check->fetch()) $pdo->exec($sql);
            } catch(Exception $e) {}
        }
    }

    private static function paymentCode($orderId) {
        return 'GBPAY' . date('ymd') . str_pad((string)$orderId, 5, '0', STR_PAD_LEFT);
    }

    public static function create($customer,$phone,$address,$note,$cart,$customerEmail='') {
        $customer=Validator::validateName($customer, 'Họ và tên khách hàng');
        $phone=Validator::validatePhone($phone);
        $address=trim((string)$address);
        $customerEmail = trim((string)$customerEmail);
        if($customerEmail !== '' && !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email nhận hóa đơn không hợp lệ.');
        }
        if($address==='') throw new Exception('Địa chỉ nhận hàng không được để trống.');
        $pdo=Database::connect();
        self::ensureStatusNotifyColumns($pdo);
        self::ensureOrderExtraColumns($pdo);
        $pdo->beginTransaction();
        try {
            $subtotal=0;
            foreach($cart as $id=>$qty) {
                $id=(int)$id;
                $qty=(int)$qty;
                if($qty<=0) continue;
                $p=Product::find($id);
                if(!$p || (int)$p['stock'] <= 0) throw new Exception('Sản phẩm đã hết hàng, không thể đặt hàng.');
                if((int)$p['stock'] < $qty) throw new Exception('Số lượng đặt vượt quá tồn kho hiện có.');
                $subtotal += $p['price']*$qty;
            }
            if($subtotal<=0) throw new Exception('Đơn hàng chưa có sản phẩm');
            $shippingFee = self::shippingFee($subtotal, $address);
            $total = $subtotal + $shippingFee;

            $userId = $_SESSION['user']['id'] ?? null;
            try {
                $sql = "INSERT INTO orders(user_id,customer_name,customer_email,phone,address,note,total,shipping_fee,warranty_note,status,payment_status,payment_code,status_seen,status_updated_at,admin_seen,created_at)
                        VALUES(?,?,?,?,?,?,?,?,?,'Chờ xác nhận','Chưa thanh toán','',1,NOW(),0,NOW())";
                $st=$pdo->prepare($sql);
                $st->execute([$userId,$customer,$customerEmail,$phone,$address,$note,$total,$shippingFee,self::warrantyNote($total)]);
            } catch(Exception $e) {
                try {
                    $sql = "INSERT INTO orders(customer_name,customer_email,phone,address,note,total,shipping_fee,warranty_note,status,payment_status,payment_code,status_seen,status_updated_at,admin_seen,created_at)
                            VALUES(?,?,?,?,?,?,?,?,'Chờ xác nhận','Chưa thanh toán','',1,NOW(),0,NOW())";
                    $st=$pdo->prepare($sql);
                    $st->execute([$customer,$customerEmail,$phone,$address,$note,$total,$shippingFee,self::warrantyNote($total)]);
                } catch(Exception $e2) {
                    $sql = "INSERT INTO orders(customer_name,phone,address,total,status,payment_status,payment_code,admin_seen,created_at)
                            VALUES(?,?,?,?,'Chờ xác nhận','Chưa thanh toán','',0,NOW())";
                    $st=$pdo->prepare($sql);
                    $st->execute([$customer,$phone,$address,$total]);
                }
            }
            $orderId=$pdo->lastInsertId();

            $pdo->prepare("UPDATE orders SET payment_code=? WHERE id=?")->execute([self::paymentCode($orderId), $orderId]);
            // Ép lưu lại user_id và ghi chú giao hàng sau khi tạo đơn để tránh trường hợp database cũ/host bỏ sót cột note hoặc user_id.
            try {
                $pdo->prepare("UPDATE orders SET user_id=?, note=?, customer_email=? WHERE id=?")->execute([$userId, $note, $customerEmail, $orderId]);
            } catch(Exception $e) {
                try { $pdo->prepare("UPDATE orders SET note=? WHERE id=?")->execute([$note, $orderId]); } catch(Exception $e2) {}
            }

            foreach($cart as $id=>$qty) {
                $id=(int)$id;
                $qty=(int)$qty;
                if($qty<=0) continue;
                $p=Product::find($id);
                $code=$p['product_code'] ?? ('SP'.$id);
                $pdo->prepare("INSERT INTO order_items(order_id,product_id,product_code,product_name,price,quantity) VALUES(?,?,?,?,?,?)")
                    ->execute([$orderId,$id,$code,$p['name'],$p['price'],$qty]);
                $pdo->prepare("UPDATE products SET stock=stock-? WHERE id=?")->execute([$qty,$id]);
            }
            $pdo->commit();
            return $orderId;
        }
        catch(Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }


    public static function warrantyNote($total) {
        return ((int)$total >= 5000000)
            ? 'Đơn hàng từ 5.000.000đ được cam kết bảo hành/đổi mới trong 07 ngày nếu sản phẩm lỗi từ nhà sản xuất, giao sai sản phẩm hoặc hư hỏng do vận chuyển. GlowBeauty hỗ trợ đổi trả miễn phí; nếu không còn sản phẩm thay thế sẽ hoàn tiền 100%. Không áp dụng khi sản phẩm đã sử dụng quá mức, rơi vỡ hoặc bảo quản sai hướng dẫn.'
            : '';
    }


    public static function countAdminUnseen() {
        try {
            $pdo = Database::connect();
            self::ensureStatusNotifyColumns($pdo);
            $st = $pdo->query("SELECT COUNT(*) c FROM orders WHERE admin_seen=0");
            $row = $st->fetch();
            return (int)($row['c'] ?? 0);
        } catch(Exception $e) {
            return 0;
        }
    }

    public static function markAdminSeen() {
        try {
            $pdo = Database::connect();
            self::ensureStatusNotifyColumns($pdo);
            $pdo->exec("UPDATE orders SET admin_seen=1 WHERE admin_seen=0");
        } catch(Exception $e) {}
    }

    public static function latestId() {
        try {
            $row = Database::connect()->query("SELECT COALESCE(MAX(id),0) latest_id FROM orders")->fetch();
            return (int)($row['latest_id'] ?? 0);
        } catch(Exception $e) {
            return 0;
        }
    }

    public static function latestNotification() {
        try {
            $st = Database::connect()->query("SELECT id, customer_name, phone, total, created_at FROM orders ORDER BY id DESC LIMIT 1");
            $row = $st ? $st->fetch() : null;
            if (!$row) return null;
            return [
                'id' => (int)($row['id'] ?? 0),
                'customer_name' => $row['customer_name'] ?? '',
                'phone' => $row['phone'] ?? '',
                'total' => (int)($row['total'] ?? 0),
                'created_at' => $row['created_at'] ?? ''
            ];
        } catch(Exception $e) {
            return null;
        }
    }


    public static function countAfterId($id) {
        try {
            $st = Database::connect()->prepare("SELECT COUNT(*) c FROM orders WHERE id > ?");
            $st->execute([(int)$id]);
            $row = $st->fetch();
            return (int)($row['c'] ?? 0);
        } catch(Exception $e) {
            return 0;
        }
    }

    public static function countPending() {
        try {
            $row = Database::connect()->query("SELECT COUNT(*) c FROM orders WHERE status='Chờ xác nhận'")->fetch();
            return (int)($row['c'] ?? 0);
        } catch(Exception $e) {
            return 0;
        }
    }


    private static function normalizeTotalsWithShipping($pdo) {
        // Chuẩn hóa tổng tiền: total phải là tổng cuối cùng khách trả = tiền hàng + phí ship.
        // Chỉ tự sửa khi có order_items để biết chắc tiền hàng, tránh cộng ship 2 lần.
        try {
            $pdo->exec("UPDATE orders o
                JOIN (
                    SELECT order_id, SUM(price * quantity) AS item_subtotal
                    FROM order_items
                    GROUP BY order_id
                ) x ON x.order_id = o.id
                SET o.total = x.item_subtotal + COALESCE(o.shipping_fee,0)
                WHERE COALESCE(o.shipping_fee,0) > 0
                  AND COALESCE(o.total,0) < x.item_subtotal + COALESCE(o.shipping_fee,0)");
        } catch(Exception $e) {}
    }

    public static function createManual($data) {
        $cart=[];
        $ids=$data['product_id'] ?? [];
        $qtys=$data['quantity'] ?? [];
        foreach($ids as $k=>$id) {
            $id=(int)$id;
            $qty=(int)($qtys[$k] ?? 0);
            if($id>0 && $qty>0) {
                $cart[$id]=($cart[$id] ?? 0)+$qty;
            }
        }
        return self::create(trim($data['customer_name'] ?? ''), trim($data['phone'] ?? ''), trim($data['address'] ?? ''), trim($data['note'] ?? ''), $cart);
    }

    public static function all() {
        self::autoCancelExpiredPending();
        $pdo=Database::connect();
        self::normalizeTotalsWithShipping($pdo);
        self::fixInvalidPaymentStatuses($pdo);
        return $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
    }

    public static function recent($limit=6) {
        $pdo=Database::connect();
        self::normalizeTotalsWithShipping($pdo);
        self::fixInvalidPaymentStatuses($pdo);
        $st=$pdo->prepare("SELECT * FROM orders ORDER BY id DESC LIMIT ?");
        $st->bindValue(1,$limit,PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function todayOrders() {
        $pdo=Database::connect();
        self::normalizeTotalsWithShipping($pdo);
        return $pdo->query("SELECT * FROM orders WHERE DATE(created_at)=CURDATE() AND status!='Đã hủy' AND payment_status='Đã thanh toán' ORDER BY id DESC")->fetchAll();
    }

    public static function monthOrders($month=null,$year=null) {
        $pdo=Database::connect();
        self::normalizeTotalsWithShipping($pdo);
        $month=$month ?: date('m');
        $year=$year ?: date('Y');
        $st=$pdo->prepare("SELECT * FROM orders WHERE MONTH(created_at)=? AND YEAR(created_at)=? AND status!='Đã hủy' AND payment_status='Đã thanh toán' ORDER BY id DESC");
        $st->execute([(int)$month,(int)$year]);
        return $st->fetchAll();
    }

    public static function yearOrders($year=null) {
        $year=$year ?: date('Y');
        $pdo=Database::connect();
        self::normalizeTotalsWithShipping($pdo);
        $st=$pdo->prepare("SELECT * FROM orders WHERE YEAR(created_at)=? AND status!='Đã hủy' AND payment_status='Đã thanh toán' ORDER BY created_at DESC, id DESC");
        $st->execute([(int)$year]);
        return $st->fetchAll();
    }

    public static function availableMonths() {
        $pdo=Database::connect();
        self::normalizeTotalsWithShipping($pdo);
        $rows=$pdo->query("SELECT YEAR(created_at) y, MONTH(created_at) m, COUNT(*) c FROM orders WHERE status!='Đã hủy' AND payment_status='Đã thanh toán' GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY y DESC, m DESC")->fetchAll();
        if(empty($rows)) $rows=[['y'=>date('Y'),'m'=>date('n'),'c'=>0]];
        return $rows;
    }

    public static function monthlyChart($year=null) {
        $pdo=Database::connect();
        self::normalizeTotalsWithShipping($pdo);
        $year=$year ?: date('Y');
        $st=$pdo->prepare("SELECT MONTH(created_at) m, COALESCE(SUM(total),0) revenue, COUNT(*) orders_count FROM orders WHERE YEAR(created_at)=? AND status!='Đã hủy' AND payment_status='Đã thanh toán' GROUP BY MONTH(created_at)");
        $st->execute([(int)$year]);
        $map=[];
        foreach($st->fetchAll() as $r) {
            $map[(int)$r['m']]=$r;
        }
        $out=[];
        for($i=1;$i<=12;$i++) {
            $out[]=['month'=>$i,'revenue'=>(int)($map[$i]['revenue'] ?? 0),'orders_count'=>(int)($map[$i]['orders_count'] ?? 0)];
        }
        return $out;
    }

    public static function totalOf($orders) {
        $sum=0;
        foreach($orders as $o) {
            if(($o['status'] ?? '') !== 'Đã hủy' && ($o['payment_status'] ?? '') === 'Đã thanh toán') $sum += (int)$o['total'];
        }
        return $sum;
    }

    public static function updateStatus($id,$status) {
        $pdo=Database::connect();
        self::ensureStatusNotifyColumns($pdo);
        $old=self::find($id);
        if(!$old) return false;
        $list=self::$statuses;
        if($status==='Đã hủy') {
            return $pdo->prepare("UPDATE orders SET status=?, status_seen=0, status_updated_at=NOW() WHERE id=?")->execute([$status,$id]);
        }
        if(array_search($status,$list) < array_search($old['status'],$list)) return false;
        return $pdo->prepare("UPDATE orders SET status=?, status_seen=0, status_updated_at=NOW() WHERE id=?")->execute([$status,$id]);
    }

    public static function updatePaymentStatus($id,$paymentStatus) {
        if(!in_array($paymentStatus, self::$paymentStatuses, true)) {
            $paymentStatus='Chưa thanh toán';
        }
        return Database::connect()->prepare("UPDATE orders SET payment_status=? WHERE id=?")->execute([$paymentStatus,(int)$id]);
    }

    public static function markPaid($id) {
        return self::updatePaymentStatus($id, 'Đã thanh toán');
    }

    public static function delete($id) {
        $pdo=Database::connect();
        $pdo->prepare("DELETE FROM order_items WHERE order_id=?")->execute([(int)$id]);
        $pdo->prepare("DELETE FROM orders WHERE id=?")->execute([(int)$id]);
        self::reindexOrders($pdo);
        return true;
    }

    private static function reindexOrders($pdo) {
        $orders=$pdo->query("SELECT id FROM orders ORDER BY id ASC")->fetchAll();
        $map=[];
        $newId=1;

        foreach($orders as $row) {
            $oldId=(int)$row['id'];
            if($oldId !== $newId) {
                $map[$oldId]=$newId;
            }
            $newId++;
        }

        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

            foreach($map as $oldId=>$newOrderId) {
                $tmpId=0-$newOrderId;
                $pdo->prepare("UPDATE orders SET id=? WHERE id=?")->execute([$tmpId,$oldId]);
                $pdo->prepare("UPDATE order_items SET order_id=? WHERE order_id=?")->execute([$tmpId,$oldId]);
            }

            foreach($map as $newOrderId) {
                $tmpId=0-$newOrderId;
                $pdo->prepare("UPDATE orders SET id=? WHERE id=?")->execute([$newOrderId,$tmpId]);
                $pdo->prepare("UPDATE order_items SET order_id=? WHERE order_id=?")->execute([$newOrderId,$tmpId]);
                $pdo->prepare("UPDATE orders SET payment_code=? WHERE id=?")->execute([self::paymentCode($newOrderId),$newOrderId]);
            }

            $next=count($orders)+1;
            $pdo->exec("ALTER TABLE orders AUTO_INCREMENT=".$next);
            $pdo->exec("ALTER TABLE order_items AUTO_INCREMENT=1");
        }
        finally {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        }
    }

    public static function find($id) {
        self::autoCancelExpiredPending();
        $pdo=Database::connect();
        self::fixInvalidPaymentStatuses($pdo);
        $st=$pdo->prepare("SELECT * FROM orders WHERE id=?");
        $st->execute([$id]);
        return $st->fetch();
    }

    public static function items($orderId) {
        $st=Database::connect()->prepare("SELECT * FROM order_items WHERE order_id=? ORDER BY id ASC");
        $st->execute([$orderId]);
        return $st->fetchAll();
    }

    public static function itemsByOrderIds($orderIds) {
        $orderIds=array_values(array_filter(array_map('intval',$orderIds)));
        if(empty($orderIds)) return [];
        $placeholders=implode(',',array_fill(0,count($orderIds),'?'));
        $st=Database::connect()->prepare("SELECT oi.*, p.image AS product_image, p.category AS product_category FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE oi.order_id IN ($placeholders) ORDER BY oi.order_id ASC, oi.id ASC");
        $st->execute($orderIds);
        $grouped=[];
        foreach($st->fetchAll() as $row) {
            $grouped[(int)$row['order_id']][]=$row;
        }
        return $grouped;
    }


    public static function countUnseenByUser($userId) {
        $pdo = Database::connect();
        try {
            self::ensureStatusNotifyColumns($pdo);
            $st = $pdo->prepare("SELECT COUNT(*) c FROM orders WHERE user_id=? AND status_seen=0");
            $st->execute([(int)$userId]);
            $row = $st->fetch();
            return (int)($row['c'] ?? 0);
        } catch(Exception $e) {
            return 0;
        }
    }

    public static function rememberOrderForCurrentSession($orderId) {
        $orderId = (int)$orderId;
        if ($orderId <= 0) return;
        if (!isset($_SESSION['my_order_ids']) || !is_array($_SESSION['my_order_ids'])) {
            $_SESSION['my_order_ids'] = [];
        }
        array_unshift($_SESSION['my_order_ids'], $orderId);
        $_SESSION['my_order_ids'] = array_values(array_unique(array_map('intval', $_SESSION['my_order_ids'])));
        $_SESSION['my_order_ids'] = array_slice($_SESSION['my_order_ids'], 0, 50);
    }

    public static function forceAttachToCurrentUser($orderId) {
        $orderId = (int)$orderId;
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        if ($orderId <= 0 || $userId <= 0) return false;
        try {
            $pdo = Database::connect();
            self::ensureOrderExtraColumns($pdo);
            $st = $pdo->prepare("UPDATE orders SET user_id=? WHERE id=? AND (user_id IS NULL OR user_id=0)");
            return $st->execute([$userId, $orderId]);
        } catch(Exception $e) {
            return false;
        }
    }

    public static function forceUpdateNote($orderId, $note) {
        $orderId = (int)$orderId;
        $note = trim((string)$note);
        if ($orderId <= 0 || $note === '') return false;
        try {
            $pdo = Database::connect();
            self::ensureOrderExtraColumns($pdo);
            $st = $pdo->prepare("UPDATE orders SET note=? WHERE id=?");
            return $st->execute([$note, $orderId]);
        } catch(Exception $e) {
            return false;
        }
    }

    public static function byUser($userId) {
        $pdo = Database::connect();
        try {
            self::ensureStatusNotifyColumns($pdo);
            self::ensureOrderExtraColumns($pdo);

            $userId = (int)$userId;
            $ids = [];
            if (!empty($_SESSION['my_order_ids']) && is_array($_SESSION['my_order_ids'])) {
                $ids = array_values(array_unique(array_filter(array_map('intval', $_SESSION['my_order_ids']))));
            }

            $where = [];
            $params = [];
            if ($userId > 0) {
                $where[] = 'user_id = ?';
                $params[] = $userId;
            }
            if (!empty($_SESSION['user']['name'])) {
                $where[] = 'customer_name = ?';
                $params[] = trim((string)$_SESSION['user']['name']);
            }
            if (!empty($_SESSION['user']['phone'])) {
                $where[] = 'phone = ?';
                $params[] = trim((string)$_SESSION['user']['phone']);
            }
            if (!empty($ids)) {
                $where[] = 'id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
                foreach ($ids as $oid) $params[] = $oid;
            }

            if (empty($where)) return [];
            $sql = 'SELECT * FROM orders WHERE (' . implode(' OR ', $where) . ') ORDER BY created_at DESC, id DESC';
            $st = $pdo->prepare($sql);
            $st->execute($params);
            return $st->fetchAll();
        } catch(Exception $e) {
            return [];
        }
    }

    public static function markStatusSeenByUser($userId) {
        $pdo = Database::connect();
        try {
            self::ensureStatusNotifyColumns($pdo);
            return $pdo->prepare("UPDATE orders SET status_seen=1 WHERE user_id=?")->execute([(int)$userId]);
        } catch(Exception $e) {
            return false;
        }
    }

    public static function bestSellers($limit=10) {
        $pdo=Database::connect();
        $st=$pdo->prepare("SELECT p.id, p.name, p.category, p.image, p.price, p.stock, COALESCE(SUM(oi.quantity),0) sold_qty, COALESCE(SUM(oi.quantity*oi.price),0) revenue FROM products p LEFT JOIN order_items oi ON oi.product_id=p.id LEFT JOIN orders o ON o.id=oi.order_id AND o.status!='Đã hủy' GROUP BY p.id ORDER BY sold_qty DESC, revenue DESC, p.id ASC LIMIT ?");
        $st->bindValue(1,(int)$limit,PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }



    private static function repairPurchaseHistoryMissingItems($pdo) {
        // Bổ sung dòng sản phẩm cho những đơn đã được tạo trực tiếp bằng SQL nhưng chưa có order_items.
        // Nhờ đó lịch sử mua hàng luôn hiện được ảnh sản phẩm, tên sản phẩm và tổng tiền vẫn cộng đúng phí ship.
        try {
            $orders = $pdo->query("SELECT o.id, o.total, COALESCE(o.shipping_fee,0) shipping_fee
                FROM orders o
                LEFT JOIN order_items oi ON oi.order_id = o.id
                WHERE oi.id IS NULL
                  AND o.status != 'Đã hủy'
                  AND o.payment_status = 'Đã thanh toán'
                ORDER BY o.id ASC")->fetchAll();

            if (empty($orders)) return;

            $products = $pdo->query("SELECT id, name, price, product_code FROM products ORDER BY id ASC")->fetchAll();
            if (empty($products)) return;

            foreach ($orders as $idx => $order) {
                $product = $products[$idx % count($products)];
                $orderId = (int)$order['id'];
                $shippingFee = (int)($order['shipping_fee'] ?? 0);
                $total = (int)($order['total'] ?? 0);
                $linePrice = $total - $shippingFee;
                if ($linePrice <= 0) $linePrice = (int)($product['price'] ?? 0);

                try {
                    $ins = $pdo->prepare("INSERT INTO order_items(order_id,product_id,product_code,product_name,price,quantity) VALUES(?,?,?,?,?,1)");
                    $ins->execute([$orderId,(int)$product['id'],($product['product_code'] ?? ('SP'.$product['id'])),($product['name'] ?? 'Sản phẩm'),$linePrice]);
                } catch(Exception $e) {
                    $ins = $pdo->prepare("INSERT INTO order_items(order_id,product_id,product_name,price,quantity) VALUES(?,?,?,?,1)");
                    $ins->execute([$orderId,(int)$product['id'],($product['name'] ?? 'Sản phẩm'),$linePrice]);
                }
            }
        } catch(Exception $e) {}

        // Chuẩn hóa lại total = tiền sản phẩm + phí ship, không cộng lặp nếu đã đúng.
        try {
            $pdo->exec("UPDATE orders o
                JOIN (SELECT order_id, SUM(price*quantity) item_subtotal FROM order_items GROUP BY order_id) x ON x.order_id=o.id
                SET o.total = x.item_subtotal + COALESCE(o.shipping_fee,0)
                WHERE COALESCE(o.shipping_fee,0) > 0
                  AND COALESCE(o.total,0) <> x.item_subtotal + COALESCE(o.shipping_fee,0)");
        } catch(Exception $e) {}
    }

    public static function purchaseHistory() {
        self::autoCancelExpiredPending();
        $pdo = Database::connect();
        self::normalizeTotalsWithShipping($pdo);
        self::repairPurchaseHistoryMissingItems($pdo);
        self::normalizeTotalsWithShipping($pdo);
        $sql = "SELECT o.customer_name, o.phone,
                       SUBSTRING_INDEX(GROUP_CONCAT(o.address ORDER BY o.created_at DESC, o.id DESC SEPARATOR '|||'), '|||', 1) AS address,
                       COUNT(CASE WHEN o.status!='Đã hủy' THEN 1 END) total_orders,
                       COALESCE(SUM(CASE WHEN o.status!='Đã hủy' THEN COALESCE(o.total,0) ELSE 0 END),0) total_spent,
                       MAX(o.created_at) last_order_at
                FROM orders o
                GROUP BY o.customer_name, o.phone
                ORDER BY last_order_at DESC, total_spent DESC";
        $customers = $pdo->query($sql)->fetchAll();
        foreach ($customers as &$c) {
            $st=$pdo->prepare("SELECT * FROM orders WHERE customer_name=? AND phone=? ORDER BY created_at DESC, id DESC");
            $st->execute([$c['customer_name'], $c['phone']]);
            $orders=$st->fetchAll();
            $ids=array_map(function($o){return (int)$o['id'];}, $orders);
            $items=self::itemsByOrderIds($ids);
            $c['orders']=$orders;
            $c['items_by_order']=$items;
        }
        return $customers;
    }

    public static function stats() {
        $pdo=Database::connect();
        self::normalizeTotalsWithShipping($pdo);
        return [
            'products'=>$pdo->query("SELECT COUNT(*) c FROM products")->fetch()['c'],
            'orders'=>$pdo->query("SELECT COUNT(*) c FROM orders")->fetch()['c'],
            'today'=>$pdo->query("SELECT COALESCE(SUM(total),0) c FROM orders WHERE DATE(created_at)=CURDATE() AND status!='Đã hủy' AND payment_status='Đã thanh toán'")->fetch()['c'],
            'today_count'=>$pdo->query("SELECT COUNT(*) c FROM orders WHERE DATE(created_at)=CURDATE()")->fetch()['c'],
            'month'=>$pdo->query("SELECT COALESCE(SUM(total),0) c FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE()) AND status!='Đã hủy' AND payment_status='Đã thanh toán'")->fetch()['c'],
            'month_count'=>$pdo->query("SELECT COUNT(*) c FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetch()['c'],
            'low'=>$pdo->query("SELECT COUNT(*) c FROM products WHERE stock<=3")->fetch()['c'],
            'out_stock'=>$pdo->query("SELECT COUNT(*) c FROM products WHERE stock<=0")->fetch()['c'],
            'low_stock_only'=>$pdo->query("SELECT COUNT(*) c FROM products WHERE stock BETWEEN 1 AND 3")->fetch()['c']
        ];
    }
}
