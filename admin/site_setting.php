<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) die('No permission!');

$title = "Cài đặt hệ thống";


$tab = $_GET['tab'] ?? 'menu';

// Handle POST (save menu, save extra)
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $post_tab = $_POST['tab'] ?? 'menu';
    if ($post_tab === 'menu' && ($_POST['action']??'')==='save_menu') {
        // Save menu
        $menu_json = $_POST['menu'] ?? '[]';
        $pdo->prepare("REPLACE INTO site_setting (`key`,`value`) VALUES (?,?)")->execute(['menu_main', $menu_json]);
        header("Location: site_setting.php?tab=menu&success=1"); exit;
    }
    if ($post_tab === 'extra' && ($_POST['action']??'')==='save_extra') {
        $enable_premium_create = !empty($_POST['enable_premium_create']) ? 1 : 0;
        $pdo->prepare("REPLACE INTO site_setting (`key`,`value`) VALUES (?,?)")->execute(['enable_premium_create', $enable_premium_create]);

        $roles = $_POST['allow_request_role'] ?? [];
        $pdo->prepare("REPLACE INTO site_setting (`key`,`value`) VALUES (?,?)")->execute(['allow_request_role', json_encode($roles)]);
        header("Location: site_setting.php?tab=extra&success=1"); exit;
    }
}

// Load setting
$premium_create_enable = get_site_setting('enable_premium_create') == 1;
$menu_json = get_site_setting('menu_main') ?: '[]';
$allow_request_role = get_site_setting('allow_request_role') ?: '[]';

include 'layout.php';
?>

<div class="mb-4">
    <ul class="flex gap-6 border-b">
        <li><a href="?tab=menu" class="py-2 px-3 border-b-2 <?= $tab==='menu'?'border-blue-600 font-bold':'' ?>">Menu</a></li>
        <li><a href="?tab=extra" class="py-2 px-3 border-b-2 <?= $tab==='extra'?'border-blue-600 font-bold':'' ?>">Extra</a></li>
        <!-- Thêm các tab khác nếu có -->
    </ul>
</div>

<div id="menu-tab" class="mt-6" style="<?= $tab==='menu'?'':'display:none' ?>">
    <h2 class="text-xl font-bold mb-4">Menu Items</h2>
    <form id="form-menu" method="POST">
        <input type="hidden" name="tab" value="menu">
        <input type="hidden" name="action" id="menu-action" value="save_menu">
        <div id="menu-items-list"></div>
        <input type="hidden" name="menu" id="menu-json-input">
        <button type="button" onclick="saveMenuItems()" class="bg-blue-600 text-white px-4 py-2 rounded ml-4">Lưu thay đổi</button>
        <button type="button" onclick="addMenuItem()" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">+ Thêm Menu</button>
    </form>
</div>

<div id="extra-tab" class="mt-6" style="<?= $tab==='extra'?'':'display:none' ?>">
    <h2 class="text-xl font-bold mb-4">Setting Extra</h2>
    <form id="form-extra" method="POST">
        <input type="hidden" name="tab" value="extra">
        <label class="flex gap-2 items-center">
            <input type="checkbox" name="enable_premium_create" id="enable-premium-create"
                <?= $premium_create_enable ? 'checked' : '' ?>>
            Cho phép Premium thêm Prompt
        </label>

        <label class="block mt-4 mb-2 font-semibold">Quyền được gửi yêu cầu Prompt:</label>
        <label><input type="checkbox" name="allow_request_role[]" value="user" <?= strpos($allow_request_role, 'user')!==false?'checked':'' ?>> User Free</label>
        <label><input type="checkbox" name="allow_request_role[]" value="premium" <?= strpos($allow_request_role, 'premium')!==false?'checked':'' ?>> Premium</label>
        <label class="flex gap-2 items-center"><input type="checkbox" disabled checked> Admin/Root (mặc định luôn có)</label>

        <button type="submit" name="action" value="save_extra" class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Lưu thay đổi</button>
    </form>
</div>

<script>
let menuItems = <?= $menu_json ?>;
if (!Array.isArray(menuItems)) menuItems = [];
function renderMenuList() {
  const wrapper = document.getElementById('menu-items-list');
  wrapper.innerHTML = menuItems.map((item, idx) => `
    <div class="menu-item flex items-center gap-3 mb-3 bg-white p-3 rounded shadow" data-idx="${idx}">
      <span class="handle cursor-move text-xl text-gray-400 mr-2">&#x2630;</span>
      <input type="text" value="${item.label||''}" oninput="menuItems[${idx}].label=this.value" placeholder="Label" class="border px-2 py-1 rounded flex-1" />
      <input type="text" value="${item.url||''}" oninput="menuItems[${idx}].url=this.value" placeholder="URL" class="border px-2 py-1 rounded flex-1" />
      <input type="checkbox" ${item.active ? 'checked' : ''} onchange="menuItems[${idx}].active=this.checked" /> Active
      <button onclick="removeMenuItem(${idx})" class="ml-2 text-red-600 text-xl">&times;</button>
    </div>
  `).join('');
  // Kích hoạt drag & drop sau khi render
  enableSortable();
}

function enableSortable() {
  let wrapper = document.getElementById('menu-items-list');
  if (!wrapper) return;
  if (window.menuSortable) window.menuSortable.destroy();
  window.menuSortable = Sortable.create(wrapper, {
    handle: '.handle',
    animation: 150,
    onEnd: function (evt) {
      // Cập nhật lại mảng menuItems theo vị trí mới
      const oldIndex = evt.oldIndex;
      const newIndex = evt.newIndex;
      if (oldIndex !== newIndex) {
        const moved = menuItems.splice(oldIndex, 1)[0];
        menuItems.splice(newIndex, 0, moved);
        renderMenuList(); // Re-render để cập nhật index
      }
    }
  });
}

function addMenuItem() {
  menuItems.push({label:'', url:'', active:true});
  renderMenuList();
}
function removeMenuItem(idx) {
  menuItems.splice(idx,1);
  renderMenuList();
}
function saveMenuItems() {
  // Cập nhật value trước khi submit form
  document.getElementById('menu-json-input').value = JSON.stringify(menuItems);
  // Đảm bảo action đúng
  document.getElementById('menu-action').value = "save_menu";
  // Submit form
  document.getElementById('form-menu').submit();
}

renderMenuList();
</script>
<?php include 'layout_end.php'; ?>
