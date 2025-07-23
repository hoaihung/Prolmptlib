<?php
// $pr, $canCopy, $isAdmin, $isOwner, $isTrash, $user_favorites
$isPendingList = ($_GET['pending'] ?? 0) == 1;
// Đảm bảo biến $isPremium đã được truyền vào nếu cần
$isPremium = $isPremium ?? (user_role() === 'premium');
?>
<div class="<?=$cardClass?> border p-0 rounded-2xl shadow-xl w-full max-w-2xl relative mx-auto my-8 flex flex-col" style="max-height:80vh;">
  <!-- Nút đóng luôn nổi -->
  <button class="absolute top-3 right-4 text-gray-400 text-3xl z-10" onclick="closeViewPrompt()">&times;</button>
  <div class="px-8 pt-8 pb-2 flex-1 flex flex-col min-h-0">
    <!-- Header -->
    <div class="mb-3 flex items-center gap-2">
      <span class="text-2xl font-bold"><?= htmlspecialchars($pr['title']) ?></span>
      <?php if ($pr['premium']): ?><span><?= inline_svg('lock', 'w-6 h-6 text-purple-500') ?></span><?php endif; ?>
      <?php if ($pr['console_enabled']): ?><span><?= inline_svg('console', 'w-6 h-6 text-green-500') ?></span><?php endif; ?>
      <?php if (!empty($pr['run_count'])): ?>
        <span class="inline-block text-xs text-gray-500"><?= $pr['run_count'] ?> lượt chạy</span>
      <?php endif; ?>

      <?php if (!empty($isTrash)): ?><span class="bg-red-200 text-red-700 px-2 py-0.5 rounded text-xs ml-1">Đã xóa</span><?php endif; ?>
      <?php if (!$pr['is_active'] && empty($isTrash)): ?><span class="bg-gray-300 text-gray-600 px-2 py-0.5 rounded text-xs ml-1">Vô hiệu</span><?php endif; ?>
      <?php
        $isFavorite = false;
        if (is_logged_in() && !empty($user_favorites)) {
            $isFavorite = in_array($pr['id'], $user_favorites);
        }
      ?>
      <button onclick="toggleFavorite(<?= $pr['id'] ?>, this)" class="favorite-btn">
        <?= $isFavorite ? inline_svg('heart-fill', 'w-6 h-6 text-red-500') : inline_svg('heart', 'w-6 h-6 text-gray-400') ?>
      </button>
    </div>
    <!-- Info -->
    <div class="mb-2 text-gray-500 flex gap-4 text-sm">
      <span><?= inline_svg('user', 'w-4 h-4 inline') ?> <?= htmlspecialchars($pr['author_name']) ?></span>
      <span><?= inline_svg('category', 'w-4 h-4 inline') ?> <?= htmlspecialchars($pr['category_name']) ?></span>
    </div>
    <div class="mb-2 flex flex-wrap gap-2">
      <?php foreach($pr['tags'] as $tag): ?>
        <span class="bg-blue-100 text-blue-700 px-3 py-0.5 rounded-full text-xs"><?= htmlspecialchars($tag) ?></span>
      <?php endforeach; ?>
    </div>
    <div class="mb-3 text-base text-gray-700"><?= nl2br(htmlspecialchars($pr['description'])) ?></div>
    <!-- Nội dung hoặc Banner Premium -->
    <?php if ($pr['premium'] && !$isAdmin && !$isPremium): ?>
      <div class="flex-1 overflow-y-auto">
        <div class="bg-purple-50 border border-purple-200 rounded-xl px-6 py-10 text-center text-purple-700 mb-5">
          <?= inline_svg('lock', 'w-12 h-12 mx-auto mb-2 text-purple-400') ?>
          <div class="text-xl font-semibold mb-1">Nội dung Premium</div>
          <div>Đăng nhập với tài khoản Premium để xem nội dung đầy đủ.</div>
        </div>
      </div>
    <?php else: ?>
      <div class="mb-3 flex-1 overflow-y-auto" style="min-height:100px; max-height:35vh;">
        <div class="font-bold mb-1">Nội dung Prompt</div>
        <div class="relative">
          <pre id="prompt-view-code" class="bg-gray-50 border rounded-xl p-4 text-sm overflow-x-auto whitespace-pre-wrap"><?= htmlspecialchars($pr['content']) ?></pre>
          
        </div>
      </div>
    <?php endif; ?>
  </div>
  <!-- Action group (cố định cuối modal, luôn thấy) -->
  <div class="flex flex-wrap gap-2 px-8 pb-6 pt-2 border-t">
    <?php if($canCopy): ?>
      <button onclick="copyPromptCode()" class="bg-gray-100 hover:bg-blue-100 text-gray-700 px-3 py-1 rounded-xl flex items-center gap-1 border"><?= inline_svg('copy', 'w-5 h-5') ?> Sao chép</button>
          
    <?php endif; ?>
    <button onclick="copyPromptShareLink(<?= $pr['id'] ?>)" class="bg-blue-100 text-blue-700 px-3 py-1 rounded-xl flex items-center gap-1 border"><?= inline_svg('share', 'w-5 h-5') ?> Chia sẻ</button>
    <?php if ($pr['console_enabled'] && ($isAdmin || $isPremium)): ?>
      <a href="<?=SITE_URL?>admin/console.php?prompt_id=<?= $pr['id'] ?>" class="bg-green-500 text-white px-4 py-2 rounded-xl ml-2">Run (Console)</a>
    <?php endif; ?>

    <?php if ($isAdmin || $isOwner): ?>
      <button onclick="openPromptModal(<?= $pr['id'] ?>)" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-xl flex items-center gap-2"><?= inline_svg('edit', 'w-5 h-5') ?> Sửa</button>
      <?php if (!$isTrash): ?>
        <?php if ($pr['is_active']): ?>
          <button onclick="deactivatePrompt(<?= $pr['id'] ?>)" class="bg-gray-400 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('deactive', 'w-4 h-4') ?> Vô hiệu</button>
        <?php else: ?>
          <button onclick="activatePrompt(<?= $pr['id'] ?>)" class="bg-green-500 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('active', 'w-4 h-4') ?> Kích hoạt</button>
          <button onclick="deletePrompt(<?= $pr['id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('trash', 'w-4 h-4') ?> Xóa</button>
        <?php endif; ?>
      <?php else: ?>
        <?php if ($isAdmin): ?>
          <button onclick="restorePrompt(<?= $pr['id'] ?>)" class="bg-green-500 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('active', 'w-4 h-4') ?> Khôi phục</button>
          <button onclick="hardDeletePrompt(<?= $pr['id'] ?>)" class="bg-red-600 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('trash', 'w-4 h-4') ?> Xóa vĩnh viễn</button>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($isPendingList) && $isAdmin): ?>
      <button onclick="approvePrompt(<?= $pr['id'] ?>)" class="bg-green-500 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('approved', 'w-4 h-4') ?> Duyệt</button>
      <button onclick="deletePrompt(<?= $pr['id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('reject', 'w-4 h-4') ?> Từ chối</button>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
          <?php if (!$pr['is_locked']): ?>
            <button onclick="lockPrompt(<?= $pr['id'] ?>)"  class="bg-yellow-400 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('lock', 'w-4 h-4 ') ?> </button>
          <?php else: ?>
            <button onclick="unlockPrompt(<?= $pr['id'] ?>)"  class="bg-blue-400 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('unlock', 'w-4 h-4') ?> </button>
          <?php endif; ?>
        <?php endif; ?>
  </div>
</div>
