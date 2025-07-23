<?php
require_once '../includes/loader.php';
if (!is_logged_in()) { // chỉ premium/admin/root truy cập
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'] ?? 0;
$isAdmin = is_admin() || is_root();
$isPremium = user_role() === 'premium';


// ------ XỬ LÝ FILTER/TÌM KIẾM/PAGINATION ------
$params = [];
$where = "p.is_deleted=0";

$isAdmin = is_admin() || is_root();
$isPremium = user_role() === 'premium';

// Khởi tạo mệnh đề where và params
$where = "p.is_deleted=0";
$params = [];

// --- ADMIN/ROOT: xem được tất cả ---
if ($isAdmin) {
    // Filter trạng thái (active/inactive/deleted)
    if (!empty($_GET['status'])) {
        if ($_GET['status']=='active')   $where .= " AND p.is_active=1";
        if ($_GET['status']=='inactive') $where .= " AND p.is_active=0";
        if ($_GET['status']=='deleted')  $where  = "p.is_deleted=1"; // ghi đè để chỉ lấy deleted
    }
    // Filter duyệt (pending/approved/rejected)
    if (isset($_GET['approval']) && $_GET['approval'] !== '') {
        if ($_GET['approval']=='pending')  $where .= " AND p.is_approved=0";
        if ($_GET['approval']=='approved') $where .= " AND p.is_approved=1";
    }
    // Filter author (nếu có)
    if (!empty($_GET['author'])) {
        $where .= " AND p.author_id=?";
        $params[] = intval($_GET['author']);
    }

// --- PREMIUM: chỉ xem prompt của mình (nếu lọc author là mình), hoặc prompt đã duyệt ---
} elseif ($isPremium) {
    // Nếu filter "prompt của tôi" (author = chính mình)
    if (!empty($_GET['author']) && intval($_GET['author']) == $user_id) {
        $where .= " AND p.author_id=?";
        $params[] = $user_id;
    } else {
        // Ngược lại chỉ xem prompt đã duyệt & active
        $where .= " AND p.is_approved=1 AND p.is_active=1";
    }

// --- KHÁCH/USER: chỉ xem prompt đã duyệt & active ---
} else {
    $where .= " AND p.is_approved=1 AND p.is_active=1";
}

// Tìm kiếm theo tên/desc/author
if (!empty($_GET['q'])) {
    $where .= " AND (p.title LIKE ? OR p.description LIKE ? OR u.name LIKE ?)";
    $params[] = '%'.$_GET['q'].'%';
    $params[] = '%'.$_GET['q'].'%';
    $params[] = '%'.$_GET['q'].'%';
}

// Filter category
if (!empty($_GET['category'])) {
    $where .= " AND p.category_id=?";
    $params[] = intval($_GET['category']);
}
// Filter loại prompt
if (!empty($_GET['type'])) {
    if ($_GET['type']==='free')     $where .= " AND p.premium=0";
    if ($_GET['type']==='premium')  $where .= " AND p.premium=1";
    if ($_GET['type']==='console')  $where .= " AND p.console_enabled=1";
}


// PHÂN TRANG
$page = max(1, intval($_GET['page'] ?? 1));
$perpage = 12;
$offset = ($page-1) * $perpage;

$sql_count = "SELECT COUNT(*) FROM prompts p LEFT JOIN users u ON p.author_id=u.id WHERE $where";
$q_count = $pdo->prepare($sql_count); $q_count->execute($params); $total = $q_count->fetchColumn();
$total_pages = ceil($total / $perpage);

$sql = "SELECT p.*, c.name AS category_name, u.name AS author_name
        FROM prompts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE $where
        ORDER BY p.id DESC
        LIMIT $perpage OFFSET $offset";
$q = $pdo->prepare($sql); $q->execute($params);
$prompts = $q->fetchAll();

// Lấy tất cả tags gắn với prompt
$promptIds = array_column($prompts, 'id');
if ($promptIds) {
  $in = implode(',', array_fill(0, count($promptIds), '?'));
  $tagMap = [];
  $tagQ = $pdo->prepare("SELECT pt.prompt_id, t.name FROM prompt_tags pt JOIN tags t ON pt.tag_id = t.id WHERE pt.prompt_id IN ($in)");
  $tagQ->execute($promptIds);
  foreach ($tagQ as $row) {
    $tagMap[$row['prompt_id']][] = $row['name'];
  }
  foreach ($prompts as &$pr) {
    $pr['tags'] = $tagMap[$pr['id']] ?? [];
  }
  unset($pr);
} else {
  foreach ($prompts as &$pr) {
    $pr['tags'] = [];
  }
  unset($pr);
}

// Load categories/tags/authors cho filter
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
$authors = $pdo->query("SELECT id, name FROM users WHERE is_deleted=0")->fetchAll();

$title = "Quản lý Prompt";
include 'layout.php';

?>

<div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-bold">Danh sách Prompt</h2>
    <?php if (is_admin() || is_root() || user_role()==='premium'): ?>
        <?php $premium_create_enable = get_site_setting('enable_premium_create') == 1;
            if (is_admin() || is_root() || (user_role()==='premium' && $premium_create_enable)): ?>
                <div>
                    <button onclick="openPromptModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl">+ Thêm Prompt</button>
                    <button onclick="openBatchAddPromptModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl">Batch Add Prompt</button>
                </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<!-- FORM FILTER -->
<form method="get" class="mb-4 flex flex-wrap gap-2 items-center">
    <input name="q" placeholder="Tìm tên hoặc mô tả..." value="<?php echo htmlspecialchars($_GET['q']??'') ?>" class="border rounded px-2 py-1" />
    <select name="category" class="border rounded px-2 py-1">
        <option value="">Tất cả danh mục</option>
        <?php foreach ($cats as $cat): ?>
        <option value="<?php echo $cat['id'] ?>" <?php if (($_GET['category']??'')==$cat['id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
    </select>
	<?php if (is_admin() || is_root()): ?>
    <select name="status" class="border rounded px-2 py-1">
		<option value="" <?php if(($_GET['status']??'')=='') echo 'selected'; ?>>Tất cả</option>
        <option value="active" <?php if(($_GET['status']??'')=='active') echo 'selected'; ?>>Hoạt động</option>
		<option value="inactive" <?php if(($_GET['status']??'')=='inactive') echo 'selected'; ?>>Vô hiệu hóa</option>
        <?php if (!is_premium()): ?>
        <option value="deleted" <?php if(($_GET['status']??'')=='deleted') echo 'selected'; ?>>Đã xóa</option>
    <?php endif; ?>
    </select>
	<?php endif; ?>
	<select name="type" class="border rounded px-2 py-1">
		<option value="">Tất cả Prompt</option>
		<option value="free" <?php if(($_GET['type']??'')==='free') echo 'selected'; ?>>Prompt Free</option>
		<option value="premium" <?php if(($_GET['type']??'')==='premium') echo 'selected'; ?>>Prompt Premium</option>
		<option value="console" <?php if(($_GET['type']??'')==='console') echo 'selected'; ?>>Prompt Console</option>
	</select>
    <select name="author" class="border rounded px-2 py-1">
        <option value="">Tất cả prompt</option>
        <option value="<?= $user_id ?>" <?= (($_GET['author']??'')==$user_id) ? 'selected' : '' ?>>Chỉ prompt của tôi</option>
    </select>
    <?php if (is_admin() || is_root() || !is_premium()): ?>
    <select name="approval" class="border rounded px-2 py-1">
        <option value="">Tất cả trạng thái</option>
        <option value="pending" <?= ($_GET['approval']??'')=='pending'?'selected':'' ?>>Chờ duyệt</option>
        <option value="approved" <?= ($_GET['approval']??'')=='approved'?'selected':'' ?>>Đã duyệt</option>
    </select>
    <?php endif; ?>

    <button class="bg-blue-600 text-white px-3 py-1 rounded">Lọc</button>
</form>
<!-- Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
<?php
$isAdmin = is_admin() || is_root();
$isPremium = user_role() === 'premium'; // Chuẩn hóa theo hàm role của bạn
$isPublic = false;
// Lấy danh sách id prompt user đã yêu thích
 
$user_favorites = [];
if (is_logged_in()) {
    $uid = $_SESSION['user_id'];
    $qfav = $pdo->prepare("SELECT prompt_id FROM prompt_favorites WHERE user_id=?");
    $qfav->execute([$uid]);
    $user_favorites = $qfav->fetchAll(PDO::FETCH_COLUMN);
}

foreach ($prompts as $pr) {
    $isOwner = isset($pr['author_id']) && $pr['author_id'] == $user_id;
    $isTrash = !empty($pr['is_deleted']);
    $isPendingList = false;
    // Chỉ owner với prompt của chính mình
    include __DIR__.'/../components/prompt_card.php';
}
?>
</div>
<!-- PHÂN TRANG -->
<?php include __DIR__.'/../components/pagination.php'; ?>


<!-- ... (modal thêm/sửa sẽ code sau) ... -->

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
                <textarea name="description" id="prompt-desc" rows="2" class="w-full border rounded px-2 py-1" maxlength="400"></textarea>
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

<!-- Modal batch add prompt -->
<div id="batch-add-modal" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
  <div class="bg-white p-6 rounded-xl shadow-xl w-[600px] max-w-full">
    <h3 class="text-xl font-bold mb-4">Batch Add Prompt (Nhập JSON)</h3>
    <textarea id="batch-prompt-json" rows="12" class="w-full border p-2 mb-4" placeholder='[
  {
    "title": "Tóm tắt văn bản",
    "category_id": 1,
    "description": "Tóm tắt nhanh đoạn văn...",
    "content": "Hãy tóm tắt đoạn văn này: {{input}}",
    "tags": [1,2],
    "console_enabled": true,
    "premium": false
  }
]'></textarea>
    <div id="batch-add-result" class="mb-3 text-green-600 font-bold hidden"></div>
    <div class="flex justify-end gap-2">
      <button onclick="closeBatchAddPromptModal()" class="btn">Đóng</button>
      <button onclick="submitBatchAddPrompt()" class="btn btn-success">Thêm Hàng Loạt</button>
    </div>
  </div>
</div>

<!-- Modal xem chi tiết Prompt -->
<div id="view-prompt-modal-root" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center" ></div>

<script>
const BASE_PATH = '<?= BASE_PATH ?>';
const BASE_URL = '<?=SITE_URL?>';
// placeholder các hàm js
// Thêm vào <script> cuối prompts.php

// Chỉ nên init Choices 1 lần khi page load, lưu biến toàn cục
let choicesPromptTags = null;
document.addEventListener('DOMContentLoaded', function() {
  const el = document.getElementById('prompt-tags');
  if (el && window.Choices) {
    choicesPromptTags = new Choices(el, {
      removeItemButton: true,
      searchEnabled: true,
      placeholder: true,
      placeholderValue: 'Chọn hoặc tìm tag...',
      noResultsText: 'Không tìm thấy tag'
    });
  }
});


function openPromptModal(id = null) {
    document.getElementById('prompt-modal').classList.remove('hidden');
    document.getElementById('modal-title').innerText = id ? 'Sửa Prompt' : 'Thêm Prompt';
    document.getElementById('prompt-form').reset();
    document.getElementById('prompt-id').value = '';
    document.getElementById('prompt-request-id').value = '';

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
            
            // ===== XỬ LÝ TAG =====
            if (choicesPromptTags && pr.tags) {
                // Remove hết các tag đã chọn
                choicesPromptTags.removeActiveItems();
                // Gán lại tag đã gán trước đó
                pr.tags.forEach(function(tagId) {
                  choicesPromptTags.setChoiceByValue(String(tagId));
                });
            }
        });
    } else {
        // Nếu thêm mới, clear tag đã chọn
        if (choicesPromptTags) choicesPromptTags.removeActiveItems();
    }
}


