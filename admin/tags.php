<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) { header('Location: dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        $pdo->prepare("UPDATE tags SET name=? WHERE id=?")
            ->execute([$_POST['name'], $_POST['id']]);
    } else {
        $pdo->prepare("INSERT INTO tags (name) VALUES (?)")
            ->execute([$_POST['name']]);
    }
    header('Location: tags.php'); exit;
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM tags WHERE id=?")->execute([$_GET['delete']]);
    header('Location: tags.php'); exit;
}
$tags = $pdo->query("SELECT * FROM tags ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$title = "Quản lý Tags Prompt";
include 'layout.php';

?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-bold">Tags Prompt</h2>
    <button onclick="openTagModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl">+ Thêm mới</button>
</div>

<div class="overflow-x-auto rounded-2xl shadow mt-4">
<table class="min-w-full bg-white rounded-2xl overflow-hidden">
  <thead class="bg-gray-100">
    <tr>
      <th class="p-4 font-semibold text-gray-700 text-base">ID</th>
      <th class="p-4 font-semibold text-gray-700 text-base">Tên Tag</th>
      <th class="p-4 font-semibold text-gray-700 text-base">Thao tác</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($tags as $tag): ?>
    <tr class="border-b last:border-0 hover:bg-blue-50 transition-all duration-150">
      <td class="p-4 text-center font-mono"><?= $tag['id'] ?></td>
      <td class="p-4 font-semibold text-gray-900"><?= htmlspecialchars($tag['name']) ?></td>
      <td class="p-4 flex gap-2">
        <button onclick="openTagModal(<?= $tag['id'] ?>, '<?= htmlspecialchars(addslashes($tag['name'])) ?>')" class="flex items-center gap-1 bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1.5 rounded shadow text-xs transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 00-4-4l-8 8v3h3z"></path></svg>
            Sửa
        </button>
        <button onclick="deleteTag(<?= $tag['id'] ?>)" class="flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded shadow text-xs transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            Xóa
        </button>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<script>
function deleteTag(id) {
    if (!confirm('Xóa tag này?')) return;
    fetch('tags.php?delete=' + id, { method: 'GET' })
    .then(() => { showToast('Đã xóa tag!','bg-blue-600 text-white'); setTimeout(()=>location.reload(),900); })
    .catch(()=>showToast('Lỗi xóa tag!','bg-red-600'));
}
</script>


<!-- Modal Thêm/Sửa -->
<div id="tag-modal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden">
  <form method="post" class="bg-white p-8 rounded-xl w-full max-w-lg relative">
    <input type="hidden" name="id" id="tag-id">
    <button type="button" class="absolute top-2 right-3 text-gray-400 text-2xl" onclick="closeTagModal()">&times;</button>
    <h3 class="text-lg font-bold mb-4" id="tag-modal-title">Thêm mới tag</h3>
    <div class="mb-3">
      <label class="font-semibold">Tên Tag</label>
      <input type="text" name="name" id="tag-name" required class="border px-3 py-2 rounded w-full">
    </div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Lưu</button>
  </form>
</div>
<script>
function openTagModal(id='', name='') {
    document.getElementById('tag-modal').classList.remove('hidden');
    document.getElementById('tag-modal-title').innerText = id ? 'Sửa tag' : 'Thêm mới tag';
    document.getElementById('tag-id').value = id;
    document.getElementById('tag-name').value = name;
}
function closeTagModal() {
    document.getElementById('tag-modal').classList.add('hidden');
}
</script>
<?php include 'layout_end.php'; ?>
