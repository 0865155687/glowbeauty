<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

// AwardSpace hay lỗi không giữ đăng nhập nếu session lưu vào thư mục mặc định.
// Đặt session về thư mục riêng của dự án để đăng nhập xong không bị mất $_SESSION.
$gbSessionDir = dirname(__DIR__) . '/storage/sessions';
if (!is_dir($gbSessionDir)) {
    @mkdir($gbSessionDir, 0755, true);
}
if (is_dir($gbSessionDir) && is_writable($gbSessionDir)) {
    ini_set('session.save_path', $gbSessionDir);
}

ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');

if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!defined('BASE_URL')) {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script, '/public/index.php') !== false) {
        $base = str_replace('/public/index.php', '', $script);
    } elseif (strpos($script, '/index.php') !== false) {
        $base = str_replace('/index.php', '', $script);
    } else {
        $base = dirname($script);
    }
    $base = '/' . trim($base, '/') . '/';
    $base = str_replace('//', '/', $base);
    define('BASE_URL', $base);
}
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Router.php';
function excerpt($text, $len=95) {
    return function_exists('mb_strimwidth') ? mb_strimwidth($text,0,$len,'...','UTF-8') : (strlen($text)>$len?substr($text,0,$len).'...':$text);
}
(new Router())->dispatch();
