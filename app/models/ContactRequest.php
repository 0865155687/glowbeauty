<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Validator.php';

class ContactRequest  {
    public static $statuses = ['Mới gửi','Đang tư vấn','Đã tư vấn','Huỷ'];
    public static function create($data) {
        $name=trim($data['customer_name'] ?? '');
        $phone=trim($data['phone'] ?? '');
        $need=trim($data['need'] ?? '');
        $message=trim($data['message'] ?? '');
        $name=Validator::validateName($name);
        $phone=Validator::validatePhone($phone);
        if($need==='') throw new Exception('Vui lòng chọn nhu cầu tư vấn.');
        $pdo=Database::connect();
        self::ensureTable($pdo);
        $st=$pdo->prepare("INSERT INTO contact_requests(customer_name,phone,need,message,status,created_at) VALUES(?,?,?,?, 'Mới gửi', NOW())");
        $st->execute([$name,$phone,$need,$message]);
        return $pdo->lastInsertId();
    }
    private static function ensureTable($pdo) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS contact_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(120) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            need VARCHAR(120) NOT NULL,
            message TEXT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'Mới gửi',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    public static function all() {
        $pdo=Database::connect();
        self::ensureTable($pdo);
        return $pdo->query("SELECT * FROM contact_requests ORDER BY id DESC")->fetchAll();
    }
    public static function recent($limit=5) {
        $pdo=Database::connect();
        self::ensureTable($pdo);
        $st=$pdo->prepare("SELECT * FROM contact_requests ORDER BY id DESC LIMIT ?");
        $st->bindValue(1,(int)$limit,PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
    public static function countNew() {
        $pdo=Database::connect();
        self::ensureTable($pdo);
        return $pdo->query("SELECT COUNT(*) c FROM contact_requests WHERE status='Mới gửi'")->fetch()['c'];
    }

    public static function latestId() {
        $pdo=Database::connect();
        self::ensureTable($pdo);
        try {
            $row=$pdo->query("SELECT COALESCE(MAX(id),0) latest_id FROM contact_requests")->fetch();
            return (int)($row['latest_id'] ?? 0);
        } catch(Exception $e) {
            return 0;
        }
    }

    public static function find($id) {
        $pdo=Database::connect();
        self::ensureTable($pdo);
        $st=$pdo->prepare("SELECT * FROM contact_requests WHERE id=?");
        $st->execute([(int)$id]);
        return $st->fetch();
    }
    public static function updateStatus($id,$status) {
        if(!in_array($status,self::$statuses,true)) return false;
        $pdo=Database::connect();
        self::ensureTable($pdo);
        return $pdo->prepare("UPDATE contact_requests SET status=? WHERE id=?")->execute([$status,(int)$id]);
    }
    public static function delete($id) {
        $pdo=Database::connect();
        self::ensureTable($pdo);
        return $pdo->prepare("DELETE FROM contact_requests WHERE id=?")->execute([(int)$id]);
    }
}
