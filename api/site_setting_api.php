<?php
require_once '../includes/loader.php';

if (!is_admin() && !is_root()) {
    header('Content-Type: application/json');
    echo json_encode(['success'=>false, 'message'=>'No permission']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_menu') {
    $menu_json = $_POST['menu'] ?? '[]';
    $pdo->prepare("REPLACE INTO site_setting (`key`,`value`) VALUES (?,?)")->execute(['menu_main', $menu_json]);
    echo json_encode(['success' => true, 'message' => 'Đã lưu cài đặt']);
    exit;
}

// Có thể bổ sung xử lý các action khác sau này...
echo json_encode(['success' => false, 'message' => 'No action specified']);
