<?php
class CommentController {
    public function store() {
        global $pdo;
        if (!is_logged_in()) { header("Location: " . SITE_URL . "login.php"); exit; }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $prompt_id = intval($_POST['prompt_id'] ?? 0);
            $content = trim($_POST['content'] ?? '');
            $user_id = $_SESSION['user_id'];

            if ($prompt_id && $content) {
                $stmt = $pdo->prepare("INSERT INTO comments (prompt_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$prompt_id, $user_id, $content]);
            }
            
            // Redirect back
            header("Location: " . SITE_URL . "prompt/" . $prompt_id);
            exit;
        }
    }
}
?>
