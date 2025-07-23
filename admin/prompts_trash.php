<?php
require_once '../includes/loader.php';
if (!is_logged_in()) {
    header('Location: login.php'); exit;
}
if (!is_admin() && !is_root() && !is_premium()) { // chỉ admin, root xem được thùng rác
    header('Location: prompts.php'); exit;
}
$isAdmin = is_admin() || is_root();
$isPremium = user_role() === 'premium';
$user_id = $_SESSION['user_id'] ?? 0;
$title = "Thùng rác Prompt";
include 'layout.php';

$params = [];
$where = "p.is_deleted=1";

// Nếu là Premium
if (!$isAdmin && $isPremium) {
    $where .= " AND p.author_id=?";
    $params[] = $user_id;
}

// Tìm kiếm/filter
if (!empty($_GET['q'])) {
    $where .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $params[] = '%'.$_GET['q'].'%';
    $params[] = '%'.$_GET['q'].'%';
}

// PHÂN TRANG
$page = max(1, intval($_GET['page'] ?? 1));
$perpage = 12;
$offset = ($page-1) * $perpage;

// Tổng số prompt
$sql_count = "SELECT COUNT(*) FROM prompts p WHERE $where";
$q_count = $pdo->prepare($sql_count); $q_count->execute($params); $total = $q_count->fetchColumn();
$total_pages = ceil($total / $perpage);

// Lấy danh sách prompt
$sql = "SELECT p.*, c.name AS category_name, u.name AS author_name
        FROM prompts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE $where
        ORDER BY p.id DESC
        LIMIT $perpage OFFSET $offset";
$q = $pdo->prepare($sql); $q->execute($params);
$prompts = $q->fetchAll();

// Tags
$promptIds = array_column($prompts, 'id');
$tagMap = [];
if ($promptIds) {
    $in = implode(',', array_fill(0, count($promptIds), '?'));
    $tagQ = $pdo->prepare("SELECT pt.prompt_id, t.name FROM prompt_tags pt JOIN tags t ON pt.tag_id = t.id WHERE pt.prompt_id IN ($in)");
    $tagQ->execute($promptIds);
    foreach ($tagQ as $row) {
        $tagMap[$row['prompt_id']][] = $row['name'];
    }
    foreach ($prompts as &$pr) $pr['tags'] = $tagMap[$pr['id']] ?? [];
    unset($pr);
} else {
    foreach ($prompts as &$pr) $pr['tags'] = [];
    unset($pr);
}
?>

<form method="get" class="mb-4 flex gap-2">
    <input name="q" placeholder="Tìm..." value="<?= htmlspecialchars($_GET['q']??'') ?>" class="border rounded px-2 py-1" />
    <button class="bg-blue-600 text-white px-3 py-1 rounded">Tìm</button>
</form>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
<?php
$isPremium = user_role() === 'premium';
$isPublic = false;
foreach ($prompts as $pr) {
    $isOwner = isset($pr['author_id']) && $pr['author_id'] == $user_id;
    $isTrash = true;
    include __DIR__.'/../components/prompt_card.php';
}
?>
</div>

<?php include __DIR__.'/../components/pagination.php'; ?>

<!-- Modal xem chi tiết Prompt -->
<div id="view-prompt-modal-root" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center"></div>

<script>
const BASE_PATH = '<?= BASE_PATH ?>';
function restorePrompt(id) {
  if(confirm("Khôi phục prompt này?")) fetch(BASE_PATH + '../api/prompts_api.php?action=restore&id='+id)
    .then(res=>res.json()).then(data=>{ if(data.success) location.reload(); else alert(data.message||"Lỗi!"); });
}
function hardDeletePrompt(id) {
  alert("Chức năng đang phát triển");
  /*if(confirm("Xóa vĩnh viễn prompt này?")) fetch(BASE_PATH + '../api/prompts_api.php?action=hard_delete&id='+id)
    .then(res=>res.json()).then(data=>{ if(data.success) location.reload(); else alert(data.message||"Lỗi!"); });
  */
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

</script>
<?php include 'layout_end.php'; ?>
