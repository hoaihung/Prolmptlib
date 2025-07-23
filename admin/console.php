<?php
require_once '../includes/loader.php';
if (!is_logged_in()) header('Location: login.php');
$title = "AI Console";
include 'layout.php';
 require_once '../includes/svg.php';

$user_id = $_SESSION['user_id'];
$user = $pdo->prepare("SELECT * FROM users WHERE id=?"); $user->execute([$user_id]);
$user = $user->fetch();
$role = $user['role'];
$api_gpt = $user['api_gpt_key'] ?? '';
$api_gemini = $user['api_gemini_key'] ?? '';

$isAllowed = in_array($role, ['premium','admin','root']);

$gemini_models = [
    'gemini-2.5-flash' => 'Gemini 2.5 Flash',
    'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash Exp',
    'gemini-1.5-pro-latest' => 'Gemini 1.5 Pro',
    'gemini-1.5-flash-latest' => 'Gemini 1.5 Flash',
];
$gpt_models = [
    'gpt-4o' => 'GPT-4o (Mới nhất)',
    'gpt-4o-mini' => 'GPT-4o Mini',
    'gpt-4-turbo' => 'GPT-4 Turbo',
    'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
];

$prompts = $pdo->query("SELECT id, title, description, content FROM prompts WHERE is_deleted=0 AND is_active=1 AND console_enabled=1")->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
.selected-prompt-row { background: #e0edff !important; border-color: #2563eb !important; }
#param-block.minimized { height: 40px; overflow: hidden; min-height: unset; }
#param-block { max-height: 270px; overflow-y: auto; transition: height 0.2s; }
#console-chat { max-height: 65vh; min-height: 360px; overflow-y: auto; }
#btn-start-chat { transition: background 0.15s; }
@media (max-width: 900px) {
    #console-chat { min-height: 200px; }
}
</style>

<div class="max-w-7xl mx-auto mt-6 flex flex-col md:flex-row gap-6">
  <!-- Sidebar -->
  <div class="w-full md:w-1/3 space-y-6">
    <div class="bg-white rounded-xl shadow p-5">
      <div class="font-bold mb-3 text-lg">AI Provider</div>
      <div>
        <label class="flex items-center gap-3 mb-2">
          <input type="radio" name="provider" value="gpt" <?= ($api_gpt ? 'checked' : '') ?> <?= (!$api_gpt ? 'disabled' : '') ?>>
          <span class="<?= $api_gpt?'':'text-gray-400' ?>"> OpenAI GPT</span>
          <?php if (!$api_gpt): ?><span class="ml-auto text-xs bg-red-100 text-red-600 px-2 rounded">Chưa có API Key</span><?php endif ?>
        </label>
        <label class="flex items-center gap-3">
          <input type="radio" name="provider" value="gemini" <?= ($api_gemini ? (!$api_gpt ? 'checked' : '') : '') ?> <?= (!$api_gemini ? 'disabled' : '') ?>>
          <span class="<?= $api_gemini?'':'text-gray-400' ?>"> Google Gemini</span>
          <?php if (!$api_gemini): ?><span class="ml-auto text-xs bg-red-100 text-red-600 px-2 rounded">Chưa có API Key</span><?php endif ?>
        </label>
        <?php if (!$api_gpt || !$api_gemini): ?>
          <div class="text-sm text-gray-500 mt-2">Hãy cấu hình API Key tại <a href="profiles.php" class="underline text-blue-600">Profile</a>.</div>
        <?php endif ?>
      </div>
      <div class="mt-4">
        <label class="font-semibold text-gray-700">Model</label>
        <select id="ai-model" class="w-full border rounded px-2 py-1 mt-1">
          <optgroup label="OpenAI">
          <?php foreach($gpt_models as $k=>$v): ?>
            <option value="gpt:<?= $k ?>"><?= $v ?></option>
          <?php endforeach ?>
          </optgroup>
          <optgroup label="Gemini">
          <?php foreach($gemini_models as $k=>$v): ?>
            <option value="gemini:<?= $k ?>"><?= $v ?></option>
          <?php endforeach ?>
          </optgroup>
        </select>
      </div>
      <div class="mt-4">
        <label class="block font-semibold">Temperature: <span id="temp-value">0.7</span></label>
        <input type="range" min="0" max="2" step="0.1" value="0.7" id="ai-temp" class="w-full">
      </div>
      <div class="mt-3">
        <label class="block font-semibold">Max Tokens</label>
        <input type="number" min="128" max="4096" value="2000" id="ai-max-tokens" class="w-full border rounded px-2 py-1">
      </div>
    </div>
    <!-- Prompt chọn -->
    <div class="bg-white rounded-xl shadow p-5">
      <div class="font-bold mb-2">Chọn Prompt</div>
      <input id="prompt-search" type="text" placeholder="Tìm prompt..." class="border w-full rounded px-2 py-1 mb-3" />
      <div id="prompt-list" class="max-h-60 overflow-y-auto space-y-2 pr-1">
        <?php foreach($prompts as $pr): ?>
        <div class="flex justify-between items-center p-2 rounded cursor-pointer hover:bg-blue-50 border"
             data-prompt-id="<?= $pr['id'] ?>"
             data-prompt-content="<?= htmlspecialchars($pr['content']) ?>"
             data-prompt-title="<?= htmlspecialchars($pr['title']) ?>"
             data-prompt-desc="<?= htmlspecialchars($pr['description']) ?>"
             onclick="handleSelectPrompt(this)"
             id="prompt-row-<?= $pr['id'] ?>">
          <div>
            <div class="font-semibold"><?= htmlspecialchars($pr['title']) ?></div>
            <div class="text-xs text-gray-500 line-clamp-2"><?= htmlspecialchars($pr['description']) ?></div>
          </div>
          <button type="button" class="ml-2 px-2 py-1 text-blue-600 hover:text-blue-800 text-xs rounded border"
                  onclick="event.stopPropagation(); openPromptViewModal(this.parentNode)">
            Xem
          </button>
        </div>
        <?php endforeach ?>
      </div>
    </div>
  </div>

  <!-- Cột phải: Block param + Console chat (trong 1 cột dọc) -->
  <div class="flex-1 flex flex-col gap-4">

    <!-- Block bổ sung param -->
    <div class="bg-white rounded-xl shadow p-4 mb-2" id="param-block" style="display:none;">
      <div class="flex justify-between items-center mb-2">
        <b>Nhập giá trị tham số:</b>
        <button type="button" id="toggle-param-block" class="text-blue-600 text-sm px-2 py-1 rounded">Thu gọn</button>
      </div>
      <div id="param-inputs"></div>
    </div>
    <!-- Nút start chat luôn ở ngoài (luôn hiển thị nếu đã chọn prompt) -->
    <button id="btn-start-chat" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg ml-auto block mb-2" style="display:none;">Bắt đầu Chat mới</button>

    <!-- Main console/chat area -->
    <div class="flex-1 bg-white rounded-xl shadow p-0 flex flex-col h-[80vh] relative">
      <div id="selected-prompt-block"></div>
      <?php if (!$isAllowed): ?>
        <div class="flex-1 flex flex-col items-center justify-center h-full opacity-70">
          <div class="text-xl font-bold mb-2 text-gray-700">Chỉ tài khoản Premium mới sử dụng Console</div>
          <a href="profiles.php" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded mt-3">Nâng cấp Premium</a>
        </div>
      <?php else: ?>
        <div id="console-chat" class="flex-1 px-6 py-4 overflow-y-auto flex flex-col gap-4 bg-gray-50 rounded-b-xl"></div>
        <form id="console-form" class="p-4 border-t flex gap-3 bg-white"
              autocomplete="off" onsubmit="handleSendMsg(event)">
          <textarea id="console-input" rows="2" maxlength="1000" style="resize: none; max-height:80px;"
            class="flex-1 border rounded px-3 py-2 overflow-y-auto"
            placeholder="Nhập input cho prompt hoặc tin nhắn..."
            oninput="limitLines(this,3)"></textarea>
          <button id="console-run" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded flex-shrink-0" type="submit">
            Gửi
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <!-- Modal xem prompt -->
  <div id="console-prompt-view-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="relative bg-white rounded-2xl shadow-lg w-full max-w-xl max-h-[90vh] flex flex-col mx-2 my-6">
      <button type="button" class="absolute top-2 right-3 text-gray-400 text-2xl z-10" onclick="closePromptViewModal()">&times;</button>
      <div class="px-8 py-6 overflow-y-auto" style="max-height:75vh" id="console-prompt-modal-content"></div>
    </div>
  </div>
</div>

<script>
let chatHistory = [];
let selectedPrompt = null;
let selectedParams = {};

// Model mặc định theo provider
const defaultModel = {
  gpt: 'gpt-4o-mini',
  gemini: 'gemini-2.5-flash'
};

function limitLines(el, maxLines) {
  let lines = el.value.split('\n');
  if (lines.length > maxLines) el.value = lines.slice(0,maxLines).join('\n');
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 80) + 'px';
}

