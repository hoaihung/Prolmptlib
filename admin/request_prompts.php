<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) die('No permission');
$title = "Quản lý yêu cầu Prompt";
include 'layout.php';

// Phân trang
$page = max(1, intval($_GET['page'] ?? 1));
$perpage = 20;
$offset = ($page-1)*$perpage;

$where = '1';
$params = [];
if (!empty($_GET['status']) && $_GET['status']==='pending') {
    $where .= ' AND is_done=0';
}
if (!empty($_GET['q'])) {
    $where .= ' AND (title LIKE ? OR description LIKE ?)';
    $params[] = '%'.$_GET['q'].'%';
    $params[] = '%'.$_GET['q'].'%';
}
$sql_count = "SELECT COUNT(*) FROM request_prompts WHERE $where";
$total = $pdo->prepare($sql_count); $total->execute($params); $total = $total->fetchColumn();
$total_pages = ceil($total/$perpage);

$sql = "SELECT rp.*, u.name FROM request_prompts rp LEFT JOIN users u ON rp.user_id=u.id
        WHERE $where ORDER BY rp.id DESC LIMIT $perpage OFFSET $offset";
$q = $pdo->prepare($sql); $q->execute($params);
$reqs = $q->fetchAll();

// Load categories/tags/authors cho filter
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
$authors = $pdo->query("SELECT id, name FROM users WHERE is_deleted=0")->fetchAll();

?>
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Quản lý Yêu cầu Prompt</h2>
</div>
<form method="get" class="flex gap-3 mb-3">
    <input name="q" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($_GET['q']??'') ?>" class="border rounded px-2 py-1">
    <select name="status" class="border rounded px-2 py-1">
        <option value="">Tất cả</option>
        <option value="pending" <?= ($_GET['status']??'')=='pending'?'selected':'' ?>>Chưa xử lý</option>
    </select>
    <button class="bg-blue-500 text-white px-3 py-1 rounded">Lọc</button>
</form>

<div class="space-y-4">
    <?php foreach($reqs as $r): ?>
    <div class="bg-white border rounded-xl shadow p-4 flex flex-col gap-2">
        <div>
            <b class="text-blue-800"><?= htmlspecialchars($r['title']) ?></b>
            <span class="ml-2 text-gray-500 text-xs">(<?= $r['name'] ?>)</span>
            <?php if ($r['is_done']): ?>
                <span class="bg-green-100 text-green-700 px-2 rounded text-xs ml-2">Đã hoàn thành</span>
            <?php endif ?>
        </div>
        <div class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($r['description'])) ?></div>
        <div class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></div>
        <?php if(!$r['is_done']): ?>
            <button class="bg-green-600 text-white px-3 py-1 rounded" onclick='openPromptAddModalFromRequest(<?= $r['id'] ?>, <?= json_encode($r['title'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>, "")'>+ Thêm prompt vào kho
            </button>

        <?php endif ?>
    </div>
    <?php endforeach ?>
</div>
<?php if($total_pages>1): include '../components/pagination.php'; endif; ?>

<!-- Modal Thêm/Sửa Prompt -->
<div id="prompt-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50" >
    <div class="bg-white p-8 rounded-xl w-full max-w-2xl relative">
        <button class="absolute top-2 right-3 text-gray-400 text-2xl" onclick="closePromptModal()">&times;</button>
        <h3 id="modal-title" class="text-lg font-bold mb-4"></h3>
        <form id="prompt-form">
            <input type="hidden" name="id" id="prompt-id">
            <input type="hidden" name="request_id" id="prompt-request-id">

            <div class="mb-3 grid grid-cols-2 gap-4">
                <div>
                    <label class="font-semibold">Tiêu đề</label>
                    <input type="text" name="title" id="prompt-title" class="w-full border rounded px-2 py-1" required maxlength="80">
                </div>
                <div>
                    <label class="font-semibold">Danh mục</label>
                    <select name="category_id" id="prompt-category" class="w-full border rounded px-2 py-1">
                        <?php foreach ($cats as $cat): ?>
                        <option value="<?php echo $cat['id'] ?>"><?php echo htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="font-semibold">Mô tả</label>
                <textarea name="description" id="prompt-desc" rows="2" class="w-full border rounded px-2 py-1"></textarea>
            </div>
            <div class="mb-3">
                <label class="font-semibold">Nội dung Prompt (code/text)</label>
                <textarea name="content" id="prompt-content" rows="5" class="w-full border rounded px-2 py-1" required></textarea>
            </div>
            <div class="mb-3 grid grid-cols-2 gap-4">
                <div>
                    <label class="font-semibold">Tags</label>
                    <select name="tags[]" id="prompt-tags" class="w-full border rounded px-2 py-1" multiple>
                    <?php $tags = $pdo->query("SELECT * FROM tags")->fetchAll();
                        foreach ($tags as $tag): 
                    ?>
                    <option value="<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
                <?php if (is_admin()||is_root()): ?>
                <div class="flex gap-4 items-center mt-4">
                    <label><input type="checkbox" name="console_enabled" id="prompt-console"> Console</label>
                    <label><input type="checkbox" name="premium" id="prompt-premium"> Premium</label>
                </div>
                <?php endif; ?>
            </div>
            <div class="text-right mt-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">Lưu</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE_PATH = '<?= BASE_PATH ?>';

