<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) { header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $pdo->prepare("DELETE FROM pages WHERE id=?")->execute([$id]);
}
header("Location: pages.php");
exit;
?>
