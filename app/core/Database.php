<?php

class Database  {
    private static $pdo = null;
    public static function connect()  {
        if (self::$pdo === null)  {
            $config = require __DIR__ . '/../../config/database.php';
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            try {
                self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
                // Đồng bộ giờ Việt Nam cho mọi lệnh NOW(), CURDATE(), TIMESTAMP trên hosting.
                // Nếu không set, host có thể lưu theo UTC khiến giờ đặt hàng bị lệch.
                try { self::$pdo->exec("SET time_zone = '+07:00'"); } catch (Exception $tzError) {}
                try { self::$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"); } catch (Exception $charsetError) {}
            } catch (PDOException $e) {
                // Prevent fatal crashes and show a clean, branded error page
                http_response_code(503);
                ?>
                <!DOCTYPE html>
                <html lang="vi">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>GlowBeauty - Máy chủ bận</title>
                    <style>
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            background-color: #fffaf7;
                            color: #5a2c1e;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            height: 100vh;
                            margin: 0;
                            text-align: center;
                        }
                        .error-card {
                            background: white;
                            padding: 40px;
                            border-radius: 24px;
                            box-shadow: 0 15px 45px rgba(125, 77, 51, 0.1);
                            border: 1px solid #efdccf;
                            max-width: 500px;
                            margin: 20px;
                        }
                        h1 { color: #8b4528; margin-top: 0; font-size: 28px; }
                        p { font-size: 16px; line-height: 1.6; color: #8a6758; }
                        .btn {
                            display: inline-block;
                            margin-top: 20px;
                            padding: 12px 24px;
                            background: linear-gradient(135deg, #d85a1c, #a63a0e);
                            color: white;
                            text-decoration: none;
                            border-radius: 12px;
                            font-weight: 600;
                            box-shadow: 0 8px 20px rgba(194, 73, 23, 0.2);
                        }
                    </style>
                </head>
                <body>
                    <div class="error-card">
                        <h1>🌸 GlowBeauty - Máy chủ bận</h1>
                        <p>Hệ thống hiện đang vượt quá tài nguyên kết nối cơ sở dữ liệu cho phép (lỗi quá tải kết nối máy chủ của hosting). Vui lòng tải lại trang sau ít phút hoặc quay lại sau.</p>
                        <a href="javascript:location.reload()" class="btn">Tải lại trang</a>
                    </div>
                </body>
                </html>
                <?php
                exit();
            }
        }
        return self::$pdo;
    }
}
