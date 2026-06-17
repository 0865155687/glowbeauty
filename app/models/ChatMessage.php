<?php
require_once __DIR__ . '/../core/Database.php';

class ChatMessage {
    public static function ensureTable() {
        try {
            Database::connect()->exec("CREATE TABLE IF NOT EXISTS chat_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                guest_key VARCHAR(80) NULL,
                customer_name VARCHAR(150) NULL,
                message TEXT NOT NULL,
                sender ENUM('customer','ai','admin') NOT NULL DEFAULT 'customer',
                is_read TINYINT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_chat_user (user_id),
                INDEX idx_chat_guest (guest_key),
                INDEX idx_chat_time (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch(Exception $e) {}
    }

    public static function guestKey() {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        if (empty($_SESSION['chat_guest_key'])) $_SESSION['chat_guest_key'] = 'guest_'.bin2hex(random_bytes(8));
        return $_SESSION['chat_guest_key'];
    }

    private static function currentUserName() {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        if (!empty($_SESSION['user']['name'])) return trim((string)$_SESSION['user']['name']);
        if (!empty($_SESSION['user']['email'])) return trim((string)$_SESSION['user']['email']);
        return 'Khách chưa đăng nhập';
    }

    public static function add($message,$sender='customer',$userId=null,$guestKey=null,$customerName='') {
        self::ensureTable();
        $message = trim((string)$message);
        if ($message === '') return false;

        if ($userId === null && !empty($_SESSION['user']['id'])) $userId = (int)$_SESSION['user']['id'];
        if (!$userId && !$guestKey) $guestKey = self::guestKey();

        if ($sender === 'admin') $name = 'Admin GlowBeauty';
        elseif ($sender === 'ai') $name = 'AI GlowBeauty';
        else $name = trim((string)$customerName) ?: self::currentUserName();

        $st = Database::connect()->prepare("INSERT INTO chat_messages(user_id,guest_key,customer_name,message,sender,is_read,created_at) VALUES(?,?,?,?,?,?,NOW())");
        return $st->execute([$userId ?: null, $guestKey ?: null, $name, $message, $sender, $sender==='customer'?0:1]);
    }

    public static function conversations() {
        self::ensureTable();
        $sql = "SELECT 
                    COALESCE(CAST(cm.user_id AS CHAR), cm.guest_key) AS conv_key,
                    MAX(cm.id) latest_id,
                    MAX(cm.created_at) latest_time,
                    COALESCE(NULLIF(MAX(u.name),''), NULLIF(MAX(cm.customer_name),''), 'Khách chưa đăng nhập') AS customer_name,
                    MAX(u.email) AS customer_email,
                    cm.user_id,
                    cm.guest_key,
                    SUM(CASE WHEN cm.sender='customer' AND cm.is_read=0 THEN 1 ELSE 0 END) unread,
                    SUBSTRING_INDEX(GROUP_CONCAT(cm.message ORDER BY cm.id DESC SEPARATOR '|||'), '|||', 1) AS last_message,
                    SUBSTRING_INDEX(GROUP_CONCAT(cm.sender ORDER BY cm.id DESC SEPARATOR '|||'), '|||', 1) AS last_sender
                FROM chat_messages cm
                LEFT JOIN users u ON u.id = cm.user_id
                WHERE (cm.user_id IS NULL OR COALESCE(u.role,'customer') <> 'admin')
                GROUP BY COALESCE(CAST(cm.user_id AS CHAR), cm.guest_key), cm.user_id, cm.guest_key
                ORDER BY latest_time DESC";
        return Database::connect()->query($sql)->fetchAll();
    }

    public static function conversationInfo($convKey) {
        self::ensureTable();
        $pdo = Database::connect();
        if (ctype_digit((string)$convKey)) {
            $st = $pdo->prepare("SELECT 
                    COALESCE(NULLIF(u.name,''), NULLIF(MAX(cm.customer_name),''), 'Khách hàng') AS customer_name,
                    u.email AS customer_email,
                    cm.user_id,
                    NULL AS guest_key
                FROM chat_messages cm
                LEFT JOIN users u ON u.id = cm.user_id
                WHERE cm.user_id=?
                GROUP BY cm.user_id, u.name, u.email
                LIMIT 1");
            $st->execute([(int)$convKey]);
        } else {
            $st = $pdo->prepare("SELECT 
                    COALESCE(NULLIF(MAX(customer_name),''), 'Khách chưa đăng nhập') AS customer_name,
                    NULL AS customer_email,
                    NULL AS user_id,
                    guest_key
                FROM chat_messages
                WHERE guest_key=?
                GROUP BY guest_key
                LIMIT 1");
            $st->execute([$convKey]);
        }
        return $st->fetch() ?: ['customer_name'=>'Khách hàng','customer_email'=>'','user_id'=>null,'guest_key'=>$convKey];
    }

    public static function messages($convKey) {
        self::ensureTable();
        $pdo=Database::connect();
        if (ctype_digit((string)$convKey)) {
            $st=$pdo->prepare("SELECT cm.*, COALESCE(NULLIF(u.name,''), cm.customer_name, 'Khách hàng') AS display_name
                               FROM chat_messages cm
                               LEFT JOIN users u ON u.id=cm.user_id
                               WHERE cm.user_id=? ORDER BY cm.id ASC");
            $st->execute([(int)$convKey]);
            $pdo->prepare("UPDATE chat_messages SET is_read=1 WHERE user_id=? AND sender='customer'")->execute([(int)$convKey]);
        } else {
            $st=$pdo->prepare("SELECT *, COALESCE(NULLIF(customer_name,''), 'Khách chưa đăng nhập') AS display_name
                               FROM chat_messages WHERE guest_key=? ORDER BY id ASC");
            $st->execute([$convKey]);
            $pdo->prepare("UPDATE chat_messages SET is_read=1 WHERE guest_key=? AND sender='customer'")->execute([$convKey]);
        }
        return $st->fetchAll();
    }


    public static function unreadTotal() {
        self::ensureTable();
        try {
            $sql = "SELECT COUNT(*) FROM chat_messages cm
                    LEFT JOIN users u ON u.id = cm.user_id
                    WHERE cm.sender='customer'
                      AND cm.is_read=0
                      AND (cm.user_id IS NULL OR COALESCE(u.role,'customer') <> 'admin')";
            return (int)Database::connect()->query($sql)->fetchColumn();
        } catch(Exception $e) {
            return 0;
        }
    }


    public static function deleteConversation($convKey) {
        self::ensureTable();
        $convKey = trim((string)$convKey);
        if ($convKey === '') return false;
        $pdo = Database::connect();
        if (ctype_digit($convKey)) {
            $st = $pdo->prepare("DELETE FROM chat_messages WHERE user_id=?");
            return $st->execute([(int)$convKey]);
        }
        $st = $pdo->prepare("DELETE FROM chat_messages WHERE guest_key=?");
        return $st->execute([$convKey]);
    }

    public static function adminReply($convKey,$message) {
        if (ctype_digit((string)$convKey)) return self::add($message,'admin',(int)$convKey,null,'Admin GlowBeauty');
        return self::add($message,'admin',null,$convKey,'Admin GlowBeauty');
    }
}

