<?php
require_once 'includes/config.php';

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM pages LIKE 'created_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE pages ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "Column created_at added to pages table.";
    } else {
        echo "Column created_at already exists.";
    }
    
    // Also check updated_at
    $stmt = $pdo->query("SHOW COLUMNS FROM pages LIKE 'updated_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE pages ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "Column updated_at added to pages table.";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
