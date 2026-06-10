<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ContactRequest.php';

class AdminController extends Controller  {
    private function guard() {
        if(empty($_SESSION['user']) || $_SESSION['user']['role']!=='admin') $this->redirect('login');
    }

    public function dashboard() {
        $this->guard();
        $this->adminView('admin/dashboard',['stats'=>Order::stats(),'orders'=>Order::recent(6),'contacts'=>ContactRequest::recent(4),'contactNew'=>ContactRequest::countNew(),'title'=>'Dashboard']);
    }

    public function products() {
        $this->guard();
        $this->adminView('admin/products',['products'=>Product::all(),'title'=>'Quản lý sản phẩm']);
    }

    public function productForm() {
        $this->guard();
        $p=!empty($_GET['id'])?Product::find($_GET['id']):null;
        if($_SERVER['REQUEST_METHOD']==='POST') {
            Product::save($_POST);
            $this->redirect('admin/products');
        }
        $this->adminView('admin/product_form',['p'=>$p,'categories'=>Product::categories(),'title'=>$p?'Sửa sản phẩm':'Thêm sản phẩm']);
    }

    public function productDelete() {
        $this->guard();
        Product::delete($_GET['id']??0);
        $this->redirect('admin/products');
    }

    public function orders() {
        $this->guard();
        $this->adminView('admin/orders',['orders'=>Order::all(),'statuses'=>Order::$statuses,'paymentStatuses'=>Order::$paymentStatuses,'title'=>'Quản lý đơn hàng']);
    }

    public function orderDetail() {
        $this->guard();
        $order=Order::find($_GET['id']??0);
        if(!$order) {
            $this->redirect('admin/orders');
        }
        $this->adminView('admin/order_detail',['order'=>$order,'items'=>Order::items($order['id']),'statuses'=>Order::$statuses,'paymentStatuses'=>Order::$paymentStatuses,'title'=>'Chi tiết đơn hàng']);
    }

