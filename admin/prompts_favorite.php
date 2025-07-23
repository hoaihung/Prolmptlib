<?php
require_once '../includes/loader.php';
if (!is_logged_in()) header('Location: login.php');
$user_id = $_SESSION['user_id'];
$title = "Prompt yêu thích";
include 'layout.php';
require_once '../includes/svg.php';

// Lấy id prompt yêu thích
$ids = $pdo->query("SELECT prompt_id FROM prompt_favorites WHERE user_id=$user_id")->fetchAll(PDO::FETCH_COLUMN);
if (!$ids) $ids = [0];

// PHÂN TRANG
$in = implode(',', array_fill(0, count($ids), '?'));
$page = max(1, intval($_GET['page'] ?? 1));
$perpage = 12;
$offset = ($page - 1) * $perpage;

$sql_count = "SELECT COUNT(*) FROM prompts p 
              WHERE p.id IN ($in) AND p.is_deleted = 0";
$q_count = $pdo->prepare($sql_count);
$q_count->execute($ids);
$total = $q_count->fetchColumn();

$total_pages = ceil($total / $perpage);

$sql = "SELECT p.*, c.name AS category_name, u.name AS author_name
        FROM prompts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE p.id IN ($in) AND p.is_deleted=0
        ORDER BY p.id DESC
        LIMIT $perpage OFFSET $offset";
$q = $pdo->prepare($sql);
$q->execute($ids);
$prompts = $q->fetchAll();

// Lấy tất cả tags gắn với prompt
$promptIds = array_column($prompts, 'id');
if ($promptIds) {
  $in_tags = implode(',', array_fill(0, count($promptIds), '?'));
  $tagMap = [];
  $tagQ = $pdo->prepare("SELECT pt.prompt_id, t.name FROM prompt_tags pt JOIN tags t ON pt.tag_id = t.id WHERE pt.prompt_id IN ($in_tags)");
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

// Lấy danh sách yêu thích để truyền vào card/modal
$user_favorites = [];
if (is_logged_in()) {
    $uid = $_SESSION['user_id'];
    $qfav = $pdo->prepare("SELECT prompt_id FROM prompt_favorites WHERE user_id=?");
    $qfav->execute([$uid]);
    $user_favorites = $qfav->fetchAll(PDO::FETCH_COLUMN);
}

// Hiển thị Grid Card
echo '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">';
foreach ($prompts as $pr) {
    $isFavorite = true; // Ở trang này đều là yêu thích
    $isAdmin = is_admin() || is_root();
    $isOwner = false;
    $isTrash = false;
    $isPremium = user_role()==='premium';
    $canCopy = !$pr['premium'] || $isAdmin || $isPremium;
    $isPublic = false;
    include __DIR__.'/../components/prompt_card.php';
}
echo '</div>';

if ($total_pages > 1) include __DIR__.'/../components/pagination.php';
?>

<!-- Modal chi tiết Prompt -->
<div id="view-prompt-modal-root" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center"></div>
<script>
function viewPrompt(id) {
    fetch('../api/prompt_detail_modal.php?id=' + id)
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
function toggleFavorite(prompt_id, btn){
    fetch('../api/favorite_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=toggle&prompt_id=' + prompt_id
    }).then(res=>res.json()).then(data=>{
        if(data.success){
          //let card = document.getElementById('card-prompt-'+prompt_id);
          //if(card) card.style.display = 'none';
            if(data.favorite)
                btn.innerHTML = "<?= inline_svg('heart-fill', 'w-6 h-6 text-red-500') ?>";
            else
                btn.innerHTML = "<?= inline_svg('heart', 'w-6 h-6 text-gray-400') ?>";

        }
    });
}
</script>
<?php include 'layout_end.php'; ?>