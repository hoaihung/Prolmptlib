<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) { header('Location: login.php'); exit; }

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$news = ['title'=>'', 'slug'=>'', 'description'=>'', 'content'=>'', 'thumbnail'=>''];
$is_edit = false;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id=?");
    $stmt->execute([$id]);
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$news) die("News not found");
    $is_edit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    $thumbnail = $_POST['thumbnail'] ?? '';

    // Auto generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }

    if ($is_edit) {
        $stmt = $pdo->prepare("UPDATE news SET title=?, slug=?, description=?, content=?, thumbnail=? WHERE id=?");
        $stmt->execute([$title, $slug, $description, $content, $thumbnail, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO news (title, slug, description, content, thumbnail, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $slug, $description, $content, $thumbnail]);
    }
    header("Location: news.php");
    exit;
}

$title = $is_edit ? "Sửa Tin Tức" : "Thêm Tin Tức";
include 'layout.php';
?>

<div class="bg-white rounded-xl shadow p-6 max-w-4xl mx-auto">
    <h2 class="text-xl font-bold text-gray-800 mb-6"><?= $title ?></h2>
    
    <form method="POST">
        <div class="mb-4">
            <label class="block font-semibold mb-1">Tiêu đề</label>
            <input type="text" name="title" value="<?= htmlspecialchars($news['title']) ?>" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        
        <div class="mb-4">
            <label class="block font-semibold mb-1">Slug (URL)</label>
            <input type="text" name="slug" value="<?= htmlspecialchars($news['slug']) ?>" class="w-full border rounded px-3 py-2 bg-gray-50 text-sm font-mono" placeholder="Tu dong tao tu tieu de neu de trong">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Thumbnail URL</label>
            <input type="text" name="thumbnail" value="<?= htmlspecialchars($news['thumbnail']) ?>" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Mô tả ngắn</label>
            <textarea name="description" rows="3" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($news['description']) ?></textarea>
        </div>

        <div class="mb-6">
            <label class="block font-semibold mb-1">Nội dung (HTML/Markdown)</label>
            <!-- CodeMirror Container -->
            <div class="border rounded overflow-hidden">
                <textarea id="news-content" name="content"><?= htmlspecialchars($news['content']) ?></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="news.php" class="px-4 py-2 border rounded hover:bg-gray-50 text-gray-700">Hủy</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded font-bold hover:bg-blue-700">Lưu Tin Tức</button>
        </div>
    </form>
</div>

<!-- CodeMirror -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/htmlmixed/htmlmixed.min.js"></script>
<script>
    var editor = CodeMirror.fromTextArea(document.getElementById("news-content"), {
        mode: "htmlmixed",
        theme: "dracula",
        lineNumbers: true,
        lineWrapping: true,
        minHeight: "400px"
    });
    editor.setSize(null, 400);
</script>

<?php include 'layout_end.php'; ?>
