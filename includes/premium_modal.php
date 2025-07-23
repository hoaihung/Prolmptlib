<?php require_once 'svg.php'; ?>
<!-- Modal Premium Info -->
<div id="premium-info-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white p-8 rounded-2xl max-w-lg mx-auto text-center shadow-xl relative">
    <button type="button" class="absolute top-2 right-3 text-gray-400 text-2xl" onclick="closePremiumInfo()">&times;</button>
    <div class="mb-2 text-3xl text-purple-500"><?= inline_svg('crown', 'w-8 h-8 inline') ?></div>
    <h2 class="font-bold text-xl mb-3 text-purple-700">Nâng cấp Premium</h2>
    <div class="mb-2 text-gray-700">Quyền Premium cho phép bạn:</div>
    <ul class="mb-4 text-left text-gray-600 list-disc list-inside text-sm">
      <li>Truy cập prompt Premium độc quyền</li>
      <li>Chạy Console AI, tạo prompt cá nhân</li>
      <li>Yêu cầu prompt cá nhân &amp; nhiều quyền năng hơn</li>
    </ul>
    <div class="mb-4 bg-purple-50 text-purple-800 rounded p-3">
      Giá chỉ từ <span class="font-bold text-lg">99.000đ/tháng</span>
    </div>
    <ul class="mb-4 text-left text-gray-600 list-disc list-inside text-sm">
      <li>Ngân hàng: <b>Vietcombank</b></li>
      <li>STK: 0721000560543</li>
      <li>Nội dung: Prompt + email (sử dụng đăng ký PromptLib)</li>
    </ul>
    <div>Liên hệ: Zalo <b>0972480586</b> sau khi chuyển khoản</div>
    <button class="bg-purple-600 hover:bg-purple-700 text-white mt-5 px-5 py-2 rounded-xl font-bold" onclick="closePremiumInfo()">Đóng</button>
  </div>
</div>

<!-- Modal ebook Prompt Mastery Info -->
<div id="epm-info-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white p-8 rounded-2xl max-w-lg mx-auto text-center shadow-xl relative">
    <button type="button" class="absolute top-2 right-3 text-gray-400 text-2xl" onclick="closeEpmInfo()">&times;</button>
    <div class="mb-2 text-3xl text-purple-500"><?= inline_svg('crown', 'w-8 h-8 inline') ?></div>
    <h2 class="font-bold text-xl mb-3 text-purple-700">ebook Prompt Mastery</h2>
    <div class="mb-2 text-gray-700">Đây là Ebook đúc kết kinh nghiệm thực chiến, giúp bạn làm chủ kỹ năng Prompt AI từ A-Z, biến AI thành trợ lý đắc lực.</div>
    <ul class="mb-4 text-left text-gray-600 list-disc list-inside text-sm">
      <li>Công thức viết Prompt hiệu quả (WWH, Ngữ cảnh...)</li>
      <li>Các kỹ thuật nâng cao (Few-Shot, Chain-of-Thought...)</li>
      <li>Tư duy thiết kế Prompt phức tạp (Bộ công cụ tư duy, Logic Flow...)</li>
      <li>Nội dung xây dựng dễ hiểu từ cơ bản đến nâng cao</li>
      <li><b>Tặng 3 tháng Premium PromptLib <span class="text-red-700">297.000đ</span></b></li>
    </ul>
    <div class="mb-4 bg-purple-50 text-purple-800 rounded p-3">
      Giá ưu đãi <span class="font-bold text-lg">399.000đ</span>
    </div>
    <div>Liên hệ: Zalo <b>0972480586</b></div>
    <button class="bg-purple-600 hover:bg-purple-700 text-white mt-5 px-5 py-2 rounded-xl font-bold" onclick="closeEpmInfo()">Đóng</button>
  </div>
</div>

<script>
function showPremiumInfo() {
  document.getElementById('premium-info-modal').classList.remove('hidden');
}
function closePremiumInfo() {
  document.getElementById('premium-info-modal').classList.add('hidden');
}
function showEpmInfo() {
  document.getElementById('epm-info-modal').classList.remove('hidden');
}
function closeEpmInfo() {
  document.getElementById('epm-info-modal').classList.add('hidden');
}
</script>
