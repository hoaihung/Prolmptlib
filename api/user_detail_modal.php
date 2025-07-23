<?php
require_once '../includes/loader.php';
require_once '../includes/svg.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) exit('Not found');
$user = $pdo->query("SELECT * FROM users WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
if (!$user) exit('Not found');

ob_start();
include '../components/user_modal.php';
$html = ob_get_clean();
header('Content-Type: text/html; charset=utf-8');
echo $html;
