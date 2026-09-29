<?php
/**
 * XML sitemap, served at /sitemap.xml (see .htaccess).
 */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
header_remove('Content-Security-Policy');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
$entries = site('sitemap');
$listed = array_column($entries, 'path');
foreach (services() as $slug => $svc) {
    if (!in_array(service_path($slug), $listed, true)) {
        $entries[] = ['path' => service_path($slug), 'priority' => '0.8'];
    }
}
// Drop service URLs whose service was removed in the admin.
$entries = array_filter($entries, static function (array $e): bool {
    $slug = ltrim($e['path'], '/');
    return $e['path'] === '/' || is_file(__DIR__ . '/' . $slug . '.php') || service($slug) !== null;
});
foreach (blog_posts() as $post) {
    $entries[] = ['path' => blog_url($post), 'priority' => '0.6', 'lastmod' => substr((string) ($post['updated_at'] ?: $post['published_at']), 0, 10)];
}
foreach ($entries as $entry) {
    $file = __DIR__ . ($entry['path'] === '/' ? '/index.php' : $entry['path'] . '.php');
    if (!is_file($file)) {
        $file = content_override_file('services');
    }
    $lastmod = $entry['lastmod'] ?? (is_file($file) ? date('Y-m-d', filemtime($file)) : date('Y-m-d'));
    echo "  <url>\n";
    echo '    <loc>' . e(abs_url($entry['path'])) . "</loc>\n";
    echo '    <lastmod>' . $lastmod . "</lastmod>\n";
    echo '    <priority>' . e($entry['priority']) . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
