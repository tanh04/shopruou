# 🍷 Shop Rượu Vang — Laravel E-Commerce

Website bán rượu vang trực tuyến: quản lý sản phẩm, giỏ hàng, đơn hàng, khách hàng và khu vực quản trị.

 🚀 Công nghệ
- PHP 8+ (Laravel)
- MySQL
- Blade / Bootstrap / JS (AJAX)
- Vite, NPM
- Git & GitHub

 ✨ Tính năng chính
- [Khách] Đăng ký / đăng nhập, duyệt sản phẩm, tìm kiếm, giỏ hàng, đặt hàng
- [Admin] Quản lý sản phẩm, danh mục, đơn hàng, người dùng
- Chat/Thông báo (tùy cấu hình)
- Tối ưu CRUD, phân trang, validate form
- Tích hợp thanh toán VNPay/MoMo
 🧩 Cấu trúc thư mục tiêu biểu
- `app/`, `routes/`, `resources/`, `database/`, `public/` (chuẩn Laravel)
- `routes/web.php` — định tuyến web
- `resources/views/` — giao diện Blade
- `database/migrations/` — bảng dữ liệu
- `public/` — assets tĩnh

 🏁 Cách chạy (local)
```bash
git clone https://github.com/tanh04/shopruou.git
cd shopruou
composer install
cp .env.example .env
php artisan key:generate
# Cấu hình DB trong .env rồi:
php artisan migrate --seed  
npm install && npm run build 
php artisan serve
