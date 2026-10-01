<?php
declare(strict_types=1);

// Text for this page is edited in Admin → Pages (content section "pages", key "quote").
$formPage = [
    'slug'  => 'get-a-quote',
    'type'  => 'quote',
    'key'   => 'quote',
    'icons' => ['clipboard-list', 'search', 'file-text'],
];
require dirname(__DIR__) . '/includes/templates/form-page.php';
