<?php
require_once '../includes/loader.php';
//if (!is_logged_in() ) exit(json_encode(['success'=>false, 'message'=>'No permission']));
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// ========= BATCH ADD PROMPT =========
// Xử lý thêm nhiều prompt bằng JSON. Phải đặt trước xử lý POST để tránh bị bắt bởi block thêm/sửa thông thường.
if ($action === 'batch_add' && $_SERVER['REQUEST_METHOD'] === 'POST' && is_admin()) {
    // Đọc dữ liệu JSON gửi lên
    $input = json_decode(file_get_contents("php://input"), true);
    $prompts = $input['prompts'] ?? [];
    $success = 0;
    $error = '';
    foreach ($prompts as $pr) {
        try {
            $title        = $pr['title'] ?? '';
            $category_id  = intval($pr['category_id'] ?? 0);
            $description  = $pr['description'] ?? '';
            $content      = $pr['content'] ?? '';
            $tags         = $pr['tags'] ?? [];
            $console_flag = !empty($pr['console_enabled']) ? 1 : 0;
            $premium_flag = !empty($pr['premium']) ? 1 : 0;
            $author_id    = $_SESSION['user_id'];
            // Bỏ qua prompt thiếu tiêu đề hoặc nội dung
            if (!$title || !$content) continue;
            // Thêm prompt vào bảng
            $stmt = $pdo->prepare("INSERT INTO prompts (title,category_id,description,content,author_id,console_enabled,premium,is_active,is_approved,created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())");
            $stmt->execute([
                $title,
                $category_id,
                $description,
                $content,
                $author_id,
                $console_flag,
                $premium_flag,
                1, // is_active
                1  // is_approved (có thể đặt lại tuỳ theo yêu cầu)
            ]);
            $prompt_id = $pdo->lastInsertId();
            // Gán tags nếu truyền vào
            if (!empty($tags) && is_array($tags)) {
                foreach ($tags as $tag_id) {
                    $pdo->prepare("INSERT INTO prompt_tags (prompt_id, tag_id) VALUES (?,?)")->execute([$prompt_id, intval($tag_id)]);
                }
            }
            $success++;
        } catch (Exception $ex) {
            $error .= "\n" . $ex->getMessage();
        }
    }
    echo json_encode(['success' => $success, 'error' => $error]);
    exit;
}

