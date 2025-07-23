<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) die('No permission!');
$title = "Quản lý Thông báo";
include 'layout.php';

// Gửi thông báo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titleN = trim($_POST['title']);
    $contentN = trim($_POST['content']);
    $to_all = !empty($_POST['to_all']);
    $user_id = !$to_all ? intval($_POST['user_id']) : null;
    $type = $_POST['type'] ?? 'news';
    $link = $_POST['link'] ?? null;

    $pdo->prepare("INSERT INTO notifications (user_id, title, content, type, link) VALUES (?,?,?,?,?)")
        ->execute([$to_all ? null : $user_id, $titleN, $contentN, $type, $link]);
    echo '<div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">Đã gửi thông báo!</div>';
}

// Lấy danh sách user để gửi riêng
$users = $pdo->query("SELECT id, name, email FROM users WHERE is_deleted=0 AND is_active=1")->fetchAll();
?>
<h2 class="text-xl font-bold mb-4">Gửi Thông báo</h2>
<form method="post" class="mb-8 bg-white rounded p-5 shadow max-w-xl">
    <div class="mb-3">
        <label class="font-semibold">Tiêu đề</label>
        <input type="text" name="title" class="border rounded px-3 py-2 w-full" required>
    </div>
    <div class="mb-3">
        <label class="font-semibold">Nội dung</label>
        <textarea name="content" rows="4" class="border rounded px-3 py-2 w-full" required></textarea>
    </div>
    <div class="mb-3 flex gap-4">
        <label><input type="checkbox" name="to_all" id="to_all" onchange="toggleUserSelect(this)"> Gửi tất cả user</label>
        <select name="user_id" id="user_id_select" class="border rounded px-2 py-1">
            <option value="">-- Chọn user --</option>
            <?php foreach($users as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name'] . ' (' . $u['email'] . ')') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label>Loại thông báo: </label>
        <select name="type" class="border rounded px-2 py-1">
            <option value="news">Tin tức</option>
            <option value="system">Hệ thống</option>
            <option value="prompt">Prompt</option>
        </select>
    </div>
    <div class="mb-3">
        <label>Link (tuỳ chọn): </label>
        <input type="text" name="link" class="border rounded px-3 py-2 w-full">
    </div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Gửi</button>
</form>
<script>
function toggleUserSelect(cb) {
    document.getElementById('user_id_select').disabled = cb.checked;
}
</script>
<?php include 'layout_end.php'; ?>
