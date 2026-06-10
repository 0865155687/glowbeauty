<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Validator.php';

class User  {
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
        return Database::connect()->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?, 'customer')")->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);
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
        $st=$db->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,'customer')");
        return $st->execute([$name,$email,password_hash($password ?: '123456',PASSWORD_DEFAULT)]);
    }
    public static function deleteCustomer($id) {
        $pdo=Database::connect();
        $pdo->prepare("DELETE FROM users WHERE id=? AND role='customer'")->execute([(int)$id]);
        self::reindexCustomers($pdo);
        return true;
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
