# Setup php để chạy
1. Copy file php_grpc.dll trong thư mục XTEMP vào thư mục php/ext/
2. Sửa file php.ini trong thư mục php
    Thêm cuối dòng "extension=php_grpc.dll"
cái này t xài bản 8.0 php nên mấy đứa xài bản khác thì hỏi GPT mà kiếm đúng bản
3. Tạo private-key
    Vào đồ án trên web firebase -> Bánh răng -> Project settings -> Service accounts -> Generate new private key
4. Sửa firebase-credentials.json dán toàn bộ file mới tải về vào thay luôn.