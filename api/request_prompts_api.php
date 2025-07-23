<?php
require_once '../includes/loader.php';
header('Content-Type: application/json; charset=utf-8');
if (!is_admin() && !is_root()) die(json_encode(['success'=>false, 'message'=>'No permission']));

if ($_POST['action']=='add_prompt') {
    $req_id = intval($_POST['req_id']);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if (!$req_id || !$title || !$content) {
        echo json_encode(['success'=>false, 'message'=>'Thiếu dữ liệu']); exit;
    }
    $rq = $pdo->prepare("SELECT * FROM request_prompts WHERE id=?"); $rq->execute([$req_id]);
    $rq = $rq->fetch();
    if (!$rq) { echo json_encode(['success'=>false, 'message'=>'Yêu cầu không tồn tại']); exit; }

    // Thêm Prompt vào bảng prompts (chỉ thêm các trường cơ bản, có thể chỉnh cho đầy đủ)
    $pdo->prepare("INSERT INTO prompts (title, content, description, author_id, is_active, is_approved, created_at, updated_at)
        VALUES (?,?,?,?,1,1,NOW(),NOW())")
        ->execute([$title, $content, $rq['description'], $_SESSION['user_id']]);

    // Đánh dấu done
    $pdo->prepare("UPDATE request_prompts SET is_done=1, done_at=NOW() WHERE id=?")->execute([$req_id]);

    // Gửi thông báo cho user yêu cầu
    $pdo->prepare("INSERT INTO notifications (user_id, title, content, type, link, created_at)
        VALUES (?,?,?,?,?,NOW())")
        ->execute([
            $rq['user_id'],
            "Yêu cầu Prompt của bạn đã được xử lý",
            "Prompt <b>".htmlspecialchars($title)."</b> đã được thêm vào kho prompt.",
            "prompt",
            BASE_URL."prompts.php"
        ]);
    echo json_encode(['success'=>true]);
}
