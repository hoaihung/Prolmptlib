<?php
// Cấu hình group menu (có thể tuỳ chỉnh, thêm nhóm tuỳ ý)
$navGroups = [
    'Hệ thống' => [
        ['label'=>'Thống kê', 'href'=>'dashboard.php', 'role'=>'user'],
        ['label'=>'Thông báo', 'href'=>'notifications.php', 'role'=>'user'],
        ['label'=>'NotifyCP', 'href'=>'notificationcp.php', 'role'=>'admin'],
        ['label'=>'Users', 'href'=>'users.php', 'role'=>'admin'],
        ['label'=>'User Trash', 'href'=>'users_trash.php', 'role'=>'admin'],
    ],
    'Quản lý Prompt' => [
        ['label'=>'Prompts', 'href'=>'prompts.php', 'role'=>'user'],
        ['label'=>'Prompts Yêu thích', 'href'=>'prompts_favorite.php', 'role'=>'user'],
        ['label'=>'Prompts Pending', 'href'=>'prompts_pending.php', 'role'=>'admin'],
        ['label'=>'Prompts Trash', 'href'=>'prompts_trash.php', 'role'=>'premium'],
        ['label'=>'Prompts Request', 'href'=>'request_prompts.php', 'role'=>'admin'],
        ['label'=>'Console', 'href'=>'console.php', 'role'=>'user'],
    ],
    'Danh mục & Cấu hình' => [
        ['label'=>'Categories', 'href'=>'categories.php', 'role'=>'admin'],
        ['label'=>'Tags', 'href'=>'tags.php', 'role'=>'admin'],
        ['label'=>'Page Module', 'href'=>'modules_manager.php', 'role'=>'admin'],
        ['label'=>'Profiles', 'href'=>'profiles.php', 'role'=>'user'],  
        ['label'=>'Settings', 'href'=>'site_setting.php', 'role'=>'admin'],
    ],
];

// Helper kiểm tra quyền
/*function has_role($role) {
    if (!$role) return true;
    if ($role === 'admin') return is_admin() || is_root();
    if ($role === 'root') return is_root();
    if ($role === 'premium') return user_role() === 'premium' || is_admin() || is_root();
    if ($role === 'user') return is_logged_in();
    return false;
}*/
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'PromptLib Admin'; ?></title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml" />
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <?php if (!empty($custom_css)) echo $custom_css; ?>
    <!-- Choices.js CSS/JS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        /* Fix mobile: sidebar trượt, overlay */
        #sidebar-mobile-bg {
            display: none;
            position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 40;
        }
        @media (max-width: 900px) {
            #sidebar { left: -260px; transition: left .25s; z-index: 50; }
            #sidebar.open { left: 0; }
            #sidebar-mobile-bg.open { display: block; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <button id="btn-open-sidebar" class="block md:hidden text-2xl text-blue-700 mr-2">&#9776;</button>
            <a href="dashboard.php" class="text-2xl font-bold text-blue-700">PromptLib Admin</a>
        </div>
        <nav class="space-x-3 flex items-center">
            <a href="<?= SITE_URL ?>index.php" class="text-gray-700 hover:text-blue-700 font-medium">Trang chủ</a>
            <a href="logout.php" class="bg-red-600 text-white px-3 py-1.5 rounded-xl ml-2">Đăng xuất</a>
        </nav>
    </header>
    <!-- Overlay cho mobile sidebar -->
    <div id="sidebar-mobile-bg" onclick="closeSidebar()"></div>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-60 bg-white shadow-md px-4 py-6 flex flex-col overflow-y-auto max-h-screen fixed md:static left-0 top-0 h-full z-50 transition-all duration-200">
            <div class="flex items-center justify-between mb-8">
                <span class="font-bold text-xl text-blue-700">PromptLib</span>
                <button class="block md:hidden text-2xl" onclick="closeSidebar()">&times;</button>
            </div>
            <nav class="flex-1 space-y-5">
                <?php $groupIdx=0; foreach ($navGroups as $group => $items): ?>
                <div>
                    <button type="button" class="font-semibold flex items-center gap-2 text-blue-700 focus:outline-none group-btn"
                        onclick="toggleMenuGroup(<?= $groupIdx ?>)">
                        <?= htmlspecialchars($group) ?>
                        <span id="arrow-<?= $groupIdx ?>" class="ml-2 transition-transform">&#9662;</span>
                    </button>
                    <div class="ml-3 mt-2 space-y-2 group-menu" id="menu-group-<?= $groupIdx ?>" style="display:<?= $groupIdx==0?'block':'block' ?>">
                        <?php foreach ($items as $item): if (!has_role($item['role'])) continue; ?>
                            <a href="<?= $item['href'] ?>" class="block px-2 py-1 rounded hover:bg-blue-100 
                                <?= $item['label']=='User Trash' ? 'text-red-600' : '' ?>">
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php $groupIdx++; endforeach; ?>
            </nav>
            <div class="mt-8">
                <a href="logout.php" class="block text-red-600 font-semibold hover:underline">Đăng xuất</a>
            </div>
        </aside>
        <!-- Main content -->
        <div class="flex-1 flex flex-col ml-0 md:ml-10 transition-all duration-200">
            <header class="bg-white shadow p-4 flex items-center justify-between">
                <h1 class="text-xl font-bold"><?= $title ?? 'PromptLib Admin'; ?></h1>
                <div>
                    Xin chào, <span class="font-semibold"><?= $_SESSION['user_name'] ?? ''; ?></span>
                </div>
            </header>
            <main class="flex-1 p-4 md:p-6">
