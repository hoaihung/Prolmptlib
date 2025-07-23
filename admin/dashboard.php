<?php
require_once '../includes/loader.php';
if (!is_logged_in()) header('Location: login.php');
$title = "Dashboard Thống kê";
include 'layout.php';

// --- Các thống kê số lượng ---
$totalPrompts = $pdo->query("SELECT COUNT(*) FROM prompts WHERE is_deleted=0")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_deleted=0")->fetchColumn();
$totalViews = $pdo->query("SELECT SUM(view_count) FROM prompts WHERE is_deleted=0")->fetchColumn();
$totalFavorites = $pdo->query("SELECT COUNT(*) FROM prompt_favorites")->fetchColumn();
$totalRun = $pdo->query("SELECT SUM(console_count) FROM prompts WHERE is_deleted=0")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalTags = $pdo->query("SELECT COUNT(*) FROM tags")->fetchColumn();

// --- Top Prompt ---
$topPrompts = $pdo->query("SELECT p.*, u.name as author_name FROM prompts p LEFT JOIN users u ON p.author_id=u.id WHERE p.is_deleted=0 ORDER BY p.view_count DESC LIMIT 5")->fetchAll();

// --- Top Favorite ---
$topFavPrompts = $pdo->query("SELECT p.*, COUNT(f.id) as favs, u.name as author_name
FROM prompts p
LEFT JOIN prompt_favorites f ON p.id = f.prompt_id
LEFT JOIN users u ON p.author_id = u.id
WHERE p.is_deleted=0
GROUP BY p.id ORDER BY favs DESC LIMIT 5")->fetchAll();

// --- Top Run ---
/*$topRunPrompts = $pdo->query("SELECT p.*, u.name as author_name FROM prompts p LEFT JOIN users u ON p.author_id=u.id WHERE p.is_deleted=0 ORDER BY p.console_count DESC LIMIT 5")->fetchAll();

// --- Top User ---
$topUsers = $pdo->query("SELECT u.*, COUNT(p.id) as prompt_count FROM users u LEFT JOIN prompts p ON u.id = p.author_id WHERE u.is_deleted=0 GROUP BY u.id ORDER BY prompt_count DESC LIMIT 5")->fetchAll();*/

$recentViewedPrompts = $pdo->query("
    SELECT p.*, u.name AS author_name, MAX(v.viewed_at) as latest_viewed_at
    FROM prompt_views v
    JOIN prompts p ON v.prompt_id = p.id
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.is_deleted = 0
    GROUP BY p.id
    ORDER BY latest_viewed_at DESC
    LIMIT 10
")->fetchAll();

$mostViewedWeeklyPrompts = $pdo->query("
    SELECT p.*, u.name AS author_name, COUNT(v.id) AS view_count
    FROM prompt_views v
    JOIN prompts p ON v.prompt_id = p.id
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.is_deleted = 0 AND v.viewed_at >= NOW() - INTERVAL 7 DAY
    GROUP BY p.id
    ORDER BY view_count DESC
    LIMIT 10
")->fetchAll();



?>
<div class="max-w-6xl mx-auto mt-6">

    <h1 class="text-2xl font-bold mb-6">Dashboard Thống kê hệ thống</h1>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <?php
        include '../components/stat_box.php';
        stat_box('Prompt', $totalPrompts, 'prompt');
        stat_box('Lượt xem', number_format($totalViews), 'eye');
        stat_box('Yêu thích', number_format($totalFavorites), 'heart');
        stat_box('Lượt Run', number_format($totalRun), 'console');
        stat_box('User', $totalUsers, 'user');
        stat_box('Category', $totalCategories, 'category');
        stat_box('Tags', $totalTags, 'tag');
        ?>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <div>
            <h2 class="text-lg font-bold mb-3">Top Prompt nhiều lượt xem</h2>
            <?php include '../components/top_prompt.php';
            top_prompt($topPrompts, 'view_count', 'Lượt xem');
            ?>
        </div>
        <div>
            <h2 class="text-lg font-bold mb-3">Top Prompt yêu thích</h2>
            <?php top_prompt($topFavPrompts, 'favs', 'Yêu thích'); ?>
        </div>
    </div>
<!--     <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <div>
            <h2 class="text-lg font-bold mb-3">Top Prompt Run nhiều nhất</h2>
            <?php top_prompt($topRunPrompts, 'console_count', 'Run'); ?>
        </div>
        <div>
            <h2 class="text-lg font-bold mb-3">Top User nhiều prompt</h2>
            <?php include '../components/top_user.php';
            top_user($topUsers);
            ?>
        </div>
    </div> -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <h2 class="text-lg font-bold mb-3">Top Prompt Xem gần nhất</h2>
            <?php include '../components/top_lastprompt.php';
            top_lastprompt($recentViewedPrompts,'',''); 
            ?>
        </div>
        <div>
            <h2 class="text-lg font-bold mb-3">Top Prompt Xem nhiều trong tuần</h2>
            <?php include '../components/top_prompt.php';
            top_prompt($mostViewedWeeklyPrompts,'view_count','Lượt xem'); 
            ?>
        </div>
    </div>
</div>
<?php include 'layout_end.php'; ?>
