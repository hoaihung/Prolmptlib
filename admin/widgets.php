<?php
require_once '../includes/loader.php';
require_once '../includes/svg.php';
if (!is_admin() && !is_root()) exit('No permission!');

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_widget') {
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title']);
        $type = $_POST['type'];
        $content = $_POST['content'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($id) {
            $pdo->prepare("UPDATE modules SET title=?, type=?, content=?, is_active=? WHERE id=?")
                ->execute([$title, $type, $content, $is_active, $id]);
        } else {
            $pdo->prepare("INSERT INTO modules (title, type, content, is_active) VALUES (?,?,?,?)")
                ->execute([$title, $type, $content, $is_active]);
        }
        header('Location: widgets.php'); exit;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM modules WHERE id=?")->execute([$id]);
    // Also delete relations
    $pdo->prepare("DELETE FROM page_modules WHERE module_id=?")->execute([$id]);
    header('Location: widgets.php'); exit;
}

$widgets = $pdo->query("SELECT * FROM modules ORDER BY id DESC")->fetchAll();

$title = "Quản lý Widgets";
include 'layout.php';
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-bold">Danh sách Widgets</h2>
    <button onclick="openWidgetModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">+ Thêm Widget</button>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="p-4 font-semibold text-gray-600">ID</th>
                <th class="p-4 font-semibold text-gray-600">Tiêu đề</th>
                <th class="p-4 font-semibold text-gray-600">Loại</th>
                <th class="p-4 font-semibold text-gray-600">Trạng thái</th>
                <th class="p-4 font-semibold text-gray-600 text-right">Hành động</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php foreach($widgets as $w): ?>
            <tr class="hover:bg-gray-50">
                <td class="p-4 text-gray-500">#<?= $w['id'] ?></td>
                <td class="p-4 font-medium text-gray-900"><?= htmlspecialchars($w['title']) ?></td>
                <td class="p-4">
                    <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs uppercase font-bold"><?= htmlspecialchars($w['type']) ?></span>
                </td>
                <td class="p-4">
                    <?php if($w['is_active']): ?>
                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Active</span>
                    <?php else: ?>
                        <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800">Inactive</span>
                    <?php endif; ?>
                </td>
                <td class="p-4 text-right space-x-2">
                    <button onclick='editWidget(<?= json_encode($w) ?>)' class="text-blue-600 hover:text-blue-800" title="Sửa">
                        <?= inline_svg('edit', 'w-5 h-5') ?>
                    </button>
                    <a href="widgets.php?delete=<?= $w['id'] ?>" onclick="return confirm('Xóa widget này?')" class="text-red-600 hover:text-red-800" title="Xóa">
                        <?= inline_svg('trash', 'w-5 h-5') ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($widgets)): ?>
                <tr><td colspan="5" class="p-8 text-center text-gray-500">Chưa có widget nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Widget -->
<div id="widget-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
    <div class="bg-white p-6 rounded-xl w-full max-w-lg relative">
        <button onclick="closeWidgetModal()" class="absolute top-2 right-3 text-gray-400 text-2xl">&times;</button>
        <h3 id="modal-title" class="text-lg font-bold mb-4">Thêm/Sửa Widget</h3>
        <form method="POST">
            <input type="hidden" name="action" value="save_widget">
            <input type="hidden" name="id" id="widget-id">
            
            <div class="mb-3">
                <label class="block font-semibold mb-1">Tiêu đề</label>
                <input type="text" name="title" id="widget-title" required class="w-full border rounded px-3 py-2">
            </div>
            
            <div class="mb-3">
                <label class="block font-semibold mb-1">Loại Widget</label>
                <select name="type" id="widget-type" class="w-full border rounded px-3 py-2">
                    <option value="html">HTML Custom</option>
                    <option value="banner">Banner Quảng cáo</option>
                    <option value="text">Văn bản đơn</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="block font-semibold mb-1">Nội dung (HTML)</label>
                <textarea name="content" id="widget-content" rows="6" class="w-full border rounded px-3 py-2 font-mono text-sm"></textarea>
            </div>

            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" id="widget-active" value="1" checked class="w-5 h-5 text-blue-600">
                    <span class="font-semibold">Kích hoạt</span>
                </label>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition">Lưu Widget</button>
            </div>
        </form>
    </div>
</div>

<script>
function openWidgetModal() {
    document.getElementById('widget-modal').classList.remove('hidden');
    document.getElementById('modal-title').innerText = "Thêm Widget";
    document.getElementById('widget-id').value = "";
    document.getElementById('widget-title').value = "";
    document.getElementById('widget-type').value = "html";
    document.getElementById('widget-content').value = "";
    document.getElementById('widget-active').checked = true;
}
function closeWidgetModal() {
    document.getElementById('widget-modal').classList.add('hidden');
}
function editWidget(w) {
    document.getElementById('widget-modal').classList.remove('hidden');
    document.getElementById('modal-title').innerText = "Sửa Widget";
    document.getElementById('widget-id').value = w.id;
    document.getElementById('widget-title').value = w.title;
    document.getElementById('widget-type').value = w.type;
    document.getElementById('widget-content').value = w.content;
    document.getElementById('widget-active').checked = w.is_active == 1;
}
</script>

<?php include 'layout_end.php'; ?>
