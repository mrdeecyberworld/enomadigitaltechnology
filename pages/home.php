<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$home = content('home');
$training = content('training');
$faqs = content('faqs');

$page = [
    'title'       => page_text('home', 'meta_title', site('name')),
    'description' => page_text('home', 'meta_description', site('description')),
    'path'        => '/',
    'schema'      => [schema_faq($faqs)],
    'body_class'  => 'page-home',
];
require INC . '/layout/header.php';
?>

<!-- 2. Hero -->
<section class="hero" aria-labelledby="hero-title">
  <div class="hero__bg" aria-hidden="true"><span class="aurora"><i></i><i></i><i></i></span><span class="hero__grid"></span><span class="hero__glow"></span></div>
  <div class="container hero__inner">
    <div class="hero__content">
      <p class="eyebrow eyebrow--light hero__eyebrow"><?= e($home['hero']['eyebrow']) ?></p>
      <?php $hl = (string) $home['hero']['headline']; $cut = strpos($hl, '. '); ?>
      <h1 class="hero__title" id="hero-title"><?php if ($cut !== false): ?><?= e(substr($hl, 0, $cut + 1)) ?> <span class="text-gradient"><?= e(substr($hl, $cut + 2)) ?></span><?php else: ?><?= e($hl) ?><?php endif; ?></h1>
      <p class="hero__text"><?= e($home['hero']['text']) ?></p>
      <div class="btn-row">
        <?= button('Book a Consultation', '/book-a-consultation', 'primary btn-lg', 'calendar-check') ?>
        <?= button('Explore Our Services', '#services', 'outline-light btn-lg', 'arrow-right') ?>
      </div>
      <ul class="hero__points">
        <?php foreach ($home['hero']['points'] as $pt): ?>
          <li><?= icon('check', 'icon icon-xs') ?><?= e($pt) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="hero__visual">
      <div class="hero__stage" data-tilt>
        <div class="media-frame media-frame--hero">
          <?= photo('hero', '(min-width: 1024px) 46vw, 100vw', ['eager' => true]) ?>
          <span class="media-sheen" aria-hidden="true"></span>
        </div>
        <span class="hero__corners" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
      </div>
      <div class="float-card float-card--top" aria-hidden="true">
        <span class="float-card__icon float-card__icon--ok"><?= icon('shield-check', 'icon icon-sm') ?></span>
        <span><strong>MFA enabled</strong><small>Account protected</small></span>
      </div>
      <div class="float-card float-card--mid" aria-hidden="true">
        <span class="float-card__icon float-card__icon--ok"><?= icon('hard-drive', 'icon icon-sm') ?></span>
        <span><strong>Backup complete</strong><small>Files protected</small></span>
      </div>
      <span class="hero__ring" aria-hidden="true"></span>
    </div>
  </div>
</section>

<!-- 3. Trust bar -->
<section class="trust" aria-labelledby="trust-heading">
  <div class="container trust__inner">
    <h2 class="trust__heading" id="trust-heading"><?= e($home['trust']['heading']) ?></h2>
    <ul class="trust__list">
      <?php foreach ($home['trust']['items'] as $item): ?>
        <li><a href="<?= e($item['path']) ?>"><?= icon($item['icon'], 'icon') ?><span><?= e($item['label']) ?></span></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- 4. Services -->
<section class="section" id="services" aria-labelledby="services-heading">
  <div class="container">
    <?= section_header('Our Services', 'Technology Services Built Around You', 'From building your digital presence to protecting it, Enoma Digital Technologies provides practical technology services designed around your goals.', ['id' => 'services-heading']) ?>
    <?= service_grid() ?>
  </div>
</section>

<!-- Capabilities strip: every service feature, scrolling -->
<?php $caps = []; foreach (services() as $navSvc) { foreach ((array) ($navSvc['includes'] ?? []) as $cap) { $caps[$cap] = $navSvc['icon'] ?? 'check'; } } ?>
<?php if ($caps): ?>
<section class="ticker" aria-label="What we help with">
  <?php foreach ([false, true] as $copy): ?>
    <ul class="ticker__track"<?= $copy ? ' aria-hidden="true"' : '' ?>>
      <?php foreach ($caps as $cap => $capIcon): ?>
        <li><?= icon($capIcon, 'icon icon-xs') ?><?= e($cap) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- 5. Why Enoma -->
