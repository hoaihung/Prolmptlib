<?php include __DIR__ . '/../../../includes/header.php'; ?>

<main class="max-w-6xl mx-auto mt-8 px-4 pb-12">
    <div class="flex items-center justify-between mb-6 border-b pb-4">
        <h1 class="text-3xl font-bold text-gray-900">Prompt của tôi</h1>
        <a href="<?=SITE_URL?>my-prompts/create" class="bg-blue-600 text-white px-4 py-2 rounded-xl font-semibold shadow hover:bg-blue-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tạo Prompt mới
        </a>
    </div>

    <?php if (empty($my_prompts)): ?>
        <div class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
            <div class="text-gray-400 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="text-xl font-medium text-gray-900 mb-2">Bạn chưa có prompt nào</h3>
            <p class="text-gray-500 mb-6">Hãy tạo prompt đầu tiên của bạn để chia sẻ hoặc lưu trữ cá nhân.</p>
            <a href="<?=SITE_URL?>my-prompts/create" class="text-blue-600 font-semibold hover:underline">Bắt đầu ngay &rarr;</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($my_prompts as $pr): ?>
                <div class="bg-white border rounded-xl shadow-sm hover:shadow-md transition p-5 flex flex-col">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg text-gray-900 line-clamp-1">
                            <a href="<?=SITE_URL?>prompt/<?= $pr['id'] ?>" class="hover:text-blue-600"><?= htmlspecialchars($pr['title']) ?></a>
                        </h3>
                        <?php if(!$pr['is_approved']): ?>
                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5 rounded-full">Chờ duyệt</span>
                        <?php elseif($pr['is_active']): ?>
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full">Active</span>
                        <?php else: ?>
                            <span class="bg-gray-100 text-gray-800 text-xs px-2 py-0.5 rounded-full">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-sm text-gray-500 mb-3"><?= htmlspecialchars($pr['category_name']) ?> • <?= date('d/m/Y', strtotime($pr['created_at'])) ?></div>
                    <p class="text-gray-600 text-sm line-clamp-2 mb-4 flex-1"><?= htmlspecialchars($pr['description']) ?></p>
                    
                    <div class="flex items-center gap-2 mt-auto pt-3 border-t">
                        <a href="<?=SITE_URL?>prompt/<?= $pr['id'] ?>" class="text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-lg text-sm font-medium transition">Xem</a>
                        <a href="<?=SITE_URL?>my-prompts/edit/<?= $pr['id'] ?>" class="text-yellow-600 hover:bg-yellow-50 px-3 py-1.5 rounded-lg text-sm font-medium transition">Sửa</a>
                        <a href="<?=SITE_URL?>my-prompts/delete/<?= $pr['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa prompt này?')" class="text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-lg text-sm font-medium transition">Xóa</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        </div>
        
        <!-- Pagination -->
        <div class="mt-8">
            <?php include __DIR__ . '/../../../components/pagination.php'; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
