<?php
require_once 'includes/loader.php';
if (is_logged_in()) header('Location: index.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate basic
    if (!$name || !$email || strlen($password) < 6) exit('Thiếu thông tin!');

    // reCAPTCHA
    $captcha = $_POST['g-recaptcha-response'] ?? '';
    $secret = '6LdT7GwrAAAAAOqmpk3HGg4GJj_vJVQkA7wnK3ZM';
    $resp = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$captcha");
    $resp = json_decode($resp, true);
    if (!$resp['success']) exit('Chưa xác thực captcha!');

    // Check email trùng
    $q = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $q->execute([$email]);
    if ($q->fetch()) exit('Email đã tồn tại!');

    // Hash password
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $pdo->prepare("INSERT INTO users (name,email,password,role,is_active,created_at) VALUES (?,?,?, 'user',1, NOW())")
        ->execute([$name,$email,$hash]);

    // Auto login sau khi đăng ký thành công (tùy chọn)
    $id = $pdo->lastInsertId();
    $_SESSION['user_id'] = $id;
    $_SESSION['user_role'] = 'user';
    $_SESSION['user_name'] = $name;

    header('Location: index.php'); exit;
}

$title = "Đăng ký tài khoản mới";
include 'includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow mt-12">
    <h1 class="text-xl font-bold mb-4">Đăng ký thành viên</h1>
    <form method="POST" action="register.php">
        <div class="mb-3">
            <label>Họ tên</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2" required minlength="6">
        </div>
        <!-- Google reCAPTCHA -->
        <div class="mb-3">
            <div class="g-recaptcha" data-sitekey="6LdT7GwrAAAAANGeXyGOTWhtgMpG5bsrRmIZ9rpr"></div>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded w-full">Đăng ký</button>
    </form>
    <div class="mt-4 text-center">
        Đã có tài khoản? <a href="/login.php" class="text-blue-600 underline">Đăng nhập</a>
    </div>
</div>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php include 'includes/footer.php'; ?>