function addPromptFromRequest(form, req_id){
    event.preventDefault();
    let fd = new FormData(form);
    fd.append('action','add_prompt');
    fd.append('req_id', req_id);
    fetch('request_prompts_api.php', {method:'POST', body: fd})
    .then(res=>res.json()).then(data=>{
        if(data.success) location.reload();
        else alert(data.message||'Có lỗi xảy ra!');
    });
    return false;
}

function openPromptModal(id = null) {
    document.getElementById('prompt-modal').classList.remove('hidden');
    document.getElementById('modal-title').innerText = id ? 'Sửa Prompt' : 'Thêm Prompt';
    document.getElementById('prompt-form').reset();
    document.getElementById('prompt-id').value = '';
    if (id) {
        fetch(BASE_PATH + '../api/prompts_api.php?action=get&id='+id)
        .then(res => res.json())
        .then(pr => {
            document.getElementById('prompt-id').value = pr.id;
            document.getElementById('prompt-title').value = pr.title;
            document.getElementById('prompt-category').value = pr.category_id;
            document.getElementById('prompt-desc').value = pr.description;
            document.getElementById('prompt-content').value = pr.content;
            document.getElementById('prompt-console').checked = pr.console_enabled==1;
            document.getElementById('prompt-premium').checked = pr.premium==1;
            // Tags (multi)
            var tagsSel = document.getElementById('prompt-tags');
            for (var i=0; i<tagsSel.options.length; i++) {
                tagsSel.options[i].selected = pr.tags && pr.tags.includes(parseInt(tagsSel.options[i].value));
            }
        });
    }
}
function closePromptModal() {
    document.getElementById('prompt-modal').classList.add('hidden');
}

function openPromptAddModalFromRequest(req_id, title, content) {
    // Reset & mở modal từ admin/prompts.php
    if (!window.openPromptModal) {
        alert('Không tìm thấy modal thêm prompt! Vào trang quản lý Prompt (admin/prompts.php) trước để load modal!');
        return;
    }
    openPromptModal(); // Hiển thị modal
    // Đổ sẵn thông tin từ yêu cầu
    setTimeout(function() {
        document.getElementById('prompt-title').value = title || '';
        document.getElementById('prompt-desc').value = '';
        document.getElementById('prompt-content').value = content || '';
        document.getElementById('prompt-id').value = '';
        // Gắn biến để lưu lại request_id (ẩn)
        if (!document.getElementById('prompt-request-id')) {
            let hid = document.createElement('input');
            hid.type = 'hidden';
            hid.name = 'request_id';
            hid.id = 'prompt-request-id';
            document.getElementById('prompt-form').appendChild(hid);
        }
        document.getElementById('prompt-request-id').value = req_id;
    }, 50);
}

document.addEventListener('DOMContentLoaded', function() {
  const el = document.getElementById('prompt-tags');
  if (el && window.Choices) {
    new Choices(el, {
      removeItemButton: true,
      searchEnabled: true,
      placeholder: true,
      placeholderValue: 'Chọn hoặc tìm tag...',
      noResultsText: 'Không tìm thấy tag'
    });
  }
});

document.getElementById('prompt-form').onsubmit = function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch(BASE_PATH + '../api/prompts_api.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Lỗi thao tác!');
    });
};

</script>
<?php include 'layout_end.php'; ?>
