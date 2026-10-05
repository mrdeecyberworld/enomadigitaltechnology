<?php
/**
 * Front controller: every page address is resolved here using the address
 * table in Admin → Page URLs, services, blog posts and resource guides.
 * Page templates live in pages/ (not directly reachable from the web).
 */

declare(strict_types=1);

// Used by check.php to confirm that clean page addresses (.htaccess rewrites) work.
if (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) === '/enoma-rewrite-test') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    exit('rewrite-ok');
}

require __DIR__ . '/includes/bootstrap.php';

$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$requestQuery = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_QUERY);

/** Resolve the request to a template file (plus extra $_GET values) or a redirect address. */
function resolve_route(string $path): array
{
    // Tidy addresses: /index, /index.php, /page.php and trailing slashes.
    if (preg_match('#^/index(\.php)?/?$#', $path)) {
        return ['redirect' => '/'];
    }
    if (str_ends_with($path, '.php')) {
        return ['redirect' => substr($path, 0, -4)];
    }
    if ($path !== '/' && str_ends_with($path, '/')) {
        return ['redirect' => rtrim($path, '/')];
    }

    $slug = trim($path, '/');
    $routes = routes();

    if ($slug === '') {
        return ['file' => 'home.php'];
    }
    if ($slug === 'sitemap.xml') {
        return ['file' => 'sitemap.php'];
    }
    if ($slug === 'theme.css') {
        return ['file' => 'theme-css.php'];
    }

    // Fixed pages (Admin → Page URLs)
    $key = array_search($slug, $routes, true);
    if ($key !== false && isset(PAGE_FILES[$key])) {
        return ['file' => PAGE_FILES[$key]];
    }

    // Blog feed and posts: /blog/feed.xml, /blog/post-address
    if ($slug === $routes['blog'] . '/feed.xml') {
        return ['file' => 'blog-feed.php'];
    }
    // (A post or guide that isn't found falls through to the redirect check below,
    // so renamed ones still work; drafts are previewable by logged-in admins.)
    if (preg_match('#^' . preg_quote($routes['blog'], '#') . '/([a-z0-9-]+)$#', $slug, $m)
        && (blog_find($m[1]) || (viewer_is_admin() && blog_find($m[1], true)) || link_path('/' . $slug) === '/' . $slug)) {
        return ['file' => 'blog-post.php', 'get' => ['slug' => $m[1]]];
    }

    // Resource guides: /resources/guide-address
    if (preg_match('#^' . preg_quote($routes['resources'], '#') . '/([a-z0-9-]+)$#', $slug, $m)
        && (resource_find($m[1]) || link_path('/' . $slug) === '/' . $slug)) {
        return ['file' => 'resource.php', 'get' => ['slug' => $m[1]]];
    }

    // Services: /service-address
    if (preg_match('/^[a-z0-9-]+$/', $slug) && isset(services()[$slug])) {
        $layout = services()[$slug]['layout'] ?? '';
        return ['file' => $layout === 'training' ? 'training.php' : 'service.php', 'get' => ['slug' => $slug]];
    }

    // An old address that has moved.
    $moved = link_path('/' . $slug);
    if ($moved !== '/' . $slug) {
        return ['redirect' => $moved];
    }
    return ['file' => '404.php', 'status' => 404];
}

$route = resolve_route($requestPath);
if (isset($route['redirect'])) {
    header('Location: ' . $route['redirect'] . ($requestQuery !== '' ? '?' . $requestQuery : ''), true, 301);
    exit;
}
if (isset($route['status'])) {
    http_response_code($route['status']);
}
$_GET = array_merge($_GET, $route['get'] ?? []);
require __DIR__ . '/pages/' . $route['file'];
