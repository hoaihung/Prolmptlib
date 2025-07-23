<?php
require_once '../includes/loader.php';
if (!is_logged_in()) header('Location: login.php');
$user_id = $_SESSION['user_id'];
$title = "Thông báo của bạn";
include 'layout.php';

// Chỉ hiện 50 thông báo gần nhất: global hoặc riêng user
$stmt = $pdo->prepare("
    SELECT n.*, 
        (SELECT 1 FROM notification_reads r WHERE r.noti_id=n.id AND r.user_id=?) as is_read
    FROM notifications n
    WHERE n.user_id=? OR n.user_id IS NULL
    ORDER BY n.id DESC LIMIT 50
");
$stmt->execute([$user_id, $user_id]);
$notis = $stmt->fetchAll();
?>
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold">Thông báo</h2>
    <button onclick="markAllRead()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">Đánh dấu đã đọc tất cả</button>
</div>
<div id="notify-list" class="space-y-3">
    <?php foreach($notis as $n): ?>
        <div class="bg-white border rounded-xl shadow p-4 flex items-center justify-between <?= $n['is_read']?'opacity-70':'border-blue-500' ?>">
            <div class="flex-1">
                <div class="font-semibold text-base flex items-center gap-2">
                    <?php if(!$n['is_read']): ?><span class="w-2 h-2 bg-blue-500 rounded-full inline-block"></span><?php endif ?>
                    <?= htmlspecialchars($n['title']) ?>
                    <?php if($n['type']=='system'): ?>
                        <span class="ml-2 px-2 py-0.5 bg-gray-200 rounded text-gray-700 text-xs">Hệ thống</span>
                    <?php elseif($n['type']=='news'): ?>
                        <span class="ml-2 px-2 py-0.5 bg-green-200 rounded text-green-700 text-xs">Tin tức</span>
                    <?php elseif($n['type']=='prompt'): ?>
                        <span class="ml-2 px-2 py-0.5 bg-blue-200 rounded text-blue-700 text-xs">Prompt</span>
                    <?php endif; ?>
                </div>
                <div class="text-gray-500 text-xs"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></div>
            </div>
            <button onclick="viewNotifyDetail(<?= $n['id'] ?>)" class="ml-4 bg-blue-600 text-white px-3 py-1 rounded text-xs">Xem chi tiết</button>
        </div>
    <?php endforeach; ?>
</div>
<!-- Modal thông báo -->
<div id="notify-modal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div
    class="relative bg-white rounded-2xl shadow-lg w-full max-w-lg
           max-h-[80vh] flex flex-col mx-2 my-8">
    <!-- Nút tắt luôn ở trên -->
    <button type="button"
      class="absolute top-2 right-3 text-gray-400 text-2xl z-10"
      onclick="closeNotifyModal()">&times;</button>
    <h3 class="text-lg font-bold mb-2 pr-8 p-4">Chi tiết thông báo</h3>
    <div class="overflow-y-auto flex-1 pr-2 p-4" style="max-height:65vh;" id="notify-modal-content">
      <!-- Nội dung rất dài sẽ cuộn ở đây -->
    </div>
  </div>
</div>



<script>
function viewNotifyDetail(id){
    fetch('../api/notification_api.php?action=detail&id='+id)
    .then(res=>res.json()).then(data=>{
        if(data.success){
            document.getElementById('notify-modal-content').innerHTML = `
                <div class="font-bold text-lg mb-2">${data.title}</div>
                <div class="text-gray-600 mb-2">${data.content}</div>
                ${data.link ? `<a href="${data.link}" target="_blank" class="text-blue-700 underline text-sm">Xem thêm</a>` : ''}
                <div class="text-xs text-gray-400 mt-4">Thời gian: ${data.created_at}</div>
            `;
            document.getElementById('notify-modal').classList.remove('hidden');
            // Cập nhật giao diện đã đọc luôn nếu muốn:
            const row = document.querySelector('button[onclick="viewNotiDetail('+id+')"]').closest('div');
            row.classList.add('opacity-70');
        }
    });
}
function closeNotifyModal(){
    document.getElementById('notify-modal').classList.add('hidden');
}
function markAllRead(){
    fetch('../api/notification_api.php?action=markall').then(res=>res.json()).then(data=>{
        if(data.success) location.reload();
    });
}
</script>
<?php include 'layout_end.php'; ?>
