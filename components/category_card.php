<?php
require_once __DIR__ . '/../includes/svg.php';
// $cat: array, $isAdmin: bool
?>
<div class="bg-white rounded-xl shadow border p-5 flex flex-col gap-2 min-h-[120px]">
  <div class="flex items-center gap-2">
    <?= inline_svg('category', 'w-6 h-6 text-pink-500') ?>
    <span class="font-semibold"><?= htmlspecialchars($cat['name']) ?></span>
  </div>
  <div class="text-gray-500 text-xs mb-2"><?= htmlspecialchars($cat['description'] ?? '') ?></div>
  <div class="flex gap-2 mt-auto">
    <button onclick="viewCategory(<?= $cat['id'] ?>)" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded flex items-center gap-2"><?= inline_svg('active', 'w-4 h-4') ?> Xem</button>
    <?php if ($isAdmin): ?>
      <a href="category_edit.php?id=<?= $cat['id'] ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded flex items-center gap-2"><?= inline_svg('edit', 'w-4 h-4') ?> Sửa</a>
    <?php endif; ?>
  </div>
</div>
