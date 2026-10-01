<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$home = content('home');
$f = cfg('founder', []);
$crumbs = [['Home', '/'], ['About', '/about']];

$page = [
    'title'       => page_text('about', 'meta_title', 'About | ' . site('name')),
    'description' => page_text('about', 'meta_description', site('description')),
    'path'        => '/about',
    'schema'      => [schema_breadcrumbs($crumbs), ['@type' => 'AboutPage', 'url' => abs_url('/about'), 'name' => 'About ' . site('name'), 'about' => ['@id' => abs_url('/#organization')]]],
];
require INC . '/layout/header.php';

echo page_hero(page_text('about', 'heading', 'About'), [
    'eyebrow' => page_text('about', 'eyebrow'),
    'text'    => page_text('about', 'intro'),
    'crumbs'  => $crumbs,
]);
?>

<section class="section" aria-labelledby="mission-heading">
  <div class="container split">
    <div class="split__content">
      <?= section_header('Our purpose', 'Making technology accessible, practical and secure', null, ['align' => 'left', 'id' => 'mission-heading']) ?>
      <?php foreach ($home['about']['paragraphs'] as $p): ?>
        <p class="prose-p reveal"><?= e($p) ?></p>
      <?php endforeach; ?>
      <?php if (page_text('about', 'body') !== ''): ?><div class="prose-p reveal"><?= simple_format(page_text('about', 'body')) ?></div><?php endif; ?>
    </div>
    <div class="split__media reveal">
      <div class="media-frame media-frame--tall">
        <?= photo('team', '(min-width: 1024px) 45vw, 100vw') ?>
      </div>
    </div>
  </div>
</section>

<section class="section section--dark" aria-labelledby="values-heading">
  <div class="section__bg" aria-hidden="true"></div>
  <div class="container">
    <?= section_header($home['why']['eyebrow'], 'What guides our work', $home['why']['text'], ['id' => 'values-heading']) ?>
    <div class="why__grid why__grid--4">
      <?php foreach ($home['why']['points'] as $pt): ?>
        <div class="feature-card reveal">
          <span class="feature-card__icon"><?= icon($pt['icon']) ?></span>
          <h3 class="feature-card__title"><?= e($pt['title']) ?></h3>
          <p class="feature-card__text"><?= e($pt['text']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if (founder_ready() || viewer_is_admin()): ?>
<section class="section" aria-labelledby="founder-heading">
  <div class="container split split--center">
    <div class="split__media reveal">
      <?= founder_card() ?>
    </div>
    <div class="split__content">
      <?= section_header('Leadership', 'Meet the founder', null, ['align' => 'left', 'id' => 'founder-heading']) ?>
      <?php if (!empty($f['bio'])): ?>
        <p class="prose-p reveal"><?= e($f['bio']) ?></p>
      <?php else: ?>
        <p class="prose-p reveal"><?= placeholder('Founder biography: add a short, factual introduction, including background, areas of focus and why Enoma Digital Technologies was started.') ?></p>
        <?= setup_notice('Add the founder name, bio and photo under "founder" in your private config file.') ?>
      <?php endif; ?>
      <div class="btn-row">
        <?= button('Book a Consultation', '/book-a-consultation', 'primary', 'calendar-check') ?>
        <?= button('Contact Us', '/contact', 'ghost', 'arrow-right') ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section--muted" aria-labelledby="how-heading">
  <div class="container">
    <?= section_header('How we work', 'Remote-first, clear and collaborative', null, ['id' => 'how-heading']) ?>
    <div class="detail-grid">
      <div class="detail-card reveal"><span class="detail-card__icon"><?= icon('globe', 'icon icon-sm') ?></span><h3 class="detail-card__title">Remote services across the U.S.</h3><p class="detail-card__text">We deliver our services remotely using secure tools, which keeps things convenient and flexible for clients wherever they are.</p></div>
      <div class="detail-card reveal"><span class="detail-card__icon"><?= icon('message-square-text', 'icon icon-sm') ?></span><h3 class="detail-card__title">Plain-language communication</h3><p class="detail-card__text">You will always know what we are doing and why, without jargon.</p></div>
      <div class="detail-card reveal"><span class="detail-card__icon"><?= icon('lock', 'icon icon-sm') ?></span><h3 class="detail-card__title">Respect for your data</h3><p class="detail-card__text">Remote access happens only with your permission, and we only request the access a task requires.</p></div>
    </div>
  </div>
</section>

<?= testimonials_section() ?>

<?= cta_banner() ?>

<?php require INC . '/layout/footer.php'; ?>
