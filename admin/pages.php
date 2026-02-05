<?php
require_once '../includes/loader.php';
require_once '../includes/svg.php';
if (!is_admin() && !is_root()) { header('Location: login.php'); exit; }

$title = "Quản lý Trang (Pages)";
include 'layout.php';

$pages = $pdo->query("SELECT * FROM pages ORDER BY created_at DESC")->fetchAll();
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-bold">Danh sách Trang</h2>
    <a href="pages_edit.php" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">+ Thêm Trang</a>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="p-4 font-semibold text-gray-600">ID</th>
                <th class="p-4 font-semibold text-gray-600">Tiêu đề</th>
                <th class="p-4 font-semibold text-gray-600">Slug (URL)</th>
                <th class="p-4 font-semibold text-gray-600">Trạng thái</th>
                <th class="p-4 font-semibold text-gray-600">Ngày tạo</th>
                <th class="p-4 font-semibold text-gray-600 text-right">Hành động</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php foreach($pages as $p): ?>
            <tr class="hover:bg-gray-50">
                <td class="p-4 text-gray-500">#<?= $p['id'] ?></td>
                <td class="p-4 font-medium text-gray-900"><?= htmlspecialchars($p['name']) ?></td>
                <td class="p-4 text-blue-600">/page/<?= htmlspecialchars($p['slug']) ?></td>
                <td class="p-4">
                    <?php if($p['is_active']): ?>
                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Active</span>
                    <?php else: ?>
                        <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800">Inactive</span>
                    <?php endif; ?>
                </td>
                <td class="p-4 text-gray-500 text-sm"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                <td class="p-4 text-right space-x-2">
                    <a href="<?=SITE_URL?>page/<?=$p['slug']?>" target="_blank" class="text-gray-500 hover:text-blue-600" title="Xem">
                        <?= inline_svg('eye', 'w-5 h-5') ?>
                    </a>
                    <a href="pages_edit.php?id=<?= $p['id'] ?>" class="text-blue-600 hover:text-blue-800" title="Sửa">
                        <?= inline_svg('edit', 'w-5 h-5') ?>
                    </a>
                    <a href="pages_delete.php?id=<?= $p['id'] ?>" onclick="return confirm('Xóa trang này?')" class="text-red-600 hover:text-red-800" title="Xóa">
                        <?= inline_svg('trash', 'w-5 h-5') ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($pages)): ?>
                <tr><td colspan="6" class="p-8 text-center text-gray-500">Chưa có trang nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'layout_end.php'; ?>
