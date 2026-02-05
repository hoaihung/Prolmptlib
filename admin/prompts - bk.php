<?php
require_once '../includes/loader.php';
if (!is_logged_in()) { // chỉ premium/admin/root truy cập
    header('Location: login.php');
    exit;
}
$title = "Quản lý Prompt";
include 'layout.php';

// ------ XỬ LÝ FILTER/TÌM KIẾM/PAGINATION ------
$params = [];
$where = "p.is_deleted=0 AND p.is_active=1";

// Tìm kiếm theo tên
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
if (!empty($_GET['type'])) {
    if ($_GET['type']==='free') $where .= " AND p.premium=0";
    if ($_GET['type']==='premium') $where .= " AND p.premium=1";
    if ($_GET['type']==='console') $where .= " AND p.console_enabled=1";
}
// Filter trạng thái
if (!empty($_GET['status'])) {
    if ($_GET['status'] == 'active') $where .= " AND p.is_active=1 AND p.is_deleted=0";
    if ($_GET['status'] == 'inactive') $where .= " AND p.is_active=0 AND p.is_deleted=0";
    if ($_GET['status'] == 'deleted') $where = "p.is_deleted=1";
}

// Filter author (cho admin)
if (!empty($_GET['author']) && (is_admin() || is_root())) {
    $where .= " AND p.author_id=?";
    $params[] = intval($_GET['author']);
}
if (is_admin() || is_root()) {
    if (!isset($_GET['status']) || $_GET['status'] === '') {
        $where = "p.is_deleted=0";
    }
    // Nếu có filter status, bên dưới sẽ thêm đúng điều kiện như cũ
}

// Nếu premium chỉ xem prompt của mình + prompt public
if (is_premium()) {
    $where = "p.is_deleted=0 AND (p.is_active=1 OR p.author_id=" . intval($_SESSION['user_id']) . ")";
}
// Tổng prompt
$countSql = "SELECT COUNT(*) FROM prompts p WHERE $where";
$countStmt = $pdo->prepare($countSql); $countStmt->execute($params);
$total = $countStmt->fetchColumn();
// Phân trang
$perPage = 1;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$sql = "SELECT p.*, c.name AS category_name, u.name AS author_name
        FROM prompts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE $where
        ORDER BY p.id DESC
        LIMIT $perPage OFFSET $offset";
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
?>

<div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-bold">Danh sách Prompt</h2>
    <?php if (is_admin() || is_root() || user_role()==='premium'): ?>
    <button onclick="openPromptModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl">+ Thêm Prompt</button>
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
        <option value="deleted" <?php if(($_GET['status']??'')=='deleted') echo 'selected'; ?>>Đã xóa</option>
    </select>
	<?php endif; ?>
	<select name="type" class="border rounded px-2 py-1">
		<option value="">Tất cả Prompt</option>
		<option value="free" <?php if(($_GET['type']??'')==='free') echo 'selected'; ?>>Prompt Free</option>
		<option value="premium" <?php if(($_GET['type']??'')==='premium') echo 'selected'; ?>>Prompt Premium</option>
		<option value="console" <?php if(($_GET['type']??'')==='console') echo 'selected'; ?>>Prompt Console</option>
	</select>

    <button class="bg-blue-600 text-white px-3 py-1 rounded">Lọc</button>
</form>
<!-- TABLE PROMPT -->
<table class="min-w-full bg-white rounded-xl shadow-lg border text-base">
  <thead class="bg-gray-50 border-b">
    <tr class="text-left text-gray-700 font-bold uppercase text-sm">
      <th class="p-3">ID</th>
      <th class="p-3">Tiêu đề</th>
      <th class="p-3">Danh mục</th>
      <th class="p-3">Tác giả</th>
      <th class="p-3">Tags</th>
      <th class="p-3">Lượt xem</th>
      <th class="p-3">Thao tác</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($prompts as $pr): ?>
		<tr class="hover:bg-blue-50 border-b transition duration-150">
		  <td class="p-3 text-gray-600"><?php echo $pr['id'] ?></td>
		  <td class="p-3 font-semibold text-lg flex items-center gap-2">
			<?php echo htmlspecialchars($pr['title']) ?>
			<?php if($pr['premium']): ?><span class="bg-yellow-300 text-yellow-800 px-2 py-0.5 rounded text-xs ml-2">Pre</span><?php endif; ?>
			<?php if($pr['console_enabled']): ?><span class="bg-green-200 text-green-700 px-2 py-0.5 rounded text-xs ml-2">Run</span><?php endif; ?>
		  </td>
		  <td class="p-3 text-blue-700 font-medium"><?php echo htmlspecialchars($pr['category_name'] ?? '') ?></td>
		  <td class="p-3 text-purple-700 font-medium"><?php echo htmlspecialchars($pr['author_name'] ?? '') ?></td>
		  <td class="p-3">
			<?php foreach ($pr['tags'] as $tag): ?>
			  <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs mr-1"><?php echo htmlspecialchars($tag) ?></span>
			<?php endforeach; ?>
		  </td>
		  <td class="p-3 text-right"><?php echo intval($pr['view_count']) ?></td>
            <td class="p-3 flex gap-1 flex-wrap">
				<button class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs" onclick="viewPrompt(<?php echo $pr['id'] ?>)">Xem</button>
                <?php if (
                    (is_admin() || is_root()) ||
                    (user_role()==='premium' && $pr['author_id']==$_SESSION['user_id'])
                ): ?>
					
                    <button class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-xs" onclick="openPromptModal(<?php echo $pr['id'] ?>)">Sửa</button>
                <?php endif; ?>
                <?php if (
                    (is_admin() || is_root()) ||
                    (user_role()==='premium' && $pr['author_id']==$_SESSION['user_id'])
                ): ?>
                    <?php if ($pr['is_active'] && !$pr['is_deleted']): ?>
                        <button class="bg-gray-400 px-2 py-1 rounded text-xs" onclick="inactivePrompt(<?php echo $pr['id'] ?>)">Vô hiệu</button>
					<?php elseif (!$pr['is_active'] && !$pr['is_deleted']): ?>
						<button class="bg-gray-400 px-2 py-1 rounded text-xs" onclick="activatePrompt(<?php echo $pr['id'] ?>)">Kích hoạt</button>
                        <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs" onclick="deletePrompt(<?php echo $pr['id'] ?>)">Xóa</button>
                    <?php endif; ?>
					<?php if ($pr['is_deleted']): ?>
                        <button class="bg-green-600 text-white px-2 py-1 rounded text-xs" onclick="restorePrompt(<?php echo $pr['id'] ?>)">Khôi phục</button>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<!-- PHÂN TRANG -->
