<?php

$modules = $pdo->query("SELECT * FROM modules ORDER BY id DESC")->fetchAll();
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-bold">Danh sách Modules</h2>
    <button onclick="openModuleModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl">+ Thêm Module</button>
</div>
<table class="min-w-full bg-white rounded-xl shadow mb-6">
<thead class="bg-gray-100"><tr>
    <th>ID</th><th>Tiêu đề</th><th>Loại</th><th>Trạng thái</th><th>Thao tác</th>
</tr></thead>
<tbody>
<?php foreach($modules as $mod): ?>
<tr>
    <td><?= $mod['id'] ?></td>
    <td><?= htmlspecialchars($mod['title']) ?></td>
    <td><?= htmlspecialchars($mod['type']) ?></td>
    <td><?= $mod['is_active'] ? '<span class="text-green-600">Hiển thị</span>' : '<span class="text-gray-400">Ẩn</span>' ?></td>
    <td>
        <button onclick="openModuleModal(<?= $mod['id'] ?>)" class="bg-yellow-400 text-white px-3 py-1 rounded">Sửa</button>
        <a href="?tab=modules&delete=<?= $mod['id'] ?>" onclick="return confirm('Xóa?')" class="bg-red-500 text-white px-3 py-1 rounded ml-2">Xóa</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- Modal thêm/sửa module -->
<div id="module-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
  <form method="post" class="bg-white p-8 rounded-xl w-full max-w-lg relative">
    <input type="hidden" name="form_action" value="modules">
    <input type="hidden" name="id" id="module-id">
    <button type="button" class="absolute top-2 right-3 text-gray-400 text-2xl" onclick="closeModuleModal()">&times;</button>
    <h3 class="text-lg font-bold mb-4" id="module-modal-title">Thêm/Sửa Module</h3>
    <div class="mb-2"><label>Tiêu đề:</label>
      <input type="text" name="title" id="module-title" required class="border px-3 py-2 rounded w-full">
    </div>
    <div class="mb-2"><label>Loại:</label>
      <select name="type" id="module-type" class="border px-3 py-2 rounded w-full">
        <option value="text">Text</option>
        <option value="statistic">Statistic</option>
        <option value="feature">Feature</option>
        <option value="banner">Banner</option>
        <option value="ads">Ads</option>
      </select>
    </div>
      <div>
        <label for="sample-box" class="text-xs font-semibold">Sample code cho <span id="sample-type"></span>:</label>
        <textarea id="sample-box" class="bg-gray-100 p-2 rounded w-full text-xs font-mono" rows="4" readonly></textarea>
      </div>
      <textarea name="content" id="module-content" rows="5" class="border px-3 py-2 rounded w-full"></textarea>

    <div class="mb-2">
      <input type="hidden" name="is_active" value="0">
      <label><input type="checkbox" name="is_active" value="1" id="module-active" checked> Hiển thị</label>
    </div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Lưu</button>
  </form>
</div>
<script>
function openModuleModal(id) {
    document.getElementById('module-modal').classList.remove('hidden');
    document.getElementById('module-modal-title').innerText = id ? 'Sửa Module' : 'Thêm Module';
    document.getElementById('module-id').value = '';
    document.getElementById('module-title').value = '';
    document.getElementById('module-type').value = 'text';
    document.getElementById('module-content').value = '';
    document.getElementById('module-active').checked = true;
    if (id) {
        fetch('../api/module_api.php?action=get&id=' + id)
          .then(res => res.json())
          .then(mod => {
             document.getElementById('module-id').value = mod.id;
             document.getElementById('module-title').value = mod.title;
             document.getElementById('module-type').value = mod.type;
             document.getElementById('module-content').value = mod.content;
             document.getElementById('module-active').checked = mod.is_active==1;
          });
    }
}
function closeModuleModal() {
    document.getElementById('module-modal').classList.add('hidden');
}
const sampleByType = {
  text: `<h2>Chào mừng đến PromptLib</h2>\n<p>Mô tả ngắn gọn...</p>`,
  statistic: `[\n  {"title":"10K+","desc":"Người dùng"},\n  {"title":"50K+","desc":"Prompts"}\n]`,
  feature: `[\n  {"icon":"<svg width='24' height='24' ...></svg>","title":"Nhanh chóng","desc":"Quản lý Prompt siêu tốc"},\n  {"icon":"<svg width='24' height='24' ...></svg>","title":"Bảo mật","desc":"Dữ liệu an toàn"}\n]`,
  banner: `<img src='banner.jpg' alt='Banner' />`,
  ads: `<a href='https://...' target='_blank'><img src='ads.jpg'></a>`
};

function updateSampleBox() {
  const type = document.getElementById('module-type').value;
  document.getElementById('sample-type').textContent = type;
  document.getElementById('sample-box').value = sampleByType[type] || '';
}

// Gọi lần đầu và khi đổi type
document.getElementById('module-type').addEventListener('change', updateSampleBox);
updateSampleBox();

</script>

