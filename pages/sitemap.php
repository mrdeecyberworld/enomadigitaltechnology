<?php
/**
 * XML sitemap at /sitemap.xml, built from the current page addresses,
 * services, resource guides and published blog posts.
 */
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
header_remove('Content-Security-Policy');

$latest = static function (string ...$files): string {
    $times = array_map(static fn ($f) => store_mtime($f), $files);
    return date('Y-m-d', max($times) ?: time());
};
$contentFile = static fn (string $name): string => store_exists(content_override_file($name)) ? content_override_file($name) : INC . '/content/' . $name . '.php';

$priorities = ['services' => '0.9', 'about' => '0.7', 'blog' => '0.8', 'resources' => '0.7', 'faq' => '0.6', 'contact' => '0.7', 'quote' => '0.7', 'consultation' => '0.7', 'privacy' => '0.3', 'terms' => '0.3', 'shop' => '0.8', 'feedback' => '0.4'];
$entries = [['/', '1.0', $latest($contentFile('home'), $contentFile('site'))]];
foreach (PAGE_FILES as $key => $file) {
    if ($key === 'shop' && !shop_is_open()) {
        continue; // listed once something is for sale
    }
    $entries[] = [page_url($key), $priorities[$key] ?? '0.6', $latest(__DIR__ . '/' . $file, $contentFile('pages'))];
}
foreach (services() as $slug => $svc) {
    $entries[] = [service_path($slug), '0.9', $latest($contentFile('services'))];
}
foreach (shop_products() as $p) {
    $entries[] = [shop_url($p), '0.7', $latest($contentFile('shop'))];
}
foreach (content('resources') as $g) {
    $entries[] = [resource_url($g), '0.6', $latest($contentFile('resources'))];
}
foreach (blog_posts() as $post) {
    $entries[] = [blog_url($post), '0.6', substr((string) ($post['updated_at'] ?: $post['published_at']), 0, 10)];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($entries as [$path, $priority, $lastmod]) {
    echo "  <url>\n    <loc>" . e(abs_url($path)) . "</loc>\n    <lastmod>" . e($lastmod) . "</lastmod>\n    <priority>" . e($priority) . "</priority>\n  </url>\n";
}
echo '</urlset>' . "\n";
