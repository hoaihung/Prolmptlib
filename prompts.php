<?php
require_once 'includes/loader.php';

$user_id = $_SESSION['user_id'] ?? 0;
$is_guest = !is_logged_in();
$is_premium = is_premium() || is_admin() || is_root();
$is_admin = is_admin() || is_root();


// Query filter search/category/type nếu muốn
$where = "p.is_active=1 AND p.is_deleted=0 AND p.is_approved=1 AND p.is_locked=0";
$params = [];

if (!empty($_GET['category'])) {
    $where .= " AND p.category_id=?";
    $params[] = intval($_GET['category']);
}
if (!empty($_GET['type'])) {
    if ($_GET['type']==='free')     $where .= " AND p.premium=0";
    if ($_GET['type']==='premium')  $where .= " AND p.premium=1";
    if ($_GET['type']==='console')  $where .= " AND p.console_enabled=1";
}
if (!empty($_GET['tag'])) {
    $where .= " AND EXISTS (SELECT 1 FROM prompt_tags pt WHERE pt.prompt_id=p.id AND pt.tag_id=?)";
    $params[] = intval($_GET['tag']);
}


if (!empty($_GET['q'])) {
    $where .= " AND (p.title LIKE ? OR p.description LIKE ? OR u.name LIKE ?)";
    $params[] = '%'.$_GET['q'].'%';
    $params[] = '%'.$_GET['q'].'%';
    $params[] = '%'.$_GET['q'].'%';
}
// ... Thêm filter category/type nếu cần ...

// PHÂN TRANG
$page = max(1, intval($_GET['page'] ?? 1));
$perpage = 12;
$offset = ($page-1) * $perpage;

$sql_count = "SELECT COUNT(*) FROM prompts p LEFT JOIN users u ON p.author_id=u.id WHERE $where";
$q_count = $pdo->prepare($sql_count); $q_count->execute($params); $total = $q_count->fetchColumn();
$total_pages = ceil($total / $perpage);

$sql = "SELECT p.*, c.name as category_name, u.name as author_name
        FROM prompts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE $where ORDER BY p.id DESC
        LIMIT $perpage OFFSET $offset";
$q = $pdo->prepare($sql); $q->execute($params);
$prompts = $q->fetchAll();

