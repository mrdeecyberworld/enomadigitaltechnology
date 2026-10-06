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
$orderFormId = 'order-form';
$orderState = $buyUrl === '' ? handle_order_form($orderFormId, $product) : null;
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
            <?= viewer_is_admin() ? setup_notice('No checkout link yet, so visitors place an order and you send payment details by email (Admin → Orders). Add a checkout link in Admin → Courses & Tools when your payments are ready.') : '' ?>
            <?= button($priceValue === 0.0 ? 'Get it free' : 'Order now', '#' . $orderFormId, 'primary btn-lg buy-box__button', 'shopping-bag') ?>
            <p class="buy-box__secure"><?= icon('lock', 'icon icon-xs') ?> No payment needed yet: we email you how to pay, then send your access.</p>
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
    <?php if ($orderState !== null): $ov = $orderState['values']; $oe = $orderState['errors'];
      $oerr = static fn (string $k): string => isset($oe[$k]) ? '<p class="field__error" id="order-' . $k . '-error">' . icon('circle-alert', 'icon icon-xs') . e($oe[$k]) . '</p>' : '';
      $oinv = static fn (string $k): string => isset($oe[$k]) ? ' aria-invalid="true" aria-describedby="order-' . $k . '-error"' : ''; ?>
    <section class="order-card" aria-labelledby="order-heading">
      <div class="order-card__head">
        <span class="order-card__icon"><?= icon('shopping-bag', 'icon') ?></span>
        <div>
          <h2 id="order-heading"><?= $priceValue === 0.0 ? 'Get' : 'Order' ?> <?= e($product['name']) ?></h2>
          <p><?= $price !== '' ? '<strong>' . e($price) . '</strong> · ' : '' ?>Send your details and we will email you how to pay. Once payment is confirmed you receive your <?= in_array($product['type'], ['Course', 'Workshop'], true) ? 'course access' : 'download link' ?>.</p>
        </div>
      </div>
      <form class="form" id="<?= e($orderFormId) ?>" method="post" action="<?= e(current_path()) ?>#<?= e($orderFormId) ?>" novalidate data-validate>
        <?php if ($orderState['status']): ?>
          <div class="form-status form-status--<?= e($orderState['status']) ?>" role="<?= $orderState['status'] === 'error' ? 'alert' : 'status' ?>" tabindex="-1" data-focus-on-load>
            <?= icon($orderState['status'] === 'success' ? 'circle-check-big' : 'circle-alert', 'icon') ?><p><?= e($orderState['message']) ?></p>
          </div>
        <?php endif; ?>
        <input type="hidden" name="form_id" value="<?= e($orderFormId) ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="started" value="<?= time() ?>">
        <div class="hp-field" aria-hidden="true"><label for="website-order">Leave this field empty</label><input type="text" id="website-order" name="website" tabindex="-1" autocomplete="off"></div>
        <div class="form__grid">
          <div class="field">
            <label for="order-name">Name <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="order-name" name="name" autocomplete="name" required maxlength="100" value="<?= e((string) ($ov['name'] ?? '')) ?>"<?= $oinv('name') ?>>
            <?= $oerr('name') ?>
          </div>
          <div class="field">
            <label for="order-email">Email <span class="req" aria-hidden="true">*</span></label>
            <input type="email" id="order-email" name="email" autocomplete="email" required maxlength="254" value="<?= e((string) ($ov['email'] ?? '')) ?>"<?= $oinv('email') ?>>
            <?= $oerr('email') ?>
          </div>
          <div class="field">
            <label for="order-country">Country <span class="optional">(optional)</span></label>
            <input type="text" id="order-country" name="country" autocomplete="country-name" maxlength="80" value="<?= e((string) ($ov['country'] ?? '')) ?>">
          </div>
          <div class="field field--full">
            <label for="order-note">Anything we should know? <span class="optional">(optional)</span></label>
            <textarea id="order-note" name="note" rows="3" maxlength="2000"><?= e((string) ($ov['note'] ?? '')) ?></textarea>
          </div>
        </div>
        <div class="form__footer">
          <button type="submit" class="btn btn-primary btn-lg"><span><?= $priceValue === 0.0 ? 'Request it' : 'Place order' ?></span><?= icon('send', 'icon btn-icon') ?></button>
          <p class="form__privacy">No payment is taken on this page. We use your details only for this order. See our <a href="<?= e(page_url('privacy')) ?>">Privacy Policy</a>.</p>
        </div>
      </form>
    </section>
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
