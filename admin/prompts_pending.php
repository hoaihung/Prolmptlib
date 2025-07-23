<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) { header('Location: login.php'); exit; }
$title = "Duyệt Prompt Pending";
include 'layout.php';

// PHÂN TRANG
$page = max(1, intval($_GET['page'] ?? 1));
$perpage = 12;
$offset = ($page - 1) * $perpage;

// Lấy prompt pending
$sql_count = "SELECT COUNT(*) FROM prompts WHERE is_approved=0 AND is_deleted=0";
$total = $pdo->query($sql_count)->fetchColumn();
$total_pages = ceil($total / $perpage);

$sql = "SELECT p.*, c.name AS category_name, u.name AS author_name
        FROM prompts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE p.is_approved=0 AND p.is_deleted=0
        ORDER BY p.id DESC
        LIMIT $perpage OFFSET $offset";
$q = $pdo->prepare($sql); $q->execute();
$prompts = $q->fetchAll();

// Lấy tags
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
    foreach ($prompts as &$pr) $pr['tags'] = [];
    unset($pr);
}

// Favorite (cho đẹp, không dùng ở pending)
$user_favorites = [];
$isAdmin = true; // Đã kiểm tra quyền
$isPremium = false; // Không quan trọng ở pending
$isPendingList = true; // Để hiển thị nút duyệt/xóa

// Hiển thị grid card
echo '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">';
foreach ($prompts as $pr) {
    $isOwner = false;
    $isTrash = false;
    $isFavorite = false;
    $isPublic = false;
    include __DIR__.'/../components/prompt_card.php';
}
echo '</div>';

// Phân trang
if ($total_pages > 1) {
    include __DIR__.'/../components/pagination.php';
}
?>

<!-- Modal chi tiết -->
<div id="view-prompt-modal-root" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center"></div>

<script>
function viewPrompt(id) {
    fetch('../api/prompt_detail_modal.php?id=' + id + '&pending=1')
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
function approvePrompt(id) {
    if(confirm('Duyệt prompt này?')) fetch('../api/prompts_api.php?action=approve&id='+id)
        .then(res=>res.json()).then(data=>{ if(data.success) location.reload(); else alert(data.message||"Lỗi!"); });
}
function deletePrompt(id) {
    if(confirm("Từ chối và xóa prompt này?")) fetch('../api/prompts_api.php?action=reject&id='+id)
        .then(res=>res.json()).then(data=>{ if(data.success) location.reload(); else alert(data.message||"Lỗi!"); });
}
</script>
<?php include 'layout_end.php'; ?>
