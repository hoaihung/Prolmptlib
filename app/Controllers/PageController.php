<?php
class PageController {
    public function show($slug) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug=? AND is_active=1");
        $stmt->execute([$slug]);
        $page = $stmt->fetch();
        
        if (!$page) {
            header("HTTP/1.0 404 Not Found");
            echo "Page not found";
            return;
        }
        
        $page_title = $page['title'] . " - PromptLib";
        
        // Simple view rendering
        require_once __DIR__ . '/../../includes/header.php';
        ?>
        <main class="max-w-4xl mx-auto mt-8 px-4 pb-12">
            <div class="bg-white rounded-2xl shadow-lg border p-8 md:p-12">
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-8"><?= htmlspecialchars($page['title']) ?></h1>
                <div class="prose max-w-none text-gray-800 leading-relaxed mb-12">
                    <?= $page['content'] ?>
                </div>

                <!-- Widgets Area -->
                <?php
                // Fetch assigned widgets
                $widgets = $pdo->query("SELECT m.* FROM modules m 
                                      JOIN page_modules pm ON m.id = pm.module_id 
                                      WHERE pm.page_id = {$page['id']} AND m.is_active = 1 
                                      ORDER BY pm.sort_order ASC, m.id DESC")->fetchAll();
                
                if (!empty($widgets)): ?>
                    <div class="border-t pt-8 grid gap-8">
                        <?php foreach($widgets as $w): ?>
                            <div class="widget-block">
                                <?php if($w['type'] === 'banner'): ?>
                                    <div class="bg-gray-100 rounded-xl p-4 text-center">
                                        <?= $w['content'] ?>
                                    </div>
                                <?php elseif($w['type'] === 'text'): ?>
                                    <div class="prose max-w-none">
                                        <?= $w['content'] ?>
                                    </div>
                                <?php else: // html ?>
                                    <?= $w['content'] ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        <?php
        require_once __DIR__ . '/../../includes/footer.php';
    }
}
?>
