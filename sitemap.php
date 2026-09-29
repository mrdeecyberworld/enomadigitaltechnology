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
foreach (site('sitemap') as $entry) {
    $file = __DIR__ . ($entry['path'] === '/' ? '/index.php' : $entry['path'] . '.php');
    $lastmod = is_file($file) ? date('Y-m-d', filemtime($file)) : date('Y-m-d');
    echo "  <url>\n";
    echo '    <loc>' . e(abs_url($entry['path'])) . "</loc>\n";
    echo '    <lastmod>' . $lastmod . "</lastmod>\n";
    echo '    <priority>' . e($entry['priority']) . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
