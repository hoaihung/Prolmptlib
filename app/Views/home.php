<?php include __DIR__ . '/../../includes/header.php'; ?>
<main class="max-w-6xl mx-auto mt-4 p-3">
  <?php 
    // $modules_page comes from Controller
    if (isset($modules_page)) {
        foreach ($modules_page as $mod) {
            include __DIR__ . '/../../components/module_'.$mod['type'].'.php'; 
        }
    }
  ?>
</main>

</body>
<?php require_once __DIR__ . '/../../includes/premium_modal.php'; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
