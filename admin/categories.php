<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) { header('Location: dashboard.php'); exit; }


// Xử lý thêm/sửa/xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty ($_POST['id'])) {
        $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id=?")
            ->execute([$_POST['name'], $_POST['description'], $_POST['id']]);
    } else {
        $pdo->prepare("INSERT INTO categories (name, description) VALUES (?,?)")
            ->execute([$_POST['name'], $_POST['description']]);
    }
    header('Location: categories.php'); exit;
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$_GET['delete']]);
    header('Location: categories.php'); exit;
}
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$title = "Quản lý Danh mục Prompt";
include 'layout.php';
?>
<!-- Bảng list đẹp hơn -->
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-bold">Danh mục Prompt</h2>
    <button onclick="openCategoryModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl">+ Thêm mới</button>
</div>

<div class="overflow-x-auto rounded-2xl shadow mt-4">
<table class="min-w-full bg-white rounded-2xl overflow-hidden">
  <thead class="bg-gray-100">
    <tr>
      <th class="p-4 font-semibold text-gray-700 text-base">ID</th>
      <th class="p-4 font-semibold text-gray-700 text-base">Tên Category</th>
      <th class="p-4 font-semibold text-gray-700 text-base">Mô tả</th>
      <th class="p-4 font-semibold text-gray-700 text-base">Thao tác</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($categories as $cat): ?>
    <tr class="border-b last:border-0 hover:bg-blue-50 transition-all duration-150">
      <td class="p-4 text-center font-mono"><?= $cat['id'] ?></td>
      <td class="p-4 font-semibold text-gray-900"><?= htmlspecialchars($cat['name']) ?></td>
      <td class="p-4 text-gray-600"><?= htmlspecialchars($cat['description']) ?></td>
      <td class="p-4 flex gap-2">
        <button onclick="openCategoryModal(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>', '<?= htmlspecialchars(addslashes($cat['description'])) ?>')" class="flex items-center gap-1 bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1.5 rounded shadow text-xs transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3h3z"></path></svg>
            Sửa
        </button>
        <button onclick="deleteCategory(<?= $cat['id'] ?>)" class="flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded shadow text-xs transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            Xóa
        </button>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>


<!-- Modal Thêm/Sửa -->
<div id="category-modal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden">
  <form id="cat-form" method="post" class="bg-white p-8 rounded-xl w-full max-w-lg relative">
    <input type="hidden" name="id" id="cat-id">
    <button type="button" class="absolute top-2 right-3 text-gray-400 text-2xl" onclick="closeCategoryModal()">&times;</button>
    <h3 class="text-lg font-bold mb-4" id="cat-modal-title">Thêm mới Category</h3>
    <div class="mb-2">
      <label>Tên:</label>
      <input type="text" name="name" id="cat-name" required class="border px-3 py-2 rounded w-full">
    </div>
    <div class="mb-2">
      <label>Mô tả:</label>
      <textarea name="description" id="cat-desc" class="border px-3 py-2 rounded w-full"></textarea>
    </div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Lưu</button>
  </form>
</div>

<script>
function openCategoryModal(id = null, name = '', desc = '') {
    document.getElementById('category-modal').classList.remove('hidden');
    document.getElementById('cat-modal-title').innerText = id ? 'Sửa Category' : 'Thêm mới Category';
    document.getElementById('cat-id').value = id || '';
    document.getElementById('cat-name').value = name || '';
    document.getElementById('cat-desc').value = desc || '';
}
function closeCategoryModal() {
    document.getElementById('category-modal').classList.add('hidden');
}
function deleteCategory(id) {
    if (!confirm('Xóa category này?')) return;
    fetch('categories.php?delete=' + id, { method: 'GET' })
    .then(() => { showToast('Đã xóa category!','bg-green-600 text-white'); setTimeout(()=>location.reload(),900); })
    .catch(()=>showToast('Lỗi xóa category!','bg-red-600 text-white'));
}


</script>

<?php include 'layout_end.php'; ?>
