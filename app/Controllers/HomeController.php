<?php
class HomeController {
    public function index() {
        global $pdo; // Use global PDO for now until we refactor DB to Singleton/Service
        
        // Logic from original index.php
        $modules = $pdo->query("SELECT * FROM home_modules WHERE is_active=1 AND (location='home' OR location='all') ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Page logic
        $page_slug = $_GET['page'] ?? 'home';
        $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug=? AND is_active=1 LIMIT 1");
        $stmt->execute([$page_slug]);
        $page = $stmt->fetch();

        if (!$page) {
            if ($page_slug !== 'home') {
                $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug='home' AND is_active=1 LIMIT 1");
                $stmt->execute();
                $page = $stmt->fetch();
            }
            if (!$page) die('Không tìm thấy trang chủ!');
        }

        $page_id = $page['id'];
        
        $modules_page = $pdo->prepare(
          "SELECT m.* FROM page_modules pm 
           JOIN modules m ON pm.module_id=m.id 
           WHERE pm.page_id=? AND m.is_active=1
           ORDER BY pm.sort_order ASC"
        );
        $modules_page->execute([$page_id]);
        
        // View
        require_once __DIR__ . '/../Views/home.php';
    }
}
?>
