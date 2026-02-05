<?php
require_once '../includes/loader.php';
if (!is_admin() && !is_root()) { header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
$prompt = [];

// Handle Post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $content = $_POST['content'];
    $cat_id = intval($_POST['category_id']);
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $premium = isset($_POST['premium']) ? 1 : 0;
    $console = isset($_POST['console_enabled']) ? 1 : 0;
    $admin_content = $_POST['admin_content'] ?? '';
    
    // Thumbnail
    $thumbnail = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg','jpeg','png','webp'])) {
            $fname = 'thumb_'.time().'_'.uniqid().'.'.$ext;
            if(move_uploaded_file($_FILES['thumbnail']['tmp_name'], '../uploads/thumbnails/'.$fname)) {
                $thumbnail = 'uploads/thumbnails/'.$fname;
            }
        }
    }

    if ($id) {
        // Update
        $sql = "UPDATE prompts SET title=?, description=?, content=?, category_id=?, is_approved=?, is_active=?, premium=?, console_enabled=?, admin_content=?, updated_at=NOW()";
        $params = [$title, $desc, $content, $cat_id, $is_approved, $is_active, $premium, $console, $admin_content];
        
        if ($thumbnail) {
            $sql .= ", thumbnail=?";
            $params[] = $thumbnail;
        }
        
        $sql .= " WHERE id=?";
        $params[] = $id;
        
        $pdo->prepare($sql)->execute($params);
    } else {
        // Create
        $author_id = $_SESSION['user_id'];
        $sql = "INSERT INTO prompts (title, description, content, category_id, is_approved, is_active, premium, console_enabled, admin_content, author_id, created_at, thumbnail) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)";
        $pdo->prepare($sql)->execute([$title, $desc, $content, $cat_id, $is_approved, $is_active, $premium, $console, $admin_content, $author_id, $thumbnail]);
        $id = $pdo->lastInsertId();
    }

    // Handle Tags
    $pdo->prepare("DELETE FROM prompt_tags WHERE prompt_id=?")->execute([$id]);
    if (!empty($_POST['tags'])) {
        $stmtT = $pdo->prepare("INSERT INTO prompt_tags (prompt_id, tag_id) VALUES (?,?)");
        foreach($_POST['tags'] as $tid) {
            $stmtT->execute([$id, $tid]);
        }
    }

    header("Location: prompts.php");
    exit;
}

// Fetch Data for View
if ($id) {
    $prompt = $pdo->query("SELECT * FROM prompts WHERE id=$id")->fetch();
    if (!$prompt) die("Prompt not found");
    // Get tags
    $pts = $pdo->query("SELECT tag_id FROM prompt_tags WHERE prompt_id=$id")->fetchAll(PDO::FETCH_COLUMN);
    $prompt['tags'] = $pts;
}

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
$tags = $pdo->query("SELECT * FROM tags")->fetchAll();
$is_admin = true;
$title = $id ? "Sửa Prompt (Admin)" : "Thêm Prompt (Admin)";

include 'layout.php';
?>

<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800"><?= $title ?></h2>
        <a href="prompts.php" class="text-gray-600 hover:underline">Quay lại danh sách</a>
    </div>

    <?php 
    $action_url = "prompts_edit.php" . ($id ? "?id=$id" : "");
    include '../app/Views/partials/prompt_form.php'; 
    ?>
</div>

<?php include 'layout_end.php'; ?>
