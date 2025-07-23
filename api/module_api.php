<?php
require_once '../includes/loader.php';
if (!is_admin()) exit(json_encode(['error'=>'No permission']));
header('Content-Type: application/json');

if ($_GET['action'] === 'get' && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $q = $pdo->prepare("SELECT * FROM modules WHERE id=?");
    $q->execute([$id]);
    $mod = $q->fetch(PDO::FETCH_ASSOC);
    if (!$mod) exit(json_encode(['success'=>false, 'message'=>'Not found']));
    echo json_encode($mod); exit;
}
exit(json_encode(['success'=>false, 'message'=>'Invalid request']));
