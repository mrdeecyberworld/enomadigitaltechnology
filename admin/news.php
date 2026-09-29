<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require INC . '/news.php';
require_admin();

$sources = content('news');
$force = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
if ($force) {
    admin_csrf_check();
}
$result = news_fetch_all((array) ($sources['feeds'] ?? []), $force);
if ($force) {
    flash('Headlines refreshed.');
    redirect('/admin/news' . (!empty($_POST['topic']) ? '?topic=' . urlencode((string) $_POST['topic']) : ''));
}

$topics = news_topics();
$topic = (string) ($_GET['topic'] ?? '');
$items = $topic !== '' && isset($topics[$topic]) ? array_values(array_filter($result['items'], static fn ($i) => $i['topic'] === $topic)) : $result['items'];
$items = array_slice($items, 0, 60);
$blogCat = static fn (string $t): string => $t === 'technology' ? 'tech-news' : 'cybersecurity';

admin_header('Tech & security news', 'news');
?>
<header class="page-head">
  <div>
    <h1>Tech &amp; security news</h1>
    <p class="muted">The latest headlines from trusted cybersecurity and technology sources. Use them to stay current and to find ideas for blog posts, training and client advice.</p>
  </div>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="topic" value="<?= e($topic) ?>">
    <button class="btn btn--light" type="submit"><?= icon('refresh-cw', 'icon icon-sm') ?> Refresh</button>
  </form>
</header>

<div class="toolbar">
  <nav class="chips" aria-label="Filter headlines">
    <a href="/admin/news"<?= $topic === '' ? ' aria-current="page"' : '' ?>>All</a>
    <?php foreach ($topics as $key => $label): ?>
      <a href="/admin/news?topic=<?= e($key) ?>"<?= $topic === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
  <span class="muted small">Updated <?= e(date('M j, g:i a', $result['fetched_at'])) ?> · refreshes hourly · <a href="/admin/edit?section=news">Edit sources</a></span>
</div>

<?php if ($result['errors']): ?>
  <details class="notice notice--warn">
    <summary><?= count($result['errors']) ?> source<?= count($result['errors']) === 1 ? '' : 's' ?> couldn’t be reached right now</summary>
    <p class="small"><?= e(implode(', ', $result['errors'])) ?>. This is usually temporary. If it keeps happening, check the feed address in <a href="/admin/edit?section=news">News sources</a>, or ask your host whether outgoing connections are allowed.</p>
  </details>
<?php endif; ?>

<div class="cols cols--news">
  <section aria-labelledby="headlines-h">
    <h2 id="headlines-h" class="sr-only">Headlines</h2>
    <?php if (!$items): ?>
      <div class="panel empty"><?= icon('wifi', 'icon') ?><p>No headlines to show yet. Try Refresh in a moment.</p></div>
    <?php else: ?>
      <ol class="news-list">
        <?php foreach ($items as $it): ?>
          <li class="news-item panel">
            <p class="news-item__meta"><span class="tag tag--<?= e($it['topic']) ?>"><?= e($topics[$it['topic']] ?? $it['topic']) ?></span><span><?= e($it['source']) ?></span><?php if ($it['ts']): ?><time datetime="<?= e(date('c', $it['ts'])) ?>"><?= e(date('M j, g:i a', $it['ts'])) ?></time><?php endif; ?></p>
            <h3><a href="<?= e($it['link']) ?>" target="_blank" rel="noopener noreferrer"><?= e($it['title']) ?></a></h3>
            <?php if ($it['summary'] !== ''): ?><p class="muted small"><?= e($it['summary']) ?></p><?php endif; ?>
            <div class="news-item__actions">
              <a class="btn btn--light btn--sm" href="<?= e($it['link']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('arrow-up-right', 'icon icon-xs') ?> Read article</a>
              <a class="btn btn--light btn--sm" href="/admin/blog-edit?new=1&amp;topic=<?= e(rawurlencode($it['title'])) ?>&amp;source=<?= e(rawurlencode($it['link'])) ?>&amp;cat=<?= e($blogCat($it['topic'])) ?>"><?= icon('file-text', 'icon icon-xs') ?> Write a post about this</a>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </section>

  <aside class="panel quick-links" aria-labelledby="links-h">
    <h2 id="links-h">Go-to resources</h2>
    <ul>
      <?php foreach ((array) ($sources['links'] ?? []) as $l): if (!preg_match('#^https?://#i', (string) ($l['url'] ?? ''))) continue; ?>
        <li><a href="<?= e($l['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($l['label']) ?> <?= icon('arrow-up-right', 'icon icon-xs') ?></a><?php if (!empty($l['note'])): ?><span class="muted small"><?= e($l['note']) ?></span><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
    <p class="muted small">Tip: when you write about a news story, explain it in your own words, add practical advice for your audience and link to the original.</p>
  </aside>
</div>
<?php admin_footer();
