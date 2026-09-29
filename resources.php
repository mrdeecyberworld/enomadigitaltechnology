<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$guides = content('resources');
$crumbs = [['Home', '/'], ['Resources', '/resources']];

$page = [
    'title'       => 'Cybersecurity & Technology Resources | Enoma Digital Technologies',
    'description' => 'Free, practical guides from Enoma Digital Technologies: securing your accounts, recognizing phishing, reliable backups and a small business website checklist.',
    'path'        => '/resources',
    'schema'      => [
        schema_breadcrumbs($crumbs),
        [
            '@type' => 'CollectionPage',
            'name' => 'Resources',
            'url' => abs_url('/resources'),
            'hasPart' => array_map(static fn (array $g) => ['@type' => 'Article', 'headline' => $g['title'], 'url' => abs_url('/resources#' . $g['slug']), 'author' => ['@id' => abs_url('/#organization')]], $guides),
        ],
    ],
];
require INC . '/layout/header.php';

echo page_hero('Practical Technology & Security Resources', [
    'eyebrow' => 'Resources',
    'text'    => 'Short, practical guides to help you stay secure and make better technology decisions. General educational information; for advice about your specific situation, talk with us.',
    'crumbs'  => $crumbs,
]);
?>

<section class="section" aria-labelledby="guides-heading">
  <div class="container">
    <h2 class="sr-only" id="guides-heading">Guides</h2>
    <nav class="toc reveal" aria-label="Guides on this page">
      <?php foreach ($guides as $g): ?>
        <a href="#<?= e($g['slug']) ?>" class="toc__item"><?= icon($g['icon'], 'icon icon-sm') ?><span><?= e($g['title']) ?></span></a>
      <?php endforeach; ?>
    </nav>

    <div class="guide-list">
      <?php foreach ($guides as $g): ?>
        <article class="guide reveal" id="<?= e($g['slug']) ?>" aria-labelledby="<?= e($g['slug']) ?>-title">
          <div class="guide__head">
            <span class="guide__icon"><?= icon($g['icon']) ?></span>
            <div>
              <p class="guide__tag"><?= e($g['tag']) ?></p>
              <h3 class="guide__title" id="<?= e($g['slug']) ?>-title"><?= e($g['title']) ?></h3>
              <p class="guide__summary"><?= e($g['summary']) ?></p>
            </div>
          </div>
          <ol class="guide__steps">
            <?php foreach ($g['steps'] as $step): ?><li><?= e($step) ?></li><?php endforeach; ?>
          </ol>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--muted" aria-labelledby="res-help-heading">
  <div class="container container--narrow center">
    <?= section_header('Need a hand?', 'Want help putting this into practice?', 'We can set up MFA, review your accounts, configure backups or train your team.', ['id' => 'res-help-heading']) ?>
    <div class="btn-row btn-row--center">
      <?= button('Explore Cybersecurity', '/cybersecurity', 'primary', 'shield-check') ?>
      <?= button('Explore Training', '/training', 'secondary', 'graduation-cap') ?>
    </div>
  </div>
</section>

<?= cta_banner() ?>

<?php require INC . '/layout/footer.php'; ?>
