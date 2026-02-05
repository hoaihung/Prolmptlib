<?php
class PromptController {
    public function detail($id) {
        global $pdo;
        
        // Extract ID if slug is present (e.g. 123-some-title -> 123)
        $id = intval($id);
        
        if (!$id) {
            header("HTTP/1.0 404 Not Found");
            echo "Prompt not found";
            return;
        }

        $user_id = $_SESSION['user_id'] ?? 0;
        $is_admin = is_admin() || is_root();
        $is_premium = true; // Allow all to view

        // Fetch prompt
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, u.name as author_name 
                               FROM prompts p 
                               LEFT JOIN categories c ON p.category_id=c.id 
                               LEFT JOIN users u ON p.author_id=u.id 
                               WHERE p.id=?");
        $stmt->execute([$id]);
        $prompt = $stmt->fetch();

        if (!$prompt) {
            header("HTTP/1.0 404 Not Found");
            echo "Prompt not found";
            return;
        }

        // Check visibility
        if (!$is_admin && ($prompt['is_deleted'] || !$prompt['is_active'] || !$prompt['is_approved'])) {
             // Allow owner to view?
             if ($prompt['author_id'] != $user_id) {
                 header("HTTP/1.0 404 Not Found");
                 echo "Prompt not available";
                 return;
             }
        }

        // Tags
        $tags = $pdo->prepare("SELECT t.id, t.name FROM prompt_tags pt JOIN tags t ON pt.tag_id=t.id WHERE pt.prompt_id=?");
        $tags->execute([$id]);
        $prompt_tags = $tags->fetchAll(PDO::FETCH_ASSOC);

        // Favorites
        $user_favorites = [];
        if (is_logged_in()) {
            $uid = $_SESSION['user_id'];
            $qfav = $pdo->prepare("SELECT prompt_id FROM prompt_favorites WHERE user_id=? AND prompt_id=?");
            $qfav->execute([$uid, $id]);
            $user_favorites = $qfav->fetchAll(PDO::FETCH_COLUMN);
        }

        // Comments
        $comments = $pdo->prepare("SELECT c.*, u.name as user_name, u.role as user_role FROM comments c JOIN users u ON c.user_id=u.id WHERE c.prompt_id=? AND c.is_active=1 ORDER BY c.created_at DESC");
        $comments->execute([$id]);
        $prompt_comments = $comments->fetchAll(PDO::FETCH_ASSOC);

        // Related Prompts (Same Category, exclude current)
        $related = $pdo->prepare("SELECT p.*, c.name as category_name FROM prompts p LEFT JOIN categories c ON p.category_id=c.id WHERE p.category_id=? AND p.id!=? AND p.is_active=1 AND p.is_approved=1 AND p.is_deleted=0 ORDER BY RAND() LIMIT 3");
        $related->execute([$prompt['category_id'], $id]);
        $related_prompts = $related->fetchAll(PDO::FETCH_ASSOC);

        // Update view count
        $pdo->prepare("UPDATE prompts SET view_count=view_count+1 WHERE id=?")->execute([$id]);

        // Render View
        $page_title = $prompt['title'] . " - PromptLib";
        require_once __DIR__ . '/../Views/prompt_detail.php';
    }
}
?>
