<?php
require_once 'includes/loader.php';

if (is_logged_in()) {
    if (is_admin() || is_root()) {
		header('Location: ./admin/dashboard.php');
		exit;
	} else {
		header('Location: ./index.php');
		exit;
	}
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (do_login($email, $password)) {
      if (is_admin() || is_root()) {

        $user_id = $_SESSION['user_id'];
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $stmtInsert = $pdo->prepare("
            INSERT INTO login_sessions (user_id, ip, user_agent) 
            VALUES (?, ?, ?)
        ");
        $stmtInsert->execute([$user_id, $ip, $user_agent]);

  			header('Location: ./admin/dashboard.php');
  			exit;
  		} else {
  			header('Location: ./index.php');
  			exit;
  		}
    } else {
        $error = 'Sai email hoặc mật khẩu, hoặc tài khoản bị khóa!';
    }
}
$title = "Đăng nhập PromptLib";
include 'includes/header.php';
?>
<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow mt-12">
<h1 class="text-2xl font-bold mb-4 text-center">PromptLib Login</h1>
  <form method="post" class="">
      
      <?php if ($error): ?>
          <div class="bg-red-100 text-red-700 p-2 mb-3 rounded text-sm text-center"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <div class="mb-4">
          <label class="block mb-1 font-semibold">Email</label>
          <input name="email" type="email" required class="w-full border rounded px-3 py-2" autofocus>
      </div>
      <div class="mb-6">
          <label class="block mb-1 font-semibold">Mật khẩu</label>
          <input name="password" type="password" required class="w-full border rounded px-3 py-2">
      </div>
      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl font-semibold transition">Đăng nhập</button>
  </form>
  <div class="mt-4 text-center">
        Chưa có tài khoản? <a href="/register.php" class="text-blue-600 underline">Đăng ký mới</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