// Lấy prompt để sửa (GET)
if ($action === 'get' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $q = $pdo->prepare("SELECT p.*, u.name AS author_name, c.name AS category_name
        FROM prompts p
        LEFT JOIN users u ON p.author_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id=? LIMIT 1");
    $q->execute([$id]);
    $pr = $q->fetch(PDO::FETCH_ASSOC);
	if (!$pr) { echo json_encode(['error'=>'Not found']); exit; }
	
	// Check quyền xem prompt
    $isPremium = $pr['premium'] ?? 0;
    $canView = false;
    if (!$isPremium) {
        $canView = true; // Free: ai cũng xem
    } elseif (is_admin() || is_root() || is_premium()) {
        $canView = true;
    }

    if (!$canView) {
		echo json_encode([
          'success' => false,
		  'title' => $pr['title'],
		  'description' => $pr['description'],
          'author_name' => $pr['author_name'],
          'category_name' => $pr['category_name'],
		  'locked' => true,
		  'content' => '',
		  // Có thể trả preview, hoặc chuỗi khoá, hoặc null
          'message' => 'Bạn cần tài khoản Premium để xem prompt này.',
		]);
		exit;
	}
	$pr['locked'] = false;
    // Lấy tag
    $tq = $pdo->prepare("SELECT t.id FROM prompt_tags pt JOIN tags t ON pt.tag_id = t.id WHERE pt.prompt_id=?");
    $tq->execute([$id]);
    $tags = $tq->fetchAll(PDO::FETCH_COLUMN);
    $pr['tags'] = $tags;
    exit(json_encode($pr));
}

// Thêm/Sửa prompt (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $console = isset($_POST['console_enabled']) ? 1 : 0;
    $premium = isset($_POST['premium']) ? 1 : 0;
    $tags = $_POST['tags'] ?? [];
    // Quyền: chỉ premium thêm/sửa prompt cá nhân, admin/root full quyền
    $is_admin = is_admin() || is_root();
    $is_approved = $is_admin ? 1 : 0;
    $user_id = $_SESSION['user_id'];
    if ($id) {
        $q = $pdo->prepare("SELECT * FROM prompts WHERE id=? LIMIT 1");
        $q->execute([$id]);
        $pr = $q->fetch();
        if (!$pr) exit(json_encode(['success'=>false,'message'=>'Prompt không tồn tại']));
        if (!$is_admin && $pr['author_id'] != $user_id)
            exit(json_encode(['success'=>false,'message'=>'Chỉ sửa prompt của bạn!']));
        $pdo->prepare("UPDATE prompts SET title=?, description=?, content=?, category_id=?, console_enabled=?, premium=?, updated_at=NOW() WHERE id=?")
            ->execute([$title, $desc, $content, $category_id, $console, $premium, $id]);
        $pdo->prepare("DELETE FROM prompt_tags WHERE prompt_id=?")->execute([$id]);
        foreach ($tags as $tagid) {
            $pdo->prepare("INSERT INTO prompt_tags (prompt_id, tag_id) VALUES (?,?)")->execute([$id, $tagid]);
        }
        exit(json_encode(['success'=>true]));
    } else {
        if (!$is_admin && user_role()!=='premium')
            exit(json_encode(['success'=>false,'message'=>'Bạn không có quyền thêm prompt']));
        $pdo->prepare("INSERT INTO prompts (title, description, content, author_id, category_id, console_enabled, premium, created_at, updated_at, is_approved)
               VALUES (?,?,?,?,?,?,?,NOW(),NOW(),?)")
            ->execute([$title, $desc, $content, $user_id, $category_id, $console, $premium, $is_approved]);
        $pid = $pdo->lastInsertId();
        foreach ($tags as $tagid) {
            $pdo->prepare("INSERT INTO prompt_tags (prompt_id, tag_id) VALUES (?,?)")->execute([$pid, $tagid]);
        }

        // XỬ LÝ nếu có request_id thì đánh dấu done, gửi noti cho user tạo yêu cầu!
        $request_id = intval($_POST['request_id'] ?? 0);
        if ($request_id) {
            // Đánh dấu done
            $pdo->prepare("UPDATE request_prompts SET is_done=1 WHERE id=?")->execute([$request_id]);
            // Lấy user_id yêu cầu prompt này
            $stmt = $pdo->prepare("SELECT user_id FROM request_prompts WHERE id=?");
            $stmt->execute([$request_id]);
            $req_user = $stmt->fetchColumn();

            if ($req_user) {
                // Gửi thông báo cho user
                $pdo->prepare("INSERT INTO notifications (user_id, title, content, type, link) VALUES (?,?,?,?,?)")
                    ->execute([
                        $req_user,
                        "Yêu cầu prompt của bạn đã được xử lý!",
                        "Prompt ".htmlspecialchars($title)." bạn yêu cầu đã được admin thêm vào hệ thống.",
                        "prompt",
                        SITE_URL."prompts.php?id=".$pid
                    ]);
            }
        }

        exit(json_encode(['success'=>true]));

    }
}

// (Đã xử lý batch_add ở đầu file để tránh xung đột với POST)


// Lock Prompt
if ($_GET['action'] === 'lock' && is_admin()) {
    $pdo->prepare("UPDATE prompts SET is_locked=1 WHERE id=?")->execute([$_GET['id']]);
    die(json_encode(['success'=>true]));
}
// Unlock Prompt
if ($_GET['action'] === 'unlock' && is_admin()) {
    $pdo->prepare("UPDATE prompts SET is_locked=0 WHERE id=?")->execute([$_GET['id']]);
    die(json_encode(['success'=>true]));
}


// Khôi phục prompt
if ($action === 'restore' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $pdo->prepare("UPDATE prompts SET is_deleted=0, deleted_at=NULL, is_active=1 WHERE id=?")->execute([$id]);
    exit(json_encode(['success'=>true]));
}

// Đổi trạng thái active/inactive
if ($action === 'status' && isset($_GET['id'], $_GET['active'])) {
    $id = intval($_GET['id']);
    $active = intval($_GET['active']);
    $pr = $pdo->query("SELECT * FROM prompts WHERE id=$id LIMIT 1")->fetch();
    if (!$pr) exit(json_encode(['success'=>false,'message'=>'Prompt không tồn tại']));
    if ($pr['is_deleted']) exit(json_encode(['success'=>false,'message'=>'Prompt đã xóa']));
    $is_admin = is_admin() || is_root();
    $user_id = $_SESSION['user_id'];
    if (!$is_admin && $pr['author_id'] != $user_id)
        exit(json_encode(['success'=>false,'message'=>'Chỉ thao tác prompt của bạn!']));
    $pdo->prepare("UPDATE prompts SET is_active=? WHERE id=?")->execute([$active, $id]);
    exit(json_encode(['success'=>true]));
}

// Xóa mềm, chỉ khi đã vô hiệu hóa
if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $pr = $pdo->query("SELECT * FROM prompts WHERE id=$id LIMIT 1")->fetch();
    $is_admin = is_admin() || is_root();
    $user_id = $_SESSION['user_id'];
    if (!$pr) exit(json_encode(['success'=>false,'message'=>'Prompt không tồn tại']));
    if ($pr['is_active']) exit(json_encode(['success'=>false,'message'=>'Phải vô hiệu hóa trước khi xóa!']));
    if (!$is_admin && $pr['author_id'] != $user_id)
        exit(json_encode(['success'=>false,'message'=>'Chỉ xóa prompt của bạn!']));
    $pdo->prepare("UPDATE prompts SET is_deleted=1, deleted_at=NOW() WHERE id=?")->execute([$id]);
    exit(json_encode(['success'=>true]));
}

