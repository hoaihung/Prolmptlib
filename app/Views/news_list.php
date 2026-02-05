<?php include __DIR__ . '/../../includes/header.php'; ?>

<main class="max-w-6xl mx-auto mt-8 px-4 pb-12">
    <h1 class="text-3xl font-bold mb-6 text-gray-900 border-b pb-4">Tin tức & Blog</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach($news_list as $item): ?>
        <div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden border">
            <?php if($item['thumbnail']): ?>
                <img src="<?= htmlspecialchars($item['thumbnail']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-full h-48 object-cover">
            <?php else: ?>
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400">No Image</div>
            <?php endif; ?>
            
            <div class="p-5">
                <div class="text-sm text-gray-500 mb-2"><?= date('d/m/Y', strtotime($item['created_at'])) ?></div>
                <h2 class="text-xl font-bold mb-2 leading-tight">
                    <a href="<?=SITE_URL?>news/<?= $item['slug'] ?>" class="hover:text-blue-600 transition"><?= htmlspecialchars($item['title']) ?></a>
                </h2>
                <p class="text-gray-600 line-clamp-3 mb-4"><?= htmlspecialchars($item['description']) ?></p>
                <a href="<?=SITE_URL?>news/<?= $item['slug'] ?>" class="text-blue-600 font-semibold hover:underline">Đọc tiếp &rarr;</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
