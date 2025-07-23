<?php
require_once '../includes/loader.php';
require_once '../includes/svg.php';

// Lấy id prompt
$id = (int)($_GET['id'] ?? 0);
if (!$id) { showNotFoundModal(); exit; }

// Lấy prompt
$sql = "SELECT p.*, c.name as category_name, u.name as author_name
        FROM prompts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE p.id=? LIMIT 1";
$q = $pdo->prepare($sql); $q->execute([$id]);
$pr = $q->fetch(PDO::FETCH_ASSOC);
if (!$pr) { showNotFoundModal(); exit; }

// Lấy tag
$tagMap = [];
$tagQ = $pdo->prepare("SELECT t.name FROM prompt_tags pt JOIN tags t ON pt.tag_id = t.id WHERE pt.prompt_id = ?");
$tagQ->execute([$id]);
$pr['tags'] = [];
foreach($tagQ as $row) $pr['tags'][] = $row['name'];

// Lấy trạng thái yêu thích
$user_favorites = [];
if (is_logged_in()) {
    $uid = $_SESSION['user_id'];
    $qfav = $pdo->prepare("SELECT prompt_id FROM prompt_favorites WHERE user_id=? AND prompt_id=?");
    $qfav->execute([$uid, $id]);
    $user_favorites = $qfav->fetchAll(PDO::FETCH_COLUMN);
}

// Quyền xem prompt
$user_id = $_SESSION['user_id'] ?? 0;
$isAdmin = is_admin() || is_root();
$isOwner = $pr['author_id'] == $user_id;
$isPremium = is_premium();
$canCopy = !$pr['premium'] || $isPremium || $isAdmin || $isOwner;
$isTrash = $pr['is_deleted'] ? true : false;
$isGuestOrFree = !$isPremium && !$isAdmin && !$isOwner;
$isLocked = !empty($pr['is_locked']);

// 3. Nếu bị LOCK => chỉ admin/root xem, còn lại báo lỗi
if ($isLocked && !$isAdmin) { showNotFoundModal("Prompt đã bị khóa bởi quản trị."); exit; }

// 4. Nếu bị xóa, chưa duyệt, chưa active
if (($pr['is_deleted'] || !$pr['is_active'] || !$pr['is_approved']) && !$isAdmin && !$isOwner) {
    showNotFoundModal("Prompt đã bị ẩn hoặc chưa duyệt."); exit;
}

// 5. Nếu là prompt premium, mà user không phải premium, admin, owner
if ($pr['premium'] && !$isAdmin && !$isOwner && !$isPremium) {
    showNotFoundModal("Bạn cần nâng cấp Premium để xem prompt này."); exit;
}

//update view count
$pdo->prepare("UPDATE prompts SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);


try {
    // Chuẩn bị truy vấn kiểm tra trong 1 phút gần nhất
    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*) 
        FROM prompt_views 
        WHERE user_id = ? AND prompt_id = ? AND viewed_at >= NOW() - INTERVAL 1 MINUTE
    ");
    $stmtCheck->execute([$uid, $id]);
    $count = $stmtCheck->fetchColumn();

    if ($count == 0) {
        // Nếu không có bản ghi nào trong vòng 1 phút, tiến hành insert
        $stmtInsert = $pdo->prepare("
            INSERT INTO prompt_views (user_id, prompt_id) 
            VALUES (?, ?)
        ");
        $stmtInsert->execute([$uid, $id]);
    } else {
        //
    }
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}


include '../includes/category_colors.php';
$catColors = cat_color();
$cardClass = $catColors[$pr['category_id']] ?? 'bg-white border-gray-200 text-gray-800';

// Hiển thị modal bình thường
ob_start();
include __DIR__.'/../components/prompt_modal.php';
$html = ob_get_clean();

header('Content-Type: text/html; charset=utf-8');
echo $html;


// ----- Hàm show modal thông báo lỗi -----
function showNotFoundModal() {
    ?>
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-xl relative text-center">
      <button class="absolute top-2 right-3 text-gray-400 text-3xl" onclick="closeViewPrompt()">&times;</button>
      <div class="text-3xl text-red-500 mb-2"><?= inline_svg('warning', 'w-10 h-10 mx-auto') ?></div>
      <div class="text-lg font-bold mb-2">Prompt không tồn tại hoặc bạn không có quyền xem.</div>
      <div class="text-gray-500 mb-3">
        Prompt này có thể đã bị xóa, chưa được duyệt, bị ẩn hoặc bạn không có quyền truy cập.
      </div>
      <button class="bg-blue-600 text-white px-4 py-2 rounded" onclick="closeViewPrompt()">Đóng</button>
    </div>
    <?php
}
?>
