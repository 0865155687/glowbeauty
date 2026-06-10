<?php
require_once __DIR__ . '/../models/ContactRequest.php';

class PageController extends Controller  {

    public function about() {
        $this->view('pages/about',['title'=>'Giới thiệu']);
    }

    public function contactThankYou() {
        $message = $_SESSION['contact_success'] ?? 'GlowBeauty đã nhận thông tin tư vấn và sẽ phản hồi cho bạn sớm nhất.';
        unset($_SESSION['contact_success']);
        $this->view('pages/contact_thank_you',['title'=>'Cảm ơn liên hệ','message'=>$message]);
    }

    public function contact() {
        $success=null;
        $error=null;
        if($_SERVER['REQUEST_METHOD']==='POST') {
            try {
                ContactRequest::create($_POST);
                $_SESSION['contact_success']='GlowBeauty đã nhận thông tin tư vấn và sẽ phản hồi cho bạn sớm nhất.';
                $this->redirect('contact-thank-you');
            }
            catch(Exception $e) {
                $error=$e->getMessage();
            }
        }
        $this->view('pages/contact',['title'=>'Liên hệ','success'=>$success,'error'=>$error]);
    }
}
