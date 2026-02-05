<?php
// includes/config.php
date_default_timezone_set('Asia/Ho_Chi_Minh');


define('DB_HOST', 'localhost');
define('DB_NAME', 'promptlib');  // Đổi theo tên database bạn tạo
define('DB_USER', 'root');        // Đổi theo user MySQL của bạn
define('DB_PASS', '');            // Đổi theo mật khẩu user

define('SITE_URL', 'http://promptlib.local/'); // Đổi URL nếu cần
define('SESSION_NAME', 'promptlibs_session');
define('SECRET_KEY', 'thay_doithanh_chuoi_bat_ky'); // Đổi chuỗi này khi deploy

// Các config khác có thể bổ sung sau
define('BASE_PATH', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');
// Ví dụ: <?= BASE_PATH ?>
