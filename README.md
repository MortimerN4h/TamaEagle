# TamaEagle - Ứng dụng Quản lý Công việc

## Giới thiệu
TamaEagle là một ứng dụng quản lý công việc và dự án hiệu quả, được thiết kế để hỗ trợ người dùng theo dõi và quản lý các nhiệm vụ hàng ngày. Ứng dụng cung cấp giao diện trực quan, hỗ trợ phân chia dự án thành các phần nhỏ (sections), quản lý nhiệm vụ với các mức độ ưu tiên khác nhau, theo dõi tiến độ dự án, và nhắc nhở thời hạn.

## Công nghệ và Framework
### Front-end
- **HTML5, CSS3, JavaScript**: Nền tảng cơ bản cho phát triển giao diện
- **jQuery**: Thư viện JavaScript cho xử lý DOM và AJAX
- **jQuery UI**: Hỗ trợ các tính năng kéo thả (drag-and-drop) và sắp xếp (sortable)
- **Firebase JavaScript SDK**: Tích hợp xác thực và cơ sở dữ liệu Firestore

### Back-end
- **PHP**: Ngôn ngữ lập trình phía máy chủ
- **Firebase PHP SDK** (Kreait): Tương tác với Firebase từ phía server
- **Composer**: Quản lý các thư viện PHP

## Các thành phần liên quan
- **Firebase Authentication**: Xử lý đăng nhập, đăng ký, phục hồi mật khẩu
- **Firebase Firestore**: Cơ sở dữ liệu NoSQL lưu trữ dữ liệu dự án, nhiệm vụ, danh mục
- **Firebase Cloud Storage**: Lưu trữ tệp và tài liệu đính kèm (nếu có)

## Hướng dẫn tải và cài đặt
### Yêu cầu hệ thống
- PHP 7.4 hoặc cao hơn
- Composer
- Máy chủ web (Apache/Nginx)

### Cài đặt trên Windows
1. Cài đặt XAMPP, WAMP hoặc môi trường PHP tương tự
2. Clone dự án từ repository:
   ```
   git clone [đường-dẫn-repository]
   ```
3. Di chuyển vào thư mục dự án:
   ```
   cd TamaEagle
   ```
4. Cài đặt các phụ thuộc PHP qua Composer:
   ```
   composer install
   ```
