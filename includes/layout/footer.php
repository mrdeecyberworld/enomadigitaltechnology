<?php
declare(strict_types=1);
?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="site-footer__top">
      <div class="site-footer__brand">
        <a class="brand brand--light" href="/" aria-label="<?= e(site('name')) ?> home">
          <?= logo_mark() ?>
          <span class="brand__text" aria-hidden="true">
            <span class="brand__name">ENOMA</span>
            <span class="brand__sub">DIGITAL TECHNOLOGIES</span>
          </span>
        </a>
        <p class="site-footer__name"><?= e(site('name')) ?></p>
        <p class="site-footer__tagline"><?= e(site('tagline')) ?></p>
        <p class="site-footer__area"><?= icon('globe', 'icon icon-sm') ?><span><?= e(site('service_area')) ?></span></p>
        <?php if (cfg('contact_email')): ?>
          <p class="site-footer__contact"><?= icon('mail', 'icon icon-sm') ?><a href="mailto:<?= e(cfg('contact_email')) ?>"><?= e(cfg('contact_email')) ?></a></p>
        <?php endif; ?>
        <?php if (cfg('contact_phone')): ?>
          <p class="site-footer__contact"><?= icon('phone', 'icon icon-sm') ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) cfg('contact_phone'))) ?>"><?= e(cfg('contact_phone')) ?></a></p>
        <?php endif; ?>
      </div>

      <nav class="site-footer__col" aria-labelledby="footer-services">
        <h2 class="site-footer__heading" id="footer-services">Services</h2>
        <ul>
          <?php foreach (services() as $navSlug => $navSvc): ?>
            <li><a href="<?= e(service_path($navSlug)) ?>"><?= e($navSvc['short_label'] ?? $navSvc['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <nav class="site-footer__col" aria-labelledby="footer-company">
        <h2 class="site-footer__heading" id="footer-company">Company</h2>
        <ul>
          <?php foreach (site('footer_company') as $link): ?>
            <li><a href="<?= e($link['path']) ?>"><?= e($link['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <div class="site-footer__col site-footer__cta">
        <h2 class="site-footer__heading">Get Started</h2>
        <p>Tell us what you need. We'll help you find the right solution.</p>
        <div class="btn-stack">
          <?= button(site('cta_primary')['label'], site('cta_primary')['path'], 'primary', 'calendar-check') ?>
          <?= button(site('cta_secondary')['label'], site('cta_secondary')['path'], 'outline-light') ?>
        </div>
      </div>
    </div>

    <div class="site-footer__bottom">
      <p>&copy; <?= date('Y') ?> <?= e(site('name')) ?>. All rights reserved.</p>
      <ul class="social" aria-label="Social media">
        <?php foreach (cfg('social', []) as $network => $url): ?>
          <li>
            <?php if ($url): ?>
              <a href="<?= e($url) ?>" rel="noopener me" target="_blank"><?= e($network) ?><span class="sr-only"> (opens in a new tab)</span></a>
            <?php else: ?>
              <span class="social__placeholder" title="Coming soon"><?= e($network) ?><span class="sr-only"> – coming soon</span></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</footer>
</body>
</html>