// Filter prompt
document.getElementById('prompt-search').addEventListener('input', function(){
  let val = this.value.toLowerCase();
  document.querySelectorAll('#prompt-list > div').forEach(el => {
    let t = el.getAttribute('data-prompt-title').toLowerCase();
    el.style.display = t.includes(val) ? '' : 'none';
  });
});

function saveChatHistory() {
    let uid = <?= (int)($_SESSION['user_id'] ?? 0) ?>;
    let pid = selectedPrompt ? selectedPrompt.id : 0;
    if (uid && pid) {
        localStorage.setItem('console_history_'+uid+'_'+pid, JSON.stringify(chatHistory));
    }
}
function loadChatHistory() {
    let uid = <?= (int)($_SESSION['user_id'] ?? 0) ?>;
    let pid = selectedPrompt ? selectedPrompt.id : 0;
    if (uid && pid) {
        let data = localStorage.getItem('console_history_'+uid+'_'+pid);
        if (data) {
            try {
                chatHistory = JSON.parse(data);
                chatHistory.forEach(msg => appendMsg(msg.role, msg.content));
            } catch(e) { chatHistory = []; }
        }
    }
}

// Chọn prompt
function handleSelectPrompt(el) {
  document.querySelectorAll('#prompt-list > div').forEach(x => x.classList.remove('selected-prompt-row'));
  el.classList.add('selected-prompt-row');

  let promptId = el.getAttribute('data-prompt-id');
  let content = el.getAttribute('data-prompt-content');
  let title = el.getAttribute('data-prompt-title');
  let params = [];
  let re = /\{([^}]+)\}/g, match;
  while ((match = re.exec(content))) if (!params.includes(match[1])) params.push(match[1]);
  // Nếu là prompt mới, reset param
  if (!selectedPrompt || selectedPrompt.id !== promptId) selectedParams = {};
  selectedPrompt = {id: promptId, content, params, title};
  renderPromptParams(params);
  document.getElementById('selected-prompt-block').innerHTML =
    `<div class="text-sm text-blue-600 py-2 px-6 bg-blue-50 rounded-t-xl font-semibold">
      Đang chọn prompt: <span class="font-bold">${title}</span>
    </div>`;
  resetSession(true);
  loadChatHistory();
  document.getElementById('btn-start-chat').style.display = selectedPrompt ? '' : 'none';
}

