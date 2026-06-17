<?php
require_once __DIR__ . '/../core/Database.php';

class Wishlist {
    public static function allByUser($userId) {
        $st = Database::connect()->prepare("SELECT p.*, w.id wishlist_id, w.created_at saved_at FROM wishlists w JOIN products p ON p.id=w.product_id WHERE w.user_id=? ORDER BY w.created_at DESC, w.id DESC");
        $st->execute([(int)$userId]);
        return $st->fetchAll();
    }
    public static function add($userId, $productId) {
        $pdo = Database::connect();
        $check = $pdo->prepare("SELECT stock,status FROM products WHERE id=? LIMIT 1");
        $check->execute([(int)$productId]);
        $p = $check->fetch();
        if (!$p || (int)$p['stock'] <= 0 || (int)$p['status'] !== 1) {
            return false;
        }
        $st = $pdo->prepare("INSERT IGNORE INTO wishlists(user_id, product_id, created_at) VALUES(?,?,NOW())");
        return $st->execute([(int)$userId,(int)$productId]);
    }
    public static function remove($userId, $productId) {
        $st = Database::connect()->prepare("DELETE FROM wishlists WHERE user_id=? AND product_id=?");
        return $st->execute([(int)$userId,(int)$productId]);
    }
    public static function countByUser($userId) {
        $st=Database::connect()->prepare("SELECT COUNT(*) c FROM wishlists WHERE user_id=?");
        $st->execute([(int)$userId]);
        $r=$st->fetch(); return (int)($r['c']??0);
    }

    public static function exists($userId, $productId) {
        $st = Database::connect()->prepare("SELECT id FROM wishlists WHERE user_id=? AND product_id=? LIMIT 1");
        $st->execute([(int)$userId, (int)$productId]);
        return (bool)$st->fetch();
    }

    public static function idsByUser($userId) {
        $st = Database::connect()->prepare("SELECT product_id FROM wishlists WHERE user_id=?");
        $st->execute([(int)$userId]);
        return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
    }
}
