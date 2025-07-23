<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) exit('No permission!');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST['form_action'] ?? '';
    if ($form === 'pages') {
      // Thêm/Sửa Page     
      $id = intval($_POST['id'] ?? 0);
      $name = trim($_POST['name']);
      $slug = trim($_POST['slug']);
      $desc = trim($_POST['description']);
      $active = isset($_POST['is_active']) ? 1 : 0;

      if ($id) {
          $pdo->prepare("UPDATE pages SET name=?, slug=?, description=?, is_active=? WHERE id=?")
              ->execute([$name, $slug, $desc, $active, $id]);
      } else {
          $pdo->prepare("INSERT INTO pages (name, slug, description, is_active) VALUES (?,?,?,?)")
              ->execute([$name, $slug, $desc, $active]);
      }
      header('Location:modules_manager.php?tab=pages'); exit;     
    }
    if ($form === 'modules') {
      $is_active = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;
      if ($_POST['id']) {
        $pdo->prepare("UPDATE modules SET title=?, type=?, content=?, is_active=? WHERE id=?")
            ->execute([$_POST['title'], $_POST['type'], $_POST['content'], $is_active, $_POST['id']]);
    } else {
        $pdo->prepare("INSERT INTO modules (title, type, content, is_active) VALUES (?,?,?,?)")
            ->execute([$_POST['title'], $_POST['type'], $_POST['content'], $is_active]);
      }
      header('Location: modules_manager.php?tab=modules'); exit;
    }
    if ($form === 'page_modules') {
      $page_id = intval($_POST['page_id']);
      $pdo->prepare("DELETE FROM page_modules WHERE page_id=?")->execute([$page_id]);
      if (!empty($_POST['modules'])) {
          foreach($_POST['modules'] as $mid) {
              $sort = intval($_POST['sort_order'][$mid] ?? 0);
              $pdo->prepare("INSERT INTO page_modules (page_id, module_id, sort_order) VALUES (?,?,?)")
                  ->execute([$page_id, $mid, $sort]);
          }
      }
      header("Location: modules_manager.php?tab=page_modules&page_id=". $page_id);
      exit;
      }
}

if ($_GET['delete'] ?? false) {
    $tab = $_GET['tab'] ?? '';
    if ($tab === 'pages') {
      $del_id = intval($_GET['delete']);
      // Xóa các bản ghi liên kết ở page_modules trước
      $pdo->prepare("DELETE FROM page_modules WHERE page_id=?")->execute([$del_id]);
      // Sau đó mới xóa page
      $pdo->prepare("DELETE FROM pages WHERE id=?")->execute([$del_id]);
      header('Location: modules_manager.php?tab=pages');
      exit;
    }
    if ($tab === 'modules') {
      $pdo->prepare("DELETE FROM modules WHERE id=?")->execute([$_GET['delete']]);
    header('Location: modules_manager.php?tab=modules'); 
      exit;
    }
    // ... Các trường hợp khác nếu có
}

$title = "Quản lý Trang & Modules";
include 'layout.php';
?>
<ul class="flex border-b gap-2 mb-6" id="tab-header">
  <li><button data-tab="tab-modules" class="tab-btn py-2 px-4 border-b-2 font-semibold border-blue-600 text-blue-700">Modules</button></li>
  <li><button data-tab="tab-page-modules" class="tab-btn py-2 px-4 border-b-2">Gán module cho Page</button></li>
  <li><button data-tab="tab-pages" class="tab-btn py-2 px-4 border-b-2">Pages</button></li>
</ul>
<div id="tab-modules" class="tab-content">
  <?php include 'modules_tab.php'; ?>
</div>
<div id="tab-page-modules" class="tab-content hidden">
  <?php include 'page_modules_tab.php'; ?>
</div>
<div id="tab-pages" class="tab-content hidden">
  <?php include 'pages_tab.php'; ?>
</div>

<script>
// Tab JS đơn giản
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.onclick = function() {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('border-blue-600','text-blue-700'));
    btn.classList.add('border-blue-600','text-blue-700');
    let tab = btn.getAttribute('data-tab');
    document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
    document.getElementById(tab).classList.remove('hidden');
  };
});
document.querySelector('.tab-btn').click();

// Tự động active đúng tab nếu có param ?tab=...
window.addEventListener('DOMContentLoaded', function() {
  const url = new URL(window.location);
  let tab = url.searchParams.get('tab') || 'modules';
  // Bản đồ tab theo tên
  let map = {
    'modules': 'tab-modules',
    'page_modules': 'tab-page-modules',
    'pages': 'tab-pages'
  };
  let tabId = map[tab] || 'tab-modules';
  // Click vào nút tab tương ứng
  document.querySelectorAll('.tab-btn').forEach(btn => {
    if (btn.getAttribute('data-tab') === tabId) btn.click();
  });
});


</script>
<?php include 'layout_end.php'; ?>
