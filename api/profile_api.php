<?php
require_once '../includes/loader.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'No permission']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $gpt = trim($_POST['api_gpt_key'] ?? '');
    $gemini = trim($_POST['api_gemini_key'] ?? '');
    $new_pass = trim($_POST['new_password'] ?? '');
    $confirm_pass = trim($_POST['confirm_password'] ?? '');

    if ($name === '') {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên']);
        exit;
    }

    // Xử lý đổi mật khẩu nếu có nhập
    if ($new_pass !== '') {
        if (strlen($new_pass) < 6) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu phải từ 6 ký tự']);
            exit;
        }
        if ($new_pass !== $confirm_pass) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu xác nhận không khớp']);
            exit;
        }
        // Hash password
        $pass_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET name=?, api_gpt_key=?, api_gemini_key=?, password=? WHERE id=?")
            ->execute([$name, $gpt, $gemini, $pass_hash, $user_id]);
    } else {
        // Không đổi pass
        $pdo->prepare("UPDATE users SET name=?, api_gpt_key=?, api_gemini_key=? WHERE id=?")
            ->execute([$name, $gpt, $gemini, $user_id]);
    }
    echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
    exit;
}

?>
