<?php
/** Product card for the Courses & Tools page, the homepage and "more products". */

declare(strict_types=1);

function product_card(array $p, string $headingTag = 'h3'): string
{
    $price = shop_price($p);
    ob_start(); ?>
    <article class="product-card reveal">
      <div class="product-card__media">
        <?php if (!empty($p['image']) && ($img = photo(ltrim((string) $p['image'], '/'), '(min-width: 1080px) 380px, (min-width: 700px) 50vw, 100vw', ['alt' => '']))): ?>
          <?= $img ?>
        <?php else: ?>
          <div class="product-card__art" aria-hidden="true"><?= icon($p['type'] === 'Course' || $p['type'] === 'Workshop' ? 'graduation-cap' : ($p['type'] === 'Template' || $p['type'] === 'Guide' ? 'file-text' : 'package'), 'icon') ?></div>
        <?php endif; ?>
        <span class="product-card__type"><?= e($p['type']) ?></span>
        <?php if (($p['status'] ?? 'published') !== 'published'): ?><span class="product-card__draft">Draft: only you can see this</span><?php endif; ?>
      </div>
      <div class="product-card__body">
        <<?= $headingTag ?> class="product-card__title"><a href="<?= e(shop_url($p)) ?>"><?= e($p['name']) ?></a></<?= $headingTag ?>>
        <?php if ($p['summary'] !== ''): ?><p class="product-card__summary"><?= e($p['summary']) ?></p><?php endif; ?>
        <div class="product-card__foot">
          <?php if ($price !== ''): ?><span class="product-card__price"><?= e($price) ?></span><?php endif; ?>
          <span class="product-card__more" aria-hidden="true">View details <?= icon('arrow-right', 'icon icon-xs') ?></span>
        </div>
      </div>
    </article>
    <?php
    return (string) ob_get_clean();
}
