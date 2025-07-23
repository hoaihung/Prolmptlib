<?php
require_once '../includes/loader.php';
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

$title = "Quản lý Users";
include 'layout.php';
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-bold">Danh sách Users</h2>
    <button onclick="openUserModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl">+ Thêm User</button>
</div>

<form method="get" class="mb-4 flex space-x-2">
    <input name="q" placeholder="Tìm tên hoặc email..." value="<?php echo htmlspecialchars($_GET['q']??'') ?>" class="border rounded px-2 py-1" />
    <select name="status" class="border rounded px-2 py-1">
        <option value="">Tất cả</option>
        <option value="active" <?php if(($_GET['status']??'')=='active') echo 'selected'; ?>>Hoạt động</option>
        <option value="inactive" <?php if(($_GET['status']??'')=='inactive') echo 'selected'; ?>>Vô hiệu</option>
        <option value="deleted" <?php if(($_GET['status']??'')=='deleted') echo 'selected'; ?>>Đã xóa</option>
    </select>
    <button class="bg-blue-600 text-white px-3 py-1 rounded">Tìm</button>
</form>

<?php /*
<table class="min-w-full bg-white rounded-xl shadow">
    <thead>
        <tr>
            <th class="p-2">ID</th>
            <th class="p-2">Tên</th>
            <th class="p-2">Email</th>
            <th class="p-2">Vai trò</th>
            <th class="p-2">Trạng thái</th>
            <th class="p-2">Thao tác</th>
        </tr>
    </thead>
    <tbody id="users-table-body">
        <!-- Dữ liệu load ở đây bằng PHP hoặc AJAX -->
        <?php
        // Tìm tất cả user chưa xóa (mặc định)
		// users.php
		$where = "1";
		$params = [];
		if (!empty($_GET['q'])) {
			$where .= " AND (name LIKE ? OR email LIKE ?)";
			$params[] = '%'.$_GET['q'].'%';
			$params[] = '%'.$_GET['q'].'%';
		}
		if (!empty($_GET['status'])) {
			if ($_GET['status'] == 'active') $where .= " AND is_active=1 AND is_deleted=0";
			if ($_GET['status'] == 'inactive') $where .= " AND is_active=0 AND is_deleted=0";
			if ($_GET['status'] == 'deleted') $where .= " AND is_deleted=1";
		} else {
			$where .= " AND is_deleted=0";
		}
		$q = $pdo->prepare("SELECT * FROM users WHERE $where ORDER BY id DESC");
		$q->execute($params);

        while ($user = $q->fetch()):
        ?>
        <tr>
            <td class="p-2"><?php echo $user['id'] ?></td>
            <td class="p-2"><?php echo htmlspecialchars($user['name']) ?></td>
            <td class="p-2"><?php echo htmlspecialchars($user['email']) ?></td>
            <td class="p-2"><?php echo $user['role'] ?></td>
            <td class="p-2">
                <span class="px-2 py-1 rounded <?php echo $user['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500'; ?>">
                    <?php echo $user['is_active'] ? 'Hoạt động' : 'Khóa'; ?>
                </span>
            </td>
            <td class="p-2 flex space-x-2">
				<?php if ($user['role'] != 'root'): ?>
					<?php if ($user['is_active'] && !$user['is_deleted']): ?>
						<button class="bg-gray-400 text-white px-2 py-1 rounded text-xs"
							onclick="changeUserStatus(<?php echo $user['id'] ?>, 0)">Vô hiệu hóa</button>
					<?php elseif (!$user['is_active'] && !$user['is_deleted']): ?>
						<button class="bg-green-500 text-white px-2 py-1 rounded text-xs"
							onclick="changeUserStatus(<?php echo $user['id'] ?>, 1)">Kích hoạt</button>
						<button class="bg-red-500 text-white px-2 py-1 rounded text-xs"
							onclick="deleteUser(<?php echo $user['id'] ?>)">Xóa</button>
					<?php endif; ?>
				<?php endif; ?>
			</td>

        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
*/ ?>

<!-- DANH SÁCH USER -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
<?php
$where = "1";
$params = [];
if (!empty($_GET['q'])) {
    $where .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = '%'.$_GET['q'].'%';
    $params[] = '%'.$_GET['q'].'%';
}
if (!empty($_GET['status'])) {
    if ($_GET['status'] == 'active') $where .= " AND is_active=1 AND is_deleted=0";
    if ($_GET['status'] == 'inactive') $where .= " AND is_active=0 AND is_deleted=0";
    if ($_GET['status'] == 'deleted') $where .= " AND is_deleted=1";
} else {
    $where .= " AND is_deleted=0";
}

$page = max(1, intval($_GET['page'] ?? 1));
$perpage = 12;
$offset = ($page - 1) * $perpage;

// Đếm tổng số user (giống điều kiện lọc data)
$sql_count = "SELECT COUNT(*) FROM users WHERE $where";
$q_count = $pdo->prepare($sql_count);
$q_count->execute($params);
$total = $q_count->fetchColumn();
$total_pages = ceil($total / $perpage);

// Lấy dữ liệu user cho trang hiện tại
$sql = "SELECT * FROM users WHERE $where ORDER BY id DESC LIMIT $perpage OFFSET $offset";
$q = $pdo->prepare($sql);
$q->execute($params);

$isAdmin = is_admin() || is_root();
while ($user = $q->fetch()):
    include __DIR__.'/../components/user_card.php';
endwhile;
?>
</div>

<?php
// Nhúng component phân trang
include __DIR__.'/../components/pagination.php';
?>

