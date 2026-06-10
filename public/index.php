<?php
session_start();
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
