<?php
/**
 * Testimonials. Real testimonials come from includes/content/testimonials.php.
 * Until then, clearly labeled placeholders are shown (nothing is invented).
 */

declare(strict_types=1);

function testimonial_card(?array $t = null): string
{
    if ($t === null) {
        return '<figure class="testimonial-card testimonial-card--placeholder reveal">'
            . icon('quote', 'icon testimonial-card__mark')
            . '<blockquote><p>Client testimonial will appear here.</p></blockquote>'
            . '<figcaption><span class="testimonial-card__avatar" aria-hidden="true">' . icon('user-round', 'icon icon-sm') . '</span>'
            . '<span><strong>Client name</strong><span class="testimonial-card__role">Placeholder</span></span></figcaption>'
            . '</figure>';
    }
    return '<figure class="testimonial-card reveal">'
        . icon('quote', 'icon testimonial-card__mark')
        . '<blockquote><p>' . e($t['quote']) . '</p></blockquote>'
        . '<figcaption><span class="testimonial-card__avatar" aria-hidden="true">' . e(mb_substr($t['name'], 0, 1)) . '</span>'
        . '<span><strong>' . e($t['name']) . '</strong><span class="testimonial-card__role">' . e($t['role'] ?? '') . (!empty($t['service']) ? ' · ' . e($t['service']) : '') . '</span></span></figcaption>'
        . '</figure>';
}

function testimonials_section(): string
{
    $items = content('testimonials');
    if (!$items && !viewer_is_admin()) {
        return ''; // nothing to show visitors until real testimonials are added
    }
    ob_start(); ?>
    <section class="section section--muted" aria-labelledby="testimonials-heading">
      <div class="container">
        <?= section_header('Client Stories', 'What Clients Say', $items ? null : 'Only you can see this section while logged in. Add real testimonials in Admin → Testimonials and it appears for visitors.', ['id' => 'testimonials-heading']) ?>
        <div class="testimonial-grid">
          <?php if ($items): foreach ($items as $t): ?>
            <?= testimonial_card($t) ?>
          <?php endforeach; else: for ($i = 0; $i < 3; $i++): ?>
            <?= testimonial_card() ?>
          <?php endfor; endif; ?>
        </div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}
