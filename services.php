<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$crumbs = [['Home', '/'], ['Services', '/services']];
$list = [];
foreach (services() as $slug => $svc) {
    $list[] = ['@type' => 'ListItem', 'position' => count($list) + 1, 'url' => abs_url(service_path($slug)), 'name' => $svc['name']];
}

$page = [
    'title'       => 'Technology Services: Web, Cybersecurity, IT Support & Training | Enoma Digital Technologies',
    'description' => 'Explore Enoma Digital Technologies services: website development, cybersecurity services, IT support, cybersecurity training, cloud services and small business technology consulting.',
    'path'        => '/services',
    'schema'      => [schema_breadcrumbs($crumbs), ['@type' => 'ItemList', 'name' => 'Services', 'itemListElement' => $list]],
];
require INC . '/layout/header.php';

echo page_hero('Technology Services Built Around You', [
    'eyebrow' => 'Services',
    'text'    => 'From building your digital presence to protecting it, Enoma Digital Technologies provides practical technology services for small businesses, startups, organizations and individuals.',
    'crumbs'  => $crumbs,
    'image'   => 'services',
    'actions' => true,
]);
?>

<section class="section" aria-labelledby="all-services-heading">
  <div class="container">
    <?= section_header('What we do', 'Six ways we help', 'Choose a service to learn more, or use the service finder below if you are not sure where to start.', ['id' => 'all-services-heading']) ?>
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
