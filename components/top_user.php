<?php
if (!function_exists('top_user')) {
function top_user($users) {
    echo "<div class='flex flex-col gap-2'>";
    foreach ($users as $u) {
        echo "<div class='bg-gray-50 rounded shadow px-4 py-2 flex justify-between items-center hover:bg-blue-50'>";
        echo "<div class='font-semibold text-base'>".htmlspecialchars($u['name'])."</div>";
        echo "<div class='font-bold text-blue-700'>Prompt: ".number_format($u['prompt_count'])."</div>";
        echo "</div>";
    }
    echo "</div>";
}
}
?>
