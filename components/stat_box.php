<?php
require_once '../includes/svg.php';
if (!function_exists('stat_box')) {
    function stat_box($label, $value, $icon = 'clipboard-list') {
        $iconSvg = inline_svg($icon, 'w-6 h-6 text-gray-400');

        echo "
        <div class='bg-white rounded-xl shadow p-5 flex flex-col items-center'>
            <div class='mb-2 text-blue-500'>$iconSvg</div>
            <div class='text-2xl font-bold mb-1'>$value</div>
            <div class='text-gray-500'>$label</div>
        </div>";
    }
}
?>
