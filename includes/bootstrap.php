<?php
/**
 * Loaded at the top of every page and API endpoint.
 */

declare(strict_types=1);

define('SITE_ROOT', dirname(__DIR__));
define('INC', __DIR__);

require INC . '/storage.php';
$GLOBALS['config'] = require INC . '/config.php';

if (!empty($GLOBALS['config']['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

mb_internal_encoding('UTF-8');
date_default_timezone_set('America/New_York');

require INC . '/functions.php';
require INC . '/routing.php';
require INC . '/appearance.php';
require INC . '/mailer.php';
require INC . '/forms.php';
require INC . '/media.php';
require INC . '/blog.php';
foreach (glob(INC . '/components/*.php') as $component) {
    require $component;
}

send_security_headers();

// Keep every link pointing at the current page addresses (see Admin → Page URLs).
if (PHP_SAPI !== 'cli') {
    ob_start('rewrite_links');
}
