<?php
function inline_svg($type, $cls = '') {
    switch ($type) {
        case 'unlock':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M7 11V7a5 5 0 0 1 9.9-1M17 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-2zm-6 4a2 2 0 1 1 4 0 2 2 0 0 1-4 0z' stroke-linecap='round' stroke-linejoin='round'/></svg>";
        case 'lock':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M12 17a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-4-6v-2a4 4 0 1 1 8 0v2m-10 0h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2z' stroke-linecap='round' stroke-linejoin='round'/></svg>";


        case 'chatgpt':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M12 2v2m0 0h4a2 2 0 0 1 2 2v2H6V6a2 2 0 0 1 2-2h4zm-6 6h12v10a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V8zm2 4h.01M16 12h.01' stroke-linecap='round' stroke-linejoin='round'/></svg>";

        case 'gemini':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='1.8' viewBox='0 0 24 24'><path d='M12 2a7 7 0 0 0 0 20 7 7 0 0 0 0-20zm0 3a4 4 0 1 1 0 16 4 4 0 0 1 0-16z' stroke-linecap='round' stroke-linejoin='round'/></svg>";

        case 'heart-fill':
            return "<svg class='{$cls}' fill='currentColor' stroke='currentColor' stroke-width='0' viewBox='0 0 24 24'><path d='M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z'/></svg>";
        case 'folder':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M3 7a2 2 0 0 1 2-2h5l2 2h9a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z' stroke-linecap='round' stroke-linejoin='round'/></svg>";
        case 'heart':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 1 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z' stroke-linecap='round' stroke-linejoin='round'/></svg>";
        case 'prompt':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M4 4h16v16H4zM8 9l3 3-3 3m5 0h3' stroke-linecap='round' stroke-linejoin='round'/></svg>";
        case 'approved':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M9 12l2 2 4-4M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z' stroke-linecap='round' stroke-linejoin='round'/></svg>";
        case 'reject':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M15 9l-6 6m0-6l6 6M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z' stroke-linecap='round' stroke-linejoin='round'/></svg>";

        case 'code':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M16 18l6-6-6-6M8 6l-6 6 6 6' stroke-linecap='round' stroke-linejoin='round'/></svg>";
        case 'shield':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z' stroke-linecap='round' stroke-linejoin='round'/></svg>";
        case 'bolt':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M13 2L3 14h9l-1 8L21 10h-9l1-8z' stroke-linecap='round' stroke-linejoin='round'/></svg>";
        case 'star':
            return '<svg class="'.$cls.'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l3.09 6.26L21 9.27l-4.91 4.78L17.18 21 12 17.77 6.82 21l1.09-6.95L3 9.27l5.91-.01L12 3z" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        case 'premium':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M4 15l3.5-10 4.5 6 4.5-6L20 15H4zm0 0v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2' stroke-linecap='round' stroke-linejoin='round'/></svg>";
        case 'active':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z' stroke-linecap='round' stroke-linejoin='round'/></svg>";

        case 'console':
            return '<svg class="'.$cls.'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 4 19 12 5 20 5 4"/></svg>';
        case 'deactive':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a17.724 17.724 0 0 1 5.06-5.94M10.58 10.58A2 2 0 1 0 13.41 13.4M1 1l22 22' stroke-linecap='round' stroke-linejoin='round'/></svg>";

        case 'detail':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M9 12h6M9 16h6M13 8H9m-4 12h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z' stroke-linecap='round' stroke-linejoin='round'/></svg>";

        case 'copy':
            return '<svg class="'.$cls.'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><rect x="1" y="1" width="15" height="15" rx="2"/></svg>';
        case 'edit':
            return '<svg class="'.$cls.'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
        case 'user':
            return '<svg class="'.$cls.'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>';
        case 'category':
            return '<svg class="'.$cls.'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg>';
        case 'tag':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M20 12V6a2 2 0 0 0-2-2h-6l-8 8 8 8 8-8zM7 7h.01' stroke-linecap='round' stroke-linejoin='round'/></svg>";
        case 'view':
            return '<svg class="'.$cls.'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
        case 'trash':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m2 0v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6h16zM10 11v6M14 11v6' stroke-linecap='round' stroke-linejoin='round'/></svg>";
        case 'deleted':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m2 0v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6m5 7l6 6m0-6l-6 6' stroke-linecap='round' stroke-linejoin='round'/></svg>";

        case 'restore':
            return "<svg class='{$cls}' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M3 12a9 9 0 1 1 9 9m-4-4l-5 5m5 0H4' stroke-linecap='round' stroke-linejoin='round'/></svg>";

        default:
            return '';
    }
}
?>