// Render input param (giữ value)
let paramBlockExpanded = true;
document.getElementById('toggle-param-block').onclick = function(){
  paramBlockExpanded = !paramBlockExpanded;
  let block = document.getElementById('param-block');
  if(paramBlockExpanded) {
      block.classList.remove('minimized');
      this.innerText = 'Thu gọn';
      document.getElementById('param-inputs').style.display = '';
  } else {
      block.classList.add('minimized');
      this.innerText = 'Mở rộng';
      document.getElementById('param-inputs').style.display = 'none';
  }
};

function renderPromptParams(params) {
  let html = '';
  params.forEach(p=>{
      html += `<div class="mb-2"><label class="block text-sm mb-1">${p}</label>
      <input type="text" class="console-param-input border px-2 py-1 rounded w-full" name="${p}" id="param-${p}" placeholder="${p}..." value="${selectedParams[p]||''}" oninput="selectedParams['${p}']=this.value;" /></div>`;
  });
  document.getElementById('param-inputs').innerHTML = html;
  document.getElementById('param-block').style.display = params.length ? '' : 'none';
  document.getElementById('btn-start-chat').style.display = selectedPrompt ? '' : 'none';
}

// Model dropbox chỉ show model đúng provider & auto chọn model mới nhất
document.querySelectorAll('input[name="provider"]').forEach(radio => {
  radio.addEventListener('change', function(){
    let provider = this.value;
    let sel = document.getElementById('ai-model');
    for(let i=0; i<sel.options.length; i++) {
      let opt = sel.options[i];
      if(opt.value === provider+':'+defaultModel[provider]) {
        sel.selectedIndex = i; break;
      }
    }
  });
});
(function(){
  let provider = document.querySelector('input[name="provider"]:checked')?.value || 'gpt';
  let sel = document.getElementById('ai-model');
  for(let i=0;i<sel.options.length;i++) {
    let opt = sel.options[i];
    if(opt.value === provider+':'+defaultModel[provider]) {
      sel.selectedIndex = i; break;
    }
  }
})();

document.getElementById('ai-temp').oninput = function(){
  document.getElementById('temp-value').innerText = this.value;
};

