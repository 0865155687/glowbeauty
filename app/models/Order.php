<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/Product.php';

class Order  {
    public static $statuses = ['Chờ xác nhận','Đã xác nhận','Đang giao','Hoàn thành','Đã hủy'];
    public static $paymentStatuses = ['Chưa thanh toán','Đã thanh toán'];


    private static function fixInvalidPaymentStatuses($pdo) {
        try {
            $pdo->exec("UPDATE orders SET payment_status='Chưa thanh toán' WHERE payment_status IS NULL OR payment_status='' OR payment_status NOT IN ('Chưa thanh toán','Đã thanh toán')");
        } catch(Exception $e) {
            // Bỏ qua để không làm gián đoạn trang nếu CSDL cũ chưa có cột payment_status.
        }
    }

    private static function paymentCode($orderId) {
        return 'GBPAY' . date('ymd') . str_pad((string)$orderId, 5, '0', STR_PAD_LEFT);
    }

    public static function create($customer,$phone,$address,$note,$cart) {
        $customer=Validator::validateName($customer, 'Họ và tên khách hàng');
        $phone=Validator::validatePhone($phone);
        $address=trim((string)$address);
        if($address==='') throw new Exception('Địa chỉ nhận hàng không được để trống.');
        $pdo=Database::connect();
        $pdo->beginTransaction();
        try {
            $total=0;
            foreach($cart as $id=>$qty) {
                $id=(int)$id;
                $qty=(int)$qty;
                if($qty<=0) continue;
                $p=Product::find($id);
                if(!$p || $p['stock'] < $qty) throw new Exception('Sản phẩm không đủ tồn kho');
                $total += $p['price']*$qty;
            }
            if($total<=0) throw new Exception('Đơn hàng chưa có sản phẩm');

            $userId = $_SESSION['user']['id'] ?? null;
            try {
                $sql = "INSERT INTO orders(user_id,customer_name,phone,address,note,total,status,payment_status,payment_code,created_at)
                        VALUES(?,?,?,?,?,?,'Chờ xác nhận','Chưa thanh toán','',NOW())";
                $st=$pdo->prepare($sql);
                $st->execute([$userId,$customer,$phone,$address,$note,$total]);
            } catch(Exception $e) {
                try {
                    $sql = "INSERT INTO orders(customer_name,phone,address,note,total,status,payment_status,payment_code,created_at)
                            VALUES(?,?,?,?,?,'Chờ xác nhận','Chưa thanh toán','',NOW())";
                    $st=$pdo->prepare($sql);
                    $st->execute([$customer,$phone,$address,$note,$total]);
                } catch(Exception $e2) {
                    $sql = "INSERT INTO orders(customer_name,phone,address,total,status,payment_status,payment_code,created_at)
                            VALUES(?,?,?,?,'Chờ xác nhận','Chưa thanh toán','',NOW())";
                    $st=$pdo->prepare($sql);
                    $st->execute([$customer,$phone,$address,$total]);
                }
            }
            $orderId=$pdo->lastInsertId();

            $pdo->prepare("UPDATE orders SET payment_code=? WHERE id=?")->execute([self::paymentCode($orderId), $orderId]);

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


    public static function latestId() {
        try {
            $row = Database::connect()->query("SELECT COALESCE(MAX(id),0) latest_id FROM orders")->fetch();
            return (int)($row['latest_id'] ?? 0);
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
        $pdo=Database::connect();
        self::fixInvalidPaymentStatuses($pdo);
        return $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
    }

    public static function recent($limit=6) {
        $pdo=Database::connect();
        self::fixInvalidPaymentStatuses($pdo);
        $st=$pdo->prepare("SELECT * FROM orders ORDER BY id DESC LIMIT ?");
        $st->bindValue(1,$limit,PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function todayOrders() {
        return Database::connect()->query("SELECT * FROM orders WHERE DATE(created_at)=CURDATE() AND status!='Đã hủy' AND payment_status='Đã thanh toán' ORDER BY id DESC")->fetchAll();
    }

    public static function monthOrders($month=null,$year=null) {
        $pdo=Database::connect();
        $month=$month ?: date('m');
        $year=$year ?: date('Y');
        $st=$pdo->prepare("SELECT * FROM orders WHERE MONTH(created_at)=? AND YEAR(created_at)=? AND status!='Đã hủy' AND payment_status='Đã thanh toán' ORDER BY id DESC");
        $st->execute([(int)$month,(int)$year]);
        return $st->fetchAll();
    }

    public static function yearOrders($year=null) {
        $year=$year ?: date('Y');
        $st=Database::connect()->prepare("SELECT * FROM orders WHERE YEAR(created_at)=? AND status!='Đã hủy' AND payment_status='Đã thanh toán' ORDER BY created_at DESC, id DESC");
        $st->execute([(int)$year]);
        return $st->fetchAll();
    }

    public static function availableMonths() {
        $pdo=Database::connect();
        $rows=$pdo->query("SELECT YEAR(created_at) y, MONTH(created_at) m, COUNT(*) c FROM orders WHERE status!='Đã hủy' AND payment_status='Đã thanh toán' GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY y DESC, m DESC")->fetchAll();
        if(empty($rows)) $rows=[['y'=>date('Y'),'m'=>date('n'),'c'=>0]];
        return $rows;
    }

    public static function monthlyChart($year=null) {
        $pdo=Database::connect();
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
        $old=self::find($id);
        if(!$old) return false;
        $list=self::$statuses;
        if($status==='Đã hủy') return $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status,$id]);
        if(array_search($status,$list) < array_search($old['status'],$list)) return false;
        return $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status,$id]);
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
        $st=Database::connect()->prepare("SELECT * FROM order_items WHERE order_id IN ($placeholders) ORDER BY order_id ASC, id ASC");
        $st->execute($orderIds);
        $grouped=[];
        foreach($st->fetchAll() as $row) {
            $grouped[(int)$row['order_id']][]=$row;
        }
        return $grouped;
    }

    public static function byUser($userId) {
        $pdo = Database::connect();
        try {
            $st=$pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC, id DESC");
            $st->execute([(int)$userId]);
            return $st->fetchAll();
        } catch(Exception $e) {
            return [];
        }
    }

    public static function bestSellers($limit=10) {
        $pdo=Database::connect();
        $st=$pdo->prepare("SELECT p.id, p.name, p.category, p.image, p.price, p.stock, COALESCE(SUM(oi.quantity),0) sold_qty, COALESCE(SUM(oi.quantity*oi.price),0) revenue FROM products p LEFT JOIN order_items oi ON oi.product_id=p.id LEFT JOIN orders o ON o.id=oi.order_id AND o.status!='Đã hủy' GROUP BY p.id ORDER BY sold_qty DESC, revenue DESC, p.id ASC LIMIT ?");
        $st->bindValue(1,(int)$limit,PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function stats() {
        $pdo=Database::connect();
        return [
            'products'=>$pdo->query("SELECT COUNT(*) c FROM products")->fetch()['c'],
            'orders'=>$pdo->query("SELECT COUNT(*) c FROM orders")->fetch()['c'],
            'today'=>$pdo->query("SELECT COALESCE(SUM(total),0) c FROM orders WHERE DATE(created_at)=CURDATE() AND status!='Đã hủy' AND payment_status='Đã thanh toán'")->fetch()['c'],
            'today_count'=>$pdo->query("SELECT COUNT(*) c FROM orders WHERE DATE(created_at)=CURDATE()")->fetch()['c'],
            'month'=>$pdo->query("SELECT COALESCE(SUM(total),0) c FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE()) AND status!='Đã hủy' AND payment_status='Đã thanh toán'")->fetch()['c'],
            'month_count'=>$pdo->query("SELECT COUNT(*) c FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetch()['c'],
            'low'=>$pdo->query("SELECT COUNT(*) c FROM products WHERE stock<=3")->fetch()['c']
        ];
    }
}
