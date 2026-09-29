<?php
/**
 * Local development router for PHP's built-in server (mirrors .htaccess):
 *   php -S localhost:8000 router.php
 * Not used on Apache hosting.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$root = __DIR__;

if (preg_match('#^/(includes|storage)(/|$)|^/(config\.sample\.php|router\.php)$#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
if ($path === '/sitemap.xml') {
    require $root . '/sitemap.php';
    return true;
}
if ($path !== '/' && is_file($root . $path) && !str_ends_with($path, '.php')) {
    return false; // static file
}
$clean = rtrim($path, '/');
$file = $clean === '' ? $root . '/index.php' : $root . $clean . '.php';
if (str_ends_with($path, '.php') && is_file($root . $path)) {
    $file = $root . $path;
}
if (is_file($file)) {
    chdir(dirname($file));
    require $file;
    return true;
}
require $root . '/404.php';
return true;
