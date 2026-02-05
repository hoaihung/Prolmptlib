<?php 
// $prompt is passed from Controller
$pr = $prompt;
$page_title = $pr['title'];
$page_desc = mb_substr($pr['description'], 0, 150) . '...';
// $og_image could be a default or generated.

include __DIR__ . '/../../includes/header.php'; 
include __DIR__ . '/../../includes/category_colors.php';
$catColors = cat_color();
$cardClass = $catColors[$pr['category_id']] ?? 'bg-white border-gray-200 text-gray-800';
?>

<main class="max-w-4xl mx-auto mt-8 px-4 pb-12">
    <!-- Breadcrumb or Back link -->
    <div class="mb-4">
        <a href="<?=SITE_URL?>prompts.php" class="text-blue-600 hover:underline flex items-center gap-1">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Quay lại thư viện
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border overflow-hidden">
        <div class="p-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row gap-6 mb-8">
                <?php if(!empty($pr['thumbnail'])): ?>
                <div class="w-full md:w-1/3 flex-shrink-0">
                    <img src="<?=SITE_URL . $pr['thumbnail']?>" class="w-full rounded-xl shadow-md object-contain bg-gray-50" style="max-height: 500px;">
                </div>
                <?php endif; ?>
                
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <?= htmlspecialchars($pr['category_name'] ?? 'Uncategorized') ?>
                        </span>
                        <?php if($pr['premium']): ?>
                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 flex items-center gap-1">
                                <?= inline_svg('star', 'w-4 h-4') ?> Premium
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 leading-tight">
                        <?= htmlspecialchars($pr['title']) ?>
                    </h1>
                    
                    <p class="text-gray-600 text-lg mb-6 leading-relaxed">
                        <?= nl2br(htmlspecialchars($pr['description'])) ?>
                    </p>

                    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-500 border-t pt-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600">
                                <?= strtoupper(substr($pr['author_name']??'U', 0, 1)) ?>
                            </div>
                            <span><?= htmlspecialchars($pr['author_name']??'Unknown') ?></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <?= inline_svg('calendar', 'w-4 h-4') ?>
                            <span><?= date('d/m/Y', strtotime($pr['created_at'])) ?></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <?= inline_svg('eye', 'w-4 h-4') ?>
                            <span><?= number_format($pr['view_count']) ?> views</span>
                        </div>
                        
                        <!-- Favorite Button -->
                        <?php
                            $isFavorite = false;
                            if (!empty($user_favorites)) {
                                $isFavorite = in_array($pr['id'], $user_favorites);
                            }
                        ?>
                        <button onclick="toggleFavorite(<?= $pr['id'] ?>, this)" class="ml-auto flex items-center gap-1 text-gray-500 hover:text-red-500 transition" title="Lưu prompt">
                            <?= $isFavorite ? inline_svg('heart-fill', 'w-6 h-6 text-red-500') : inline_svg('heart', 'w-6 h-6') ?>
                            <span>Lưu</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tags -->
            <?php if(!empty($prompt_tags)): ?>
            <div class="mb-8 flex flex-wrap gap-2">
                <?php foreach($prompt_tags as $tag): ?>
                    <a href="<?=SITE_URL?>prompts.php?tag=<?= $tag['id'] ?>" class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-sm font-medium hover:bg-gray-200 transition">#<?= htmlspecialchars($tag['name']) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Main Content (Code) -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold text-gray-900">Nội dung Prompt</h3>
                    <button onclick="copyPromptCode()" class="text-blue-600 text-sm font-medium hover:underline flex items-center gap-1">
                        <?= inline_svg('copy', 'w-4 h-4') ?> Copy
                    </button>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 overflow-auto max-h-[500px] relative group">
                    <pre id="prompt-view-code" class="text-gray-800 font-mono text-sm whitespace-pre-wrap"><?= htmlspecialchars($pr['content']) ?></pre>
                </div>
            </div>

            <!-- Admin Content (SEO) -->
            <?php if(!empty($pr['admin_content'])): ?>
            <div class="mt-8 pt-8 border-t">
                <h3 class="font-bold text-gray-900 text-xl mb-4">Thông tin bổ sung</h3>
                <div class="prose max-w-none text-gray-700">
                    <?= nl2br($pr['admin_content']) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Share -->
            <div class="mt-8 pt-8 border-t flex items-center justify-between">
                <div class="font-semibold text-gray-500">Chia sẻ prompt này:</div>
                <div class="flex gap-3">
                     <button onclick="copyPromptShareLink(<?= $pr['id'] ?>)" class="bg-white text-gray-700 px-4 py-2 rounded-xl flex items-center gap-2 border shadow-sm hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 transition font-medium">
                        <?= inline_svg('share', 'w-5 h-5') ?> Copy Link
                     </button>
                     <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL.'prompt/'.$pr['id']) ?>" target="_blank" class="bg-[#1877f2] text-white px-4 py-2 rounded-xl flex items-center gap-2 shadow-sm hover:bg-[#166fe5] transition font-medium">
                        <?= inline_svg('facebook', 'w-5 h-5') ?> Facebook
                     </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Prompts -->
    <?php if(!empty($related_prompts)): ?>
    <div class="mt-12">
        <h3 class="font-bold text-gray-900 text-2xl mb-6">Có thể bạn quan tâm</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach($related_prompts as $pr): 
                $isOwner = false; 
                $isTrash = false; 
                $isPendingList = false;
                // Fix undefined variables for prompt_card.php
                $isAdmin = is_admin() || is_root();
                $isPremium = is_premium();
                $isPublic = false; // Set false to show favorite button, but ensure logic doesn't show edit buttons unless owner/admin
                
                include __DIR__ . '/../../components/prompt_card.php'; 
            endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Comments Section -->
    <div class="mt-12 bg-white rounded-2xl shadow-lg border p-8">
        <h3 class="font-bold text-gray-900 text-xl mb-6 flex items-center gap-2">
            <?= inline_svg('chat', 'w-6 h-6') ?> Binh luận (<?= count($prompt_comments ?? []) ?>)
        </h3>
        
        <?php if(is_logged_in()): ?>
        <form action="<?= SITE_URL ?>comments/store" method="POST" class="mb-8 flex gap-4">
            <input type="hidden" name="prompt_id" value="<?= $prompt['id'] ?>">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-600 flex-shrink-0">
                <?= strtoupper(substr($_SESSION['user_name']??'U', 0, 1)) ?>
            </div>
            <div class="flex-1">
                <textarea name="content" rows="2" class="w-full border rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Viết bình luận của bạn..." required></textarea>
                <div class="text-right mt-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition">Gửi bình luận</button>
                </div>
            </div>
        </form>
        <?php else: ?>
            <div class="bg-gray-50 rounded-xl p-4 text-center mb-8 border border-dashed border-gray-300">
                <p class="text-gray-600">Vui lòng <a href="<?= SITE_URL ?>login.php" class="text-blue-600 font-bold hover:underline">đăng nhập</a> để bình luận.</p>
            </div>
        <?php endif; ?>

        <div class="space-y-6">
            <?php if(empty($prompt_comments)): ?>
                <div class="text-gray-500 text-center py-4">Chưa có bình luận nào. Hãy là người đầu tiên!</div>
            <?php else: ?>
                <?php foreach($prompt_comments as $cmt): ?>
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600 flex-shrink-0">
                            <?= strtoupper(substr($cmt['user_name']??'U', 0, 1)) ?>
                        </div>
                        <div class="flex-1">
                            <div class="bg-gray-50 rounded-2xl px-4 py-3">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-bold text-gray-900"><?= htmlspecialchars($cmt['user_name']) ?></span>
                                    <span class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($cmt['created_at'])) ?></span>
                                </div>
                                <p class="text-gray-800"><?= nl2br(htmlspecialchars($cmt['content'])) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</main>

