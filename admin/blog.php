<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    if (($_POST['action'] ?? '') === 'delete') {
        blog_delete((string) ($_POST['id'] ?? ''));
        flash('Post deleted.');
    }
    redirect('/admin/blog');
}

$status = (string) ($_GET['status'] ?? '');
$q = trim((string) ($_GET['q'] ?? ''));
$all = blog_posts(true);
$label = static function (array $p): array {
    if (($p['status'] ?? '') !== 'published') {
        return ['Draft', 'draft'];
    }
    return strtotime((string) $p['published_at']) > time() ? ['Scheduled', 'scheduled'] : ['Published', 'published'];
};
$list = array_filter($all, static function ($p) use ($status, $q, $label) {
    if ($status !== '' && $label($p)[1] !== $status) {
        return false;
    }
    return $q === '' || stripos($p['title'] . ' ' . $p['excerpt'], $q) !== false;
});
$counts = ['published' => 0, 'draft' => 0, 'scheduled' => 0];
foreach ($all as $p) {
    $counts[$label($p)[1]]++;
}

admin_header('Blog posts', 'blog');
?>
<header class="page-head">
  <div>
    <h1>Blog posts</h1>
    <p class="muted">Write, schedule and publish articles for <a href="/blog" target="_blank" rel="noopener">your blog</a>. Need ideas? Browse the <a href="/admin/news">latest tech &amp; security news</a>.</p>
  </div>
  <a class="btn btn--primary" href="/admin/blog-edit?new=1"><?= icon('plus', 'icon icon-sm') ?> New post</a>
</header>

<div class="toolbar">
  <nav class="chips" aria-label="Filter posts">
    <a href="/admin/blog"<?= $status === '' ? ' aria-current="page"' : '' ?>>All (<?= count($all) ?>)</a>
    <a href="/admin/blog?status=published"<?= $status === 'published' ? ' aria-current="page"' : '' ?>>Published (<?= $counts['published'] ?>)</a>
    <a href="/admin/blog?status=scheduled"<?= $status === 'scheduled' ? ' aria-current="page"' : '' ?>>Scheduled (<?= $counts['scheduled'] ?>)</a>
    <a href="/admin/blog?status=draft"<?= $status === 'draft' ? ' aria-current="page"' : '' ?>>Drafts (<?= $counts['draft'] ?>)</a>
  </nav>
  <form method="get" class="search" role="search">
    <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    <label for="post-search" class="sr-only">Search posts</label>
    <input type="search" id="post-search" name="q" value="<?= e($q) ?>" placeholder="Search posts">
  </form>
</div>

<?php if (!$list): ?>
  <div class="panel empty"><?= icon('file-text', 'icon') ?><p><?= $all ? 'No posts match.' : 'No posts yet.' ?> <a href="/admin/blog-edit?new=1">Write your first post</a>.</p></div>
<?php else: ?>
  <div class="post-rows">
    <?php foreach ($list as $p): [$lbl, $cls] = $label($p); $cat = blog_category((string) $p['category']); $thumb = image_preview_src((string) $p['cover']); ?>
      <article class="post-row panel">
        <a class="post-row__thumb" href="/admin/blog-edit?id=<?= e($p['id']) ?>" tabindex="-1" aria-hidden="true">
          <?php if ($thumb): ?><img src="<?= e($thumb) ?>" alt=""><?php else: ?><span class="post-row__art"><?= icon($cat['icon'], 'icon') ?></span><?php endif; ?>
        </a>
        <div class="post-row__main">
          <h2><a href="/admin/blog-edit?id=<?= e($p['id']) ?>"><?= e($p['title']) ?></a></h2>
          <p class="muted small">
            <span class="status status--<?= e($cls) ?>"><?= e($lbl) ?></span>
            <?= e($cat['name']) ?> · <?= $p['published_at'] ? e(date('M j, Y g:i a', strtotime($p['published_at']))) : 'No date' ?>
            <?php if (!empty($p['featured'])): ?> · <strong>Featured</strong><?php endif; ?>
          </p>
        </div>
        <div class="post-row__actions">
          <a class="btn btn--light btn--sm" href="/admin/blog-edit?id=<?= e($p['id']) ?>">Edit</a>
          <a class="btn btn--light btn--sm" href="<?= e(blog_url($p)) ?>" target="_blank" rel="noopener"><?= $cls === 'published' ? 'View' : 'Preview' ?></a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php admin_footer();
