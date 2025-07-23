<?php
require_once 'includes/loader.php';
if (!is_logged_in()) {
    header('Location: login.php'); exit;
}

$allow_roles = json_decode(get_site_setting('allow_request_role') ?? '["user","premium"]', true);
$role = user_role();
if (!in_array($role, $allow_roles) && !is_admin() && !is_root()) {
  header('Location: index.php');
}

$title = "Yêu cầu Prompt mới";
include 'includes/header.php';
?>

<div class="max-w-lg mx-auto mt-8 bg-white rounded-xl shadow p-6">
    <h2 class="text-xl font-bold mb-4">Yêu cầu Prompt mới</h2>
    <span class="text-base text-gray-700 ">Xem danh sách Prompt đã yêu cầu <a href="my_requests.php" class="text-blue-600">tại đây</a></span>
    <form id="request-form" class="mt-4">
        <label class="font-semibold">Tiêu đề prompt <span class="text-red-500">*</span></label>
        <input type="text" name="title" required class="w-full border rounded px-3 py-2 mb-3" maxlength="180" placeholder="VD: Prompt viết content Facebook">
        <label class="font-semibold">Mô tả yêu cầu</label>
        <textarea name="description" class="w-full border rounded px-3 py-2 mb-3" rows="4" placeholder="Bạn mong muốn gì ở prompt này?"></textarea>
        <button class="bg-blue-600 text-white px-5 py-2 rounded" type="submit">Gửi yêu cầu</button>
    </form>
    <div id="req-result" class="mt-3 text-green-600 font-bold hidden"></div>
</div>

<script>
document.getElementById('request-form').onsubmit = function(e){
    e.preventDefault();
    var fd = new FormData(this);
    fetch('api/request_prompt_api.php', {
        method:'POST',
        body:fd
    }).then(r=>r.json())
    .then(d=>{
        if(d.success) {
            document.getElementById('req-result').innerText = d.message || 'Đã gửi yêu cầu!';
            document.getElementById('req-result').classList.remove('hidden');
            document.getElementById('request-form').reset();
        } else {
            alert(d.message || 'Có lỗi xảy ra!');
        }
    });
};
</script>
<?php require_once 'includes/premium_modal.php'; ?>
<?php include 'includes/footer.php'; ?>