<!-- Toast copy -->
<div id="toast-copy" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-green-600 text-white rounded-xl px-6 py-3 shadow-2xl z-50 hidden text-base font-medium transition duration-300 flex items-center gap-2">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
    Đã copy nội dung prompt!
</div>

<script>
const BASE_PATH = '<?= BASE_PATH ?>';
const BASE_URL = '<?= SITE_URL ?>';

function copyTextToClipboard(text) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    return navigator.clipboard.writeText(text);
  }
  const textarea = document.createElement('textarea');
  textarea.value = text;
  textarea.setAttribute('readonly', '');
  textarea.style.position = 'absolute';
  textarea.style.left = '-9999px';
  document.body.appendChild(textarea);
  textarea.select();
  try { document.execCommand('copy'); } catch (err) {}
  document.body.removeChild(textarea);
  return Promise.resolve();
}

function copyPromptCode() {
    const code = document.getElementById('prompt-view-code');
    if (!code) return;
    copyTextToClipboard(code.innerText).then(function() {
      showToastCopy();
    });
}

function showToastCopy() {
    let toast = document.getElementById('toast-copy');
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 2000);
}

function copyPromptShareLink(id) {
    const url = BASE_URL + 'prompt/' + id; 
    copyTextToClipboard(url).then(function() {
      alert('Đã copy link chia sẻ: ' + url);
    });
}

function toggleFavorite(prompt_id, btn){
    fetch(BASE_PATH + 'api/favorite_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=toggle&prompt_id=' + prompt_id
    }).then(res=>res.json()).then(data=>{
        if(data.success){
            const icon = btn.querySelector('svg');
            if(data.favorite) {
                btn.innerHTML = `<?= inline_svg('heart-fill', 'w-6 h-6 text-red-500') ?><span>Lưu</span>`;
                btn.classList.add('text-red-500');
            } else {
                btn.innerHTML = `<?= inline_svg('heart', 'w-6 h-6') ?><span>Lưu</span>`;
                btn.classList.remove('text-red-500');
            }
        }
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/premium_modal.php'; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
