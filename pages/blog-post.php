<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$slug = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['slug'] ?? ''));

// Logged-in admins can preview drafts.
start_session();
$isAdmin = !empty($_SESSION['admin_user']);
$post = blog_find($slug) ?? ($isAdmin ? blog_find($slug, true) : null);
if ($post === null) {
    require __DIR__ . '/404.php';
    exit;
}

$settings = content('blog');
$cat = blog_category((string) $post['category']);
$url = abs_url(blog_url($post));
$crumbs = [['Home', '/'], ['Blog', '/blog'], [$post['title'], blog_url($post)]];
$coverUrl = '';
if ($post['cover'] !== '' && preg_match('#^/?assets/#', $post['cover'])) {
    $coverUrl = abs_url('/' . ltrim($post['cover'], '/'));
}

$related = array_values(array_filter(blog_posts(), static fn ($p) => $p['id'] !== $post['id'] && $p['category'] === $post['category']));
foreach (blog_posts() as $p) {
    if (count($related) >= 3) break;
    if ($p['id'] !== $post['id'] && !in_array($p, $related, true)) $related[] = $p;
}
$related = array_slice($related, 0, 3);

$page = [
    'title'       => ($post['meta_title'] ?: $post['title']) . ($post['meta_title'] ? '' : ' | ' . site('name')),
    'description' => $post['meta_description'] ?: $post['excerpt'],
    'path'        => blog_url($post),
    'og_type'     => 'article',
    'noindex'     => !blog_is_live($post),
    'schema'      => [schema_breadcrumbs($crumbs), array_filter([
        '@type'         => 'BlogPosting',
        'headline'      => $post['title'],
        'description'   => $post['excerpt'],
        'url'           => $url,
        'mainEntityOfPage' => $url,
        'datePublished' => blog_date($post, 'c'),
        'dateModified'  => $post['updated_at'] ?: blog_date($post, 'c'),
        'author'        => ['@type' => $post['author'] !== '' ? 'Person' : 'Organization', 'name' => $post['author_display']],
        'publisher'     => ['@id' => abs_url('/#organization')],
        'image'         => $coverUrl ?: abs_url('/assets/img/og-image.png'),
        'articleSection' => $cat['name'],
        'keywords'      => implode(', ', (array) $post['tags']),
    ])],
];
if ($coverUrl) {
    $page['og_image'] = '/' . ltrim($post['cover'], '/');
}
require INC . '/layout/header.php';
$share = rawurlencode($url);
$shareTitle = rawurlencode($post['title']);
?>
<article class="post" aria-labelledby="post-title">
  <header class="post-hero">
    <div class="page-hero__bg" aria-hidden="true"></div>
    <div class="container container--narrow">
      <?= breadcrumbs($crumbs) ?>
      <?php if (!blog_is_live($post)): ?><p class="post-draft"><?= icon('circle-alert', 'icon icon-xs') ?> Draft preview: only visible to you while logged in to the admin.</p><?php endif; ?>
      <a class="post-hero__cat" href="/blog?category=<?= e($cat['slug']) ?>"><?= icon($cat['icon'], 'icon icon-xs') ?><?= e($cat['name']) ?></a>
      <h1 class="post-hero__title" id="post-title"><?= e($post['title']) ?></h1>
      <?php if ($post['excerpt'] !== ''): ?><p class="post-hero__lead"><?= e($post['excerpt']) ?></p><?php endif; ?>
      <p class="post-hero__meta">
        <span class="post-hero__avatar" aria-hidden="true"><?= logo_mark('post-hero__logo') ?></span>
        <span><strong><?= e($post['author_display']) ?></strong><span><time datetime="<?= e(blog_date($post, 'Y-m-d')) ?>"><?= e(blog_date($post)) ?></time> · <?= blog_reading_minutes($post) ?> min read</span></span>
      </p>
    </div>
  </header>

  <div class="container post-layout">
    <div class="post-cover media-frame"><?= blog_cover($post, '(min-width: 1024px) 900px, 100vw', true) ?></div>
    <div class="post-body prose">
      <?= simple_format((string) $post['body']) ?>

      <?php if (!empty($post['sources'])): ?>
        <aside class="post-sources" aria-labelledby="sources-h">
          <h2 id="sources-h">Sources</h2>
          <ul><?php foreach ((array) $post['sources'] as $src): if (!preg_match('#^https?://#', (string) $src)) continue; ?>
            <li><a href="<?= e($src) ?>" rel="noopener noreferrer" target="_blank"><?= e(parse_url($src, PHP_URL_HOST) ?: $src) ?></a></li>
          <?php endforeach; ?></ul>
        </aside>
      <?php endif; ?>

      <?php if (!empty($post['tags'])): ?>
        <ul class="post-tags" aria-label="Tags"><?php foreach ((array) $post['tags'] as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>

      <div class="post-share">
        <span>Share this article</span>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $share ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
        <a href="https://twitter.com/intent/tweet?url=<?= $share ?>&amp;text=<?= $shareTitle ?>" target="_blank" rel="noopener noreferrer">X</a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $share ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
        <a href="mailto:?subject=<?= $shareTitle ?>&amp;body=<?= $share ?>">Email</a>
      </div>

      <div class="post-cta">
        <div>
          <h2><?= e((string) ($settings['cta_heading'] ?? 'Want help putting this into practice?')) ?></h2>
          <p><?= e((string) ($settings['cta_text'] ?? '')) ?></p>
        </div>
        <?= button('Book a Consultation', '/book-a-consultation', 'primary', 'calendar-check') ?>
      </div>
    </div>
  </div>
</article>

<?php if ($related): ?>
<section class="section section--muted" aria-labelledby="related-posts">
  <div class="container">
    <?= section_header('Keep reading', 'Related articles', null, ['id' => 'related-posts']) ?>
    <div class="post-grid"><?php foreach ($related as $p) echo blog_card($p); ?></div>
  </div>
</section>
<?php endif; ?>
<?php require INC . '/layout/footer.php'; ?>