function resetSession(skipPrompt = false) {
  chatHistory = [];
  document.getElementById('console-chat').innerHTML = '';
  document.getElementById('console-input').value = '';
  if (!skipPrompt && selectedPrompt && selectedPrompt.content) {
    renderPromptParams(selectedPrompt.params);
    document.getElementById('selected-prompt-block').innerHTML = '';
  }
  document.getElementById('btn-start-chat').style.display = selectedPrompt ? '' : 'none';
}

// Gửi tin nhắn tiếp theo
function handleSendMsg(e){
  e.preventDefault();
  let input = document.getElementById('console-input');
  let msg = input.value.trim();
  if (!msg) return;
  appendMsg('user', msg);
  input.value = '';
  chatHistory.push({role:'user', content: msg});
  sendToAI(msg);
}

// Khi nhấn Bắt đầu Chat mới
document.getElementById('btn-start-chat').onclick = function(){
  resetSession();
  if (selectedPrompt && selectedPrompt.content) {
    // Lưu lại param vừa nhập
    if (selectedPrompt.params && selectedPrompt.params.length > 0) {
      selectedPrompt.params.forEach(p => {
        let v = document.getElementById('param-'+p)?.value || '';
        selectedParams[p] = v;
      });
    }
    let promptText = selectedPrompt.content;
    if (selectedPrompt.params && selectedPrompt.params.length > 0) {
      selectedPrompt.params.forEach(p => {
        let v = selectedParams[p] || '';
        promptText = promptText.replaceAll('{'+p+'}', v);
      });
    }
    if (selectedPrompt.id) {
      fetch('../api/prompt_stat_api.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'action=inc_run&prompt_id=' + encodeURIComponent(selectedPrompt.id)
      });
    }
    appendMsg('user', promptText);
    chatHistory.push({role:'user', content: promptText});
    saveChatHistory();
    sendToAI(promptText);
  }
};

function appendMsg(role, text){
  let el = document.createElement('div');
  el.className = "mb-2 " + (role==='user' ? "text-right" : "text-left");
  el.innerHTML = `<div class="inline-block rounded-lg px-4 py-2 ${role==='user'?'bg-blue-100 text-blue-800':'bg-gray-100 text-gray-900'} max-w-xl break-words whitespace-pre-line">
      <span>${text}</span>
    </div>`;
  document.getElementById('console-chat').appendChild(el);
  document.getElementById('console-chat').scrollTop = 99999;
}

// Xem chi tiết prompt (modal)
function openPromptViewModal(node){
  let title = node.getAttribute('data-prompt-title');
  let content = node.getAttribute('data-prompt-content');
  let desc = node.getAttribute('data-prompt-desc');
  document.getElementById('console-prompt-modal-content').innerHTML =
    `<div class="font-bold text-lg mb-2">${title}</div>
     <div class="mb-2 text-sm text-gray-500">${desc}</div>
     <pre class="bg-gray-100 rounded px-3 py-2 whitespace-pre-wrap break-words mb-2" style="max-height:300px;overflow:auto;">${content}</pre>`;
  document.getElementById('console-prompt-view-modal').classList.remove('hidden');
}
function closePromptViewModal(){
  document.getElementById('console-prompt-view-modal').classList.add('hidden');
}

// Gửi API
function sendToAI(msg) {
  appendMsg('ai', `<span class="animate-pulse text-gray-500">Đang lấy kết quả...</span>`);
  let provider = document.querySelector('input[name="provider"]:checked')?.value;
  let model = document.getElementById('ai-model').value.split(':')[1];
  let temp = document.getElementById('ai-temp').value;
  let max_tokens = document.getElementById('ai-max-tokens').value;
  fetch('../api/console_api.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
        provider, model, temp, max_tokens, chat: chatHistory
    })
  })
  .then(res=>res.json())
  .then(data=>{
    let lastAI = document.querySelectorAll('#console-chat > div:last-child')[0];
    if (lastAI) lastAI.remove();
    if (data && data.output) {
        appendMsg('ai', data.output);
        chatHistory.push({role:'assistant', content: data.output});
        saveChatHistory();
    } else {
        appendMsg('ai', "❌ Lỗi hoặc không có phản hồi.");
    }
  });
}

window.onload = function() {
    var pid = <?= isset($_GET['prompt_id']) ? intval($_GET['prompt_id']) : 0 ?>;
    if (pid) {
        var el = document.querySelector('[data-prompt-id="'+pid+'"]');
        if (el) handleSelectPrompt(el);
    }
}

</script>
<?php include 'layout_end.php'; ?>
