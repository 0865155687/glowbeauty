<?php
require_once __DIR__ . '/../core/Database.php';

class Product  {
    public static function all($q='', $cat='')  {
        $pdo = Database::connect();
        $sql = "SELECT * FROM products WHERE status=1";
        $params=[];
        if ($q !== '')  {
            $sql .= " AND (name LIKE ? OR description LIKE ? OR brand LIKE ? OR product_code LIKE ? OR category LIKE ? OR benefit LIKE ? OR usage_text LIKE ?)";
            $params = array_merge($params,["%$q%","%$q%","%$q%","%$q%","%$q%","%$q%","%$q%"]);
        }
        if ($cat !== '')  {
            $sql .= " AND category=?";
            $params[]=$cat;
        }
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $st=$pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
    public static function featured($limit=8)  {
        $pdo=Database::connect();
        $st=$pdo->prepare("SELECT * FROM products WHERE status=1 ORDER BY sort_order ASC, id ASC LIMIT ?");
        $st->bindValue(1,$limit,PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
    public static function find($id)  {
        $st=Database::connect()->prepare("SELECT * FROM products WHERE id=?");
        $st->execute([$id]);
        return $st->fetch();
    }

    public static function findMany($ids) {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array)$ids))));
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $st = Database::connect()->prepare("SELECT * FROM products WHERE status=1 AND id IN ($placeholders)");
        $st->execute($ids);
        $rows = $st->fetchAll();
        $byId = [];
        foreach ($rows as $row) { $byId[(int)$row['id']] = $row; }
        $ordered = [];
        foreach ($ids as $id) { if (isset($byId[$id])) $ordered[] = $byId[$id]; }
        return $ordered;
    }

    public static function lowStock($limit=50) {
        $st=Database::connect()->prepare("SELECT * FROM products WHERE stock<=3 ORDER BY stock ASC, id ASC LIMIT ?");
        $st->bindValue(1,(int)$limit,PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
    public static function categories() {
        return ['Skincare','Sữa rửa mặt','Toner & Essence','Serum','Kem dưỡng','Chống nắng','Kem nền','Che khuyết điểm','Son môi','Phấn má','Phấn mắt','Mascara','Combo'];
    }
    public static function save($d)  {
        $pdo=Database::connect();
        if (!empty($d['id']))  {
            $sql="UPDATE products SET product_code=?,name=?,brand=?,category=?,price=?,stock=?,image=?,description=?,benefit=?,ingredients=?,usage_text=?,status=? WHERE id=?";
            return $pdo->prepare($sql)->execute([$d['product_code'],$d['name'],$d['brand'],$d['category'],$d['price'],$d['stock'],$d['image'],$d['description'],$d['benefit'],$d['ingredients'],$d['usage_text'],$d['status'],$d['id']]);
        }
        $sql="INSERT INTO products(product_code,name,brand,category,price,stock,image,description,benefit,ingredients,usage_text,status,sort_order) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,99)";
        return $pdo->prepare($sql)->execute([$d['product_code'],$d['name'],$d['brand'],$d['category'],$d['price'],$d['stock'],$d['image'],$d['description'],$d['benefit'],$d['ingredients'],$d['usage_text'],$d['status']]);
    }
    public static function delete($id) {
        return Database::connect()->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    }
}
