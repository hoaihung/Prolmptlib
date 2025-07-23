<?php
require_once 'includes/loader.php';
require_once 'includes/svg.php';

$modules = $pdo->query("SELECT * FROM home_modules WHERE is_active=1 AND (location='home' OR location='all') ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<main class="max-w-6xl mx-auto mt-4 p-3">
  <?php 
    $page_slug = $_GET['page'] ?? 'home';
    $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug=? AND is_active=1 LIMIT 1");
    $stmt->execute([$page_slug]);
    $page = $stmt->fetch();

    if (!$page) {
        // Nếu không tìm thấy page, fallback về home
        if ($page_slug !== 'home') {
            $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug='home' AND is_active=1 LIMIT 1");
            $stmt->execute();
            $page = $stmt->fetch();
        }
        // Nếu vẫn không có, báo lỗi
        if (!$page) die('Không tìm thấy trang chủ!');
    }

    $page_id = $page['id'];
    
    if (!$page_id) die("Trang không tồn tại hoặc bị ẩn!");
    $modules = $pdo->prepare(
      "SELECT m.* FROM page_modules pm 
       JOIN modules m ON pm.module_id=m.id 
       WHERE pm.page_id=? AND m.is_active=1
       ORDER BY pm.sort_order ASC"
    );
    $modules->execute([$page_id]);
    foreach ($modules as $mod) {
        include 'components/module_'.$mod['type'].'.php'; // load từng module dạng component
    }

  ?>
</main>

</body>
<?php require_once 'includes/premium_modal.php'; ?>
<?php include 'includes/footer.php'; ?>
