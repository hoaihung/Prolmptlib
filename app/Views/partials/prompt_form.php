<?php
// Expects: $prompt (array|null), $cats (array), $tags (array), $is_admin (bool)
$p = $prompt ?? [];
$actionUrl = $action_url ?? '';
?>
<form action="<?= $actionUrl ?>" method="POST" enctype="multipart/form-data" id="prompt-form-shared">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left Column: Main Content -->
        <div class="md:col-span-2 space-y-6">
            <!-- Title -->
            <div>
                <label class="block font-semibold mb-2 text-gray-700">Tiêu đề Prompt <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="<?= htmlspecialchars($p['title']??'') ?>" required class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Ví dụ: Viết bài SEO chuẩn...">
            </div>

            <!-- Description -->
            <div>
                <label class="block font-semibold mb-2 text-gray-700">Mô tả ngắn</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Mô tả công dụng của prompt này..."><?= htmlspecialchars($p['description']??'') ?></textarea>
            </div>

            <!-- Content (CodeMirror) -->
            <div>
                <label class="block font-semibold mb-2 text-gray-700">Nội dung Prompt <span class="text-red-500">*</span></label>
                <div class="border rounded-xl overflow-hidden shadow-sm">
                    <textarea id="prompt-content-editor" name="content" required class="w-full"><?= htmlspecialchars($p['content']??'') ?></textarea>
                </div>
                <p class="text-xs text-gray-500 mt-1">Mẹo: Sử dụng <code>{{input}}</code> hoặc <code>{{topic}}</code> để tạo biến nhập liệu.</p>
            </div>

            <!-- Admin Content (SEO) - Only Admin -->
            <?php if($is_admin): ?>
            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                <label class="block font-semibold mb-2 text-blue-800">Admin Content (SEO/Bài viết)</label>
                <textarea name="admin_content" rows="5" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Nội dung bài viết SEO hiển thị bên dưới prompt..."><?= htmlspecialchars($p['admin_content']??'') ?></textarea>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Settings & Meta -->
        <div class="space-y-6">
            <!-- Thumbnail -->
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <label class="block font-semibold mb-2 text-gray-700">Thumbnail</label>
                <?php if(!empty($p['thumbnail'])): ?>
                    <div class="mb-3 relative group">
                        <img src="<?=SITE_URL . $p['thumbnail']?>" class="w-full h-40 object-cover rounded-lg border">
                    </div>
                <?php endif; ?>
                <input type="file" name="thumbnail" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*">
            </div>

            <!-- Category -->
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <label class="block font-semibold mb-2 text-gray-700">Danh mục</label>
                <select name="category_id" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    <?php foreach($cats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($p['category_id']??0) == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tags -->
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <label class="block font-semibold mb-2 text-gray-700">Tags</label>
                <select name="tags[]" id="prompt-tags-select" class="w-full border rounded-xl" multiple>
                    <?php 
                    // Get selected tags
                    $selectedTags = $p['tags'] ?? [];
                    // If tags are objects/arrays (from prompt detail), extract IDs
                    $selectedTagIds = [];
                    foreach($selectedTags as $t) {
                        if(is_array($t)) $selectedTagIds[] = $t['id'];
                        elseif(is_numeric($t)) $selectedTagIds[] = $t;
                    }
                    
                    foreach($tags as $t): 
                    ?>
                    <option value="<?= $t['id'] ?>" <?= in_array($t['id'], $selectedTagIds) ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Admin Options -->
            <?php if($is_admin): ?>
            <div class="bg-white p-4 rounded-xl border shadow-sm space-y-3">
                <h3 class="font-bold text-gray-800 border-b pb-2">Cài đặt Admin</h3>
                
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_approved" value="1" <?= ($p['is_approved']??0) ? 'checked' : '' ?> class="w-5 h-5 text-green-600 rounded">
                    <span>Đã duyệt</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" <?= ($p['is_active']??1) ? 'checked' : '' ?> class="w-5 h-5 text-blue-600 rounded">
                    <span>Kích hoạt (Hiển thị)</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="premium" value="1" <?= ($p['premium']??0) ? 'checked' : '' ?> class="w-5 h-5 text-purple-600 rounded">
                    <span>Premium (Huy hiệu)</span>
                </label>
                
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="console_enabled" value="1" <?= ($p['console_enabled']??0) ? 'checked' : '' ?> class="w-5 h-5 text-gray-600 rounded">
                    <span>Console Enabled</span>
                </label>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3 pt-4">
                <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 text-white font-bold shadow hover:bg-blue-700 transition">
                    <?= !empty($p['id']) ? 'Cập nhật Prompt' : 'Tạo Prompt Mới' ?>
                </button>
                <a href="javascript:history.back()" class="w-full py-3 rounded-xl border border-gray-300 text-gray-700 font-medium text-center hover:bg-gray-50 transition">Hủy bỏ</a>
            </div>
        </div>
    </div>
</form>

<!-- CodeMirror & Choices JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css"> -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/markdown/markdown.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
    // Init CodeMirror
    var editor = CodeMirror.fromTextArea(document.getElementById("prompt-content-editor"), {
        mode: "markdown",
        // theme: "dracula", // Use default light theme
        lineNumbers: true,
        lineWrapping: true,
        minHeight: "300px"
    });
    editor.setSize(null, 400);

    // Init Choices for Tags
    const tagsEl = document.getElementById('prompt-tags-select');
    if (tagsEl) {
        new Choices(tagsEl, {
            removeItemButton: true,
            searchEnabled: true,
            placeholder: true,
            placeholderValue: 'Chọn tags...',
            noResultsText: 'Không tìm thấy tag'
        });
    }
</script>
