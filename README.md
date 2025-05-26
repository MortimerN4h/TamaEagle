# TamaEagle - Task Management System

## Tổng quan
TamaEagle là một hệ thống quản lý công việc được phát triển bằng PHP và MySQL, với giao diện người dùng đẹp và hiện đại.

## Cấu trúc Dự án

### Auth System (Hệ thống xác thực)
- **login.php** - Trang đăng nhập với giao diện TamaEagle
- **register.php** - Trang đăng ký tài khoản mới
- **forgot-password.php** - Trang quên mật khẩu
- **logout.php** - Xử lý đăng xuất

### File HTML Gốc (Đã tích hợp vào PHP)
- **Login/Login.html** - Giao diện đăng nhập gốc
- **Login/Register.html** - Giao diện đăng ký gốc  
- **Login/ForgotPW.html** - Giao diện quên mật khẩu gốc

### CSS & JavaScript
- **assets/css/Login/** - CSS cho các trang authentication
  - `Login.css` - Styles cho trang đăng nhập
  - `Register.css` - Styles cho trang đăng ký
  - `ForgotPW.css` - Styles cho trang quên mật khẩu
- **assets/css/welcome.css** - CSS cho trang chào mừng
- **assets/js/Login/** - JavaScript cho authentication
  - `simple-auth.js` - Xử lý toggle password và validation cơ bản
  - `register-validation.js` - Validation cho form đăng ký

## Tính năng đã tích hợp

### 1. Giao diện đẹp
- ✅ Design gradient background (ADD8E6 → 9370DB)
- ✅ Form styling với transparency và shadow
- ✅ Responsive design
- ✅ Modern button styling

### 2. Xác thực người dùng
- ✅ Đăng ký tài khoản với validation
- ✅ Đăng nhập với email/username
- ✅ Toggle hiển thị/ẩn mật khẩu
- ✅ Validation form client-side và server-side
- ✅ Thông báo lỗi/thành công đẹp

### 3. Bảo mật
- ✅ Password hashing với PHP password_hash()
- ✅ SQL injection protection với prepared statements
- ✅ XSS protection với htmlspecialchars()
- ✅ Session management

### 4. Trang chào mừng
- ✅ Landing page đẹp cho người dùng chưa đăng nhập
- ✅ Hero section với CTA buttons
- ✅ Features showcase
- ✅ Task preview animation

## Cách sử dụng

### 1. Thiết lập
1. Đảm bảo XAMPP/WAMP đang chạy
2. Đặt project trong thư mục htdocs
3. Truy cập `http://localhost/TamaEagle`

### 2. Database
Database sẽ tự động được tạo khi chạy lần đầu:
- Database: `todoist_clone`
- Tables: `users`, `projects`, `tasks`, `sections`

### 3. Flow người dùng
1. Truy cập trang chủ → Welcome page
2. Click "Sign Up" → Đăng ký tài khoản
3. Hoặc "Sign In" → Đăng nhập
4. Sau khi đăng nhập → Inbox page

## Các tính năng JavaScript

### Toggle Password
```javascript
// Tự động thêm tính năng hiển thị/ẩn mật khẩu
document.querySelectorAll('.toggle-password')
```

### Form Validation
```javascript
// Validation real-time cho form đăng ký
// Kiểm tra password match, email format, etc.
```

## Cấu trúc CSS

### Color Scheme
- Primary: `#9370DB` (Medium Slate Blue)
- Secondary: `#ADD8E6` (Light Blue)
- Background: Linear gradient
- Text: `#202020` (Dark Gray)

### Components
- `.error-message` - Thông báo lỗi
- `.success-message` - Thông báo thành công
- `.input-group` - Nhóm input với label
- `.toggle-password` - Button toggle password

## Tích hợp hoàn tất

Các file HTML gốc đã được tích hợp thành công vào hệ thống PHP:

1. **ForgotPW.html** → **forgot-password.php**
2. **Login.html** → **login.php** 
3. **Register.html** → **register.php**

Tất cả đều giữ nguyên:
- ✅ Giao diện và styling gốc
- ✅ Functionality JavaScript
- ✅ Responsive design
- ✅ Plus thêm PHP backend processing

## Lưu ý
- File HTML gốc vẫn được giữ trong thư mục `auth/Login/` để tham khảo
- CSS và JS được tối ưu để hoạt động với cả HTML và PHP
- Hệ thống authentication hoàn toàn functional với database MySQL
