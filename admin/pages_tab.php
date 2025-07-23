<?php

$pages = $pdo->query("SELECT * FROM pages ORDER BY id")->fetchAll();
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-bold">Danh sách Page</h2>
    <button onclick="openPageModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl">+ Thêm Page</button>
</div>
<table class="min-w-full bg-white rounded-xl shadow text-base">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên Page</th>
            <th>Slug</th>
            <th>Mô tả</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($pages as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['slug']) ?></td>
            <td><?= htmlspecialchars($p['description']) ?></td>
            <td>
                <span class="px-2 py-1 rounded <?= $p['is_active']?'bg-green-100 text-green-700':'bg-gray-200 text-gray-500' ?>">
                <?= $p['is_active']?'Kích hoạt':'Ẩn' ?>
                </span>
            </td>
            <td>
                <button class="bg-yellow-400 text-white px-3 py-1 rounded" onclick="openPageModal(<?= $p['id'] ?>,'<?= htmlspecialchars(addslashes($p['name'])) ?>','<?= htmlspecialchars(addslashes($p['slug'])) ?>','<?= htmlspecialchars(addslashes($p['description'])) ?>',<?= $p['is_active'] ?>)">Sửa</button>
                <a href="?tab=pages&delete=<?= $p['id'] ?>" class="bg-red-500 text-white px-3 py-1 rounded ml-2" onclick="return confirm('Xóa page này?')">Xóa</a>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<!-- Modal Thêm/Sửa Page -->
<div id="page-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <form method="post" class="bg-white p-8 rounded-xl w-full max-w-lg relative">
        <input type="hidden" name="form_action" value="pages">
        <input type="hidden" name="id" id="page-id">
        <button type="button" class="absolute top-2 right-3 text-gray-400 text-2xl" onclick="closePageModal()">&times;</button>
        <h3 class="text-lg font-bold mb-4" id="page-modal-title">Thêm mới Page</h3>
        <div class="mb-2">
            <label>Tên Page</label>
            <input type="text" name="name" id="page-name" class="border px-3 py-2 rounded w-full" required>
        </div>
        <div class="mb-2">
            <label>Slug (không dấu, vd: about, contact...)</label>
            <input type="text" name="slug" id="page-slug" class="border px-3 py-2 rounded w-full" required>
        </div>
        <div class="mb-2">
            <label>Mô tả</label>
            <textarea name="description" id="page-desc" class="border px-3 py-2 rounded w-full"></textarea>
        </div>
        <div class="mb-2">
            <label><input type="checkbox" name="is_active" id="page-active" checked> Kích hoạt</label>
        </div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Lưu</button>
    </form>
</div>
<script>
function openPageModal(id='', name='', slug='', desc='', active=1) {
    document.getElementById('page-modal').classList.remove('hidden');
    document.getElementById('page-modal-title').innerText = id ? 'Sửa Page' : 'Thêm Page';
    document.getElementById('page-id').value = id||'';
    document.getElementById('page-name').value = name||'';
    document.getElementById('page-slug').value = slug||'';
    document.getElementById('page-desc').value = desc||'';
    document.getElementById('page-active').checked = !!active;
}
function closePageModal() {
    document.getElementById('page-modal').classList.add('hidden');
}
</script>