function closePromptModal() {
    document.getElementById('prompt-modal').classList.add('hidden');
}

function openBatchAddPromptModal() {
    document.getElementById('batch-add-modal').classList.remove('hidden');
    document.getElementById('batch-add-result').classList.add('hidden');
    document.getElementById('batch-prompt-json').value = '';
}
function closeBatchAddPromptModal() {
    document.getElementById('batch-add-modal').classList.add('hidden');
}

function submitBatchAddPrompt() {
    const data = document.getElementById('batch-prompt-json').value;
    let prompts;
    try {
        prompts = JSON.parse(data);
        if (!Array.isArray(prompts)) throw "Phải là mảng JSON";
    } catch (e) {
        alert('JSON không hợp lệ: ' + e);
        return;
    }
    fetch(BASE_PATH + '../api/prompts_api.php?action=batch_add', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({prompts: prompts})
    })
    .then(res => res.json())
    .then(result => {
        document.getElementById('batch-add-result').classList.remove('hidden');
        document.getElementById('batch-add-result').innerText = `Đã thêm thành công ${result.success} prompt!${result.error ? "\nCó lỗi: "+result.error : ''}`;
        if (result.success > 0) setTimeout(()=>window.location.reload(), 1500);
    })
    .catch(e => alert("Có lỗi khi gửi dữ liệu: " + e));
}



