<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) { header('Location: login.php'); exit; }
$title = "Quản lý Bình luận";
include 'layout.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM comments WHERE id=?")->execute([$id]);
    header("Location: comments.php");
    exit;
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perpage = 20;
$offset = ($page - 1) * $perpage;

$total = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$total_pages = ceil($total / $perpage);

$comments = $pdo->query("
    SELECT c.*, u.name as user_name, p.title as prompt_title 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    JOIN prompts p ON c.prompt_id = p.id 
    ORDER BY c.created_at DESC 
    LIMIT $perpage OFFSET $offset
")->fetchAll();
?>

<div class="bg-white rounded-xl shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Danh sách Bình luận</h2>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-3 font-semibold text-gray-600">ID</th>
                    <th class="p-3 font-semibold text-gray-600">Người dùng</th>
                    <th class="p-3 font-semibold text-gray-600">Prompt</th>
                    <th class="p-3 font-semibold text-gray-600">Nội dung</th>
                    <th class="p-3 font-semibold text-gray-600">Ngày tạo</th>
                    <th class="p-3 font-semibold text-gray-600 text-right">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($comments as $item): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 text-gray-500">#<?= $item['id'] ?></td>
                    <td class="p-3 font-medium text-gray-900"><?= htmlspecialchars($item['user_name']) ?></td>
                    <td class="p-3 text-blue-600 hover:underline text-sm truncate max-w-[150px]">
                        <a href="<?=SITE_URL?>prompt/<?= $item['prompt_id'] ?>" target="_blank"><?= htmlspecialchars($item['prompt_title']) ?></a>
                    </td>
                    <td class="p-3 text-gray-700 text-sm max-w-[300px] truncate" title="<?= htmlspecialchars($item['content']) ?>">
                        <?= htmlspecialchars($item['content']) ?>
                    </td>
                    <td class="p-3 text-gray-500 text-sm"><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></td>
                    <td class="p-3 text-right">
                        <a href="comments.php?delete=<?= $item['id'] ?>" onclick="return confirm('Xóa bình luận này?')" class="text-red-600 hover:underline text-sm">Xóa</a>
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
