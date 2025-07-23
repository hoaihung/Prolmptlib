<?php
// Đặt cuối mỗi trang (kết thúc main/layout)
?>
            </main>
        </div>
    </div>
	
<!-- Toast hiển thị copy thành công -->
<div id="toast-copy" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-green-600 text-white rounded px-4 py-2 shadow-lg z-50 hidden text-base transition duration-300">
    Đã copy nội dung prompt!
</div>

<!-- Toast Thông báo đa năng -->
<div id="admin-toast"
     class="fixed left-1/2 bottom-10 -translate-x-1/2 bg-blue-600 text-white rounded-xl px-6 py-3 shadow-lg z-[9999] text-base transition opacity-0 pointer-events-none"
     style="min-width:200px;text-align:center;"></div>

<?php require_once '../includes/premium_modal.php';?>

<script>
function showToast(msg, color = 'bg-blue-600 text-white') {
    let toast = document.getElementById('admin-toast');
    toast.className = 'fixed left-1/2 bottom-10 -translate-x-1/2 rounded-xl px-6 py-3 shadow-lg z-[9999] text-base transition pointer-events-auto ' + color;
    toast.innerHTML = msg;
    toast.style.opacity = 1;
    setTimeout(() => { toast.style.opacity = 0; }, 1500);
}

function showToastErr(msg, error) {
  let toast = document.getElementById('toast-main');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast-main';
    toast.className = "fixed bottom-8 left-1/2 transform -translate-x-1/2 z-50 px-5 py-3 rounded text-white shadow-lg text-base";
    document.body.appendChild(toast);
  }
  toast.className = "fixed bottom-8 left-1/2 transform -translate-x-1/2 z-50 px-5 py-3 rounded text-white shadow-lg text-base";
  toast.style.background = error ? "#e53e3e" : "#2ecc71";
  toast.innerText = msg;
  toast.classList.remove('hidden');
  setTimeout(()=>toast.classList.add('hidden'), 1800);
}

function toggleMenuGroup(idx) {
    let menu = document.getElementById('menu-group-'+idx);
    let arrow = document.getElementById('arrow-'+idx);
    if(menu.style.display === 'none') {
        menu.style.display = 'block';
        arrow.style.transform = 'rotate(0deg)';
    } else {
        menu.style.display = 'none';
        arrow.style.transform = 'rotate(-90deg)';
    }
}

// Mobile sidebar toggle
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebar-mobile-bg').classList.add('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-mobile-bg').classList.remove('open');
}
document.getElementById('btn-open-sidebar').onclick = openSidebar;

</script>


</body>
</html>
