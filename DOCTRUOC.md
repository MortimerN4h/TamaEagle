# Setup php để chạy
1. Copy file php_grpc.dll vào thư mục php/ext/
2. Sửa file php.ini trong thư mục php
    Thêm cuối dòng "extension=php_grpc.dll"
3. Tạo private-key
    Vào đồ án -> Bánh răng -> Project settings -> Service accounts -> Generate new private key
4. Sửa firebase-credentials.json dán private key vào
    "private_key_id": "placeholder_key_id",