5. Tạo tài khoản Firebase và tải file credentials:
   - Đăng nhập vào Firebase Console (https://console.firebase.google.com/)
   - Tạo một dự án mới
   - Thêm ứng dụng web vào dự án
   - Tạo file Service Account và tải về dưới dạng JSON
   - Đổi tên file thành `firebase-credentials.json` và đặt vào thư mục gốc của dự án
6. Cấu hình file `assets/js/firebase-config.js` với thông tin Firebase của bạn
7. Khởi động máy chủ web và truy cập ứng dụng qua trình duyệt

### Cài đặt trên Linux
1. Cài đặt Apache, PHP và các extension cần thiết:
   ```
   sudo apt update
   sudo apt install apache2 php libapache2-mod-php php-cli php-json php-common php-mbstring php-zip php-gd php-xml php-curl composer
   ```
2. Clone dự án:
   ```
   git clone [đường-dẫn-repository]
   ```
3. Cấu hình quyền thư mục:
   ```
   sudo chown -R www-data:www-data TamaEagle/
   sudo chmod -R 755 TamaEagle/
   ```
4. Di chuyển vào thư mục dự án:
   ```
   cd TamaEagle
   ```
5. Cài đặt dependencies:
   ```
   composer install
   ```
6. Thực hiện tương tự các bước 5-6 như trong hướng dẫn Windows

## Database
TamaEagle sử dụng Firebase Firestore, một cơ sở dữ liệu NoSQL. Cấu trúc dữ liệu chính gồm:

- **users**: Lưu trữ thông tin người dùng
  - Các trường: id, email, name, created_at, updated_at

- **projects**: Lưu trữ các dự án
  - Các trường: id, name, description, color, user_id, created_at, updated_at

- **sections**: Lưu trữ các phân đoạn trong dự án
  - Các trường: id, name, project_id, position, created_at, updated_at

- **tasks**: Lưu trữ nhiệm vụ
  - Các trường: id, name, description, user_id, project_id, section_id, start_date, due_date, 
    priority, is_completed, position, created_at, updated_at

## Quy trình hoạt động

### Front-end
1. **Xác thực người dùng**:
   - Đăng ký tài khoản thông qua Firebase Authentication
   - Đăng nhập bằng email/mật khẩu hoặc tài khoản xã hội
   - Xử lý và hiển thị thông báo lỗi

2. **Tương tác với dữ liệu**:
   - Hiển thị và cập nhật thông tin dự án/nhiệm vụ từ back-end qua AJAX
   - Sử dụng jQuery UI để kéo thả và sắp xếp các nhiệm vụ
   - Cập nhật giao diện người dùng theo thời gian thực

3. **Quản lý dự án và nhiệm vụ**:
   - Tạo, sửa, xóa dự án
   - Tạo, sửa, xóa các phân đoạn
   - Tạo, sửa, xóa và theo dõi các nhiệm vụ

### Back-end
1. **Xử lý xác thực**:
   - Xác minh token người dùng
   - Quản lý phiên và kiểm tra quyền

2. **Xử lý yêu cầu dữ liệu**:
   - Truy xuất dữ liệu từ Firestore
   - Kiểm tra và xác thực dữ liệu
   - Gửi phản hồi cho client

3. **Tác vụ hệ thống**:
   - Thông báo nhiệm vụ đến hạn
   - Cập nhật trạng thái nhiệm vụ
   - Đồng bộ hóa dữ liệu

## Các chức năng chính
1. **Quản lý tài khoản**
   - Đăng ký, đăng nhập, khôi phục mật khẩu
   - Đăng nhập với Google, Facebook (nếu được cấu hình)

2. **Quản lý dự án**
   - Tạo, chỉnh sửa và xóa dự án
   - Phân loại dự án theo màu sắc
   - Theo dõi tiến độ dự án

3. **Quản lý nhiệm vụ**
   - Tạo nhiệm vụ với thời gian bắt đầu/kết thúc
   - Thiết lập mức độ ưu tiên
   - Thêm mô tả chi tiết
   - Đánh dấu hoàn thành/chưa hoàn thành

4. **Quản lý phân đoạn**
   - Tổ chức nhiệm vụ theo phân đoạn
   - Kéo thả để sắp xếp phân đoạn
   - Thay đổi tên phân đoạn

5. **Theo dõi và báo cáo**
   - Xem nhiệm vụ hôm nay
   - Xem nhiệm vụ sắp tới
   - Xem nhiệm vụ đã hoàn thành
   - Theo dõi nhiệm vụ quá hạn

6. **Kéo thả và sắp xếp**
   - Di chuyển nhiệm vụ giữa các phân đoạn
   - Sắp xếp thứ tự ưu tiên của nhiệm vụ
   - Sắp xếp các phân đoạn trong dự án

## Bảo mật
- **Xác thực người dùng**: Sử dụng Firebase Authentication với các phương thức mã hóa hiện đại
- **Kiểm soát truy cập**: Mỗi người dùng chỉ có thể truy cập dữ liệu của mình
- **Xác thực phía máy chủ**: Kiểm tra quyền truy cập trước khi thực hiện các thao tác với dữ liệu
- **Bảo vệ API**: Các API endpoint được bảo vệ để ngăn chặn truy cập trái phép
- **Mã hóa dữ liệu**: Dữ liệu được mã hóa khi truyền tải qua HTTPS

## Ghi chú
- Ứng dụng yêu cầu kết nối internet để đồng bộ hóa dữ liệu với Firebase
- Để sử dụng đầy đủ chức năng, cần tạo tài khoản Firebase và cấu hình đúng file credentials
- Nên đặt giới hạn truy vấn và lưu trữ trong Firebase để tránh vượt quá hạn mức miễn phí
- Có thể cấu hình thêm các tính năng nâng cao như thông báo email hoặc tích hợp với các dịch vụ khác thông qua Firebase Cloud Functions
- Cấu trúc thư mục tuân theo mô hình MVC đơn giản, giúp dễ dàng mở rộng và bảo trì
