<?php
// includes/auth.php
require_once __DIR__ . '/db.php';
if(session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);

    ini_set('session.cookie_lifetime', 259200);
    session_set_cookie_params(259200);

    session_start();
}

// Check login

// Check role
function user_role() {
    return $_SESSION['user_role'] ?? 'guest';
}
function user_id() {
    return $_SESSION['user_id'] ?? null;
}
function is_logged_in() {
    return !!user_id();
}
function is_guest() {
    return user_role() === 'guest';
}
function is_user() {
    return user_role() === 'user';
}
function is_premium() {
    return user_role() === 'premium';
}
function is_admin() {
    return user_role() === 'admin' || user_role() === 'root';
}
function is_root() {
    return user_role() === 'root';
}
function is_premium_user($user) {
    return isset($user['role']) && in_array($user['role'], ['premium','admin','root']);
}
function has_role($required) {
    $roles = ['guest'=>0, 'user'=>1, 'premium'=>2, 'admin'=>3, 'root'=>4];
    $userRole = user_role();
    return isset($roles[$userRole]) && $roles[$userRole] >= $roles[$required];
}


// Thực hiện login
function do_login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND is_active=1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        return true;
    }
    return false;
}

// Đăng xuất
function do_logout() {
    session_unset();
    session_destroy();
}
