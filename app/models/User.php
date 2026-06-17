<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Validator.php';

class User  {

    private static function ensureAdminSeenColumn() {
        try {
            $pdo = Database::connect();
            $check = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'admin_seen'");
            $check->execute();
            if (!$check->fetch()) $pdo->exec("ALTER TABLE users ADD admin_seen TINYINT NOT NULL DEFAULT 1");
        } catch(Exception $e) {}
    }


    public static function findByEmail($email) {
        $st=Database::connect()->prepare("SELECT * FROM users WHERE email=?");
        $st->execute([$email]);
        return $st->fetch();
    }
    public static function create($name,$email,$pass) {
        $name=Validator::validateName($name);
        $email=trim((string)$email);
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new Exception('Email không hợp lệ.');
        if(trim((string)$pass)==='') throw new Exception('Mật khẩu không được để trống.');
        $pdo=Database::connect(); self::ensureAdminSeenColumn(); return $pdo->prepare("INSERT INTO users(name,email,password,role,admin_seen) VALUES(?,?,?,'customer',0)")->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);
    }
    public static function allCustomers() {
        $st=Database::connect()->query("SELECT * FROM users WHERE role='customer' ORDER BY id DESC");
        return $st->fetchAll();
    }
    public static function find($id) {
        $st=Database::connect()->prepare("SELECT * FROM users WHERE id=?");
        $st->execute([$id]);
        return $st->fetch();
    }
    public static function saveCustomer($data) {
        $db=Database::connect();
        $name=Validator::validateName($data['name']??'');
        $email=trim($data['email']??'');
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new Exception('Email không hợp lệ.');
        $password=trim($data['password']??'');
        if(!empty($data['id'])) {
            if($password!=='') {
                $st=$db->prepare("UPDATE users SET name=?, email=?, password=?, role='customer' WHERE id=? AND role='customer'");
                return $st->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),(int)$data['id']]);
            }
            $st=$db->prepare("UPDATE users SET name=?, email=?, role='customer' WHERE id=? AND role='customer'");
            return $st->execute([$name,$email,(int)$data['id']]);
        }
        self::ensureAdminSeenColumn();
        $st=$db->prepare("INSERT INTO users(name,email,password,role,admin_seen) VALUES(?,?,?,'customer',0)");
        return $st->execute([$name,$email,password_hash($password ?: '123456',PASSWORD_DEFAULT)]);
    }
    public static function deleteCustomer($id) {
        $pdo=Database::connect();
        $pdo->prepare("DELETE FROM users WHERE id=? AND role='customer'")->execute([(int)$id]);
        self::reindexCustomers($pdo);
        return true;
    }


    public static function latestCustomerId() {
        try {
            $row = Database::connect()->query("SELECT COALESCE(MAX(id),0) latest_id FROM users WHERE role='customer'")->fetch();
            return (int)($row['latest_id'] ?? 0);
        } catch(Exception $e) {
            return 0;
        }
    }

    public static function countCustomersAfterId($id) {
        try {
            $st = Database::connect()->prepare("SELECT COUNT(*) c FROM users WHERE role='customer' AND id > ?");
            $st->execute([(int)$id]);
            $row = $st->fetch();
            return (int)($row['c'] ?? 0);
        } catch(Exception $e) {
            return 0;
        }
    }


    public static function countAdminUnseen() {
        self::ensureAdminSeenColumn();
        try {
            $row = Database::connect()->query("SELECT COUNT(*) c FROM users WHERE role='customer' AND admin_seen=0")->fetch();
            return (int)($row['c'] ?? 0);
        } catch(Exception $e) {
            return 0;
        }
    }

    public static function markAdminSeen() {
        self::ensureAdminSeenColumn();
        try {
            Database::connect()->exec("UPDATE users SET admin_seen=1 WHERE role='customer' AND admin_seen=0");
        } catch(Exception $e) {}
    }

    private static function reindexCustomers($pdo) {
        $customers=$pdo->query("SELECT id FROM users WHERE role='customer' ORDER BY id ASC")->fetchAll();
        $map=[];
        $next=2;

        foreach($customers as $row) {
            $oldId=(int)$row['id'];
            if($oldId!==$next) {
                $map[$oldId]=$next;
            }
            $next++;
        }

        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

            foreach($map as $oldId=>$newId) {
                $tmpId=0-$newId;
                $pdo->prepare("UPDATE users SET id=? WHERE id=? AND role='customer'")->execute([$tmpId,$oldId]);
            }

            foreach($map as $newId) {
                $tmpId=0-$newId;
                $pdo->prepare("UPDATE users SET id=? WHERE id=? AND role='customer'")->execute([$newId,$tmpId]);
            }

            $max=(int)$pdo->query("SELECT COALESCE(MAX(id),0) FROM users")->fetchColumn();
            $pdo->exec("ALTER TABLE users AUTO_INCREMENT=".($max+1));
        }
        finally {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        }
    }
}
