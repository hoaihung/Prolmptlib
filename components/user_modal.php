<?php
require_once __DIR__ . '/../includes/svg.php';
/** @var $user array */
?>
<div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-lg relative">
  <button class="absolute top-2 right-3 text-gray-400 text-3xl" onclick="closeViewUser()">&times;</button>
  <div class="flex items-center gap-3 mb-3">
    <?= inline_svg('user', 'w-8 h-8 text-blue-600') ?>
    <span class="text-2xl font-bold"><?= htmlspecialchars($user['name']) ?></span>
  </div>
  <div class="text-gray-600 text-base mb-2"><?= htmlspecialchars($user['email']) ?></div>
  <div class="mb-2 flex gap-2 text-sm">
    <span>Vai trò: </span>
    <?php if ($user['role'] === 'premium'): ?>
      <span class="bg-yellow-100 text-yellow-800 px-2 rounded">Premium</span>
    <?php else: ?>
      <span class="bg-red-100 text-red-800 px-2 rounded">Admin</span>
      <?php endif; ?>
    <?php if ($user['is_active']): ?>
      <span class="bg-green-100 text-green-700 px-2 rounded">Đang hoạt động</span>
    <?php else: ?>
      <span class="bg-red-100 text-red-700 px-2 rounded">Đã khóa</span>
    <?php endif; ?>
  </div>
  <?php if ($user['role'] === 'premium'): ?>
    <div class="mb-2 text-base">
      Ngày hết hạn: <?=$user['premium_expire']?>
    </div>
  <?php endif; ?>
  <div class="mt-5 flex justify-end">
    <button onclick="closeViewUser()" class="bg-gray-600 text-white px-6 py-2 rounded-lg">Đóng</button>
  </div>
</div>
