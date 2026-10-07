<?php
/** Product card for the Courses & Tools page, the homepage and "more products". */

declare(strict_types=1);

function product_card(array $p, string $headingTag = 'h3'): string
{
    $price = shop_price($p);
    $kind = strtolower(preg_replace('/[^A-Za-z]/', '', (string) $p['type'])) ?: 'course';
    ob_start(); ?>
    <article class="product-card product-card--<?= e($kind) ?><?= !empty($p['featured']) ? ' product-card--featured' : '' ?> reveal">
      <div class="product-card__media">
        <?php if (!empty($p['image']) && ($img = photo(ltrim((string) $p['image'], '/'), '(min-width: 1080px) 380px, (min-width: 700px) 50vw, 100vw', ['alt' => '']))): ?>
          <?= $img ?>
        <?php else: ?>
          <div class="product-card__art" aria-hidden="true"><span class="product-card__art-ring"><?= icon(shop_type_icon((string) $p['type']), 'icon') ?></span></div>
        <?php endif; ?>
        <span class="product-card__type"><?= icon(shop_type_icon((string) $p['type']), 'icon icon-xs') ?><?= e($p['type']) ?></span>
        <?php if (!empty($p['featured'])): ?><span class="product-card__featured">★ Featured</span><?php endif; ?>
        <?php if (($p['status'] ?? 'published') !== 'published'): ?><span class="product-card__draft">Draft: only you can see this</span><?php endif; ?>
      </div>
      <div class="product-card__body">
        <<?= $headingTag ?> class="product-card__title"><a href="<?= e(shop_url($p)) ?>"><?= e($p['name']) ?></a></<?= $headingTag ?>>
        <?php if ($p['summary'] !== ''): ?><p class="product-card__summary"><?= e($p['summary']) ?></p><?php endif; ?>
        <?php if (trim((string) $p['format']) !== ''): ?><p class="product-card__format"><?= icon('check', 'icon icon-xs') ?><?= e($p['format']) ?></p><?php endif; ?>
        <div class="product-card__foot">
          <?php if ($price !== ''): ?><span class="product-card__price"><?= e($price) ?></span><?php endif; ?>
          <span class="product-card__more" aria-hidden="true">View details <span class="product-card__arrow"><?= icon('arrow-right', 'icon icon-xs') ?></span></span>
        </div>
      </div>
    </article>
    <?php
    return (string) ob_get_clean();
}