<section class="section section--dark why" aria-labelledby="why-heading">
  <div class="section__bg" aria-hidden="true"></div>
  <div class="container why__inner">
    <div class="why__intro">
      <?= section_header($home['why']['eyebrow'], $home['why']['heading'], $home['why']['text'], ['align' => 'left', 'id' => 'why-heading']) ?>
      <?= button('Learn About Enoma', '/about', 'outline-light', 'arrow-right') ?>
    </div>
    <div class="why__grid">
      <?php foreach ($home['why']['points'] as $pt): ?>
        <div class="feature-card reveal">
          <span class="feature-card__icon"><?= icon($pt['icon']) ?></span>
          <h3 class="feature-card__title"><?= e($pt['title']) ?></h3>
          <p class="feature-card__text"><?= e($pt['text']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 6. Cybersecurity feature -->
<section class="section security" aria-labelledby="security-heading">
  <div class="container split">
    <div class="split__media reveal">
      <div class="media-frame media-frame--tall">
        <?= photo('cyber', '(min-width: 1024px) 45vw, 100vw') ?>
        <div class="media-badge" aria-hidden="true">
          <?= icon('lock', 'icon icon-sm') ?><span>Protect what matters</span>
        </div>
      </div>
    </div>
    <div class="split__content">
      <?= section_header($home['security']['eyebrow'], $home['security']['heading'], $home['security']['text'], ['align' => 'left', 'id' => 'security-heading']) ?>
      <ul class="pill-grid reveal">
        <?php foreach ($home['security']['items'] as $item): ?>
          <li class="pill"><?= icon($item['icon'], 'icon icon-sm') ?><span><?= e($item['label']) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <div class="btn-row">
        <?= button($home['security']['cta']['label'], $home['security']['cta']['path'], 'primary', 'shield-check') ?>
        <?= button('Security Resources', '/resources', 'ghost', 'arrow-right') ?>
      </div>
    </div>
  </div>
</section>

<!-- 7. AI Assistant + 7B. Service Finder -->
<section class="section section--muted assist" id="assistant" aria-labelledby="assistant-heading">
  <div class="container">
    <?= section_header('Guidance', 'Not Sure What You Need?', "Tell us what you're trying to accomplish or the technology problem you're facing. Our AI assistant can help identify the right Enoma service.", ['id' => 'assistant-heading']) ?>
    <div class="assist__grid">
      <div class="assist__panel">
        <h3 class="assist__label"><?= icon('sparkles', 'icon icon-sm') ?> Ask the AI assistant</h3>
        <?= ai_assistant() ?>
      </div>
      <div class="assist__panel">
        <h3 class="assist__label"><?= icon('compass', 'icon icon-sm') ?> Find the right service</h3>
        <?= service_finder() ?>
      </div>
    </div>
  </div>
</section>

<!-- 8. Training -->
<section class="section training-feature" aria-labelledby="training-heading">
  <div class="container split split--reverse">
    <div class="split__content">
      <?= section_header('Training', 'Technology Education for Everyone', 'Practical, friendly technology and cybersecurity education for every age and experience level, delivered in plain language.', ['align' => 'left', 'id' => 'training-heading']) ?>
      <h3 class="mini-heading">Who we train</h3>
      <ul class="tag-list reveal">
        <?php foreach ($training['audiences'] as $a): ?>
          <li><?= icon($a['icon'], 'icon icon-xs') ?><?= e($a['label']) ?></li>
        <?php endforeach; ?>
      </ul>
      <h3 class="mini-heading">Popular topics</h3>
      <ul class="topic-grid reveal">
        <?php foreach ($training['topics'] as $t): ?>
          <li><?= icon($t['icon'], 'icon icon-sm') ?><span><?= e($t['label']) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <?= button('View Training Programs', '/training', 'primary', 'arrow-right') ?>
    </div>
    <div class="split__media reveal">
      <div class="media-frame media-frame--tall">
        <?= photo('training', '(min-width: 1024px) 45vw, 100vw') ?>
      </div>
    </div>
  </div>
</section>

<!-- Courses & Tools (only once something is for sale) -->
<?php $homeProducts = array_slice(shop_products(), 0, 3); ?>
<?php if ($homeProducts): $shop = content('shop'); ?>
<section class="section" aria-labelledby="shop-home-heading">
  <div class="container">
    <div class="section-split-head">
      <?= section_header((string) ($shop['eyebrow'] ?: 'Courses & Tools'), (string) ($shop['home_heading'] ?: 'Courses & Tools'), (string) $shop['home_intro'], ['align' => 'left', 'id' => 'shop-home-heading']) ?>
      <?= button('See all courses & tools', page_url('shop'), 'ghost', 'arrow-right') ?>
    </div>
    <div class="product-grid">
      <?php foreach ($homeProducts as $p): ?><?= product_card($p, 'h3') ?><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- 9. Process -->
<section class="section section--dark process" aria-labelledby="process-heading">
  <div class="section__bg" aria-hidden="true"></div>
  <div class="container">
    <?= section_header($home['process']['eyebrow'], $home['process']['heading'], $home['process']['text'], ['id' => 'process-heading']) ?>
    <ol class="process__steps">
      <?php foreach ($home['process']['steps'] as $i => $step): ?>
        <li class="process-step reveal">
          <span class="process-step__num" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <span class="process-step__icon"><?= icon($step['icon']) ?></span>
          <h3 class="process-step__title"><?= e($step['title']) ?></h3>
          <p class="process-step__text"><?= e($step['text']) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- 10. About -->
<section class="section about-feature" aria-labelledby="about-heading">
  <div class="container split">
    <div class="split__media reveal">
      <?php if (founder_ready() || viewer_is_admin()): ?>
        <?= founder_card() ?>
      <?php else: ?>
        <div class="media-frame media-frame--tall"><?= photo('people', '(min-width: 1024px) 45vw, 100vw') ?></div>
      <?php endif; ?>
    </div>
    <div class="split__content">
      <?= section_header($home['about']['eyebrow'], $home['about']['heading'], null, ['align' => 'left', 'id' => 'about-heading']) ?>
      <?php foreach ($home['about']['paragraphs'] as $p): ?>
        <p class="prose-p reveal"><?= e($p) ?></p>
      <?php endforeach; ?>
      <?= check_list($home['about']['values'], 'check-list--2col reveal') ?>
      <?= button('More About Enoma', '/about', 'secondary', 'arrow-right') ?>
    </div>
  </div>
</section>

<!-- 11. Testimonials -->
<?= testimonials_section() ?>

<!-- Latest blog posts -->
<?php $blogSettings = content('blog'); $latest = array_slice(blog_posts(), 0, 3); ?>
<?php if (!empty($blogSettings['show_on_home']) && $latest): ?>
<section class="section" aria-labelledby="blog-heading">
  <div class="container">
    <div class="section-split-head">
      <?= section_header('Blog', (string) ($blogSettings['home_heading'] ?? 'Latest Insights'), (string) ($blogSettings['home_intro'] ?? ''), ['align' => 'left', 'id' => 'blog-heading']) ?>
      <?= button('View all articles', '/blog', 'ghost', 'arrow-right') ?>
    </div>
    <div class="post-grid"><?php foreach ($latest as $p) echo blog_card($p); ?></div>
  </div>
</section>
<?php endif; ?>

<!-- 12. FAQ -->
<section class="section section--muted" aria-labelledby="faq-heading">
  <div class="container container--narrow">
    <?= section_header('FAQ', 'Frequently Asked Questions', 'Quick answers about our services. Still have questions? Our team is happy to help.', ['id' => 'faq-heading']) ?>
    <?= faq_list($faqs) ?>
    <p class="section-foot">More questions? <a href="/faq">See all FAQs</a> or <a href="/contact">contact us</a>.</p>
  </div>
</section>

<!-- 13. Final CTA -->
<?= cta_banner() ?>

<?php require INC . '/layout/footer.php'; ?>