<!-- Modal thêm/sửa user -->
<div id="user-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-xl w-full max-w-md relative">
        <button class="absolute top-2 right-3 text-gray-400 text-2xl" onclick="closeUserModal()">&times;</button>
        <h3 id="modal-title" class="text-lg font-bold mb-4"></h3>
        <form id="user-form">
            <input type="hidden" name="id" id="user-id">
            <div class="mb-3">
                <label class="font-semibold">Tên</label>
                <input type="text" name="name" id="user-name" class="w-full border rounded px-2 py-1" required>
            </div>
            <div class="mb-3">
                <label class="font-semibold">Email</label>
                <input type="email" name="email" id="user-email" class="w-full border rounded px-2 py-1" required>
            </div>
            <div class="mb-3">
                <label class="font-semibold">Mật khẩu <span id="pass-required">*</span></label>
                <input type="password" name="password" id="user-password" class="w-full border rounded px-2 py-1">
            </div>
            <div class="mb-3">
                <label class="font-semibold">Vai trò</label>
                <select name="role" id="user-role" class="w-full border rounded px-2 py-1">
                    <option value="user">User</option>
                    <option value="premium">Premium</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="mb-3" id="premium-expire-block" style="display:none;">
                <label class="font-semibold">Hạn Premium</label>
                <input type="datetime-local" name="premium_expire" id="user-premium-expire" class="w-full border rounded px-2 py-1">
                <div class="text-xs text-gray-500 mt-1">Để trống nếu không phải premium, hoặc muốn hủy premium.</div>
            </div>

            <div class="mb-3">
                <label class="font-semibold">Trạng thái</label>
                <select name="is_active" id="user-active" class="w-full border rounded px-2 py-1">
                    <option value="1">Hoạt động</option>
                    <option value="0">Khóa</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Lưu</button>
        </form>
    </div>
</div>

<div id="view-user-modal-root" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center"></div>
<script>
function viewUser(id) {
  fetch(BASE_PATH + '../api/user_detail_modal.php?id=' + id)
    .then(res => res.text())
    .then(html => {
      let root = document.getElementById('view-user-modal-root');
      root.innerHTML = html;
      root.classList.remove('hidden');
    });
}
function closeViewUser() {
  let root = document.getElementById('view-user-modal-root');
  root.classList.add('hidden');
  root.innerHTML = '';
}
</script>


<script>
const BASE_PATH = '<?= BASE_PATH ?>';
function openUserModal(id = null) {
    document.getElementById('user-modal').classList.remove('hidden');
    document.getElementById('modal-title').innerText = id ? 'Sửa User' : 'Thêm User';
    document.getElementById('user-form').reset();
    document.getElementById('user-id').value = '';
    document.getElementById('pass-required').style.display = id ? 'none' : 'inline';

    // Reset trường premium_expire
    document.getElementById('user-premium-expire').value = '';
    document.getElementById('premium-expire-block').style.display = 'none';

    if (id) {
        fetch(BASE_PATH + '../api/users_api.php?action=get&id='+id)
        .then(res => res.json())
        .then(user => {
            document.getElementById('user-id').value = user.id;
            document.getElementById('user-name').value = user.name;
            document.getElementById('user-email').value = user.email;
            document.getElementById('user-role').value = user.role;
            document.getElementById('user-active').value = user.is_active;
            document.getElementById('user-password').value = '';

            // Xử lý hạn premium
            if(user.role === 'premium') {
                document.getElementById('premium-expire-block').style.display = '';
                if (user.premium_expire) {
                    // Format về yyyy-MM-ddTHH:mm
                    let val = user.premium_expire.replace(' ', 'T').substring(0, 16);
                    document.getElementById('user-premium-expire').value = val;
                } else {
                    document.getElementById('user-premium-expire').value = '';
                }
            } else {
                document.getElementById('premium-expire-block').style.display = 'none';
                document.getElementById('user-premium-expire').value = '';
            }
        });
    }
    // Lắng nghe thay đổi role mỗi lần mở
    document.getElementById('user-role').onchange = function() {
        if(this.value === 'premium'){
            document.getElementById('premium-expire-block').style.display = '';
        } else {
            document.getElementById('premium-expire-block').style.display = 'none';
            document.getElementById('user-premium-expire').value = '';
        }
    };
    // Đảm bảo hiển thị đúng khi modal vừa mở
    document.getElementById('user-role').dispatchEvent(new Event('change'));
}


// Ẩn/hiện field premium_expire nếu chọn Premium
document.getElementById('user-role').addEventListener('change', function(){
    if(this.value === 'premium'){
        document.getElementById('premium-expire-block').style.display = '';
    } else {
        document.getElementById('premium-expire-block').style.display = 'none';
        document.getElementById('user-premium-expire').value = '';
    }
});



function closeUserModal() {
    document.getElementById('user-modal').classList.add('hidden');
}
document.getElementById('user-form').onsubmit = function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch(BASE_PATH + '../api/users_api.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Lỗi thao tác!');
    });
};
function editUser(id) { openUserModal(id); }
function changeUserStatus(id, status) {
    fetch(BASE_PATH + '../api/users_api.php?action=status&id='+id+'&status='+status)
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Thao tác thất bại!');
    });
}
function deleteUser(id) {
    if (confirm('Xác nhận xóa user này?')) {
        fetch(BASE_PATH + '../api/users_api.php?action=delete&id='+id)
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Không thể xóa user!');
        });
    }
}

</script>
<?php include 'layout_end.php'; ?>
