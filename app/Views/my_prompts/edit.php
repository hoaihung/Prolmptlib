<?php include __DIR__ . '/../../../includes/header.php'; ?>

<main class="max-w-3xl mx-auto mt-8 px-4 pb-12">
    <div class="mb-6">
        <a href="<?=SITE_URL?>my-prompts" class="text-blue-600 hover:underline">&larr; Quay lại danh sách</a>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border p-6 md:p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Sửa Prompt</h1>
        
        <?php 
        $action_url = SITE_URL . "my-prompts/update/" . $prompt['id'];
        $is_admin = false; // User context
        include __DIR__ . '/../partials/prompt_form.php'; 
        ?>
    </div>
</main>



<?php include __DIR__ . '/../../../includes/footer.php'; ?>