function editPrompt(id){}
function deactivatePrompt(id) {
  if(confirm("Vô hiệu prompt này?")) fetch(BASE_PATH + '../api/prompts_api.php?action=status&active=0&id='+id)
    .then(res=>res.json()).then(data=>{ if(data.success) location.reload(); else alert(data.message||"Lỗi!"); });
}
function activatePrompt(id) {
  if(confirm("Kích hoạt lại prompt này?")) fetch(BASE_PATH + '../api/prompts_api.php?action=status&active=1&id='+id)
    .then(res=>res.json()).then(data=>{ if(data.success) location.reload(); else alert(data.message||"Lỗi!"); });
}
function deletePrompt(id) {
  if(confirm("Xóa prompt này?")) fetch(BASE_PATH + '../api/prompts_api.php?action=delete&id='+id)
    .then(res=>res.json()).then(data=>{ if(data.success) location.reload(); else alert(data.message||"Lỗi!"); });
}
function restorePrompt(id) {
  if(confirm("Khôi phục prompt này?")) fetch(BASE_PATH + '../api/prompts_api.php?action=restore&id='+id)
    .then(res=>res.json()).then(data=>{ if(data.success) location.reload(); else alert(data.message||"Lỗi!"); });
}
function hardDeletePrompt(id) {
  if(confirm("Xóa vĩnh viễn prompt này?")) fetch(BASE_PATH + '../api/prompts_api.php?action=hard_delete&id='+id)
    .then(res=>res.json()).then(data=>{ if(data.success) location.reload(); else alert(data.message||"Lỗi!"); });
}


