<?php
require_once '../includes/loader.php';
if (!is_logged_in()) die(json_encode(['error'=>'Not logged in']));
$user_id = $_SESSION['user_id'];
$user = $pdo->prepare("SELECT * FROM users WHERE id=?"); $user->execute([$user_id]);
$user = $user->fetch();

$role = $user['role'];
if (!in_array($role, ['premium','admin','root'])) die(json_encode(['error'=>'No permission']));

// Nhận data
$body = file_get_contents('php://input');
$data = json_decode($body, true);
$provider = $data['provider'] ?? 'gpt';
$model = $data['model'] ?? 'gpt-4o';
$temp = floatval($data['temp'] ?? 0.7);
$max_tokens = intval($data['max_tokens'] ?? 2000);
$chat = $data['chat'] ?? [];

if ($provider === 'gemini') {
    $api_key = $user['api_gemini_key'] ?? '';
    if (!$api_key) die(json_encode(['error'=>'No Gemini API Key']));
    // Call Google Gemini API (latest)
    $res = gemini_call($api_key, $model, $chat, $temp, $max_tokens);
    echo json_encode(['output'=>$res]); exit;
} else {
    $api_key = $user['api_gpt_key'] ?? '';
    if (!$api_key) die(json_encode(['error'=>'No OpenAI API Key']));
    // Call OpenAI API (latest)
    $res = gpt_call($api_key, $model, $chat, $temp, $max_tokens);
    echo json_encode(['output'=>$res]); exit;
}

// Hàm gọi Gemini (chỉ mẫu, cần dùng curl)
function gemini_call($api_key, $model, $chat, $temp, $max_tokens) {
    // Chuyển chat format của bạn sang messages của Gemini (hoặc tham khảo tài liệu Google Gemini)
    // Ví dụ:
    $input = [
        "contents" => array_map(function($m){
            return ['role'=>$m['role']=='user'?'user':'model', 'parts'=>[ ['text'=>$m['content']] ] ];
        }, $chat),
        "generationConfig"=>[
            "temperature"=> $temp,
            "maxOutputTokens"=> $max_tokens
        ]
    ];
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$api_key";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    $arr = json_decode($result, true);
    return $arr['candidates'][0]['content']['parts'][0]['text'] ?? 'Lỗi hoặc không có phản hồi.';
}

function gpt_call($api_key, $model, $chat, $temp, $max_tokens) {
    // Chuyển sang messages của OpenAI
    $messages = [];
    foreach ($chat as $m) $messages[] = ["role"=>$m['role'], "content"=>$m['content']];
    $data = [
        "model" => $model,
        "messages" => $messages,
        "temperature" => $temp,
        "max_tokens" => $max_tokens
    ];
    $url = "https://api.openai.com/v1/chat/completions";
    $headers = [
        "Authorization: Bearer $api_key",
        "Content-Type: application/json"
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    $arr = json_decode($result, true);
    return $arr['choices'][0]['message']['content'] ?? 'Lỗi hoặc không có phản hồi.';
}
