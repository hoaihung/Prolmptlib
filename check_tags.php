<?php
require_once 'includes/config.php';

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    
    echo "<h3>Tags:</h3>";
    $tags = $pdo->query("SELECT * FROM tags")->fetchAll(PDO::FETCH_ASSOC);
    foreach($tags as $t) {
        echo "ID: {$t['id']} - Name: {$t['name']}<br>";
    }

    echo "<h3>Prompt Tags (First 20):</h3>";
    $pt = $pdo->query("SELECT * FROM prompt_tags LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($pt)) {
        echo "No data in prompt_tags table!<br>";
    } else {
        foreach($pt as $row) {
            echo "Prompt ID: {$row['prompt_id']} - Tag ID: {$row['tag_id']}<br>";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