    public function orderUpdate() {
        $this->guard();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && isset($_POST['status']) && $_POST['status'] !== '') {
            Order::updateStatus($id, $_POST['status']);
        }
        if($id > 0 && isset($_POST['payment_status']) && $_POST['payment_status'] !== '') {
            Order::updatePaymentStatus($id, $_POST['payment_status']);
        }
        $this->redirect('admin/orders');
    }

    public function orderDelete() {
        $this->guard();
        Order::delete($_GET['id']??0);
        $this->redirect('admin/orders');
    }

    public function orderCreate() {
        $this->guard();
        $error=null;
        if($_SERVER['REQUEST_METHOD']==='POST') {
            try {
                $id=Order::createManual($_POST);
                $this->redirect('admin/order-detail?id='.$id);
            }
            catch(Exception $e) {
                $error=$e->getMessage();
            }
        }
        $this->adminView('admin/order_form',['products'=>Product::all(),'error'=>$error,'title'=>'Thêm đơn hàng']);
    }

    public function revenueToday() {
        $this->guard();
        $orders=Order::todayOrders();
        $this->adminView('admin/revenue_detail',['orders'=>$orders,'statuses'=>Order::$statuses,'heading'=>'Chi tiết doanh thu hôm nay','summary'=>'','total'=>Order::totalOf($orders),'title'=>'Đơn hàng hôm nay','mode'=>'today']);
    }

    public function revenueMonth() {
        $this->guard();
        $month=(int)($_GET['month'] ?? date('n'));
        $year=(int)($_GET['year'] ?? date('Y'));
        if($month<1 || $month>12) $month=(int)date('n');
        if($year<2000) $year=(int)date('Y');
        $orders=Order::monthOrders($month,$year);
        $this->adminView('admin/revenue_detail',[
        'orders'=>$orders,'statuses'=>Order::$statuses,
        'heading'=>'Chi tiết doanh thu tháng '.$month.'/'.$year,
        'summary'=>'', 'total'=>Order::totalOf($orders),'title'=>'Doanh thu tháng',
        'mode'=>'month','month'=>$month,'year'=>$year,'months'=>Order::availableMonths(),'chart'=>Order::monthlyChart($year)
        ]);
    }

    public function revenueAll() {
        $this->guard();
        $year=(int)($_GET['year'] ?? date('Y'));
        if($year<2000) $year=(int)date('Y');
        $orders=Order::yearOrders($year);
        $this->adminView('admin/revenue_detail',[
            'orders'=>$orders,
            'statuses'=>Order::$statuses,
            'heading'=>'Báo cáo doanh thu tất cả các tháng năm '.$year,
            'summary'=>'',
            'total'=>Order::totalOf($orders),
            'title'=>'Báo cáo tất cả các tháng',
            'mode'=>'all',
            'year'=>$year,
            'months'=>Order::availableMonths(),
            'chart'=>Order::monthlyChart($year)
        ]);
    }

    public function revenueExportExcel() {
        // Tắt thông báo Deprecated để tránh làm lỗi file CSV xuất ra
        error_reporting(E_ALL & ~E_DEPRECATED);
        // Xóa sạch bộ đệm đầu ra để đảm bảo không có khoảng trắng hay lỗi HTML bị chèn vào file
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $this->guard();
        $mode=$_GET['mode'] ?? 'month';
        $month=(int)($_GET['month'] ?? date('n'));
        $year=(int)($_GET['year'] ?? date('Y'));
        if($month<1 || $month>12) $month=(int)date('n');
        if($year<2000) $year=(int)date('Y');

        if($mode==='today') {
            $orders=Order::todayOrders();
            $filename='doanh_thu_hom_nay.csv';
        }
        elseif($mode==='all') {
            $orders=Order::yearOrders($year);
            $filename='bao_cao_doanh_thu_nam_'.$year.'.csv';
        }
        else {
            $orders=Order::monthOrders($month,$year);
            $filename='doanh_thu_thang_'.$month.'_'.$year.'.csv';
        }

        $itemsByOrder=Order::itemsByOrderIds(array_column($orders,'id'));

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out=fopen('php://output','w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, ['Mã đơn', 'Mã thanh toán', 'Khách hàng', 'Số điện thoại', 'Địa chỉ', 'Ghi chú', 'Sản phẩm', 'Tổng tiền (đ)', 'Trạng thái đơn', 'Trạng thái thanh toán', 'Ngày tạo'], ',', '"', "\\");
        foreach($orders as $o) {
            $items=[];
            foreach(($itemsByOrder[$o['id']] ?? []) as $it) {
                $code = $it['product_code'] ?? ('SP'.$it['product_id']);
                $items[]=$code.' - '.$it['product_name'].' x'.$it['quantity'].' = '.number_format($it['price']*$it['quantity'],0,',','.').'đ';
            }
            fputcsv($out,[
                '#'.$o['id'],
                $o['payment_code'] ?? '',
                $o['customer_name'],
                $o['phone'],
                $o['address'],
                $o['note'],
                implode(' | ',$items),
                (int)$o['total'],
                $o['status'],
                $o['payment_status'] ?? 'Chưa thanh toán',
                $o['created_at']
            ], ',', '"', "\\");
        }
        fputcsv($out, [], ',', '"', "\\");
        fputcsv($out, ['TỔNG DOANH THU', '', '', '', '', '', '', Order::totalOf($orders).'đ', '', '', ''], ',', '"', "\\");
        fclose($out);
        exit;
    }

    public function lowStock() {
        $this->guard();
        $this->adminView('admin/low_stock',['products'=>Product::lowStock(100),'title'=>'Sản phẩm sắp hết hàng']);
    }

    public function bestSellers() {
        $this->guard();
        $this->adminView('admin/best_sellers',['products'=>Order::bestSellers(20),'title'=>'Thống kê sản phẩm bán chạy']);
    }

    public function contacts() {
        $this->guard();
        $this->adminView('admin/contacts',['contacts'=>ContactRequest::all(),'statuses'=>ContactRequest::$statuses,'title'=>'Khách hàng tư vấn']);
    }

    public function contactUpdate() {
        $this->guard();
        ContactRequest::updateStatus($_POST['id'] ?? 0, $_POST['status'] ?? 'Mới gửi');
        $this->redirect('admin/contacts');
    }

    public function contactDelete() {
        $this->guard();
        ContactRequest::delete($_GET['id'] ?? 0);
        $this->redirect('admin/contacts');
    }

    public function customers() {
        $this->guard();
        $this->adminView('admin/customers',['customers'=>User::allCustomers(),'title'=>'Quản lý khách hàng']);
    }

    public function customerForm() {
        $this->guard();
        $c=!empty($_GET['id'])?User::find($_GET['id']):null;
        $error=null;
        if($_SERVER['REQUEST_METHOD']==='POST') {
            try {
                User::saveCustomer($_POST);
                $this->redirect('admin/customers');
            } catch(Exception $e) {
                $error=$e->getMessage();
                $c=$_POST;
            }
        }
        $this->adminView('admin/customer_form',['c'=>$c,'error'=>$error,'title'=>$c?'Sửa khách hàng':'Thêm khách hàng']);
    }

    public function customerDelete() {
        $this->guard();
        User::deleteCustomer($_GET['id']??0);
        $this->redirect('admin/customers');
    }


    public function notificationStatus() {
        $this->guard();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => true,
            'latest_order_id' => Order::latestId(),
            'pending_orders' => Order::countPending(),
            'latest_contact_id' => ContactRequest::latestId(),
            'new_contacts' => ContactRequest::countNew(),
            'time' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
    }

}
