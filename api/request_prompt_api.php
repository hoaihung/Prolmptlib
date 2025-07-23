<?php
require_once '../includes/loader.php';
header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in() || !(is_premium() || is_admin() || is_root())) {
    echo json_encode(['success'=>false, 'message'=>'Bạn không có quyền yêu cầu prompt.']); exit;
}

$user_id = $_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');
$desc = trim($_POST['description'] ?? '');

if (!$title) {
    echo json_encode(['success'=>false, 'message'=>'Vui lòng nhập tiêu đề.']); exit;
}

$pdo->prepare("INSERT INTO request_prompts (user_id, title, description) VALUES (?,?,?)")
    ->execute([$user_id, $title, $desc]);
echo json_encode(['success'=>true, 'message'=>'Yêu cầu prompt đã được gửi!']);
