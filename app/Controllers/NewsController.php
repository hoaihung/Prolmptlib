<?php
class NewsController {
    public function index() {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT * FROM news WHERE is_active=1 ORDER BY created_at DESC");
        $stmt->execute();
        $news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $page_title = "Tin tức & Blog - PromptLib";
        
        require_once __DIR__ . '/../Views/news_list.php';
    }

    public function detail($slug) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT * FROM news WHERE slug=? AND is_active=1 LIMIT 1");
        $stmt->execute([$slug]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$news) {
            header("HTTP/1.0 404 Not Found");
            echo "News not found";
            return;
        }

        // Update view count
        $pdo->prepare("UPDATE news SET view_count=view_count+1 WHERE id=?")->execute([$news['id']]);

        $page_title = $news['title'];
        $page_desc = $news['description'];
        
        require_once __DIR__ . '/../Views/news_detail.php';
    }
}
?>
