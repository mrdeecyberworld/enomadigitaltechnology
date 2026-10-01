<?php
/**
 * Shared template for individual service pages.
 * Set $serviceSlug before requiring this file.
 */

declare(strict_types=1);
require_once dirname(__DIR__) . '/bootstrap.php';

/** @var string $serviceSlug */
$svc = service($serviceSlug);
if ($svc === null) {
    http_response_code(404);
    require SITE_ROOT . '/pages/404.php';
    exit;
}

$path = service_path($serviceSlug);
$formId = 'inquiry-form';
$formState = handle_form($formId, 'inquiry', $serviceSlug);
$crumbs = [['Home', '/'], ['Services', '/services'], [$svc['name'], $path]];

$page = [
    'title'       => $svc['meta_title'],
    'description' => $svc['meta_description'],
    'path'        => $path,
    'schema'      => array_filter([
        schema_service($serviceSlug, $svc),
        schema_breadcrumbs($crumbs),
        !empty($svc['faqs']) ? schema_faq($svc['faqs']) : null,
    ]),
];
require INC . '/layout/header.php';

echo page_hero($svc['headline'], [
    'eyebrow' => $svc['eyebrow'],
    'text'    => $svc['intro'],
    'crumbs'  => $crumbs,
    'image'   => $svc['image'],
    'actions' => true,
    'keyword_h1' => true,
]);
?>

<section class="section" aria-labelledby="included-heading">
  <div class="container">
    <?= section_header('What we help with', $svc['name'] . ' services', $svc['summary'], ['id' => 'included-heading']) ?>
    <div class="detail-grid">
      <?php foreach ($svc['details'] as $d): ?>
        <div class="detail-card reveal">
          <span class="detail-card__icon"><?= icon('check', 'icon icon-sm') ?></span>
          <h3 class="detail-card__title"><?= e($d['title']) ?></h3>
          <p class="detail-card__text"><?= e($d['text']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if (!empty($svc['note'])): ?>
      <p class="callout reveal"><?= icon('shield-alert', 'icon icon-sm') ?><span><?= e($svc['note']) ?></span></p>
    <?php endif; ?>
  </div>
</section>

<section class="section section--muted" aria-labelledby="audience-heading">
  <div class="container split split--center">
    <div class="split__content">
      <?= section_header('Who it’s for', 'Built for people and organizations like yours', 'Every engagement starts with understanding your goals, your budget and how you work today.', ['align' => 'left', 'id' => 'audience-heading']) ?>
      <?= check_list($svc['audience'], 'check-list--2col reveal') ?>
    </div>
    <div class="split__content">
      <ol class="mini-process reveal" aria-label="How we work">
        <?php foreach (content('home')['process']['steps'] as $i => $step): ?>
          <li><span class="mini-process__num" aria-hidden="true"><?= $i + 1 ?></span><span><strong><?= e($step['title']) ?></strong><?= e($step['text']) ?></span></li>
        <?php endforeach; ?>
      </ol>
    </div>
  </div>
</section>

<?php if (!empty($svc['faqs'])): ?>
<section class="section" aria-labelledby="svc-faq-heading">
  <div class="container container--narrow">
    <?= section_header('FAQ', $svc['name'] . ' questions', null, ['id' => 'svc-faq-heading']) ?>
    <?= faq_list($svc['faqs']) ?>
  </div>
</section>
<?php endif; ?>

<section class="section section--muted" aria-labelledby="inquiry-heading">
  <div class="container form-layout">
    <div class="form-layout__intro">
      <?= section_header('Get in touch', 'Ask about ' . $svc['name'], 'Tell us a little about what you need. We will review your request and follow up with next steps.', ['align' => 'left', 'id' => 'inquiry-heading']) ?>
      <ul class="contact-points">
        <li><?= icon('calendar-check', 'icon icon-sm') ?><span>Prefer to talk? <a href="/book-a-consultation">Book a consultation</a>.</span></li>
        <li><?= icon('clipboard-list', 'icon icon-sm') ?><span>Have a defined project? <a href="/get-a-quote">Request a quote</a>.</span></li>
        <li><?= icon('globe', 'icon icon-sm') ?><span><?= e(site('service_area')) ?></span></li>
      </ul>
    </div>
    <div class="form-card">
      <?= contact_form($formId, 'inquiry', $formState) ?>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="related-heading">
  <div class="container">
    <?= section_header('Related services', 'Explore more ways we can help', null, ['id' => 'related-heading']) ?>
    <?= service_grid(array_values(array_diff(array_keys(services()), [$serviceSlug])), ['compact' => true]) ?>
  </div>
</section>

<?= cta_banner() ?>

<?php require INC . '/layout/footer.php'; ?>
