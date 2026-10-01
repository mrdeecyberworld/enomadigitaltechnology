<?php
/**
 * Legal page template. Set $legalKey ('privacy' | 'terms') and $legalPath first.
 * Text is edited in Admin → Legal pages.
 */

declare(strict_types=1);
require_once dirname(__DIR__) . '/bootstrap.php';

/** @var string $legalKey */
/** @var string $legalPath */
$legal = content('legal')[$legalKey] ?? [];
$heading = $legalKey === 'privacy' ? 'Privacy Policy' : 'Terms of Service';
$crumbs = [['Home', '/'], [$heading, $legalPath]];
$page = [
    'title'       => $legal['meta_title'] ?? $heading . ' | ' . site('name'),
    'description' => $legal['meta_description'] ?? site('description'),
    'path'        => $legalPath,
    'schema'      => [schema_breadcrumbs($crumbs)],
];
require INC . '/layout/header.php';
echo page_hero($heading, ['eyebrow' => 'Legal', 'crumbs' => $crumbs]);

$body = (string) ($legal['body'] ?? '');
$state = trim((string) ($legal['governing_state'] ?? ''));
$html = simple_format($body);
if ($state === '' && !viewer_is_admin()) {
    $html = str_replace(e('{state}') . ', United States', 'the United States', $html);
}
$html = str_replace(e('{state}'), $state !== '' ? e($state) : (viewer_is_admin() ? placeholder('State') : 'the United States'), $html);
?>
<section class="section">
  <div class="container container--narrow prose">
    <?php if (!empty($legal['updated']) || viewer_is_admin()): ?>
      <p class="prose__updated">Last updated: <?= !empty($legal['updated']) ? e($legal['updated']) : placeholder('Effective date') ?></p>
    <?php endif; ?>
    <?php if (empty($legal['updated'])): ?>
      <?= setup_notice('Review this page with a qualified professional, then add the effective date in Admin → Legal pages.') ?>
    <?php endif; ?>
    <?= $html ?>
  </div>
</section>
<?php require INC . '/layout/footer.php'; ?>
