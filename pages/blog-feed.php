<?php
/** RSS feed at /blog/feed.xml */
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/rss+xml; charset=utf-8');
header_remove('Content-Security-Policy');
$settings = content('blog');
$x = static fn (string $s): string => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>' . "\n";
echo '<title>' . $x((string) ($settings['heading'] ?? site('name') . ' Blog')) . '</title>';
echo '<link>' . $x(abs_url('/blog')) . '</link>';
echo '<atom:link href="' . $x(abs_url('/blog/feed.xml')) . '" rel="self" type="application/rss+xml"/>';
echo '<description>' . $x((string) ($settings['intro'] ?? '')) . '</description><language>en-us</language>' . "\n";
foreach (array_slice(blog_posts(), 0, 20) as $p) {
    echo '<item><title>' . $x($p['title']) . '</title><link>' . $x(abs_url(blog_url($p))) . '</link>'
        . '<guid isPermaLink="true">' . $x(abs_url(blog_url($p))) . '</guid>'
        . '<pubDate>' . date(DATE_RSS, strtotime($p['published_at'])) . '</pubDate>'
        . '<category>' . $x(blog_category((string) $p['category'])['name']) . '</category>'
        . '<description>' . $x($p['excerpt']) . '</description></item>' . "\n";
}
echo '</channel></rss>' . "\n";
