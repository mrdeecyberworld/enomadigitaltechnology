<?php
/**
 * Shared template for Contact, Get a Quote and Book a Consultation.
 * Set $formPage before requiring this file: ['slug', 'type', 'key', 'icons'].
 * Text comes from content('pages')[key] and is editable in the admin panel.
 */

declare(strict_types=1);
require_once dirname(__DIR__) . '/bootstrap.php';

/** @var array $formPage */
$pageContent = content('pages')[$formPage['key']] ?? [];
$fp = $formPage + [
    'title'            => $pageContent['heading'] ?? ucfirst($formPage['slug']),
    'meta_title'       => $pageContent['meta_title'] ?? site('name'),
    'meta_description' => $pageContent['meta_description'] ?? site('description'),
    'eyebrow'          => $pageContent['eyebrow'] ?? '',
    'intro'            => $pageContent['intro'] ?? '',
    'side_heading'     => $pageContent['side_heading'] ?? '',
    'form_heading'     => $pageContent['form_heading'] ?? '',
];
$fp['side_points'] = [];
foreach (array_values($pageContent['side_points'] ?? []) as $i => $text) {
    $fp['side_points'][] = [$formPage['icons'][$i] ?? 'check', $text];
}
$path = '/' . $fp['slug'];
$formId = $fp['type'] . '-form';
$preselect = isset($_GET['service']) && array_key_exists((string) $_GET['service'], service_options()) ? (string) $_GET['service'] : null;
$formState = handle_form($formId, $fp['type'], $preselect);
$crumbs = [['Home', '/'], [$fp['title'], $path]];

$page = [
    'title'       => $fp['meta_title'],
    'description' => $fp['meta_description'],
    'path'        => $path,
    'schema'      => [schema_breadcrumbs($crumbs), ['@type' => 'ContactPage', 'url' => abs_url($path), 'name' => $fp['title'], 'about' => ['@id' => abs_url('/#organization')]]],
];
require INC . '/layout/header.php';

echo page_hero($fp['title'], ['eyebrow' => $fp['eyebrow'], 'text' => $fp['intro'], 'crumbs' => $crumbs]);
?>

<section class="section section--overlap">
  <div class="container form-layout form-layout--page">
    <aside class="form-layout__intro" aria-labelledby="side-heading">
      <h2 class="side-heading" id="side-heading"><?= e($fp['side_heading']) ?></h2>
      <ul class="contact-points contact-points--lg">
        <?php foreach ($fp['side_points'] as [$ic, $text]): ?>
          <li><span class="contact-points__icon"><?= icon($ic, 'icon icon-sm') ?></span><span><?= e($text) ?></span></li>
        <?php endforeach; ?>
      </ul>

      <?php if ($fp['type'] === 'consultation' && cfg('booking_url')): ?>
        <div class="booking-card">
          <p><strong>Prefer to pick a time now?</strong> Use our online scheduler.</p>
          <?= button('Open Scheduler', (string) cfg('booking_url'), 'primary', 'calendar-check', ['target' => '_blank', 'rel' => 'noopener']) ?>
        </div>
      <?php elseif ($fp['type'] === 'consultation'): ?>
        <?= setup_notice('Add an online scheduling link (booking_url) in your private config to show a "pick a time" button here.') ?>
      <?php endif; ?>

      <div class="side-links">
        <p class="side-links__label">Other ways to start</p>
        <?php foreach ([['/book-a-consultation', 'Book a Consultation'], ['/get-a-quote', 'Get a Quote'], ['/contact', 'Contact Us'], ['/#assistant', 'Ask the AI Assistant']] as [$href, $label]): ?>
          <?php if ($href !== $path): ?><a href="<?= e($href) ?>"><?= e($label) ?><?= icon('arrow-right', 'icon icon-xs') ?></a><?php endif; ?>
        <?php endforeach; ?>
      </div>
    </aside>

    <div class="form-card form-card--raised">
      <h2 class="form-card__title"><?= e($fp['form_heading']) ?></h2>
      <?= contact_form($formId, $fp['type'], $formState) ?>
    </div>
  </div>
</section>

<?php require INC . '/layout/footer.php'; ?>
