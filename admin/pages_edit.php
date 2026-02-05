<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) { header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
$page = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id=?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();
    if (!$page) die("Page not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $content = $_POST['content']; // HTML content
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Auto generate slug if empty
    if (empty($slug)) $slug = to_slug($title);

    if ($id) {
        $stmt = $pdo->prepare("UPDATE pages SET name=?, slug=?, content=?, is_active=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$title, $slug, $content, $is_active, $id]);
        $page_id = $id;
    } else {
        $stmt = $pdo->prepare("INSERT INTO pages (name, slug, content, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $slug, $content, $is_active]);
        $page_id = $pdo->lastInsertId();
    }

    // Save Widgets
    $pdo->prepare("DELETE FROM page_modules WHERE page_id=?")->execute([$page_id]);
    if (!empty($_POST['widgets'])) {
        $stmtW = $pdo->prepare("INSERT INTO page_modules (page_id, module_id, sort_order) VALUES (?, ?, 0)");
        foreach ($_POST['widgets'] as $wid) {
            $stmtW->execute([$page_id, $wid]);
        }
    }
    header("Location: pages.php");
    exit;
}

$title = $id ? "Sửa Trang" : "Thêm Trang Mới";
include 'layout.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold"><?= $title ?></h2>
        <a href="pages.php" class="text-gray-600 hover:underline">Quay lại</a>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <form method="POST">
            <div class="mb-4">
                <label class="block font-semibold mb-2">Tiêu đề trang</label>
                <input type="text" name="title" value="<?= htmlspecialchars($page['name']??'') ?>" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" onkeyup="generateSlug(this.value)">
            </div>
            
            <div class="mb-4">
                <label class="block font-semibold mb-2">Slug (URL)</label>
                <div class="flex items-center">
                    <span class="bg-gray-100 border border-r-0 rounded-l px-3 py-2 text-gray-500"><?= SITE_URL ?>page/</span>
                    <input type="text" name="slug" id="slug-input" value="<?= htmlspecialchars($page['slug']??'') ?>" class="flex-1 border rounded-r px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-2">Nội dung (HTML)</label>
                <!-- Use CodeMirror or TinyMCE. For now, simple textarea or CodeMirror HTML mode -->
                <div class="border rounded overflow-hidden">
                    <textarea name="content" id="page-content" rows="15" class="w-full p-4 font-mono text-sm"><?= htmlspecialchars($page['content']??'') ?></textarea>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" <?= ($page['is_active']??1) ? 'checked' : '' ?> class="w-5 h-5 text-blue-600">
                    <span class="font-semibold">Kích hoạt (Hiển thị công khai)</span>
                </label>
            </div>

            <!-- Widget Selection -->
            <div class="mb-6 border-t pt-6">
                <h3 class="font-bold text-lg mb-4">Widgets hiển thị trên trang này</h3>
                <div class="bg-gray-50 rounded-xl p-4 border grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php 
                    // Fetch all active widgets
                    $allWidgets = $pdo->query("SELECT * FROM modules WHERE is_active=1 ORDER BY id DESC")->fetchAll();
                    // Fetch assigned widgets for this page
                    $assignedIds = [];
                    if($id) {
                        $assignedIds = $pdo->query("SELECT module_id FROM page_modules WHERE page_id=$id")->fetchAll(PDO::FETCH_COLUMN);
                    }
                    
                    if(empty($allWidgets)): ?>
                        <div class="text-gray-500 col-span-2">Chưa có widget nào. <a href="widgets.php" class="text-blue-600 hover:underline">Tạo widget ngay</a></div>
                    <?php else: foreach($allWidgets as $w): ?>
                        <label class="flex items-center gap-3 p-3 bg-white border rounded-lg cursor-pointer hover:shadow-sm transition">
                            <input type="checkbox" name="widgets[]" value="<?= $w['id'] ?>" <?= in_array($w['id'], $assignedIds) ? 'checked' : '' ?> class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                            <div>
                                <div class="font-semibold text-gray-800"><?= htmlspecialchars($w['title']) ?></div>
                                <div class="text-xs text-gray-500 uppercase"><?= htmlspecialchars($w['type']) ?></div>
                            </div>
                        </label>
                    <?php endforeach; endif; ?>
                </div>
                <p class="text-sm text-gray-500 mt-2">Các widget được chọn sẽ hiển thị ở cuối nội dung trang.</p>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition">Lưu Trang</button>
            </div>
        </form>
    </div>
</div>

<!-- CodeMirror for HTML editing -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
    var editor = CodeMirror.fromTextArea(document.getElementById("page-content"), {
        mode: "htmlmixed",
        theme: "dracula",
        lineNumbers: true,
        lineWrapping: true,
        minHeight: "400px"
    });
    editor.setSize(null, 400);

    function generateSlug(val) {
        // Simple slug generator
        if(document.getElementById('slug-input').value && document.getElementById('slug-input').value !== toSlug(val)) return; // Don't overwrite if user edited
        document.getElementById('slug-input').value = toSlug(val);
    }
    
    function toSlug(str) {
        str = str.toLowerCase();
        str = str.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
        str = str.replace(/[đĐ]/g, "d");
        str = str.replace(/([^0-9a-z-\s])/g, '');
        str = str.replace(/(\s+)/g, '-');
        str = str.replace(/^-+|-+$/g, '');
        return str;
    }
</script>

<?php include 'layout_end.php'; ?>
