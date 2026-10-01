<?php
/** A single resource guide, e.g. /resources/spot-phishing */
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$guide = resource_find((string) ($_GET['slug'] ?? ''));
if ($guide === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$steps = array_values((array) ($guide['steps'] ?? []));
$crumbs = [['Home', '/'], [page_text('resources', 'eyebrow', 'Resources'), page_url('resources')], [$guide['title'], resource_url($guide)]];
$others = array_values(array_filter(content('resources'), static fn ($g) => ($g['slug'] ?? '') !== $guide['slug']));

$page = [
    'title'       => ($guide['meta_title'] ?? '') ?: $guide['title'] . ' | ' . site('name'),
    'description' => ($guide['meta_description'] ?? '') ?: $guide['summary'],
    'path'        => resource_url($guide),
    'og_type'     => 'article',
    'schema'      => [schema_breadcrumbs($crumbs), [
        '@type'       => 'HowTo',
        'name'        => $guide['title'],
        'description' => $guide['summary'],
        'step'        => array_map(static fn ($s, $i) => ['@type' => 'HowToStep', 'position' => $i + 1, 'text' => $s], $steps, array_keys($steps)),
    ]],
];
require INC . '/layout/header.php';
?>
<article aria-labelledby="guide-title">
  <header class="post-hero post-hero--guide">
    <div class="page-hero__bg" aria-hidden="true"></div>
    <div class="container container--narrow">
      <?= breadcrumbs($crumbs) ?>
      <span class="post-hero__cat"><?= icon($guide['icon'] ?? 'book-open', 'icon icon-xs') ?><?= e($guide['tag'] ?? 'Guide') ?></span>
      <h1 class="post-hero__title" id="guide-title"><?= e($guide['title']) ?></h1>
      <p class="post-hero__lead"><?= e($guide['summary']) ?></p>
    </div>
  </header>

  <div class="container container--narrow guide-page">
    <?php if (!empty($guide['body'])): ?>
      <div class="prose guide-page__intro"><?= simple_format((string) $guide['body']) ?></div>
    <?php endif; ?>
    <ol class="guide-steps">
      <?php foreach ($steps as $i => $step): ?>
        <li class="guide-step reveal">
          <span class="guide-step__num" aria-hidden="true"><?= $i + 1 ?></span>
          <p><?= e($step) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>

    <div class="post-cta">
      <div>
        <h2>Want help with this?</h2>
        <p>We can set it up with you, or train your team to do it confidently.</p>
      </div>
      <?= button('Book a Consultation', page_url('consultation'), 'primary', 'calendar-check') ?>
    </div>
  </div>
</article>

<?php if ($others): ?>
<section class="section section--muted" aria-labelledby="more-guides">
  <div class="container">
    <?= section_header('More guides', 'Keep learning', null, ['id' => 'more-guides']) ?>
    <div class="guide-cards">
      <?php foreach (array_slice($others, 0, 3) as $g): ?>
        <article class="guide-card reveal">
          <span class="guide__icon"><?= icon($g['icon']) ?></span>
          <p class="guide__tag"><?= e($g['tag']) ?></p>
          <h3 class="guide-card__title"><a href="<?= e(resource_url($g)) ?>"><?= e($g['title']) ?></a></h3>
          <p class="guide__summary"><?= e($g['summary']) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php require INC . '/layout/footer.php'; ?>