<div class="flex justify-center mt-4 gap-1">
<?php
$totalPages = ceil($total / $perPage);
for ($i = 1; $i <= $totalPages; $i++):
    $query = $_GET; $query['page'] = $i;
    $url = '?' . http_build_query($query);
?>
    <a href="<?php echo $url ?>" class="px-3 py-1 rounded <?php if($page==$i) echo 'bg-blue-600 text-white'; else echo 'bg-gray-200'; ?>">
        <?php echo $i ?>
    </a>
<?php endfor; ?>
</div>

<!-- ... (modal thêm/sửa sẽ code sau) ... -->

<!-- Modal Thêm/Sửa Prompt -->
<div id="prompt-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-xl w-full max-w-2xl relative">
        <button class="absolute top-2 right-3 text-gray-400 text-2xl" onclick="closePromptModal()">&times;</button>
        <h3 id="modal-title" class="text-lg font-bold mb-4"></h3>
        <form id="prompt-form">
            <input type="hidden" name="id" id="prompt-id">
            <div class="mb-3 grid grid-cols-2 gap-4">
                <div>
                    <label class="font-semibold">Tiêu đề</label>
                    <input type="text" name="title" id="prompt-title" class="w-full border rounded px-2 py-1" required>
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
                    <select multiple name="tags[]" id="prompt-tags" class="w-full border rounded px-2 py-1 h-20">
                        <?php $tags = $pdo->query("SELECT * FROM tags")->fetchAll();
                        foreach ($tags as $tag): ?>
                        <option value="<?php echo $tag['id'] ?>"><?php echo htmlspecialchars($tag['name']) ?></option>
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

<!-- Modal xem chi tiết Prompt -->
<div id="view-prompt-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-xl w-full max-w-xl relative">
        <button class="absolute top-2 right-3 text-gray-400 text-2xl" onclick="closeViewPrompt()">&times;</button>
        <div id="view-prompt-content"></div>
    </div>
</div>


<script>
const BASE_PATH = '<?= BASE_PATH ?>';
// placeholder các hàm js
// Thêm vào <script> cuối prompts.php
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
function editPrompt(id){}
function inactivePrompt(id) {
    fetch(BASE_PATH + '../api/prompts_api.php?action=status&id='+id+'&active=0')
    .then(res => res.json()).then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Không thể vô hiệu!');
    });
}
function activatePrompt(id) {
    fetch(BASE_PATH + '../api/prompts_api.php?action=status&id='+id+'&active=1')
    .then(res => res.json()).then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Không thể kích hoạt!');
    });
}
function deletePrompt(id) {
    if (confirm('Xác nhận xóa prompt này?')) {
        fetch(BASE_PATH + '../api/prompts_api.php?action=delete&id='+id)
        .then(res => res.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Không thể xóa!');
        });
    }
}
function restorePrompt(id) {
    fetch(BASE_PATH + '../api/prompts_api.php?action=restore&id='+id)
    .then(res => res.json()).then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Không thể khôi phục!');
    });
}


function viewPrompt(id) {
    fetch(BASE_PATH + '../api/prompts_api.php?action=get&id='+id)
    .then(res => res.json())
    .then(pr => {
        let tagHtml = pr.tags && pr.tags.length
            ? pr.tags.map(t => `<span class='bg-blue-100 text-blue-700 px-2 py-1 mr-1 rounded text-xs'>${t}</span>`).join(' ')
            : '';
        let html = `
        <div class="mb-4">
            <div class="text-2xl font-bold mb-1">${pr.title}</div>
            <div class="text-gray-500 mb-1 flex flex-wrap items-center gap-2">
                <span>Danh mục: <b>${pr.category_name||'---'}</b></span>
                <span>Tác giả: <b>${pr.author_name||'---'}</b></span>
                <span class="ml-3">Tags: ${tagHtml}</span>
            </div>
            <div class="text-gray-600 mb-2 italic">${pr.description||''}</div>
        </div>
        <pre class="bg-gray-50 p-4 rounded-xl mb-3 text-sm overflow-x-auto border" id="prompt-view-code">${pr.content}</pre>
        <button onclick="copyPromptCode()" class="bg-green-600 text-white px-5 py-2 rounded mb-2">Copy Prompt</button>`;
        document.getElementById('view-prompt-content').innerHTML = html;
        document.getElementById('view-prompt-modal').classList.remove('hidden');
    });
}

function closeViewPrompt() {
    document.getElementById('view-prompt-modal').classList.add('hidden');
}
function showToastCopy() {
    let toast = document.getElementById('toast-copy');
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 1500);
}
function copyPromptCode() {
    let code = document.getElementById('prompt-view-code').innerText;
    navigator.clipboard.writeText(code).then(function() {
        showToastCopy();
    });
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
