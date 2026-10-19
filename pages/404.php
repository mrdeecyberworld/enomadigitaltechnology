<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

http_response_code(404);
$page = [
    'title'       => 'Page Not Found | Enoma Digital Technologies',
    'description' => 'The page you are looking for could not be found.',
    'path'        => '/404',
    'noindex'     => true,
];
require INC . '/layout/header.php';
?>
<section class="page-hero page-hero--center">
  <div class="page-hero__bg" aria-hidden="true"></div>
  <div class="container page-hero__inner">
    <div class="page-hero__content">
      <p class="eyebrow eyebrow--light">Error 404</p>
      <h1 class="page-hero__title">This page doesn’t exist.</h1>
      <p class="page-hero__text">The link may be old, or the page may have moved. Try one of these instead.</p>
      <div class="btn-row btn-row--center">
        <?= button('Go to Homepage', '/', 'primary', 'arrow-right') ?>
        <?= button('View Services', '/services', 'outline-light') ?>
        <?= button('Contact Me', '/contact', 'outline-light') ?>
      </div>
    </div>
  </div>
</section>
<?php require INC . '/layout/footer.php'; ?>
