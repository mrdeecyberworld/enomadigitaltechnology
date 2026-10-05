<?php
/** A single course or tool, e.g. /courses-and-tools/website-security-checklist */
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$shop = content('shop');
$product = shop_find((string) ($_GET['slug'] ?? ''), viewer_is_admin());
if ($product === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$price = shop_price($product);
$priceValue = shop_price_value($product);
$buyUrl = trim((string) $product['buy_url']);
$buyLabel = trim((string) $product['buy_label']) ?: ($priceValue === 0.0 ? 'Get it free' : 'Buy now');
$includes = array_values(array_filter(array_map('trim', (array) $product['includes'])));
$crumbs = [['Home', '/'], [$shop['eyebrow'] ?: 'Courses & Tools', page_url('shop')], [$product['name'], shop_url($product)]];
$others = array_values(array_filter(shop_products(), static fn ($p) => $p['slug'] !== $product['slug']));

$offer = ['@type' => 'Offer', 'url' => abs_url(shop_url($product)), 'availability' => 'https://schema.org/InStock', 'priceCurrency' => 'USD'];
if ($priceValue !== null) {
    $offer['price'] = number_format($priceValue, 2, '.', '');
}
$schemaProduct = array_filter([
    '@type'       => $product['type'] === 'Course' ? 'Course' : 'Product',
    'name'        => $product['name'],
    'description' => $product['summary'] ?: $product['name'],
    'image'       => !empty($product['image']) ? abs_url('/' . ltrim((string) $product['image'], '/')) : null,
    'brand'       => $product['type'] === 'Course' ? null : ['@type' => 'Brand', 'name' => site('name')],
    'provider'    => $product['type'] === 'Course' ? ['@type' => 'Organization', 'name' => site('name'), 'url' => abs_url('/')] : null,
    'offers'      => $product['type'] === 'Course' ? null : $offer,
    'hasCourseInstance' => $product['type'] === 'Course' ? ['@type' => 'CourseInstance', 'courseMode' => 'Online', 'offers' => $offer] : null,
]);

$page = [
    'title'       => ($product['meta_title'] ?? '') ?: $product['name'] . ' | ' . site('name'),
    'description' => ($product['meta_description'] ?? '') ?: ($product['summary'] ?: $product['name']),
    'path'        => shop_url($product),
    'schema'      => [schema_breadcrumbs($crumbs), $schemaProduct],
];
require INC . '/layout/header.php';
?>
<article aria-labelledby="product-title">
  <header class="post-hero product-hero">
    <div class="page-hero__bg" aria-hidden="true"></div>
    <div class="container product-hero__inner">
      <div class="product-hero__text">
        <?= breadcrumbs($crumbs) ?>
        <span class="post-hero__cat"><?= icon($product['type'] === 'Course' || $product['type'] === 'Workshop' ? 'graduation-cap' : 'package', 'icon icon-xs') ?><?= e($product['type']) ?></span>
        <h1 class="post-hero__title" id="product-title"><?= e($product['name']) ?></h1>
        <?php if ($product['summary'] !== ''): ?><p class="post-hero__lead"><?= e($product['summary']) ?></p><?php endif; ?>
        <?php if (($product['status'] ?? 'published') !== 'published'): ?><?= setup_notice('This product is a draft: only you can see it. Set it to Published in Admin → Courses & Tools.') ?><?php endif; ?>
      </div>
      <aside class="buy-box" aria-label="Purchase">
        <?php if (!empty($product['image']) && ($img = photo(ltrim((string) $product['image'], '/'), '(min-width: 1024px) 420px, 100vw', ['alt' => '', 'eager' => true]))): ?>
          <div class="buy-box__media"><?= $img ?></div>
        <?php endif; ?>
        <div class="buy-box__body">
          <?php if ($price !== ''): ?><p class="buy-box__price"><?= e($price) ?></p><?php endif; ?>
          <?php if ($product['price_note'] !== ''): ?><p class="buy-box__note"><?= e($product['price_note']) ?></p><?php endif; ?>
          <?php if ($buyUrl !== ''): ?>
            <?= button($buyLabel, $buyUrl, 'primary btn-lg buy-box__button', 'credit-card', ['rel' => 'noopener']) ?>
            <p class="buy-box__secure"><?= icon('lock', 'icon icon-xs') ?> <?= e((string) $shop['checkout_note']) ?></p>
          <?php else: ?>
            <?= viewer_is_admin() ? setup_notice('Add a checkout link (Stripe Payment Link, PayPal, Gumroad…) for this product in Admin → Courses & Tools. Until then, visitors are asked to contact you.') : '' ?>
            <?= button($priceValue === 0.0 ? 'Contact us to get it' : 'Contact us to buy', page_url('contact'), 'primary btn-lg buy-box__button', 'arrow-right') ?>
          <?php endif; ?>
          <?php if ($product['format'] !== ''): ?><p class="buy-box__format"><?= icon('check', 'icon icon-xs') ?> <?= e($product['format']) ?></p><?php endif; ?>
        </div>
      </aside>
    </div>
  </header>

  <div class="container container--narrow product-page">
    <?php if ($includes): ?>
      <section class="product-includes" aria-labelledby="includes-heading">
        <h2 id="includes-heading">What’s included</h2>
        <ul class="check-list">
          <?php foreach ($includes as $item): ?><li><?= icon('check', 'icon icon-sm') ?><span><?= e($item) ?></span></li><?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>
    <?php if (trim((string) $product['description']) !== ''): ?>
      <div class="prose product-page__description"><?= simple_format((string) $product['description']) ?></div>
    <?php endif; ?>
    <div class="post-cta">
      <div>
        <h2>Questions before you buy?</h2>
        <p>Ask us anything about <?= e($product['name']) ?>. We reply by email.</p>
      </div>
      <?= button('Ask a Question', page_url('contact'), 'primary', 'arrow-right') ?>
    </div>
  </div>
</article>

<?php if ($others): ?>
<section class="section section--muted" aria-labelledby="more-products">
  <div class="container">
    <?= section_header($shop['eyebrow'] ?: 'Courses & Tools', 'You might also like', null, ['id' => 'more-products']) ?>
    <div class="product-grid">
      <?php foreach (array_slice($others, 0, 3) as $p): ?><?= product_card($p, 'h3') ?><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require INC . '/layout/footer.php'; ?>
