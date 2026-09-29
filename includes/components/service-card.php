<?php
/**
 * Service card used on the homepage, /services and "related services".
 */

declare(strict_types=1);

function service_card(string $slug, array $svc, array $opts = []): string
{
    $compact = !empty($opts['compact']);
    $headingLevel = (int) ($opts['level'] ?? 3);
    $id = 'svc-' . $slug;
    ob_start(); ?>
    <article class="service-card<?= $compact ? ' service-card--compact' : '' ?> reveal" aria-labelledby="<?= e($id) ?>">
      <div class="service-card__icon"><?= icon($svc['icon']) ?></div>
      <h<?= $headingLevel ?> class="service-card__title" id="<?= e($id) ?>"><?= e($svc['name']) ?></h<?= $headingLevel ?>>
      <p class="service-card__text"><?= e($svc['summary']) ?></p>
      <?php if (!$compact): ?>
      <ul class="service-card__list">
        <?php foreach ($svc['includes'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <a class="service-card__link" href="<?= e(service_path($slug)) ?>">
        <span><?= e($svc['cta']) ?></span><?= icon('arrow-right', 'icon icon-sm') ?>
      </a>
    </article>
    <?php
    return (string) ob_get_clean();
}

function service_grid(?array $only = null, array $opts = []): string
{
    $html = '<div class="service-grid' . (!empty($opts['compact']) ? ' service-grid--compact' : '') . '">';
    foreach (services() as $slug => $svc) {
        if ($only !== null && !in_array($slug, $only, true)) {
            continue;
        }
        $html .= service_card($slug, $svc, $opts);
    }
    return $html . '</div>';
}
