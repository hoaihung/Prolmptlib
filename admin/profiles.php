<?php
require_once '../includes/loader.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];
$title = "Cài đặt Profile";
include 'layout.php';

$user = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$user->execute([$user_id]);
$user = $user->fetch();

$user_name = $user['name'];
$user_email = $user['email'];
$user_role = $user['role'];
$api_gpt_key = $user['api_gpt_key'];
$api_gemini_key = $user['api_gemini_key'];
$user_initial = strtoupper(substr($user_name,0,1));
?>

<form id="profile-form" class="max-w-2xl mx-auto mt-8 space-y-6">
  <!-- Header user info -->
  <div class="bg-white shadow rounded-xl p-6 flex items-center gap-5">
    <div class="flex-shrink-0">
      <div class="bg-blue-100 text-blue-800 rounded-full w-14 h-14 flex items-center justify-center text-2xl font-bold">
        <?= htmlspecialchars($user_initial ?? 'A') ?>
      </div>
    </div>
    <div>
      <div class="font-bold text-lg"><?= htmlspecialchars($user_name ?? 'Tên User') ?></div>
      <div class="text-gray-600 text-sm"><?= htmlspecialchars($user_email ?? 'email@domain.com') ?></div>
      <?php if ($user_role === 'premium'): ?>
        <span class="inline-block bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-semibold mt-1">Premium</span>
        <span class="bg-gray-100 text-black-800 px-2 py-0.5 rounded text-xs ml-2"><?=$user['premium_expire']?></span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Thông tin cá nhân -->
  <div class="bg-white shadow rounded-xl p-6 space-y-3">
    <div class="font-semibold text-gray-700">Tên đầy đủ</div>
    <input type="text" name="name" value="<?= htmlspecialchars($user_name) ?>" class="w-full border rounded px-3 py-2 bg-gray-50" />

    <div class="font-semibold text-gray-700">Địa chỉ Email</div>
    <input type="email" value="<?= htmlspecialchars($user_email) ?>" readonly class="w-full border rounded px-3 py-2 bg-gray-50 opacity-70" />
  </div>

  <div class="bg-white shadow rounded-xl p-6">
    <div class="font-semibold mb-2">Đổi mật khẩu</div>
    <input type="password" name="new_password" placeholder="Mật khẩu mới (ít nhất 6 ký tự)" class="w-full border rounded px-3 py-2 mb-2 bg-gray-50" />
    <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" class="w-full border rounded px-3 py-2 bg-gray-50" />
    <div class="text-xs text-gray-500 mt-2">Để trống nếu không muốn thay đổi mật khẩu.</div>
  </div>


  <!-- API Key Config -->
  <div class="bg-white shadow rounded-xl p-6">
    <div class="flex items-center gap-3 mb-2">
      <span class="font-semibold text-gray-700 text-lg">Cấu hình API</span>
      <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">Chỉ hoạt động với Premium</span>
    </div>
    <div class="mb-2">
      <div class="font-medium text-gray-600 flex items-center gap-2">
        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 20v-6M12 4v2m0 2v2m4 0v2m-8 0v2m8 0v2m-8 0v2m4 0v6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        OpenAI GPT API Key
      </div>
      <input type="text" name="api_gpt_key" value="<?= htmlspecialchars($api_gpt_key ?? '') ?>" placeholder="Nhập OpenAI GPT API key..." class="mt-1 w-full border rounded px-3 py-2 bg-gray-50" <?= (($user_role !== 'premium') && ($user_role !== 'admin') && ($user_role !== 'root') ? 'readonly disabled' : '') ?> />
    </div>
    <div>
      <div class="font-medium text-gray-600 flex items-center gap-2">
        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Google Gemini API Key
      </div>
      <input type="text" name="api_gemini_key" value="<?= htmlspecialchars($api_gemini_key ?? '') ?>" placeholder="Nhập Google Gemini API key..." class="mt-1 w-full border rounded px-3 py-2 bg-gray-50" <?= (($user_role !== 'premium') && ($user_role !== 'admin') && ($user_role !== 'root') ? 'readonly disabled' : '') ?> />
    </div>
    <div class="text-xs text-gray-500 mt-2">
      * Chỉ tài khoản Premium mới sử dụng được tính năng này. API key của bạn được lưu trữ cục bộ và mã hóa.
    </div>
    <!--Hướng dẫn get api key-->
    <div class="text-sm text-gray-500 mt-2">Hướng dẫn lấy Key API tại <a href="https://www.facebook.com/groups/aiartvietnam/permalink/589952677144812" class="underline text-blue-600" target="_blank">Bài viết hướng dẫn API Key</a>.</div>
  </div>

  <!-- Gói đăng ký -->
  <div class="bg-white shadow rounded-xl p-6">
    <div class="font-semibold mb-2">Gói đăng ký</div>
    <?php if ($user_role === 'premium'): ?>
      <div class="bg-purple-50 text-purple-700 px-4 py-2 rounded flex items-center gap-3">
        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 12h3v8h14v-8h3z" /></svg>
        Tài khoản Premium - Đã kích hoạt
      </div>
    <?php else: ?>
      <div class="bg-purple-50 text-purple-700 px-4 py-2 rounded flex items-center gap-3">
        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 12h3v8h14v-8h3z" /></svg>
        <div>
          Tài khoản <b>Premium</b><br>
          <span class="text-xs text-gray-600">Truy cập tất cả prompt premium và console</span>
        </div>
        <a onclick="showPremiumInfo()" href="#" class="ml-auto bg-purple-200 hover:bg-purple-300 text-purple-800 px-3 py-1 rounded font-semibold">Liên hệ nâng cấp</a>
      </div>
    <?php endif; ?>
  </div>

  <div class="text-right mt-8">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow font-bold">Lưu thay đổi</button>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var pf = document.getElementById('profile-form');
  if(pf){
    pf.onsubmit = function(e){
      e.preventDefault();
      var fd = new FormData(this);
      fetch('../api/profile_api.php', {method:'POST',body:fd})
        .then(res=>res.json())
        .then(d=>{
            if(d.success) alert('Đã lưu thành công!');
            else alert('Có lỗi xảy ra!');
        })
        .catch(()=>alert('Có lỗi hệ thống hoặc server!'));
    };
  }
});

</script>
<?php include 'layout_end.php'; ?>
