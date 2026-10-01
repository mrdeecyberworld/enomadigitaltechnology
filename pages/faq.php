<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$general = content('faqs');
$crumbs = [['Home', '/'], ['FAQ', '/faq']];

// General FAQs plus each service's FAQs, grouped.
$groups = [['title' => 'General', 'id' => 'general', 'faqs' => $general]];
foreach (services() as $slug => $svc) {
    if (!empty($svc['faqs'])) {
        $groups[] = ['title' => $svc['name'], 'id' => $slug, 'faqs' => $svc['faqs']];
    }
}
$all = array_merge(...array_column($groups, 'faqs'));

$page = [
    'title'       => page_text('faq', 'meta_title', 'FAQ | ' . site('name')),
    'description' => page_text('faq', 'meta_description', site('description')),
    'path'        => '/faq',
    'schema'      => [schema_breadcrumbs($crumbs), schema_faq($all)],
];
require INC . '/layout/header.php';

echo page_hero(page_text('faq', 'heading', 'Frequently Asked Questions'), [
    'eyebrow' => page_text('faq', 'eyebrow'),
    'text'    => page_text('faq', 'intro'),
    'crumbs'  => $crumbs,
]);
?>

<section class="section">
  <div class="container container--narrow">
    <nav class="chip-nav" aria-label="FAQ topics">
      <?php foreach ($groups as $g): ?><a class="chip" href="#faq-<?= e($g['id']) ?>"><?= e($g['title']) ?></a><?php endforeach; ?>
    </nav>
    <?php foreach ($groups as $g): ?>
      <section class="faq-group" id="faq-<?= e($g['id']) ?>" aria-labelledby="faq-<?= e($g['id']) ?>-h">
        <h2 class="faq-group__title" id="faq-<?= e($g['id']) ?>-h"><?= e($g['title']) ?></h2>
        <?= faq_list($g['faqs']) ?>
      </section>
    <?php endforeach; ?>
  </div>
</section>

<?= cta_banner('Still have questions?', 'Ask our AI assistant on the homepage, or talk with us directly.') ?>

<?php require INC . '/layout/footer.php'; ?>
