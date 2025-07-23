<?php
require_once 'includes/db.php';
echo 'Kết nối DB thành công!';
echo password_hash('ngochuyen', PASSWORD_DEFAULT); // Hash mạnh dùng để lưu mật khẩu