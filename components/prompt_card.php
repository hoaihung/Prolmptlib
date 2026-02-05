<?php
require_once __DIR__ . '/../includes/svg.php';
// $pr: prompt, $isAdmin: bool, $isPremium: bool, $isOwner: bool, $isTrash: bool
// Chỉ guest và user thường mới cần cảnh báo
$needPremium = false; // Disable Premium Lock
?>

<div class="<?=$cardClass?> border rounded-2xl shadow-lg p-5 flex flex-col gap-2 min-h-[240px] relative hover:shadow-2xl transition group" id="card-prompt-<?=$pr['id']?>">
    <?php if(!empty($pr['thumbnail'])): ?>
    <a href="<?=SITE_URL?>prompt/<?=$pr['id']?>" class="block mb-3 -mx-5 -mt-5 relative h-48 overflow-hidden rounded-t-xl group-hover:opacity-90 transition">
        <img src="<?=SITE_URL . $pr['thumbnail']?>" alt="<?=htmlspecialchars($pr['title'])?>" class="w-full h-full object-cover">
    </a>
    <?php endif; ?>

    <div class="flex items-center gap-2 mb-1">
        <a href="<?=SITE_URL?>prompt/<?= $pr['id'] ?>" <?= ($isAdmin??false) ? 'target="_blank"' : '' ?> class="font-bold text-lg text-gray-900 group-hover:text-blue-700 hover:underline"><?= htmlspecialchars($pr['title']) ?></a>
        <?php if ($pr['is_approved']==0): ?>
            <span class="bg-gray-100 text-black-600 px-2 py-0.5 rounded-full text-xs">Chờ duyệt</span>
        <?php endif; ?>

        <?php if ($pr['premium']): ?>
            <span class="text-purple-500 ml-1" title="Premium"><?= inline_svg('premium', 'w-5 h-5 inline') ?></span>
        <?php endif; ?>

        <?php if (!empty($isTrash)): ?>
            <span class="bg-red-200 text-red-700 px-2 py-0.5 rounded text-xs ml-1">Đã xóa</span>
        <?php elseif (!$pr['is_active']): ?>
            <span class="bg-gray-300 text-gray-600 px-2 py-0.5 rounded text-xs ml-1">Vô hiệu</span>
        <?php endif; ?>
        <?php
        $isFavorite = false;
        if (is_logged_in() && !empty($user_favorites)) {
            $isFavorite = in_array($pr['id'], $user_favorites);
        }
        ?>
        <?php if (!$isPublic){ ?>
        <button onclick="toggleFavorite(<?= $pr['id'] ?>, this)" class="favorite-btn">
            <?= $isFavorite ? inline_svg('heart-fill', 'w-6 h-6 text-red-500') : inline_svg('heart', 'w-6 h-6 text-gray-400') ?>
        </button>
        <?php } ?>

    </div>

    
    <div class="flex flex-wrap gap-2 mb-1">
        <span class="bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full text-xs"><?= htmlspecialchars($pr['category_name']) ?></span>
        <?php
        // Một số trang có thể không truyền biến tags cho prompt, tránh lỗi undefined key
        foreach (($pr['tags'] ?? []) as $tag): 
            // Support both old format (string) and new format (array) for backward compatibility during migration
            $tagName = is_array($tag) ? $tag['name'] : $tag;
            $tagId = is_array($tag) ? $tag['id'] : 0; // If 0, link might not work perfectly but prevents crash
        ?>
            <?php if($tagId): ?>
                <a href="<?=SITE_URL?>prompts.php?tag=<?= $tagId ?>" class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs hover:bg-gray-200 transition"><?= htmlspecialchars($tagName) ?></a>
            <?php else: ?>
                <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs"><?= htmlspecialchars($tagName) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="text-gray-700 text-base mb-1 line-clamp-2"><?= htmlspecialchars($pr['description']) ?></div>
    <?php if($pr['author_id']!=1): ?>
    <div class="flex items-center gap-2 text-xs text-gray-400 mt-auto">
        <span><?= inline_svg('user', 'w-4 h-4 inline') ?> <?= htmlspecialchars($pr['author_name']) ?></span>
    </div>
    <?php endif; ?>
    <?php if ($needPremium): ?>
    <div class="bg-purple-50 border border-purple-200 rounded-xl mt-2 px-3 py-3 text-purple-700 flex flex-col items-center text-sm">
        <?= inline_svg('lock', 'w-5 h-5 mr-1 inline') ?>
        <b>Chỉ dành cho Premium</b>
        <button onclick="showPremiumInfo()" class="mt-2 text-purple-700 font-semibold flex items-center"><?= inline_svg('detail', 'w-5 h-5 mr-1') ?> Xem thông tin</button>
    </div>
    <?php else: ?>
    <div class="flex flex-wrap gap-2 mt-3">
        <a href="<?=SITE_URL?>prompt/<?= $pr['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-semibold flex items-center gap-2"><?= inline_svg('detail', 'w-5 h-5') ?> Xem</a>
        <?php if (($isAdmin || ($isPremium && $isOwner)) && !($isPublic??false)): ?>
            <?php if ($pr['is_locked'] && !$isAdmin): ?>
                <button class="bg-yellow-300 text-yellow-900 px-4 py-2 rounded-xl font-semibold cursor-not-allowed" disabled><?= inline_svg('lock', 'w-5 h-5') ?> </button>
            <?php else: ?>
                <?php if($isAdmin): ?>
                    <a href="<?=SITE_URL?>admin/prompts_edit.php?id=<?= $pr['id'] ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-xl flex items-center gap-2"><?= inline_svg('edit', 'w-5 h-5') ?> </a>
                <?php else: ?>
                    <a href="<?=SITE_URL?>my-prompts/edit/<?= $pr['id'] ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-xl flex items-center gap-2"><?= inline_svg('edit', 'w-5 h-5') ?> </a>
                <?php endif; ?>
                <?php if (!$isTrash): ?>
                    <?php if ($pr['is_active']): ?>
                        <button onclick="deactivatePrompt(<?= $pr['id'] ?>)" class="bg-gray-400 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('deactive', 'w-4 h-4') ?> </button>
                    <?php else: ?>
                        <button onclick="activatePrompt(<?= $pr['id'] ?>)" class="bg-green-500 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('active', 'w-4 h-4') ?> </button>
                        <button onclick="deletePrompt(<?= $pr['id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('trash', 'w-4 h-4') ?> </button>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($isAdmin): ?>
                        <button onclick="restorePrompt(<?= $pr['id'] ?>)" class="bg-green-500 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('restore', 'w-4 h-4') ?> </button>
                        <button onclick="hardDeletePrompt(<?= $pr['id'] ?>)" class="bg-red-600 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('deleted', 'w-4 h-4') ?> </button>
                    <?php elseif ($isPremium): ?>
                        <button onclick="restorePrompt(<?= $pr['id'] ?>)" class="bg-green-500 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('restore', 'w-4 h-4') ?> Restore</button>
                    <?php endif; ?>  
                <?php endif; ?>
            <?php endif; ?>    
        <?php endif; ?>
        <?php if (!empty($isPendingList) && $isAdmin): ?>
            <button onclick="approvePrompt(<?= $pr['id'] ?>)" class="bg-green-500 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('approved', 'w-4 h-4') ?> </button>
            <button onclick="deletePrompt(<?= $pr['id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('reject', 'w-4 h-4') ?> </button>
        <?php endif; ?>
        <?php if ($isAdmin && !$isPublic): ?>
          <?php if (!$pr['is_locked']): ?>
            <button onclick="lockPrompt(<?= $pr['id'] ?>)"  class="bg-yellow-400 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('lock', 'w-4 h-4 ') ?> </button>
          <?php else: ?>
            <button onclick="unlockPrompt(<?= $pr['id'] ?>)"  class="bg-blue-400 text-white px-3 py-1 rounded-xl flex items-center gap-2"><?= inline_svg('unlock', 'w-4 h-4') ?> </button>
          <?php endif; ?>
        <?php endif; ?>


    </div>
    <?php endif; ?>
</div>
