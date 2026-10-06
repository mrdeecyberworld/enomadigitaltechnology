<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$shop = content('shop');
$products = shop_products(viewer_is_admin());
$crumbs = [['Home', '/'], [$shop['eyebrow'] ?: 'Courses & Tools', page_url('shop')]];

$page = [
    'title'       => ($shop['meta_title'] ?? '') ?: ($shop['eyebrow'] ?: 'Courses & Tools') . ' | ' . site('name'),
    'description' => ($shop['meta_description'] ?? '') ?: (string) $shop['intro'],
    'path'        => page_url('shop'),
    'schema'      => [schema_breadcrumbs($crumbs)],
];
require INC . '/layout/header.php';

echo page_hero((string) $shop['heading'], [
    'eyebrow' => (string) $shop['eyebrow'],
    'text'    => (string) $shop['intro'],
    'crumbs'  => $crumbs,
]);
?>

<section class="section" aria-labelledby="shop-heading">
  <div class="container">
    <h2 class="sr-only" id="shop-heading">All courses and tools</h2>
    <?php if (viewer_is_admin() && !shop_products(true)): ?>
      <?= setup_notice('Only you can see this note. Add your first course or software in Admin → Sell courses & software → Products & prices: set a price, then upload the file or paste a download link. The menu link appears for visitors once one is published.') ?>
      <p class="section-foot"><?= button('Add a course or software', '/admin/edit?section=shop', 'primary', 'plus') ?></p>
    <?php endif; ?>
    <?php if ($products): ?>
      <div class="product-grid">
        <?php foreach ($products as $p): ?><?= product_card($p, 'h3') ?><?php endforeach; ?>
      </div>
      <p class="shop-trust"><?= icon('lock', 'icon icon-xs') ?> <?= e((string) $shop['checkout_note']) ?></p>
    <?php else: ?>
      <div class="shop-empty">
        <span class="shop-empty__icon" aria-hidden="true"><?= icon('shopping-bag', 'icon') ?></span>
        <p><?= e((string) $shop['empty']) ?></p>
        <?= button('Contact Us', page_url('contact'), 'primary', 'arrow-right') ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?= cta_banner('Not sure which one is right for you?', 'Tell us what you want to learn or fix, and we will point you to the right course, tool or service.') ?>
<?php require INC . '/layout/footer.php'; ?>
