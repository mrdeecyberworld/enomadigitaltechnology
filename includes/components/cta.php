<?php
/**
 * Final call-to-action band.
 */

declare(strict_types=1);

function cta_banner(
    string $heading = 'Ready to Build, Secure and Improve Your Technology?',
    string $text = "Let's talk about what you need and find the right solution."
): string {
    ob_start(); ?>
    <section class="cta-band" aria-labelledby="cta-heading">
      <div class="container">
        <div class="cta-band__panel reveal">
          <div class="cta-band__glow" aria-hidden="true"></div>
          <p class="eyebrow eyebrow--light"><?= e(site('tagline')) ?></p>
          <h2 class="cta-band__title" id="cta-heading"><?= e($heading) ?></h2>
          <p class="cta-band__text"><?= e($text) ?></p>
          <div class="btn-row btn-row--center">
            <?= button(site('cta_primary')['label'], site('cta_primary')['path'], 'primary', 'calendar-check') ?>
            <?= button(site('cta_secondary')['label'], site('cta_secondary')['path'], 'outline-light', 'arrow-right') ?>
          </div>
        </div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}
