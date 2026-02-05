<?php
$user_id = $_SESSION['user_id'] ?? 0;
$count_unread = 0;
if ($user_id) {
    $sql = "SELECT COUNT(*) FROM notifications n 
            LEFT JOIN notification_reads r ON n.id=r.noti_id AND r.user_id=? 
            WHERE (n.user_id=? OR n.user_id IS NULL) AND r.id IS NULL";
    $q = $pdo->prepare($sql);
    $q->execute([$user_id, $user_id]);
    $count_unread = $q->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title><?= $page_title ?? 'PromptLib – Kho Prompt Public' ?></title>
  <meta name="description" content="<?= $page_desc ?? 'Thư viện Prompt AI công khai, chia sẻ prompt ChatGPT, Gemini, Midjourney...' ?>">
  <?php if(isset($og_image)): ?>
  <meta property="og:image" content="<?= $og_image ?>">
  <?php endif; ?>
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    #user-menu { min-width:170px; box-shadow: 0 4px 24px 0 #0002; }
    #user-menu a { white-space: nowrap; }
    .text-gradient {
      background: linear-gradient(90deg,#2563eb 10%,#a21caf 80%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .scrollbar-thin { scrollbar-width: thin; }
    .scrollbar-thumb-blue-200::-webkit-scrollbar-thumb { background: #dbeafe; }
  </style>
</head>
<body class="bg-gradient-to-b from-blue-50 via-white to-purple-50 min-h-screen text-gray-800">
<header class="bg-white shadow px-4 py-3 flex items-center justify-between sticky top-0 z-10">

  <!-- Logo -->
  <div class="flex items-center gap-3">
    <a href="<?=SITE_URL?>" class="text-2xl font-bold text-blue-700">PromptLib</a>
    <!-- Hamburger mobile -->
    <button id="menu-toggle" class="block md:hidden ml-1" onclick="toggleMobileMenu()" aria-label="Mở menu">
      <svg viewBox="0 0 24 24" fill="none" class="w-7 h-7"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path></svg>
    </button>
  </div>

  <!-- Main Menu desktop -->
  <?php $menu = json_decode(get_site_setting('menu_main'), true) ?? []; ?>
  <nav class="hidden md:flex gap-3 ml-6 flex-1">
    <?php foreach($menu as $item): if(empty($item['active'])) continue; 
        $mUrl = $item['url'];
        if (!preg_match('/^https?:\/\//', $mUrl)) {
            $mUrl = SITE_URL . ltrim($mUrl, '/');
        }
    ?>
      <a href="<?= htmlspecialchars($mUrl) ?>" class="px-3 py-2 hover:text-blue-600"><?= htmlspecialchars($item['label']) ?></a>
    <?php endforeach ?>
    <a href="<?=SITE_URL?>news" class="px-3 py-2 hover:text-blue-600">Tin tức</a>
  </nav>

  <!-- User/Avatar/Action -->
  <?php if(is_logged_in()): ?>
  <div class="flex items-center gap-3">
    <a href="<?=SITE_URL?>my-prompts" class="hidden md:block bg-blue-50 text-blue-700 px-3 py-2 rounded-lg font-medium hover:bg-blue-100 transition">Prompt của tôi</a>
    <div class="relative flex-shrink-0" id="user-avatar-box">
    <button id="avatar-btn" class="w-10 h-10 bg-blue-200 rounded-full flex items-center justify-center text-lg font-bold focus:outline-none"><?= strtoupper(substr($_SESSION['user_name'],0,1)) ?></button>
    <?php if ($count_unread > 0): ?>
      <span class="absolute top-0 right-0 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs"><?= $count_unread ?></span>
    <?php endif; ?>
    <div id="user-menu" class="absolute right-0 mt-2 bg-white border rounded-lg shadow p-3 z-30 w-44 hidden transition">
      <div class="font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
      <div class="text-xs text-gray-500"><?= htmlspecialchars($_SESSION['user_role']) ?></div>
      <hr>
      <a href="<?=SITE_URL?>admin/notifications.php" class="block px-3 py-2 hover:bg-gray-100">Thông báo</a>
      <a href="<?=SITE_URL?>admin/" class="block px-3 py-2 hover:bg-gray-100">Quản trị</a>
      <a href="<?=SITE_URL?>admin/profiles.php" class="block px-3 py-2 hover:bg-gray-100">Cài đặt cá nhân</a>
      <?php 
        $allow_roles = json_decode(get_site_setting('allow_request_role') ?? '["user","premium"]', true);
        $role = user_role();
        if (in_array($role, $allow_roles) || is_admin() || is_root()) {
      ?>
        <a href="<?=SITE_URL?>request_prompt.php" class="block px-3 py-2 hover:bg-gray-100">Yêu cầu Prompt</a>
      <?php } else { ?>
        <a href="#" onclick="showPremiumInfo()" class="block px-3 py-2 hover:bg-gray-100">Yêu cầu Prompt</a>
      <?php } ?>
      <a href="<?=SITE_URL?>admin/logout.php" class="block px-3 py-2 hover:bg-gray-100 text-red-600">Đăng xuất</a>
    </div>
  </div>
  <?php else: ?>
    <a href="<?=SITE_URL?>login.php" class="bg-blue-600 text-white px-4 py-2 rounded ml-3">Đăng nhập</a>
  <?php endif; ?>
</header>

<!-- Mobile menu offcanvas -->
<div id="mobile-menu" class="fixed inset-0 z-40 bg-black bg-opacity-40 hidden md:hidden">
  <div class="bg-white w-60 min-h-full p-6 shadow-lg">
    <button onclick="toggleMobileMenu()" class="text-xl mb-4">&times;</button>
    <?php foreach($menu as $item): if(empty($item['active'])) continue; 
        $mUrl = $item['url'];
        if (!preg_match('/^https?:\/\//', $mUrl)) {
            $mUrl = SITE_URL . ltrim($mUrl, '/');
        }
    ?>
      <a href="<?= htmlspecialchars($mUrl) ?>" class="block px-3 py-2 mb-2 rounded hover:bg-blue-50"><?= htmlspecialchars($item['label']) ?></a>
    <?php endforeach ?>
    <a href="<?=SITE_URL?>news" class="block px-3 py-2 mb-2 rounded hover:bg-blue-50">Tin tức</a>
    <hr class="my-2">
    <?php if(is_logged_in()): ?>
      <div class="font-semibold mb-1"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
      <div class="text-xs text-gray-500 mb-3"><?= htmlspecialchars($_SESSION['user_role']) ?></div>
      <a href="<?=SITE_URL?>my-prompts" class="block px-3 py-2 hover:bg-gray-100 font-semibold text-blue-700">Prompt của tôi</a>
      <a href="<?=SITE_URL?>admin/notifications.php" class="block px-3 py-2 hover:bg-gray-100">Thông báo</a>
      <a href="<?=SITE_URL?>admin/" class="block px-3 py-2 hover:bg-gray-100">Quản trị</a>
      <a href="<?=SITE_URL?>admin/profiles.php" class="block px-3 py-2 hover:bg-gray-100">Cài đặt cá nhân</a>
      <?php 
        $allow_roles = json_decode(get_site_setting('allow_request_role') ?? '["user","premium"]', true);
        $role = user_role();
        if (in_array($role, $allow_roles) || is_admin() || is_root()) {
      ?>
        <a href="<?=SITE_URL?>request_prompt.php" class="block px-3 py-2 hover:bg-gray-100">Yêu cầu Prompt</a>
      <?php } else { ?>
        <a href="#" onclick="showPremiumInfo()" class="block px-3 py-2 hover:bg-gray-100">Yêu cầu Prompt</a>
      <?php } ?>
      <a href="<?=SITE_URL?>admin/logout.php" class="block px-3 py-2 hover:bg-gray-100 text-red-600">Đăng xuất</a>
    <?php else: ?>
      <a href="<?=SITE_URL?>login.php" class="block bg-blue-600 text-white px-4 py-2 rounded mb-2">Đăng nhập</a>
    <?php endif; ?>
  </div>
</div>

<script>
// Hamburger menu (mobile)
function toggleMobileMenu() {
  document.getElementById('mobile-menu').classList.toggle('hidden');
}

// Avatar dropdown
document.addEventListener('DOMContentLoaded', function() {
  const avatarBtn = document.getElementById('avatar-btn');
  const userMenu = document.getElementById('user-menu');
  if (avatarBtn && userMenu) {
    avatarBtn.onclick = function(e) {
      e.stopPropagation();
      userMenu.classList.toggle('hidden');
    };
    document.addEventListener('click', function(e) {
      if (!userMenu.classList.contains('hidden')) {
        if (!userMenu.contains(e.target) && e.target !== avatarBtn) {
          userMenu.classList.add('hidden');
        }
      }
    });
  }
});
</script>
