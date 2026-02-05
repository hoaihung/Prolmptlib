<?php
/**
 * API xử lý lượt thích (like) cho prompt.
 *
 * - action=count: trả về tổng số lượt thích và trạng thái người dùng hiện tại (đã thích hay chưa)
 *   yêu cầu truyền `prompt_id` (GET)
 * - action=toggle: bật/tắt like, yêu cầu đăng nhập (POST hoặc GET)
 */
require_once '../includes/loader.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$prompt_id = intval($_GET['prompt_id'] ?? ($_POST['prompt_id'] ?? 0));
if (!$prompt_id) {
    echo json_encode(['success' => false, 'message' => 'Missing prompt_id']);
    exit;
}

// Lấy số lượt thích và trạng thái đã thích hay chưa
function getLikeInfo($pdo, $prompt_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM prompt_likes WHERE prompt_id=?");
    $stmt->execute([$prompt_id]);
    $totalLikes = (int)$stmt->fetchColumn();
    $liked = false;
    if (is_logged_in()) {
        $uid = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT 1 FROM prompt_likes WHERE prompt_id=? AND user_id=? LIMIT 1");
        $stmt->execute([$prompt_id, $uid]);
        $liked = $stmt->fetchColumn() ? true : false;
    }
    return ['likes' => $totalLikes, 'liked' => $liked];
}

if ($action === 'count') {
    echo json_encode(getLikeInfo($pdo, $prompt_id));
    exit;
}

if ($action === 'toggle') {
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thích.']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    // Kiểm tra xem đã thích chưa
    $stmt = $pdo->prepare("SELECT 1 FROM prompt_likes WHERE prompt_id=? AND user_id=? LIMIT 1");
    $stmt->execute([$prompt_id, $user_id]);
    $liked = $stmt->fetchColumn();
    if ($liked) {
        // Đã thích -> bỏ thích
        $del = $pdo->prepare("DELETE FROM prompt_likes WHERE prompt_id=? AND user_id=?");
        $del->execute([$prompt_id, $user_id]);
    } else {
        // Chưa thích -> thích
        $ins = $pdo->prepare("INSERT INTO prompt_likes (prompt_id, user_id, created_at) VALUES (?,?,NOW())");
        $ins->execute([$prompt_id, $user_id]);
    }
    // Trả về thông tin mới
    $info = getLikeInfo($pdo, $prompt_id);
    echo json_encode(['success' => true, 'likes' => $info['likes'], 'liked' => $info['liked']]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;