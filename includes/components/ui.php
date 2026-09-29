<?php
/**
 * Small shared UI components: buttons, section headers, page heroes, breadcrumbs.
 */

declare(strict_types=1);

/** Button link. Variants: primary, secondary, ghost, light, outline-light. */
function button(string $label, string $href, string $variant = 'primary', ?string $iconName = null, array $attrs = []): string
{
    $extra = '';
    foreach ($attrs as $k => $v) {
        $extra .= ' ' . e($k) . '="' . e((string) $v) . '"';
    }
    $iconHtml = $iconName ? icon($iconName, 'icon btn-icon') : '';
    return '<a class="btn btn-' . e($variant) . '" href="' . e($href) . '"' . $extra . '><span>' . e($label) . '</span>' . $iconHtml . '</a>';
}

/**
 * Section header.
 * $opts: align ('left'|'center'), level (2|3), id (for aria-labelledby), light (bool, for dark sections)
 */
function section_header(string $eyebrow, string $heading, ?string $text = null, array $opts = []): string
{
    $align = $opts['align'] ?? 'center';
    $level = (int) ($opts['level'] ?? 2);
    $id = isset($opts['id']) ? ' id="' . e($opts['id']) . '"' : '';
    $html = '<header class="section-header section-header--' . e($align) . ' reveal">';
    if ($eyebrow !== '') {
        $html .= '<p class="eyebrow">' . e($eyebrow) . '</p>';
    }
    $html .= '<h' . $level . ' class="section-title"' . $id . '>' . e($heading) . '</h' . $level . '>';
    if ($text) {
        $html .= '<p class="section-lead">' . e($text) . '</p>';
    }
    return $html . '</header>';
}

/** @param array<int, array{0:string,1:string}> $crumbs */
function breadcrumbs(array $crumbs): string
{
    $html = '<nav class="breadcrumbs" aria-label="Breadcrumb"><ol>';
    $last = count($crumbs) - 1;
    foreach (array_values($crumbs) as $i => [$label, $path]) {
        $html .= $i === $last
            ? '<li><span aria-current="page">' . e($label) . '</span></li>'
            : '<li><a href="' . e($path) . '">' . e($label) . '</a></li>';
    }
    return $html . '</ol></nav>';
}

/**
 * Inner page hero (dark). $opts: eyebrow, text, crumbs, image (photo key), actions (bool),
 * keyword_h1 (bool: include the eyebrow keyword phrase in the H1 for SEO)
 */
function page_hero(string $title, array $opts = []): string
{
    $hasImage = !empty($opts['image']);
    ob_start(); ?>
    <section class="page-hero<?= $hasImage ? ' page-hero--image' : '' ?>">
      <div class="page-hero__bg" aria-hidden="true"></div>
      <div class="container page-hero__inner">
        <div class="page-hero__content">
          <?php if (!empty($opts['crumbs'])): ?><?= breadcrumbs($opts['crumbs']) ?><?php endif; ?>
          <?php if (!empty($opts['keyword_h1'])): ?>
          <h1 class="page-hero__h1"><span class="eyebrow eyebrow--light"><?= e($opts['eyebrow']) ?></span><span class="page-hero__title"><?= e($title) ?></span></h1>
          <?php else: ?>
          <?php if (!empty($opts['eyebrow'])): ?><p class="eyebrow eyebrow--light"><?= e($opts['eyebrow']) ?></p><?php endif; ?>
          <h1 class="page-hero__title"><?= e($title) ?></h1>
          <?php endif; ?>
          <?php if (!empty($opts['text'])): ?><p class="page-hero__text"><?= e($opts['text']) ?></p><?php endif; ?>
          <?php if (!empty($opts['actions'])): ?>
          <div class="btn-row">
            <?= button(site('cta_primary')['label'], site('cta_primary')['path'], 'primary', 'arrow-right') ?>
            <?= button(site('cta_secondary')['label'], site('cta_secondary')['path'], 'outline-light') ?>
          </div>
          <?php endif; ?>
        </div>
        <?php if ($hasImage): ?>
        <div class="page-hero__media">
          <div class="media-frame media-frame--hero">
            <?= photo($opts['image'], '(min-width: 1024px) 40vw, 100vw', ['eager' => true]) ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/** Check list. */
function check_list(array $items, string $class = ''): string
{
    $html = '<ul class="check-list ' . e($class) . '">';
    foreach ($items as $item) {
        $html .= '<li>' . icon('check', 'icon icon-check') . '<span>' . e($item) . '</span></li>';
    }
    return $html . '</ul>';
}

/** Brand mark: a geometric "E" inside a shield-like tile. */
function logo_mark(string $class = 'brand__mark'): string
{
    static $n = 0;
    $id = 'lg' . (++$n);
    return '<svg class="' . e($class) . '" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">'
        . '<defs><linearGradient id="' . $id . '" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">'
        . '<stop offset="0" stop-color="#2f6bff"/><stop offset="1" stop-color="#0fb5d6"/></linearGradient></defs>'
        . '<rect x="1" y="1" width="38" height="38" rx="10" fill="#0b1324"/>'
        . '<rect x="1" y="1" width="38" height="38" rx="10" fill="none" stroke="url(#' . $id . ')" stroke-opacity=".55"/>'
        . '<path d="M12 11h16v4H16.5v3H26v4h-9.5v3H28v4H12z" fill="url(#' . $id . ')"/>'
        . '<circle cx="30.5" cy="20" r="2" fill="#5ee0f5"/>'
        . '</svg>';
}

/** Founder portrait card. Shows clearly marked placeholders until details are configured. */
function founder_card(): string
{
    $f = cfg('founder', []);
    $name = trim((string) ($f['name'] ?? ''));
    $photo = trim((string) ($f['photo'] ?? ''));
    ob_start(); ?>
    <figure class="founder-card">
      <div class="founder-card__photo<?= $photo ? '' : ' founder-card__photo--empty' ?>">
        <?php if ($photo): ?>
          <img src="/<?= e(ltrim($photo, '/')) ?>" alt="Portrait of <?= e($name ?: 'the founder of ' . site('name')) ?>" width="800" height="1000" loading="lazy" decoding="async">
        <?php else: ?>
          <?= icon('user-round', 'icon founder-card__placeholder-icon') ?>
          <span class="founder-card__placeholder-label">Founder photo will appear here</span>
        <?php endif; ?>
      </div>
      <figcaption class="founder-card__caption">
        <strong><?= $name !== '' ? e($name) : placeholder('Founder name') ?></strong>
        <span><?= e(($f['title'] ?? 'Founder') . ', ' . site('name')) ?></span>
      </figcaption>
    </figure>
    <?php
    return (string) ob_get_clean();
}
