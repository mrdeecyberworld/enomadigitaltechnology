<?php
/**
 * Local development router for PHP's built-in server (mirrors .htaccess):
 *   php -S localhost:8000 router.php
 * Not used on Apache hosting.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$root = __DIR__;

if (preg_match('#^/(includes|storage|admin/includes)(/|$)|^/(config\.sample\.php|router\.php)$#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
if ($path === '/blog/feed.xml') {
    require $root . '/blog-feed.php';
    return true;
}
if (preg_match('#^/blog/([a-z0-9-]+)/?$#', $path, $m)) {
    $_GET['slug'] = $m[1];
    require $root . '/blog-post.php';
    return true;
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
if ($clean !== '' && is_dir($root . $clean) && is_file($root . $clean . '/index.php')) {
    $file = $root . $clean . '/index.php';
}
if (str_ends_with($path, '.php') && is_file($root . $path)) {
    $file = $root . $path;
}
if (is_file($file)) {
    chdir(dirname($file));
    require $file;
    return true;
}
if (preg_match('#^/([a-z0-9-]+)/?$#', $path, $m)) {
    $_GET['slug'] = $m[1];
    require $root . '/service.php';
    return true;
}
require $root . '/404.php';
return true;
