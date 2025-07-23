<?php
require_once '../includes/loader.php';
if (!is_logged_in()) { die(json_encode(['success'=>false,'msg'=>'Chưa đăng nhập'])); }
$user_id = $_SESSION['user_id'];
$prompt_id = intval($_POST['prompt_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($action == 'toggle') {
    $q = $pdo->prepare("SELECT * FROM prompt_favorites WHERE user_id=? AND prompt_id=?");
    $q->execute([$user_id, $prompt_id]);
    if ($q->fetch()) {
        $pdo->prepare("DELETE FROM prompt_favorites WHERE user_id=? AND prompt_id=?")->execute([$user_id, $prompt_id]);
        die(json_encode(['success'=>true, 'favorite'=>0]));
    } else {
        $pdo->prepare("INSERT IGNORE INTO prompt_favorites(user_id, prompt_id) VALUES (?,?)")->execute([$user_id, $prompt_id]);
        die(json_encode(['success'=>true, 'favorite'=>1]));
    }
}
die(json_encode(['success'=>false]));

?>