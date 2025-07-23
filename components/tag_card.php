<?php
require_once __DIR__ . '/../includes/svg.php';
// $tag: array, $isAdmin: bool
?>
<div class="bg-white rounded-xl shadow border p-5 flex flex-col gap-2 min-h-[80px]">
  <div class="flex items-center gap-2">
    <?= inline_svg('tag', 'w-6 h-6 text-blue-400') ?>
    <span class="font-semibold"><?= htmlspecialchars($tag['name']) ?></span>
  </div>
  <div class="flex gap-2 mt-auto">
    <button onclick="viewTag(<?= $tag['id'] ?>)" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded flex items-center gap-2"><?= inline_svg('eye', 'w-4 h-4') ?> Xem</button>
    <?php if ($isAdmin): ?>
      <a href="tag_edit.php?id=<?= $tag['id'] ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded flex items-center gap-2"><?= inline_svg('edit', 'w-4 h-4') ?> Sửa</a>
    <?php endif; ?>
  </div>
</div>
