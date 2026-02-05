<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) { header('Location: login.php'); exit; }
$title = "Quản lý Tin tức";
include 'layout.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM news WHERE id=?")->execute([$id]);
    header("Location: news.php");
    exit;
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perpage = 10;
$offset = ($page - 1) * $perpage;

$total = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
$total_pages = ceil($total / $perpage);

$news = $pdo->query("SELECT * FROM news ORDER BY created_at DESC LIMIT $perpage OFFSET $offset")->fetchAll();
?>

<div class="bg-white rounded-xl shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Danh sách Tin tức</h2>
        <a href="news_edit.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">+ Thêm tin mới</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-3 font-semibold text-gray-600">ID</th>
                    <th class="p-3 font-semibold text-gray-600">Tiêu đề</th>
                    <th class="p-3 font-semibold text-gray-600">Slug</th>
                    <th class="p-3 font-semibold text-gray-600">Ngày tạo</th>
                    <th class="p-3 font-semibold text-gray-600 text-right">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($news as $item): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 text-gray-500">#<?= $item['id'] ?></td>
                    <td class="p-3 font-medium text-gray-900"><?= htmlspecialchars($item['title']) ?></td>
                    <td class="p-3 text-gray-500 text-sm"><?= htmlspecialchars($item['slug']) ?></td>
                    <td class="p-3 text-gray-500 text-sm"><?= date('d/m/Y', strtotime($item['created_at'])) ?></td>
                    <td class="p-3 text-right space-x-2">
                        <a href="<?=SITE_URL?>news/<?= $item['slug'] ?>" target="_blank" class="text-blue-600 hover:underline text-sm">Xem</a>
                        <a href="news_edit.php?id=<?= $item['id'] ?>" class="text-yellow-600 hover:underline text-sm">Sửa</a>
                        <a href="news.php?delete=<?= $item['id'] ?>" onclick="return confirm('Xóa tin này?')" class="text-red-600 hover:underline text-sm">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if($total_pages > 1): ?>
    <div class="mt-6 flex justify-center gap-2">
        <?php for($i=1; $i<=$total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="px-3 py-1 rounded border <?= $i==$page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'layout_end.php'; ?>
