<?php
/**
 * Router for PHP's built-in web server, used when running the site on your
 * own computer (see LOCAL-SETUP.md):
 *
 *   php -S localhost:8000 router.php
 *
 * It mirrors the .htaccess rules used on real hosting. Not used on Apache.
 */
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$root = __DIR__;

// Private folders and files are never served.
if (preg_match('#^/(includes|storage|pages|admin/includes)(/|$)|^/(config\.sample\.php|router\.php|README\.md|LOCAL-SETUP\.md|Dockerfile|docker-compose\.yml|\.user\.ini|start[.-][^/]*)$|/\.#', $path)
    || preg_match('#^/assets/uploads/.*\.(php\d?|phtml|phar|html?|svg)$#i', $path)) {
    http_response_code(403);
    exit('Forbidden');
}

// Real static files (CSS, JS, images, fonts) are served directly.
if ($path !== '/' && is_file($root . $path) && !str_ends_with($path, '.php')) {
    return false;
}

// Admin and API scripts: /admin, /admin/login, /api/ai-assistant
if (preg_match('#^/(admin|api)(/[A-Za-z0-9_/-]*)?$#', $path)) {
    $file = rtrim($root . $path, '/');
    $file = is_dir($file) ? $file . '/index.php' : $file . '.php';
    if (is_file($file)) {
        chdir(dirname($file));
        require $file;
        return true;
    }
}
if (preg_match('#^/(admin|api)/.+\.php$#', $path) && is_file($root . $path)) {
    require $root . $path;
    return true;
}

// Everything else goes to the front controller.
require $root . '/index.php';
return true;
