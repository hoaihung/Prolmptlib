<?php
require_once '../includes/loader.php';
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}
$title = "Quản lý Users Bị Xóa";
include 'layout.php';
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-bold">Danh sách Users</h2>
    <button onclick="openUserModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl">+ Thêm User</button>
</div>

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
        $q = $pdo->query("SELECT * FROM users WHERE is_deleted=1 ORDER BY deleted_at DESC");
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
            <td class="p-2">
				<?php if ($user['role'] != 'root'): ?>
					<button class="bg-green-600 text-white px-3 py-1 rounded" onclick="restoreUser(<?php echo $user['id'] ?>)">
						Khôi phục
					</button>
				<?php endif; ?>
			</td>


        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
const BASE_PATH = '<?= BASE_PATH ?>';
function openUserModal(id = null) {
    document.getElementById('user-modal').classList.remove('hidden');
    document.getElementById('modal-title').innerText = id ? 'Sửa User' : 'Thêm User';
    document.getElementById('user-form').reset();
    document.getElementById('user-id').value = '';
    document.getElementById('pass-required').style.display = id ? 'none' : 'inline';
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
        });
    }
}
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

function restoreUser(id) {
    if (confirm('Khôi phục user này?')) {
        fetch(BASE_PATH + '../api/users_api.php?action=restore&id='+id)
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Không thể khôi phục!');
        });
    }
}


</script>
<?php include 'layout_end.php'; ?>
