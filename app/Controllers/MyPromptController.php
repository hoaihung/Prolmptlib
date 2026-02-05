<?php
class MyPromptController {
    public function index() {
        global $pdo;
        if (!is_logged_in()) { header("Location: " . SITE_URL . "login.php"); exit; }
        
        $user_id = $_SESSION['user_id'];
        
        // Fetch user prompts
        // Pagination
        $page = max(1, intval($_GET['page'] ?? 1));
        $per_page = 12;
        $offset = ($page - 1) * $per_page;

        // Count total
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM prompts WHERE author_id=? AND is_deleted=0");
        $stmtCount->execute([$user_id]);
        $total = $stmtCount->fetchColumn();
        $total_pages = ceil($total / $per_page);

        // Fetch user prompts
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM prompts p LEFT JOIN categories c ON p.category_id=c.id WHERE p.author_id=? AND p.is_deleted=0 ORDER BY p.id DESC LIMIT $per_page OFFSET $offset");
        $stmt->execute([$user_id]);
        $my_prompts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $page_title = "Prompt của tôi - PromptLib";
        require_once __DIR__ . '/../Views/my_prompts/index.php';
    }

    public function create() {
        global $pdo;
        if (!is_logged_in()) { header("Location: " . SITE_URL . "login.php"); exit; }
        
        // Check Premium
        if (!is_premium() && !is_admin() && !is_root()) {
            // Redirect or show error
            // For now, redirect to upgrade info or similar
            // But user said: "phân quyền lợi của premium... có thể ở việc dc quản lý prompt cá nhân, up prompt"
            // So only Premium can create.
             header("Location: " . SITE_URL . "prompts.php?msg=premium_required"); 
             exit;
        }

        $cats = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
        
        $page_title = "Tạo Prompt mới - PromptLib";
        require_once __DIR__ . '/../Views/my_prompts/create.php';
    }

    public function store() {
        global $pdo;
        if (!is_logged_in()) { header("Location: " . SITE_URL . "login.php"); exit; }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $content = $_POST['content'] ?? '';
            $category_id = $_POST['category_id'] ?? 1;
            $user_id = $_SESSION['user_id'];

            // Handle Thumbnail
            $thumbnail = null;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $filename = 'thumb_' . time() . '_' . uniqid() . '.' . $ext;
                    $target = __DIR__ . '/../../uploads/thumbnails/' . $filename;
                    if (!is_dir(__DIR__ . '/../../uploads/thumbnails')) mkdir(__DIR__ . '/../../uploads/thumbnails', 0777, true);
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
                        $thumbnail = 'uploads/thumbnails/' . $filename;
                    }
                }
            }

            if ($title && $content) {
                $stmt = $pdo->prepare("INSERT INTO prompts (title, description, content, category_id, author_id, is_active, is_approved, thumbnail, created_at) VALUES (?, ?, ?, ?, ?, 1, 0, ?, NOW())");
                // is_approved = 0 (pending)
                $stmt->execute([$title, $description, $content, $category_id, $user_id, $thumbnail]);
                
                header("Location: " . SITE_URL . "my-prompts");
                exit;
            }
        }
    }

    public function edit($id) {
        global $pdo;
        if (!is_logged_in()) { header("Location: " . SITE_URL . "login.php"); exit; }
        
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT * FROM prompts WHERE id=? AND author_id=? AND is_deleted=0");
        $stmt->execute([$id, $user_id]);
        $prompt = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$prompt) {
            header("Location: " . SITE_URL . "my-prompts");
            exit;
        }

        $cats = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
        $page_title = "Sửa Prompt - PromptLib";
        require_once __DIR__ . '/../Views/my_prompts/edit.php';
    }

    public function update($id) {
        global $pdo;
        if (!is_logged_in()) { header("Location: " . SITE_URL . "login.php"); exit; }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $content = $_POST['content'] ?? '';
            $category_id = $_POST['category_id'] ?? 1;

            // Verify ownership
            $stmt = $pdo->prepare("SELECT id FROM prompts WHERE id=? AND author_id=?");
            $stmt->execute([$id, $user_id]);
            if (!$stmt->fetch()) { die("Unauthorized"); }

            // Handle Thumbnail
            $thumbnail = null;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $filename = 'thumb_' . time() . '_' . uniqid() . '.' . $ext;
                    $target = __DIR__ . '/../../uploads/thumbnails/' . $filename;
                    if (!is_dir(__DIR__ . '/../../uploads/thumbnails')) mkdir(__DIR__ . '/../../uploads/thumbnails', 0777, true);
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
                        $thumbnail = 'uploads/thumbnails/' . $filename;
                    }
                }
            }

            if ($title && $content) {
                // Reset approval status on update
                $sql = "UPDATE prompts SET title=?, description=?, content=?, category_id=?, is_approved=0";
                $params = [$title, $description, $content, $category_id];
                
                if ($thumbnail) {
                    $sql .= ", thumbnail=?";
                    $params[] = $thumbnail;
                }
                
                $sql .= " WHERE id=?";
                $params[] = $id;

                $pdo->prepare($sql)->execute($params);
                
                header("Location: " . SITE_URL . "my-prompts");
                exit;
            }
        }
    }

    public function delete($id) {
        global $pdo;
        if (!is_logged_in()) { header("Location: " . SITE_URL . "login.php"); exit; }
        
        $user_id = $_SESSION['user_id'];
        // Soft delete
        $stmt = $pdo->prepare("UPDATE prompts SET is_deleted=1 WHERE id=? AND author_id=?");
        $stmt->execute([$id, $user_id]);
        
        header("Location: " . SITE_URL . "my-prompts");
        exit;
    }
}
?>
