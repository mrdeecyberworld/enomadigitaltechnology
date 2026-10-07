<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$shop = content('shop');
$allProducts = shop_products(viewer_is_admin());
$types = array_values(array_unique(array_map(static fn ($p) => (string) $p['type'], $allProducts)));
$activeType = in_array((string) ($_GET['type'] ?? ''), $types, true) ? (string) $_GET['type'] : '';
$products = $activeType !== '' ? array_values(array_filter($allProducts, static fn ($p) => $p['type'] === $activeType)) : $allProducts;
$plural = static fn (string $t): string => match ($t) { 'Software' => 'Software', 'Bundle' => 'Bundles', default => $t . 's' };
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

<div class="shop-perks" aria-label="How buying works">
  <div class="container shop-perks__inner">
    <?php foreach ([['shopping-bag', 'Order in a minute', 'Pick a course or tool and send your details'], ['mail', 'Access by email', 'Payment details and access come to your inbox'], ['lock', 'Private download links', 'Your files are only shared with you'], ['headset', 'Real help', 'Questions answered by a person']] as [$ic, $t, $d]): ?>
      <div class="shop-perk"><span class="shop-perk__icon"><?= icon($ic, 'icon icon-sm') ?></span><span><strong><?= e($t) ?></strong><small><?= e($d) ?></small></span></div>
    <?php endforeach; ?>
  </div>
</div>

<section class="section" aria-labelledby="shop-heading">
  <div class="container">
    <h2 class="sr-only" id="shop-heading">All courses and tools</h2>
    <?php if (viewer_is_admin() && !shop_products(true)): ?>
      <?= setup_notice('Only you can see this note. Add your first course or software in Admin → Sell courses & software → Products & prices: set a price, then upload the file or paste a download link. The menu link appears for visitors once one is published.') ?>
      <p class="section-foot"><?= button('Add a course or software', '/admin/edit?section=shop', 'primary', 'plus') ?></p>
    <?php endif; ?>
    <?php if (count($types) > 1): ?>
      <nav class="shop-tabs" aria-label="Filter by type">
        <a href="<?= e(page_url('shop')) ?>"<?= $activeType === '' ? ' aria-current="page"' : '' ?>>All <span><?= count($allProducts) ?></span></a>
        <?php foreach ($types as $t): $n = count(array_filter($allProducts, static fn ($p) => $p['type'] === $t)); ?>
          <a href="<?= e(page_url('shop') . '?type=' . rawurlencode($t)) ?>"<?= $activeType === $t ? ' aria-current="page"' : '' ?>><?= icon(shop_type_icon($t), 'icon icon-xs') ?><?= e($plural($t)) ?> <span><?= $n ?></span></a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>
    <?php if ($products): ?>
      <div class="product-grid">
        <?php foreach ($products as $p): ?><?= product_card($p, 'h3') ?><?php endforeach; ?>
      </div>
      <?php $hasCheckout = (bool) array_filter($products, static fn ($p) => trim((string) $p['buy_url']) !== ''); ?>
      <p class="shop-trust"><?= icon('lock', 'icon icon-xs') ?> <?= e($hasCheckout ? (string) $shop['checkout_note'] : 'No payment is taken on this website: after you order, we email you how to pay, then send your access.') ?></p>
    <?php else: ?>
      <div class="shop-soon">
        <p class="eyebrow">Coming soon</p>
        <p class="shop-soon__text"><?= e((string) $shop['empty']) ?></p>
        <div class="shop-soon__grid">
          <?php foreach ([['graduation-cap', 'Courses', 'Step-by-step lessons in plain language, at your own pace.'], ['code-xml', 'Software', 'Practical tools that make everyday work faster and safer.'], ['file-text', 'Templates & guides', 'Ready-to-use checklists, templates and e-books.']] as [$ic, $t, $d]): ?>
            <div class="shop-soon__tile"><span class="shop-soon__icon"><?= icon($ic, 'icon') ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div>
          <?php endforeach; ?>
        </div>
        <?= button('Tell me when it’s ready', page_url('contact'), 'primary', 'arrow-right') ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?= cta_banner('Not sure which one is right for you?', 'Tell us what you want to learn or fix, and we will point you to the right course, tool or service.') ?>
<?php require INC . '/layout/footer.php'; ?>
