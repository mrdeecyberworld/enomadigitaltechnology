<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$settings = content('blog');
$cats = blog_categories();
$catSlug = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['category'] ?? ''));
$activeCat = $catSlug !== '' && isset($cats[$catSlug]) ? $cats[$catSlug] : null;
$all = blog_posts();
$list = $activeCat ? array_values(array_filter($all, static fn ($p) => $p['category'] === $catSlug)) : $all;

$perPage = max(3, (int) ($settings['posts_per_page'] ?? 9));

// On the main blog page, the newest "featured" post (or the newest post) leads.
if (!$activeCat && $list) {
    $lead = null;
    foreach ($list as $p) {
        if (!empty($p['featured'])) { $lead = $p; break; }
    }
    $lead ??= $list[0];
    $list = array_merge([$lead], array_values(array_filter($list, static fn ($p) => $p['id'] !== $lead['id'])));
}
$pages = max(1, (int) ceil(count($list) / $perPage));
$pageNum = min($pages, max(1, (int) ($_GET['page'] ?? 1)));
$shown = array_slice($list, ($pageNum - 1) * $perPage, $perPage);
$featured = (!$activeCat && $pageNum === 1 && $shown) ? array_shift($shown) : null;

$path = '/blog';
$query = static fn (array $q): string => $path . ($q ? '?' . http_build_query($q) : '');
$crumbs = [['Home', '/'], ['Blog', '/blog']];
if ($activeCat) {
    $crumbs[] = [$activeCat['name'], $query(['category' => $catSlug])];
}

$page = [
    'title'       => $activeCat ? $activeCat['name'] . ' Articles | ' . site('name') : ($settings['meta_title'] ?? 'Blog | ' . site('name')),
    'description' => $activeCat ? 'Articles about ' . strtolower($activeCat['name']) . ' from ' . site('name') . '.' : ($settings['meta_description'] ?? site('description')),
    'path'        => $activeCat ? $query(['category' => $catSlug]) : $path,
    'noindex'     => $pageNum > 1,
    'schema'      => [schema_breadcrumbs($crumbs), [
        '@type' => 'Blog',
        '@id'   => abs_url('/blog#blog'),
        'name'  => $settings['heading'] ?? 'Blog',
        'url'   => abs_url('/blog'),
        'publisher' => ['@id' => abs_url('/#organization')],
        'blogPost' => array_map(static fn ($p) => ['@type' => 'BlogPosting', 'headline' => $p['title'], 'url' => abs_url(blog_url($p)), 'datePublished' => blog_date($p, 'c')], array_slice($all, 0, 10)),
    ]],
];
require INC . '/layout/header.php';

echo page_hero($activeCat ? $activeCat['name'] : (string) ($settings['heading'] ?? 'Blog'), [
    'eyebrow' => (string) ($settings['eyebrow'] ?? 'Blog'),
    'text'    => $activeCat ? 'Articles about ' . strtolower($activeCat['name']) . '.' : (string) ($settings['intro'] ?? ''),
    'crumbs'  => $crumbs,
]);
?>
<section class="section section--blog">
  <div class="container">
    <nav class="blog-filter reveal" aria-label="Blog categories">
      <a href="/blog" class="chip"<?= !$activeCat ? ' aria-current="page"' : '' ?>>All articles</a>
      <?php foreach ($cats as $c): ?>
        <a href="<?= e($query(['category' => $c['slug']])) ?>" class="chip"<?= $activeCat && $activeCat['slug'] === $c['slug'] ? ' aria-current="page"' : '' ?>><?= icon($c['icon'], 'icon icon-xs') ?><?= e($c['name']) ?></a>
      <?php endforeach; ?>
      <a href="/blog/feed.xml" class="chip chip--rss"><?= icon('wifi', 'icon icon-xs') ?>RSS</a>
    </nav>

    <?php if (!$featured && !$shown): ?>
      <div class="blog-empty reveal"><?= icon('file-text', 'icon') ?><p>No articles here yet. Check back soon<?= $activeCat ? ', or <a href="/blog">see all articles</a>' : '' ?>.</p></div>
    <?php endif; ?>

    <?php if ($featured): ?>
      <div class="post-featured"><?= blog_card($featured, 'h2', true) ?></div>
    <?php endif; ?>

    <?php if ($shown): ?>
      <h2 class="sr-only"><?= $featured ? 'More articles' : 'Articles' ?></h2>
      <div class="post-grid"><?php foreach ($shown as $p) echo blog_card($p); ?></div>
    <?php endif; ?>

    <?php if ($pages > 1): ?>
      <nav class="pagination" aria-label="Blog pages">
        <?php for ($i = 1; $i <= $pages; $i++): $q = array_filter(['category' => $catSlug ?: null, 'page' => $i > 1 ? $i : null]); ?>
          <a href="<?= e($query($q)) ?>"<?= $i === $pageNum ? ' aria-current="page"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>

<?= cta_banner((string) ($settings['cta_heading'] ?? 'Want help putting this into practice?'), (string) ($settings['cta_text'] ?? '')) ?>
<?php require INC . '/layout/footer.php'; ?>
