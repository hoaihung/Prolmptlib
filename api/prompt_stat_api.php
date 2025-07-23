<?php
require_once '../includes/loader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'inc_run') {
    $prompt_id = intval($_POST['prompt_id']);
    if ($prompt_id) {
        $pdo->prepare("UPDATE prompts SET console_count = console_count + 1 WHERE id=?")->execute([$prompt_id]);
        echo json_encode(['success'=>true]);
        exit;
    }
}
echo json_encode(['success'=>false]);
