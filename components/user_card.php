<?php
require_once __DIR__ . '/../includes/svg.php';
/** $user: array, $isAdmin: bool */
?>
<div class="bg-white rounded-2xl shadow-lg border p-5 flex flex-col gap-2 min-h-[180px] relative hover:shadow-2xl transition group">
  <div class="flex items-center gap-2 mb-1">
    <?= inline_svg('user', 'w-7 h-7 text-blue-600') ?>
    <span class="font-bold text-base text-gray-900"><?= htmlspecialchars($user['name']) ?></span>
    <?php if ($user['role'] === 'admin' || $user['role'] === 'root'): ?>
      <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded text-xs ml-2"><?= strtoupper($user['role']) ?></span>
    <?php endif; ?>
    <?php if ($user['role'] === 'premium'): ?>
      <span class="bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded text-xs ml-2">Premium</span>
      <span class="bg-gray-100 text-black-800 px-2 py-0.5 rounded text-xs ml-2"><?=$user['premium_expire']?></span>
    <?php endif; ?>
  </div>
  <div class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($user['email']) ?></div>
  <div class="flex gap-2 text-xs mb-2">
    <span class="px-2 py-1 rounded <?= $user['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500'; ?>">
      <?= $user['is_active'] ? 'Hoạt động' : 'Khóa'; ?>
    </span>
    <?php if ($user['is_deleted']): ?>
      <span class="bg-red-100 text-red-700 px-2 py-1 rounded">Đã xóa</span>
    <?php endif; ?>
  </div>
  <div class="flex gap-2 mt-auto">
    <button onclick="viewUser(<?= $user['id'] ?>)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-semibold flex-1 flex items-center gap-2 justify-center"><?= inline_svg('detail', 'w-5 h-5') ?> Xem</button>
    <?php if ($isAdmin && $user['role'] != 'root'): ?>
      <button onclick="editUser(<?= $user['id'] ?>)" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-xl flex items-center gap-2"><?= inline_svg('edit', 'w-5 h-5') ?> Sửa</button>
      <?php if ($user['is_active'] && !$user['is_deleted']): ?>
        <button class="bg-gray-400 text-white px-2 py-1 rounded text-xs" onclick="changeUserStatus(<?= $user['id'] ?>, 0)">Vô hiệu hóa</button>
      <?php elseif (!$user['is_active'] && !$user['is_deleted']): ?>
        <button class="bg-green-500 text-white px-2 py-1 rounded text-xs" onclick="changeUserStatus(<?= $user['id'] ?>, 1)">Kích hoạt</button>
        <button class="bg-red-500 text-white px-2 py-1 rounded text-xs" onclick="deleteUser(<?= $user['id'] ?>)">Xóa</button>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