// Duyệt prompt
if ($_GET['action'] === 'approve' && is_admin()) {
    $pdo->prepare("UPDATE prompts SET is_approved=1, is_active=1 WHERE id=?")->execute([$_GET['id']]);
    // Lấy prompt để thông báo
    // Sau khi duyệt prompt
    $stmt = $pdo->prepare("SELECT * FROM prompts WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $prompt = $stmt->fetch();

    $author_id = $prompt['author_id'];

    // Kiểm tra user tạo prompt KHÔNG phải admin/root mới gửi noti
    $author_stmt = $pdo->prepare("SELECT role FROM users WHERE id=?");
    $author_stmt->execute([$author_id]);
    $author_role = $author_stmt->fetchColumn();

    if ($author_role !== 'admin' && $author_role !== 'root') {
        $pdo->prepare("INSERT INTO notifications (user_id, title, content, type, link) VALUES (?,?,?,?,?)")
            ->execute([
                $author_id,
                "Prompt của bạn đã được duyệt",
                "Prompt <b>".htmlspecialchars($prompt['title'])."</b> đã được duyệt và hiển thị trên hệ thống.",
                "prompt",
                SITE_URL."prompts.php?id=".$prompt['id']
            ]);
    }
    die(json_encode(['success'=>true]));
}

// Xóa prompt pending
if ($_GET['action'] === 'reject' && is_admin()) {
    $pdo->prepare("DELETE FROM prompts WHERE id=? AND is_approved=0")->execute([$_GET['id']]);
    die(json_encode(['success'=>true]));
}



exit(json_encode(['success'=>false, 'message'=>'Sai action!']));

