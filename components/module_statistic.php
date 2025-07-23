<?php
// $mod['content'] là JSON dạng [{"title":"...","desc":123}, ...]
$data = json_decode($mod['content'], true) ?: [];
?>
<div class="module-statistic flex flex-wrap gap-6 justify-center my-12">
  <?php foreach($data as $item): ?>
    <div class="bg-white rounded-xl shadow p-5 min-w-[120px] text-center">
      <div class="text-2xl font-bold text-blue-700"><?= htmlspecialchars($item['desc']) ?></div>
      <div class="text-gray-600"><?= htmlspecialchars($item['title']) ?></div>
    </div>
  <?php endforeach ?>
</div>
