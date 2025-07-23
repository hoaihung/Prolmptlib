<?php
require_once 'includes/loader.php';
if (!is_logged_in()) header('Location: login.php');

$allow_roles = json_decode(get_site_setting('allow_request_role') ?? '["user","premium"]', true);
$role = user_role();
if (!in_array($role, $allow_roles) && !is_admin() && !is_root()) {
  header('Location: index.php');
}

$user_id = $_SESSION['user_id'];


$page = max(1, intval($_GET['page'] ?? 1));
$perpage = 15;
$offset = ($page-1)*$perpage;

$sql = "SELECT * FROM request_prompts WHERE user_id=? ORDER BY id DESC LIMIT $perpage OFFSET $offset";
$q = $pdo->prepare($sql); $q->execute([$user_id]);
$reqs = $q->fetchAll();
$total = $pdo->prepare("SELECT COUNT(*) FROM request_prompts WHERE user_id=?");
$total->execute([$user_id]); $total = $total->fetchColumn();
$total_pages = ceil($total/$perpage);

$title = "Yêu cầu Prompt của bạn";
include 'includes/header.php';
?>
<main class="max-w-2xl mx-auto mt-8">
  <h2 class="text-xl font-bold mb-4">Lịch sử yêu cầu Prompt</h2>
  <span class="text-base text-gray-700 ">Yêu cầu Prompt mới <a href="request_prompt.php" class="text-blue-600">tại đây</a></span>
  <div class="space-y-4 mt-4">
    <?php foreach($reqs as $r): ?>
      <div class="bg-white border rounded-xl shadow p-4">
        <div class="font-semibold"><?= htmlspecialchars($r['title']) ?></div>
        <div class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($r['description'])) ?></div>
        <div class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></div>
        <?php if ($r['is_done']): ?>
          <span class="inline-block bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs">Đã xử lý</span>
        <?php else: ?>
          <span class="inline-block bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded text-xs">Chờ xử lý</span>
        <?php endif ?>
      </div>
    <?php endforeach ?>
  </div>
  <?php if($total_pages>1): include 'components/pagination.php'; endif; ?>
</main>
<?php require_once 'includes/premium_modal.php'; ?>
<?php include 'includes/footer.php'; ?>
