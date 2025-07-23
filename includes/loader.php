<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/db.php';
require_once __DIR__.'/auth.php';
// require thêm các helper khác nếu có
require_once __DIR__.'/functions.php';

if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $user = $pdo->prepare("SELECT role, premium_expire FROM users WHERE id=? LIMIT 1");
    $user->execute([$user_id]);
    $row = $user->fetch();
    if ($row && $row['role'] == 'premium' && $row['premium_expire'] && strtotime($row['premium_expire']) < time()) {
        // Hết hạn: về free
        $pdo->prepare("UPDATE users SET role='user', premium_expire=NULL WHERE id=?")->execute([$user_id]);
        $_SESSION['user_role'] = 'user'; // Nếu dùng session lưu role
    }
}
