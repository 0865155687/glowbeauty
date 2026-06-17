<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Wishlist.php';

class AuthController extends Controller  {

    public function login() {
        if($_SERVER['REQUEST_METHOD']==='POST') {
            $u=User::findByEmail($_POST['email']);
            if($u && password_verify($_POST['password'],$u['password'])) {
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }
                $role = strtolower(trim($u['role'] ?? 'customer'));
                $_SESSION['user'] = [
                    'id' => (int)($u['id'] ?? 0),
                    'name' => $u['name'] ?? '',
                    'email' => $u['email'] ?? '',
                    'role' => $role,
                    'admin_seen' => $u['admin_seen'] ?? 0,
                    'created_at' => $u['created_at'] ?? null,
                ];
                if (!empty($_SESSION['guest_wishlist']) && is_array($_SESSION['guest_wishlist'])) {
                    foreach (array_unique(array_map('intval', $_SESSION['guest_wishlist'])) as $productId) {
                        if ($productId > 0) { Wishlist::add((int)$u['id'], $productId); }
                    }
                    unset($_SESSION['guest_wishlist']);
                }
                $redirect = $_SESSION['redirect_after_login'] ?? ($role === 'admin' ? 'admin/dashboard' : 'home');
                unset($_SESSION['redirect_after_login']);
                $this->redirect($redirect);
            }
            $error='Email hoặc mật khẩu không đúng.';
        }
        $this->view('auth/login',['error'=>$error??null,'title'=>'Đăng nhập']);
    }

    public function register() {
        if($_SERVER['REQUEST_METHOD']==='POST') {
            try {
                User::create($_POST['name'],$_POST['email'],$_POST['password']);
                $_SESSION['success']='Đăng ký thành công. Bạn hãy đăng nhập để đặt hàng.';
                $this->redirect('login');
            } catch(Exception $e) {
                $error=$e->getMessage();
            }
        }
        $this->view('auth/register',['title'=>'Đăng ký','error'=>$error??null]);
    }

    public function logout() {
        session_destroy();
        $this->redirect('home');
    }
}
