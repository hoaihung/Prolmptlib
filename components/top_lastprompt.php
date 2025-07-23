<?php
if (!function_exists('top_lastprompt')) {
function top_lastprompt($prompts, $field, $label) {
    echo "<div class='flex flex-col gap-2'>";
    foreach ($prompts as $pr) {
        echo "<div class='bg-gray-50 rounded shadow px-4 py-2 flex justify-between items-center hover:bg-blue-50'>";
        echo "<div>
            <div class='font-semibold text-base'>".htmlspecialchars($pr['title'])."</div>
            <div class='text-xs text-gray-500'>".htmlspecialchars($pr['author_name'] ?? '')."</div>
        </div>";
        echo "</div>";
    }
    echo "</div>";
}
}
?>
