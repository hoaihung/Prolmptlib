<?php
// $mod['content'] là JSON [{"icon":"<svg>...</svg>","title":"...","desc":"..."}]
$data = json_decode($mod['content'], true) ?: [];
?>
<div class="module-feature grid md:grid-cols-3 gap-6 my-12">
  <?php foreach($data as $f): ?>
    <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center">
      <div class="mb-2"><?php 
        $icon = $f['icon'] ?? '';
        if (str_starts_with($icon, '<svg')) {
            // Nếu là SVG inline, in raw (không encode html)
            echo $icon;
        } else {
            // Nếu là tên, dùng inline_svg
            echo inline_svg($icon, 'w-8 h-8 text-blue-500');
        }
      ?>
      </div>
      <div class="font-bold text-lg mb-1"><?= htmlspecialchars($f['title']) ?></div>
      <div class="text-gray-500"><?= htmlspecialchars($f['desc']) ?></div>
    </div>
  <?php endforeach ?>
</div>
