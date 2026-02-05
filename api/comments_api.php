<?php
/**
 * API xử lý bình luận cho prompt.
 *
 * Hỗ trợ các action:
 * - list: Lấy danh sách bình luận của một prompt (`GET` với `prompt_id`)
 * - add:  Thêm bình luận mới (`POST` với JSON hoặc form), yêu cầu đăng nhập
 */
require_once '../includes/loader.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

// Lấy danh sách bình luận cho prompt
if ($action === 'list') {
    $prompt_id = intval($_GET['prompt_id'] ?? 0);
    if (!$prompt_id) {
        echo json_encode([]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT pc.id, pc.content, pc.created_at, u.name AS author_name, u.id AS user_id
        FROM prompt_comments pc
        JOIN users u ON u.id = pc.user_id
        WHERE pc.prompt_id = ?
        ORDER BY pc.created_at DESC");
    $stmt->execute([$prompt_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($comments);
    exit;
}

// Thêm bình luận mới
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để bình luận.']);
        exit;
    }
    // Đọc dữ liệu đầu vào có thể ở JSON hoặc form
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }
    $prompt_id = intval($input['prompt_id'] ?? 0);
    $content   = trim($input['content'] ?? '');
    if (!$prompt_id || $content === '') {
        echo json_encode(['success' => false, 'message' => 'Thiếu prompt_id hoặc nội dung.']);
        exit;
    }
    $user_id = $_SESSION['user_id'] ?? 0;
    try {
        // Chèn bình luận
        $stmt = $pdo->prepare("INSERT INTO prompt_comments (prompt_id, user_id, content, created_at) VALUES (?,?,?,NOW())");
        $stmt->execute([$prompt_id, $user_id, $content]);
        $comment_id = $pdo->lastInsertId();
        // Lấy lại bình luận vừa thêm để trả về
        $stmt = $pdo->prepare("SELECT pc.id, pc.content, pc.created_at, u.name AS author_name, u.id AS user_id FROM prompt_comments pc JOIN users u ON u.id=pc.user_id WHERE pc.id=?");
        $stmt->execute([$comment_id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'comment' => $comment]);
    } catch (Exception $ex) {
        echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
    }
    exit;
}

// Mặc định: action không hỗ trợ
echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;