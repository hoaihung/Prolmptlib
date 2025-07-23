<?php
require_once '../includes/loader.php';
header('Content-Type: application/json');
if (!is_logged_in()) die(json_encode(['success'=>false,'msg'=>'No auth']));
$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
if ($action == 'detail') {
    $id = intval($_GET['id']);
    $n = $pdo->prepare("SELECT * FROM notifications WHERE (user_id=? OR user_id IS NULL) AND id=?");
    $n->execute([$user_id, $id]);
    $noti = $n->fetch();
    if ($noti) {
        // Đánh dấu đã đọc
        $pdo->prepare("INSERT IGNORE INTO notification_reads (noti_id, user_id) VALUES (?,?)")->execute([$id, $user_id]);
        echo json_encode([
            'success'=>true,
            'title'=>$noti['title'],
            'content'=>nl2br(htmlspecialchars($noti['content'])),
            'created_at'=>date('d/m/Y H:i', strtotime($noti['created_at'])),
            'link'=>$noti['link']
        ]);
    } else {
        echo json_encode(['success'=>false, 'msg'=>'Not found']);
    }
    exit;
}
if ($action == 'markall') {
    // Đánh dấu đã đọc toàn bộ
    $notis = $pdo->prepare("SELECT id FROM notifications WHERE user_id=? OR user_id IS NULL");
    $notis->execute([$user_id]);
    foreach($notis as $row){
        $pdo->prepare("INSERT IGNORE INTO notification_reads (noti_id, user_id) VALUES (?,?)")->execute([$row['id'],$user_id]);
    }
    echo json_encode(['success'=>true]);
    exit;
}
echo json_encode(['success'=>false, 'msg'=>'Unknown action']);
