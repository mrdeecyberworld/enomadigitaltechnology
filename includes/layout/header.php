<?php
/**
 * Document head + site header / navigation.
 *
 * Pages set $page before including this file:
 *   $page = [
 *     'title'       => 'Unique page title',
 *     'description' => 'Unique meta description',
 *     'path'        => '/canonical-path',
 *     'schema'      => [ ...extra JSON-LD graph nodes... ],
 *     'noindex'     => false,
 *   ];
 */

declare(strict_types=1);

$page = array_merge([
    'title'       => site('name'),
    'description' => site('description'),
    'path'        => current_path(),
    'schema'      => [],
    'noindex'     => false,
    'og_type'     => 'website',
    'og_image'    => '/assets/img/og-image.png',
    'body_class'  => '',
], $page ?? []);

$canonical = abs_url($page['path']);
$ogImage = abs_url($page['og_image']);
$graph = array_merge(schema_base(), $page['schema']);
?><!doctype html>
<?php $look = appearance(); ?>
<html lang="en-US" data-site-theme="<?= e($look['theme']) ?>" data-palette="<?= e($look['palette']) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page['title']) ?></title>
  <meta name="description" content="<?= e($page['description']) ?>">
  <link rel="canonical" href="<?= e($canonical) ?>">
  <?php if ($page['noindex']): ?><meta name="robots" content="noindex, follow"><?php endif; ?>

  <meta property="og:type" content="<?= e($page['og_type']) ?>">
  <meta property="og:site_name" content="<?= e(site('name')) ?>">
  <meta property="og:title" content="<?= e($page['title']) ?>">
  <meta property="og:description" content="<?= e($page['description']) ?>">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <meta property="og:image" content="<?= e($ogImage) ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="<?= e(site('name') . ' – ' . site('tagline')) ?>">
  <meta property="og:locale" content="en_US">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($page['title']) ?>">
  <meta name="twitter:description" content="<?= e($page['description']) ?>">
  <meta name="twitter:image" content="<?= e($ogImage) ?>">

  <meta name="theme-color" content="<?= $look['theme'] === 'light' ? '#ffffff' : e(palettes()[$look['palette']]['swatch'][0]) ?>">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="icon" href="/favicon.ico" sizes="32x32">
  <link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
  <link rel="manifest" href="/site.webmanifest">

  <link rel="preload" href="/assets/fonts/inter-var.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/assets/fonts/manrope-var.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preconnect" href="https://images.unsplash.com">
  <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
  <?php if ($look['accent'] !== ''): ?><link rel="stylesheet" href="/theme.css?v=<?= e(substr(md5($look['accent'] . $look['theme']), 0, 8)) ?>"><?php endif; ?>
  <script src="<?= e(asset('js/main.js')) ?>" defer></script>

  <?= json_ld(['@context' => 'https://schema.org', '@graph' => $graph]) ?>
</head>
<body class="<?= e($page['body_class']) ?>">
<a class="skip-link" href="#main">Skip to main content</a>

<header class="site-header" data-header>
  <div class="container site-header__inner">
    <a class="brand" href="/" aria-label="<?= e(site('name')) ?> home">
      <?= logo_mark() ?>
      <span class="brand__text" aria-hidden="true">
        <span class="brand__name">ENOMA</span>
        <span class="brand__sub">DIGITAL TECHNOLOGIES</span>
      </span>
    </a>

    <nav class="primary-nav" aria-label="Primary">
      <ul class="primary-nav__list">
        <?php foreach (site('nav') as $item): ?>
          <?php $active = is_current($item['path']) || (($item['children'] ?? '') === 'services' && is_current('/services')); ?>
          <?php if (($item['children'] ?? '') === 'services'): ?>
            <li class="has-menu" data-menu>
              <a href="<?= e($item['path']) ?>" class="primary-nav__link"<?= $active ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
              <button type="button" class="menu-toggle" aria-expanded="false" aria-controls="services-menu" data-menu-toggle>
                <span class="sr-only">Show services</span><?= icon('chevron-down', 'icon icon-xs') ?>
              </button>
              <div class="mega-menu" id="services-menu" data-menu-panel>
                <ul class="mega-menu__grid">
                  <?php foreach (services() as $navSlug => $navSvc): ?>
                    <li>
                      <a class="mega-menu__item" href="<?= e(service_path($navSlug)) ?>">
                        <span class="mega-menu__icon"><?= icon($navSvc['icon'], 'icon icon-sm') ?></span>
                        <span><strong><?= e(($navSvc['nav_label'] ?? '') ?: $navSvc['name']) ?></strong><span class="mega-menu__desc"><?= e($navSvc['summary']) ?></span></span>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
                <a class="mega-menu__all" href="/services">View all services <?= icon('arrow-right', 'icon icon-xs') ?></a>
              </div>
            </li>
          <?php else: ?>
            <li><a href="<?= e($item['path']) ?>" class="primary-nav__link"<?= is_current($item['path']) ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="site-header__actions">
      <a class="btn btn-ghost btn-sm header-quote" href="<?= e(site('cta_secondary')['path']) ?>"><span><?= e(site('cta_secondary')['label']) ?></span></a>
      <a class="btn btn-primary btn-sm header-book" href="<?= e(site('cta_primary')['path']) ?>"><span><?= e(site('cta_primary')['label']) ?></span></a>
      <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="mobile-nav" data-nav-toggle>
        <span class="sr-only">Open menu</span>
        <span class="nav-toggle__bars" aria-hidden="true"><span></span><span></span></span>
      </button>
    </div>
  </div>

  <div class="mobile-nav" id="mobile-nav" data-mobile-nav hidden>
    <nav aria-label="Mobile">
      <ul class="mobile-nav__list">
        <?php foreach (site('nav') as $item): ?>
          <?php if (($item['children'] ?? '') === 'services'): ?>
            <li>
              <details class="mobile-nav__group">
                <summary><?= e($item['label']) ?><?= icon('chevron-down', 'icon icon-sm') ?></summary>
                <ul>
                  <li><a href="/services">All services</a></li>
                  <?php foreach (services() as $navSlug => $navSvc): ?>
                    <li><a href="<?= e(service_path($navSlug)) ?>"><?= icon($navSvc['icon'], 'icon icon-sm') ?><?= e(($navSvc['nav_label'] ?? '') ?: $navSvc['name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </details>
            </li>
          <?php else: ?>
            <li><a href="<?= e($item['path']) ?>"<?= is_current($item['path']) ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
      <div class="mobile-nav__actions">
        <?= button(site('cta_primary')['label'], site('cta_primary')['path'], 'primary', 'calendar-check') ?>
        <?= button(site('cta_secondary')['label'], site('cta_secondary')['path'], 'secondary') ?>
      </div>
      <p class="mobile-nav__tagline"><?= e(site('tagline')) ?></p>
    </nav>
  </div>
</header>

<main id="main" tabindex="-1">
