<?php

class Controller  {
    protected function view($view, $data = [])  {
        extract($data);
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
    protected function adminView($view, $data = [])  {
        extract($data);
        require __DIR__ . '/../views/layouts/admin_header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/admin_footer.php';
    }
    protected function redirect($url)  {
        $target = BASE_URL . $url;
        $scrollY = $_POST['_scroll_y'] ?? $_GET['_scroll_y'] ?? null;
        if ($scrollY !== null && is_numeric($scrollY)) {
            $sep = (strpos($target, '?') === false) ? '?' : '&';
            $target .= $sep . '_scroll_y=' . urlencode((string)(int)$scrollY);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header('Location: ' . $target);
        exit;
    }
}
