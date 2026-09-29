<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$crumbs = [['Home', '/'], ['Services', '/services']];
$list = [];
foreach (services() as $slug => $svc) {
    $list[] = ['@type' => 'ListItem', 'position' => count($list) + 1, 'url' => abs_url(service_path($slug)), 'name' => $svc['name']];
}

$page = [
    'title'       => page_text('services', 'meta_title', 'Services | ' . site('name')),
    'description' => page_text('services', 'meta_description', site('description')),
    'path'        => '/services',
    'schema'      => [schema_breadcrumbs($crumbs), ['@type' => 'ItemList', 'name' => 'Services', 'itemListElement' => $list]],
];
require INC . '/layout/header.php';

echo page_hero(page_text('services', 'heading', 'Services'), [
    'eyebrow' => page_text('services', 'eyebrow'),
    'text'    => page_text('services', 'intro'),
    'crumbs'  => $crumbs,
    'image'   => 'services',
    'actions' => true,
]);
?>

<section class="section" aria-labelledby="all-services-heading">
  <div class="container">
    <?= section_header('What we do', 'How we help', 'Choose a service to learn more, or use the service finder below if you are not sure where to start.', ['id' => 'all-services-heading']) ?>
    <?= service_grid() ?>
  </div>
</section>

<section class="section section--muted" aria-labelledby="finder-heading">
  <div class="container container--narrow">
    <?= section_header('Service finder', 'Find the right service', 'Answer two quick questions and we will suggest where to start.', ['id' => 'finder-heading']) ?>
    <?= service_finder() ?>
  </div>
</section>

<?= cta_banner() ?>

<?php require INC . '/layout/footer.php'; ?>