$cats = $pdo->query("SELECT id, name FROM categories")->fetchAll();
// Lấy 10 tag nổi bật
$tags = $pdo->query("SELECT t.id, t.name, COUNT(pt.prompt_id) as cnt
                    FROM tags t 
                    JOIN prompt_tags pt ON t.id = pt.tag_id 
                    GROUP BY t.id, t.name 
                    ORDER BY cnt DESC LIMIT 10")->fetchAll();

// Merge tags nếu cần
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
?>
<?php include 'includes/header.php'; ?>
<style>
.prompt-filters {
  display: flex;
  flex-wrap: wrap;    /* Cho phép tự xuống dòng khi tràn */
  gap: 10px;
  margin-bottom: 18px;
}
.prompt-filter-btn {
  min-width: 88px;
  padding: 8px 18px;
  background: #fff;
  border: 1.5px solid #e0e7ef;
  border-radius: 999px;
  font-weight: 500;
  color: #333;
  transition: 0.16s;
  cursor: pointer;
  margin-bottom: 5px;  /* Cho đẹp khi wrap */
}
.prompt-filter-btn.active {
  background: #e3eafe;
  border-color: #2563eb;
  color: #2563eb;
  font-weight: 600;
  box-shadow: 0 2px 8px #2563eb15;
}
.prompt-filter-btn:hover {
  background: #f2f7fe;
  color: #2563eb;
}
</style>

<main class="max-w-6xl mx-auto px-2 md:px-6 pt-5 pb-8">
  <section class="mb-7">
    <h1 class="text-3xl md:text-4xl font-extrabold mb-2"><svg width="28" height="28" viewBox="0 0 24 24" class="inline-block mr-2 text-blue-700" fill="currentColor">
    <path d="M12 2a10 10 0 100 20 10 10 0 000-20zM11 14h2v2h-2zm0-8h2v6h-2z"/>
  </svg> Thư viện <span class="text-blue-700">Prompt AI</span> <span class="text-purple-600">công khai</span></h1>
    <p class="mb-4 text-gray-700 text-base md:text-lg">Khám phá hàng ngàn Prompt miễn phí, chọn lọc bởi cộng đồng và chuyên gia, cập nhật liên tục.<br>
      <span class="inline-block bg-yellow-50 text-yellow-800 text-xs px-2 py-1 rounded-lg mt-2">Hiện có <b><?= number_format($total) ?></b> prompt phù hợp!</span>
    </p>
    <!-- Search Form -->
    <form class="flex gap-2 mb-5 max-w-2xl" method="get" autocomplete="off">
      <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Tìm prompt tên, mô tả, tác giả..." class="border rounded-xl px-4 py-2 flex-1 bg-gray-50 focus:border-blue-600 focus:ring-1 focus:ring-blue-400 transition" />
      <button class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold shadow hover:bg-blue-700 transition">Tìm</button>
    </form>
    <!-- Filters sticky, scrollable -->
    <div class="prompt-filters" style="scrollbar-width:thin">
      <button class="filter-btn px-4 py-1.5 rounded-xl border border-blue-100 bg-white text-gray-700 font-semibold transition hover:bg-blue-50 hover:border-blue-500 focus:outline-none <?= empty($_GET['category']) && empty($_GET['type']) && empty($_GET['tag']) ? 'bg-blue-100 border-blue-500 text-blue-800' : '' ?>" data-filter="type" data-value="">Tất cả</button>
      <?php foreach($cats as $cat): ?>
        <button class="filter-btn px-4 py-1.5 rounded-xl border border-blue-100 bg-white text-gray-700 transition hover:bg-blue-50 focus:outline-none <?= (($_GET['category']??'')==$cat['id']) ? 'bg-blue-100 border-blue-500 text-blue-800 font-semibold' : '' ?>" data-filter="category" data-value="<?= $cat['id'] ?>">
          <?= htmlspecialchars($cat['name']) ?>
        </button>
      <?php endforeach; ?>
      <button class="filter-btn px-4 py-1.5 rounded-xl border border-blue-100 bg-white text-gray-700 transition hover:bg-blue-50 focus:outline-none <?= (($_GET['type']??'')==='free') ? 'bg-blue-100 border-blue-500 text-blue-800 font-semibold' : '' ?>" data-filter="type" data-value="free">Free</button>
      <button class="filter-btn px-4 py-1.5 rounded-xl border border-blue-100 bg-white text-gray-700 transition hover:bg-blue-50 focus:outline-none <?= (($_GET['type']??'')==='premium') ? 'bg-blue-100 border-blue-500 text-blue-800 font-semibold' : '' ?>" data-filter="type" data-value="premium">Premium</button>
      <button class="filter-btn px-4 py-1.5 rounded-xl border border-blue-100 bg-white text-gray-700 transition hover:bg-blue-50 focus:outline-none <?= (($_GET['type']??'')==='console') ? 'bg-blue-100 border-blue-500 text-blue-800 font-semibold' : '' ?>" data-filter="type" data-value="console">Console</button>
      <?php foreach($tags as $tag): ?>
        <button class="filter-btn px-4 py-1.5 rounded-xl border border-purple-100 bg-white text-purple-700 hover:bg-purple-50 focus:outline-none <?= (($_GET['tag']??'')==$tag['id']) ? 'bg-purple-100 border-purple-500 text-purple-800 font-semibold' : '' ?>" data-filter="tag" data-value="<?= $tag['id'] ?>">
          #<?= htmlspecialchars($tag['name']) ?>
        </button>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Prompt grid -->
  <?php
    include 'includes/category_colors.php';
    $catColors = cat_color();

  ?>
  <section class="mb-8 min-h-[400px]">
    <?php if (count($prompts) == 0): ?>
      <div class="text-center py-24">
        <div class="text-6xl mb-3 text-blue-300">🔎</div>
        <div class="text-xl font-semibold mb-2 text-gray-600">Không tìm thấy prompt nào phù hợp!</div>
        <div class="text-gray-500 mb-2">Thử điều chỉnh bộ lọc, từ khóa tìm kiếm hoặc <a href="prompts.php" class="underline text-blue-600">xem tất cả</a></div>
        <a href="request_prompt.php" class="mt-4 inline-block bg-pink-600 text-white font-bold px-5 py-2 rounded-xl hover:bg-pink-700 transition shadow">Yêu cầu Prompt riêng</a>
      </div>
    <?php else: ?>
      <?php 
        $isAdmin = is_admin() || is_root();
        $isPremium = user_role() === 'premium'; // Chuẩn hóa theo hàm role của bạn
        $isPublic = true;
     ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <?php foreach ($prompts as $pr): 
          $isOwner = isset($pr['author_id']) && $pr['author_id'] == $user_id;
          $isTrash = !empty($pr['is_deleted']);
         // echo $pr['category_id'] & ' | ' & $catColors[$pr['category_id']];
          $cardClass = $catColors[$pr['category_id']] ?? 'bg-white border-gray-200 text-gray-800';
          include 'components/prompt_card.php';
        endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- Gợi ý đề xuất nếu không filter (nếu muốn) -->

  <?php
    // Lấy 3 prompt gợi ý
    $sugg = $pdo->query("SELECT p.*, c.name as category_name FROM prompts p LEFT JOIN categories c ON p.category_id=c.id WHERE p.is_active=1 AND p.is_deleted=0 AND p.is_approved=1 ORDER BY p.view_count DESC LIMIT 3")->fetchAll();
    if ($sugg && count($sugg) > 0):
    ?>
    <section class="mb-8">
      <div class="text-lg font-bold mb-2 text-blue-700">🧠 Đề xuất cho bạn:</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
        <?php foreach ($sugg as $pr): ?>
          <?php $cardClass = $catColors[$pr['category_id']] ?? 'bg-white border-gray-200 text-gray-800';?>
          <?php include 'components/prompt_card.php'; ?>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>



  <!-- PHÂN TRANG -->
  <div class="my-6"><?php include __DIR__.'/components/pagination.php'; ?></div>
</main>


<!-- Modal wrapper -->
<div id="view-prompt-modal-root" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center"></div>

<!-- Toast copy -->
<div id="toast-copy" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-green-600 text-white rounded px-4 py-2 shadow-lg z-50 hidden text-base transition duration-300">
    Đã copy nội dung prompt!
</div>

</body>
<script>
const BASE_PATH = '<?= BASE_PATH ?>';
const BASE_URL = '<?=SITE_URL?>';
// Hàm hỗ trợ copy clipboard, fallback cho trình duyệt không hỗ trợ navigator.clipboard
function copyTextToClipboard(text) {
  // Sử dụng Clipboard API nếu có
  if (navigator.clipboard && navigator.clipboard.writeText) {
    return navigator.clipboard.writeText(text);
  }
  // Fallback cho các trình duyệt cũ
  const textarea = document.createElement('textarea');
  textarea.value = text;
  textarea.setAttribute('readonly', '');
  textarea.style.position = 'absolute';
  textarea.style.left = '-9999px';
  document.body.appendChild(textarea);
  textarea.select();
  try {
    document.execCommand('copy');
  } catch (err) {}
  document.body.removeChild(textarea);
  return Promise.resolve();
}
// Xem chi tiết prompt free
function viewPrompt(id) {
    fetch(BASE_PATH + 'api/prompt_detail_modal.php?id=' + id)
    .then(res => res.text())
    .then(html => {
        let root = document.getElementById('view-prompt-modal-root');
        root.innerHTML = html;
        root.classList.remove('hidden');
    });
}
function closeViewPrompt() {
    document.getElementById('view-prompt-modal-root').classList.add('hidden');
    document.getElementById('view-prompt-modal-root').innerHTML = '';
}
// Copy prompt code trong modal
function copyPromptCode() {
    const code = document.getElementById('prompt-view-code');
    if (!code) return;
    copyTextToClipboard(code.innerText).then(function() {
      showToastCopy();
    });
}
// Toast copy
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
function copyPromptShareLink(id) {
    const url = BASE_URL + 'prompts.php?id=' + id;
    copyTextToClipboard(url).then(function() {
      alert('Đã copy link chia sẻ!');
    });
}
// Guest xem premium
function showPremiumLocked() {
  // Có thể lấy từ DB popup info hoặc viết cứng luôn
  document.getElementById('view-prompt-content').innerHTML = `
    <div class="text-center p-6">
      <div class="text-4xl mb-3 text-purple-400"><svg class="mx-auto" width="50" height="50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 17v.01M12 7v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
      <h2 class="text-xl font-bold mb-2 text-purple-600">Nội dung Premium</h2>
      <div class="text-purple-700 mb-4">Bạn cần đăng nhập với tài khoản Premium để xem nội dung prompt này đầy đủ.</div>
      <a href="<?= BASE_PATH ?>login.php" class="text-blue-600 underline">Đăng nhập / Nâng cấp Premium</a>
    </div>
  `;
  document.getElementById('view-prompt-modal').classList.remove('hidden');
}

function closePremiumInfo() {
  document.getElementById('premium-info-modal').classList.add('hidden');
}

document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.onclick = function() {
    let f = btn.getAttribute('data-filter');
    let v = btn.getAttribute('data-value');
    let url = new URL(window.location.href);
    if(f==='type') {
      url.searchParams.set('type', v);
      url.searchParams.delete('category');
      url.searchParams.delete('tag');
    }
    if(f==='category') {
      url.searchParams.set('category', v);
      url.searchParams.delete('type');
      url.searchParams.delete('tag');
    }
    if(f==='tag') {
      url.searchParams.set('tag', v);
      url.searchParams.delete('type');
      url.searchParams.delete('category');
    }
    window.location = url;
  }
});

// --------- Tự động mở modal nếu có id trên URL ----------
document.addEventListener('DOMContentLoaded', function() {
    // Lấy id trên URL nếu có
    const urlParams = new URLSearchParams(window.location.search);
    const pid = urlParams.get('id');
    if (pid && /^\d+$/.test(pid)) { // Chỉ mở nếu id là số hợp lệ
        setTimeout(()=>viewPrompt(pid), 300); // Delay 1 chút để page render xong
        // Đẩy lại url về không có id nếu muốn, hoặc giữ nguyên để copy link share
        // window.history.replaceState(null, '', 'prompts.php');
    }
});


</script>
<?php require_once 'includes/premium_modal.php'; ?>
<?php include 'includes/footer.php'; ?>
