<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$crumbs = [['Home', '/'], ['Photo credits', '/photo-credits']];

// Backup photos are shown when a main photo can't load; their licence asks for credit.
$backups = [];
foreach (content_default('fallback-photos') as $photo) {
    $backups[$photo['file']] = $photo;
}

$page = [
    'title'       => 'Photo Credits | ' . site('name'),
    'description' => 'Credits for the photographs used on the ' . site('name') . ' website.',
    'path'        => '/photo-credits',
    'schema'      => [schema_breadcrumbs($crumbs)],
];
require INC . '/layout/header.php';

echo page_hero('Photo Credits', [
    'eyebrow' => 'Thank you, photographers',
    'text'    => 'The photographs on this website and who took them.',
    'crumbs'  => $crumbs,
]);
?>

<section class="section">
  <div class="container container--narrow prose credits">
    <h2>Main photos</h2>
    <p>Most photos come from <a href="https://unsplash.com" rel="noopener" target="_blank">Unsplash<span class="sr-only"> (opens in a new tab)</span></a> and are used under the <a href="https://unsplash.com/license" rel="noopener" target="_blank">Unsplash License<span class="sr-only"> (opens in a new tab)</span></a>. Photos of people are illustrations, not our staff or clients.</p>

    <h2>Backup photos</h2>
    <p>When a main photo can't load, one of these is shown instead. They come from Google's <a href="https://storage.googleapis.com/openimages/web/index.html" rel="noopener" target="_blank">Open Images<span class="sr-only"> (opens in a new tab)</span></a> collection and are used under the Creative Commons Attribution 2.0 licence (CC BY 2.0). Some have been cropped to fit.</p>
    <ul class="credits__list">
      <?php foreach ($backups as $photo): ?>
        <li class="credits__item">
          <img src="<?= e(asset($photo['file'])) ?>" alt="" width="<?= (int) $photo['w'] ?>" height="<?= (int) $photo['h'] ?>" loading="lazy" decoding="async">
          <span>
            <a href="<?= e($photo['source']) ?>" rel="noopener" target="_blank"><?= e($photo['title'] ?: 'Untitled') ?><span class="sr-only"> (opens in a new tab)</span></a>
            by <?= e($photo['author']) ?>,
            <a href="<?= e($photo['license_url']) ?>" rel="noopener" target="_blank"><?= e($photo['license']) ?><span class="sr-only"> (opens in a new tab)</span></a>
          </span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php require INC . '/layout/footer.php'; ?>