function viewPrompt(id) {
    fetch(BASE_PATH + '../api/prompt_detail_modal.php?id=' + id)
    .then(res => res.text())
    .then(html => {
        let root = document.getElementById('view-prompt-modal-root');
        root.innerHTML = html;
        root.classList.remove('hidden');
    });
}
function closeViewPrompt() {
    let root = document.getElementById('view-prompt-modal-root');
    root.classList.add('hidden');
    root.innerHTML = '';
}
function copyPromptCode() {
    let code = document.getElementById('prompt-view-code');
    if (!code) return;
    navigator.clipboard.writeText(code.innerText).then(function() {
        showToastCopy();
    });
}
function showToastCopy() {
    if (!window._toastCopy) {
        let toast = document.createElement('div');
        toast.id = "toast-copy";
        toast.className = "fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-green-600 text-white rounded px-4 py-2 shadow-lg z-50 text-base transition duration-300";
        toast.innerText = "Đã copy nội dung prompt!";
        document.body.appendChild(toast);
        window._toastCopy = toast;
    }
    let toast = window._toastCopy;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 1500);
}

function toggleFavorite(prompt_id, btn){
    fetch(BASE_PATH + '../api/favorite_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=toggle&prompt_id=' + prompt_id
    }).then(res=>res.json()).then(data=>{
        if(data.success){
            if(data.favorite)
                btn.innerHTML = "<?= inline_svg('heart-fill', 'w-6 h-6 text-red-500') ?>";
            else
                btn.innerHTML = "<?= inline_svg('heart', 'w-6 h-6 text-gray-400') ?>";
        }
    });
}

function copyPromptShareLink(id) {
    const url = BASE_URL + 'prompts.php?id=' + id;
    navigator.clipboard.writeText(url);
    alert('Đã copy link chia sẻ!');
}

function lockPrompt(id) {
  if (!confirm("Khóa prompt này?")) return;
  fetch(BASE_PATH + '../api/prompts_api.php?action=lock&id='+id)
    .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}
function unlockPrompt(id) {
  if (!confirm("Mở khóa prompt này?")) return;
  fetch(BASE_PATH + '../api/prompts_api.php?action=unlock&id='+id)
    .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}


// Gắn ở <script> cuối prompts.php

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
