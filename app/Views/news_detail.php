<?php include __DIR__ . '/../../includes/header.php'; ?>

<main class="max-w-4xl mx-auto mt-8 px-4 pb-12">
    <div class="mb-4">
        <a href="<?=SITE_URL?>news" class="text-blue-600 hover:underline">&larr; Quay lại tin tức</a>
    </div>

    <article class="bg-white rounded-2xl shadow-lg overflow-hidden border">
        <?php if($news['thumbnail']): ?>
            <img src="<?= htmlspecialchars($news['thumbnail']) ?>" alt="<?= htmlspecialchars($news['title']) ?>" class="w-full h-64 md:h-96 object-cover">
        <?php endif; ?>
        
        <div class="p-6 md:p-10">
            <h1 class="text-3xl md:text-4xl font-extrabold mb-4 text-gray-900 leading-tight"><?= htmlspecialchars($news['title']) ?></h1>
            <div class="flex items-center gap-4 text-gray-500 text-sm mb-8 border-b pb-4">
                <span><?= date('d/m/Y', strtotime($news['created_at'])) ?></span>
                <span>•</span>
                <span><?= $news['view_count'] ?> lượt xem</span>
            </div>
            
            <div class="prose prose-lg max-w-none text-gray-800">
                <?= $news['content'] // Content is assumed to be HTML safe or from trusted admin ?>
            </div>
        </div>
    </article>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
