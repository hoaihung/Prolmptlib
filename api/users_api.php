<?php
require_once '../includes/loader.php';
if (!is_logged_in() || !is_admin()) exit(json_encode(['success'=>false, 'message'=>'No permission']));
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Thêm/Sửa user
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

    if ($role == 'premium') {
        $premium_expire = $_POST['premium_expire'] ? date('Y-m-d H:i:s', strtotime($_POST['premium_expire'])) : null;
    } else {
        $premium_expire = null;
    }

    if ($id) {
        // Update
        if ($role == 'root') exit(json_encode(['success'=>false, 'message'=>'Không được sửa Root']));
        $sql = "UPDATE users SET name=?, email=?, role=?, is_active=?, premium_expire=?";
        $params = [$name, $email, $role, $is_active, $premium_expire];
        if ($password) {
            $sql .= ", password=?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        $sql .= " WHERE id=?";
        $params[] = $id;
        $pdo->prepare($sql)->execute($params);
        exit(json_encode(['success'=>true]));
    } else {
        // Insert
        if (!$password) exit(json_encode(['success'=>false, 'message'=>'Phải nhập mật khẩu']));
        $q = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $q->execute([$email]);
        if ($q->fetch()) exit(json_encode(['success'=>false, 'message'=>'Email đã tồn tại']));
        $pdo->prepare("INSERT INTO users (name,email,password,role,is_active,premium_expire,created_at) VALUES (?,?,?,?,?,?,NOW())")
            ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $is_active, $premium_expire]);
        exit(json_encode(['success'=>true]));
    }
}
if ($action === 'get' && isset($_GET['id'])) {
    // Lấy user
    $id = intval($_GET['id']);
    $user = $pdo->query("SELECT * FROM users WHERE id=$id LIMIT 1")->fetch();
    exit(json_encode($user));
}
if ($action === 'status' && isset($_GET['id'], $_GET['status'])) {
    // Đổi trạng thái active/inactive
    $id = intval($_GET['id']);
    $status = intval($_GET['status']);
    $user = $pdo->query("SELECT * FROM users WHERE id=$id LIMIT 1")->fetch();
    if (!$user || $user['role'] == 'root') exit(json_encode(['success'=>false,'message'=>'Không thao tác trên root']));
    if ($user['is_deleted']) exit(json_encode(['success'=>false,'message'=>'User đã xóa']));
    $pdo->prepare("UPDATE users SET is_active=? WHERE id=?")->execute([$status, $id]);
    exit(json_encode(['success'=>true]));
}

if ($action === 'delete' && isset($_GET['id'])) {
    // Chỉ xóa khi đã inactive
    $id = intval($_GET['id']);
    $user = $pdo->query("SELECT * FROM users WHERE id=$id LIMIT 1")->fetch();
    if (!$user || $user['role'] == 'root') exit(json_encode(['success'=>false,'message'=>'Không xóa root']));
    if ($user['is_active']) exit(json_encode(['success'=>false,'message'=>'Vô hiệu hóa user trước khi xóa!']));
    if ($user['is_deleted']) exit(json_encode(['success'=>false,'message'=>'Đã xóa rồi']));
    $pdo->prepare("UPDATE users SET is_deleted=1, deleted_at=NOW() WHERE id=?")->execute([$id]);
    exit(json_encode(['success'=>true]));
}

// (Các code xử lý thêm/sửa/get giữ nguyên như phiên bản trước)

if ($action === 'restore' && isset($_GET['id'])) {
    // Khôi phục user đã xóa
    $id = intval($_GET['id']);
    $user = $pdo->query("SELECT * FROM users WHERE id=$id LIMIT 1")->fetch();
    if (!$user || !$user['is_deleted']) exit(json_encode(['success'=>false,'message'=>'User chưa xóa']));
    $pdo->prepare("UPDATE users SET is_deleted=0, deleted_at=NULL WHERE id=?")->execute([$id]);
    exit(json_encode(['success'=>true]));
}
exit(json_encode(['success'=>false, 'message'=>'Lỗi dữ liệu']));
