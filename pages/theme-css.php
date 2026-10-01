<?php
/** /theme.css: custom accent color from Admin → Appearance. */
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$look = appearance();
header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: public, max-age=31536000, immutable');
echo $look['accent'] !== '' ? custom_accent_css($look['accent'], $look['theme']) : '/* No custom accent color set. */';
