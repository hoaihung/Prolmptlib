<?php
// components/pagination.php
// Yêu cầu biến $page (int), $total_pages (int) đã tồn tại ở ngoài file gọi
if ($total_pages > 1): ?>
<nav class="flex justify-center mt-8 mb-4">
  <ul class="inline-flex items-center space-x-1">
    <?php if ($page > 1): ?>
      <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$page-1])); ?>" class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-100">&laquo;</a></li>
    <?php endif;
    $from = max(1, $page - 2);
    $to = min($total_pages, $page + 2);
    for ($i = $from; $i <= $to; $i++): ?>
      <li>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$i])); ?>"
           class="px-3 py-2 rounded-lg <?= $i == $page ? 'bg-blue-600 text-white font-bold' : 'bg-gray-100 hover:bg-blue-100' ?>">
          <?php echo $i ?>
        </a>
      </li>
    <?php endfor; ?>
    <?php if ($page < $total_pages): ?>
      <li><a href="?<?php echo http_build_query(array_merge($_GET, ['page'=>$page+1])); ?>" class="px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-100">&raquo;</a></li>
    <?php endif; ?>
  </ul>
</nav>
<?php endif; ?>
