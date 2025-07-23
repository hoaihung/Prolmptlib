<?php
function get_site_setting($key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT value FROM site_setting WHERE `key`=? LIMIT 1");
    $stmt->execute([$key]);
    return $stmt->fetchColumn();
}
?>