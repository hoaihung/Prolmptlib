<?php

// Lấy danh sách page
$pages = $pdo->query("SELECT * FROM pages ORDER BY id")->fetchAll();
$page_id = intval($_GET['page_id'] ?? 1); // Mặc định home
$page = null; foreach($pages as $p) if ($p['id']==$page_id) $page = $p;

// Lấy tất cả module
$modules = $pdo->query("SELECT * FROM modules ORDER BY id")->fetchAll();

// Lấy module đã gán cho page này
$pm = $pdo->prepare("SELECT * FROM page_modules WHERE page_id=?");
$pm->execute([$page_id]);
$page_mods = [];
while ($r = $pm->fetch()) $page_mods[$r['module_id']] = $r['sort_order'];


?>

<div class="mb-5 flex gap-2 items-end">
    <form method="get" class="flex gap-2">
        <input type="hidden" name="tab" value="page_modules">
        <label class="font-bold">Chọn page:</label>
        <select name="page_id" class="border rounded px-2 py-1" onchange="this.form.submit()">
            <?php foreach($pages as $p): ?>
              <option value="<?= $p['id'] ?>" <?= $p['id']==$page_id?'selected':'' ?>><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach ?>
        </select>
    </form>
</div>
<form method="post" class="mb-10">
    <input type="hidden" name="form_action" value="page_modules">
    <input type="hidden" name="page_id" value="<?= $page_id ?>">
    <table class="min-w-full bg-white rounded-xl shadow text-base">
        <thead><tr>
            <th>Chọn</th><th>Module</th><th>Loại</th><th>Thứ tự</th>
        </tr></thead>
        <tbody>
        <?php foreach($modules as $mod): ?>
        <tr>
            <td><input type="checkbox" name="modules[]" value="<?= $mod['id'] ?>" <?= isset($page_mods[$mod['id']])?'checked':'' ?>></td>
            <td><?= htmlspecialchars($mod['title']) ?></td>
            <td><?= htmlspecialchars($mod['type']) ?></td>
            <td><input type="number" name="sort_order[<?= $mod['id'] ?>]" value="<?= $page_mods[$mod['id']] ?? '' ?>" class="w-16 border rounded"></td>
        </tr>
        <?php endforeach ?>
        </tbody>
    </table>
    <button class="bg-blue-600 text-white px-4 py-2 rounded mt-3">Lưu vị trí & module</button>
</form>

