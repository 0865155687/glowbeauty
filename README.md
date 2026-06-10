# GLOWBEAUTY - WEBSITE BÁN MỸ PHẨM

## 1. Giới thiệu dự án

GLOWBEAUTY là website bán mỹ phẩm được xây dựng bằng PHP thuần theo mô hình MVC, sử dụng cơ sở dữ liệu MySQL. Dự án có giao diện người dùng để xem sản phẩm, thêm giỏ hàng, đặt hàng và có trang quản trị để quản lý sản phẩm, đơn hàng, khách hàng, doanh thu.

## 2. Chức năng chính

### Chức năng người dùng

- Xem trang chủ website.
- Xem danh sách sản phẩm.
- Xem chi tiết sản phẩm.
- Thêm sản phẩm vào giỏ hàng.
- Cập nhật số lượng sản phẩm trong giỏ hàng.
- Xóa sản phẩm khỏi giỏ hàng.
- Thanh toán và gửi thông tin đặt hàng.
- Đăng ký tài khoản khách hàng.
- Đăng nhập, đăng xuất tài khoản.
- Xem sản phẩm yêu thích.
- Xem lịch sử đơn hàng.
- Gửi form liên hệ/tư vấn.

### Chức năng quản trị

- Đăng nhập trang quản trị.
- Xem dashboard tổng quan.
- Quản lý sản phẩm: thêm, sửa, xóa, cập nhật thông tin sản phẩm.
- Quản lý đơn hàng: xem danh sách, xem chi tiết, cập nhật trạng thái, xóa đơn hàng.
- Quản lý khách hàng.
- Quản lý yêu cầu tư vấn/liên hệ.
- Xem doanh thu hôm nay.
- Xem doanh thu theo tháng.
- Xem tổng doanh thu.
- Xuất báo cáo doanh thu Excel.
- Xem sản phẩm sắp hết hàng.
- Xem sản phẩm bán chạy.

## 3. Công nghệ sử dụng

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP/Apache để chạy local

## 4. Cách chạy dự án trên local

### Bước 1: Cài đặt XAMPP

Cài XAMPP và bật 2 dịch vụ:

- Apache
- MySQL

### Bước 2: Đưa dự án vào thư mục chạy web

Copy thư mục dự án `beauty_makeup_shop` vào thư mục:

```text
C:\xampp\htdocs\
```

Sau khi copy xong, đường dẫn dự án sẽ là:

```text
C:\xampp\htdocs\beauty_makeup_shop\
```

### Bước 3: Tạo cơ sở dữ liệu

Mở trình duyệt và truy cập:

```text
http://localhost/phpmyadmin
```

Sau đó thực hiện:

1. Tạo database tên:

```text
beauty_makeup_shop
```

2. Chọn database `beauty_makeup_shop`.
3. Vào tab **Import**.
4. Chọn file:

```text
database.sql
```

5. Bấm **Go** để import dữ liệu.

Nếu muốn dùng file dữ liệu đầy đủ hơn, có thể import file:

```text
beauty_makeup_shop (1).sql
```

### Bước 4: Kiểm tra cấu hình database

Mở file:

```text
config/database.php
```

Cấu hình mặc định khi chạy bằng XAMPP:

```php
'host' => '127.0.0.1',
'dbname' => 'beauty_makeup_shop',
'username' => 'root',
'password' => '',
'charset' => 'utf8mb4'
```

Nếu MySQL của máy có mật khẩu thì sửa lại phần `password` cho đúng.

### Bước 5: Chạy website

Mở trình duyệt và truy cập:

```text
http://localhost/beauty_makeup_shop/public/
```

Hoặc nếu cấu hình `.htaccess` hoạt động tốt, có thể truy cập:

```text
http://localhost/beauty_makeup_shop/
```

## 5. Tài khoản đăng nhập mẫu

### Tài khoản quản trị

```text
Email: admin@gmail.com
Mật khẩu: 123456
```

Đường dẫn trang quản trị:

```text
http://localhost/beauty_makeup_shop/public/admin
```

### Tài khoản khách hàng mẫu

Nếu import file dữ liệu có khách hàng mẫu, có thể dùng một trong các tài khoản sau:

```text
Email: maianh@example.com
Mật khẩu: 123456
```

```text
Email: baongoc@example.com
Mật khẩu: 123456
```

```text
Email: thuyduong@example.com
Mật khẩu: 123456
```

Người dùng cũng có thể tự tạo tài khoản mới tại trang đăng ký.

## 6. Một số đường dẫn chính

```text
Trang chủ: http://localhost/beauty_makeup_shop/public/
Danh sách sản phẩm: http://localhost/beauty_makeup_shop/public/products
Giỏ hàng: http://localhost/beauty_makeup_shop/public/cart
Đăng nhập: http://localhost/beauty_makeup_shop/public/login
Đăng ký: http://localhost/beauty_makeup_shop/public/register
Trang quản trị: http://localhost/beauty_makeup_shop/public/admin
```

## 7. Lưu ý khi chạy

- Cần bật Apache và MySQL trước khi chạy website.
- Tên database phải đúng là `beauty_makeup_shop`.
- Nếu website báo lỗi kết nối database, kiểm tra lại file `config/database.php`.
- Nếu ảnh sản phẩm không hiển thị, kiểm tra thư mục ảnh trong `public/assets/images/`.
- Nên chạy bằng đường dẫn `http://localhost/beauty_makeup_shop/public/` để tránh lỗi đường dẫn.

## 8. Cấu trúc thư mục chính

```text
beauty_makeup_shop/
│
├── app/
│   ├── controllers/     Chứa controller xử lý chức năng
│   ├── models/          Chứa model làm việc với database
│   ├── views/           Chứa giao diện hiển thị
│   └── core/            Chứa các file lõi như Router, Database
│
├── config/
│   └── database.php     File cấu hình kết nối MySQL
│
├── public/
│   ├── assets/          Chứa CSS, JS, hình ảnh
│   └── index.php        File chạy chính của website
│
├── database.sql         File import cơ sở dữ liệu
└── README.md            File hướng dẫn sử dụng dự án
```

## 9. Thông tin dự án

- Tên dự án: GLOWBEAUTY - Website bán mỹ phẩm
- Loại dự án: Website thương mại điện tử cơ bản
- Môi trường chạy: Localhost bằng XAMPP
- Cơ sở dữ liệu: MySQL
