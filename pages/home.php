<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$home = content('home');
$training = content('training');
$sectionDefaults = content_default('home')['sections'];
$show = static fn (string $key): bool => (bool) ((array) ($home['sections'] ?? []) + $sectionDefaults)[$key];
$faqCount = max(1, (int) ($home['faq_count'] ?? 4));
$faqs = array_slice(content('faqs'), 0, $faqCount);

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
<?php $founder = cfg('founder', []); $founderPhoto = trim((string) ($founder['photo'] ?? '')); ?>
<section class="hero hero--calm" aria-labelledby="hero-title">
  <div class="hero__bg" aria-hidden="true"></div>
  <div class="container hero__inner">
    <div class="hero__content">
      <?php if ($fl = freelance_status()): ?>
        <a class="hero__status" href="<?= e($fl['link'] ?: '/get-a-quote') ?>"><span class="status-dot" aria-hidden="true"></span><?= e($fl['status']) ?></a>
      <?php endif; ?>
      <p class="hero__kicker"><?= e($home['hero']['eyebrow']) ?></p>
      <?php $hl = (string) $home['hero']['headline']; $cut = strpos($hl, '. '); ?>
      <h1 class="hero__title" id="hero-title"><?php if ($cut !== false): ?><?= e(substr($hl, 0, $cut + 1)) ?> <span class="hero__accent"><?= e(substr($hl, $cut + 2)) ?></span><?php else: ?><?= e($hl) ?><?php endif; ?></h1>
      <p class="hero__text"><?= e($home['hero']['text']) ?></p>
      <div class="btn-row">
        <?= button('Book a Consultation', '/book-a-consultation', 'primary btn-lg', 'calendar-check') ?>
        <?= button('See what I can help with', '#services', 'outline-light btn-lg', 'arrow-right') ?>
      </div>
      <ul class="hero__creds">
        <?php foreach ($home['hero']['points'] as $pt): ?>
          <li><?= icon('check', 'icon icon-xs') ?><?= e($pt) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <figure class="hero__visual hero__visual--calm">
      <div class="media-frame media-frame--hero">
        <?php if ($founderPhoto !== ''): ?>
          <?= photo(ltrim($founderPhoto, '/'), '(min-width: 1024px) 46vw, 100vw', ['eager' => true, 'alt' => 'Portrait of ' . ($founder['name'] ?? 'the founder')]) ?>
        <?php else: ?>
          <?= photo('hero', '(min-width: 1024px) 46vw, 100vw', ['eager' => true]) ?>
        <?php endif; ?>
      </div>
      <?php if ($founderPhoto !== '' && !empty($founder['name'])): ?>
        <figcaption class="hero__caption"><strong><?= e($founder['name']) ?></strong><span><?= e(($founder['title'] ?? 'Founder') . ', ' . site('name')) ?></span></figcaption>
      <?php endif; ?>
    </figure>
  </div>
</section>

<?php $fr = $home['freelancer'] ?? content_default('home')['freelancer']; ?>
<?php if ($show('freelancer') && trim((string) ($fr['heading'] ?? '')) !== ''): ?>
<!-- I'm a freelancer -->
<?php $fn = cfg('founder', []); $fName = trim((string) ($fn['name'] ?? '')); $fPhoto = trim((string) ($fn['photo'] ?? '')); $fl = freelance_status(); ?>
<section class="section freelancer" id="freelancer" aria-labelledby="freelancer-heading">
  <div class="container freelancer__inner">
    <aside class="freelancer__card reveal" aria-label="Freelancer profile">
      <div class="freelancer__avatar">
        <?php if ($fPhoto !== ''): ?>
          <?= photo(ltrim($fPhoto, '/'), '120px', ['alt' => 'Portrait of ' . ($fName ?: 'the freelancer')]) ?>
        <?php else: ?>
          <span aria-hidden="true"><?= e(implode('', array_map(static fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)), array_slice(preg_split('/\s+/', $fName ?: site('name')), 0, 2)))) ?></span>
        <?php endif; ?>
      </div>
      <p class="freelancer__name"><?= e($fName ?: site('name')) ?></p>
      <p class="freelancer__role"><?= e((string) ($fr['role'] ?? 'Freelancer')) ?></p>
      <?php if ($fl): ?><p class="freelancer__status"><span class="status-dot" aria-hidden="true"></span><?= e(preg_replace('/^Freelancer\s*·\s*/u', '', $fl['status'])) ?></p><?php endif; ?>
      <?php if (!empty($fr['facts'])): ?>
        <ul class="freelancer__facts">
          <?php foreach ((array) $fr['facts'] as $fact): ?><li><?= icon('check', 'icon icon-xs') ?><?= e((string) $fact) ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </aside>
    <div class="freelancer__body">
      <?= section_header((string) ($fr['eyebrow'] ?? ''), (string) $fr['heading'], (string) ($fr['text'] ?? ''), ['align' => 'left', 'id' => 'freelancer-heading']) ?>
      <?php if (!empty($fr['ways'])): ?>
        <h3 class="freelancer__ways-title">Ways to hire me</h3>
        <ul class="freelancer__ways">
          <?php foreach ((array) $fr['ways'] as $w): ?>
            <li class="freelancer__way reveal">
              <span class="freelancer__way-icon"><?= icon((string) ($w['icon'] ?? 'check'), 'icon') ?></span>
              <strong><?= e((string) ($w['title'] ?? '')) ?></strong>
              <span><?= e((string) ($w['text'] ?? '')) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <div class="btn-row">
        <?= button(($fl['link_label'] ?? '') ?: 'Hire me', ($fl['link'] ?? '') ?: '/get-a-quote', 'primary', 'arrow-right') ?>
        <?= button('My background', '/about', 'ghost', 'user-round') ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($show('trust')): ?>
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
<?php endif; ?>

<!-- 4. Services -->
<section class="section" id="services" aria-labelledby="services-heading">
  <div class="container">
    <?= section_header('Services', 'What I can help with', 'From building your website to keeping your accounts and devices safe. Pick an area to see exactly what is included.', ['id' => 'services-heading', 'align' => 'left']) ?>
    <?= service_grid(null, ['compact' => true, 'photo' => true, 'class' => 'service-grid--home']) ?>
  </div>
</section>



<?php if ($show('why')): ?>
<!-- 5. Why Enoma -->
<section class="section section--dark why" aria-labelledby="why-heading">
  <div class="section__bg" aria-hidden="true"></div>
  <div class="container why__inner why__inner--photo">
    <div class="why__media reveal">
      <div class="media-frame media-frame--tall"><?= photo('people', '(min-width: 1024px) 40vw, 100vw') ?></div>
    </div>
    <div class="why__body">
    <div class="why__intro">
      <?= section_header($home['why']['eyebrow'], $home['why']['heading'], $home['why']['text'], ['align' => 'left', 'id' => 'why-heading']) ?>
    </div>
    <div class="why__grid why__grid--list">
      <?php foreach ($home['why']['points'] as $pt): ?>
        <div class="feature-card reveal">
          <span class="feature-card__icon"><?= icon($pt['icon']) ?></span>
          <h3 class="feature-card__title"><?= e($pt['title']) ?></h3>
          <p class="feature-card__text"><?= e($pt['text']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="why__more"><?= button('More about me', '/about', 'outline-light', 'arrow-right') ?></p>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($show('security')): ?>
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
<?php endif; ?>

<?php if ($show('assistant')): ?>
<!-- 7. AI Assistant + 7B. Service Finder -->
<section class="section section--muted assist" id="assistant" aria-labelledby="assistant-heading">
  <div class="container">
    <?= section_header('Not sure where to start?', 'Tell me what is going on', 'Pick what you need help with, or describe the problem, and you will be pointed to the right service.', ['id' => 'assistant-heading', 'align' => 'left']) ?>
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
<?php endif; ?>

<?php if ($show('training')): ?>
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
<?php endif; ?>

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

<?php if ($show('process')): ?>
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
<?php endif; ?>

<?php if ($show('about')): ?>
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
<?php endif; ?>

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

<?php if ($show('faq')): ?>
<!-- 12. FAQ -->
<section class="section section--muted" aria-labelledby="faq-heading">
  <div class="container container--narrow">
    <?= section_header('FAQ', 'Common questions', 'Short answers to what people usually ask first. Anything else, just send me a message.', ['id' => 'faq-heading', 'align' => 'left']) ?>
    <?= faq_list($faqs) ?>
    <p class="section-foot">More questions? <a href="/faq">See all FAQs</a> or <a href="/contact">contact me</a>.</p>
  </div>
</section>
<?php endif; ?>

<?php $showcase = $home['showcase'] ?? content_default('home')['showcase']; $slides = array_values(array_filter((array) ($showcase['slides'] ?? []), static fn ($s) => trim((string) ($s['title'] ?? '')) !== '')); ?>
<?php if ($show('showcase') && $slides): $n = count($slides); ?>
<!-- Slider -->
<section class="section showcase" aria-labelledby="showcase-heading">
  <div class="container">
    <div class="showcase__head">
      <?= section_header((string) ($showcase['eyebrow'] ?? ''), (string) ($showcase['heading'] ?? ''), (string) ($showcase['text'] ?? ''), ['align' => 'left', 'id' => 'showcase-heading']) ?>
      <div class="showcase__arrows">
        <button type="button" class="showcase__arrow" data-slider-prev aria-label="Previous slide"><?= icon('arrow-left', 'icon') ?></button>
        <button type="button" class="showcase__arrow" data-slider-next aria-label="Next slide"><?= icon('arrow-right', 'icon') ?></button>
      </div>
    </div>
    <div class="showcase__slider" data-slider aria-roledescription="carousel" aria-label="<?= e((string) ($showcase['heading'] ?? 'Services')) ?>">
      <div class="showcase__viewport">
        <div class="showcase__track" data-slider-track>
          <?php foreach ($slides as $i => $sl): ?>
            <article class="showcase__slide<?= $i === 0 ? ' is-active' : '' ?>" role="group" aria-roledescription="slide" aria-label="<?= $i + 1 ?> of <?= $n ?>" data-slide>
              <div class="media-frame showcase__media"><?= photo(ltrim((string) ($sl['photo'] ?? ''), '/') ?: 'services', '(min-width: 1024px) 1200px, 100vw') ?></div>
              <div class="showcase__body">
                <?php if (!empty($sl['tag'])): ?><span class="showcase__tag"><?php if (!empty($sl['icon'])): ?><?= icon((string) $sl['icon'], 'icon icon-xs') ?><?php endif; ?><?= e((string) $sl['tag']) ?></span><?php endif; ?>
                <h3 class="showcase__title"><?= e((string) $sl['title']) ?></h3>
                <?php if (!empty($sl['text'])): ?><p class="showcase__text"><?= e((string) $sl['text']) ?></p><?php endif; ?>
                <?php if (!empty($sl['label']) && !empty($sl['path'])): ?><?= button((string) $sl['label'], (string) $sl['path'], 'primary', 'arrow-right') ?><?php endif; ?>
              </div>
              <span class="showcase__count" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?> / <?= str_pad((string) $n, 2, '0', STR_PAD_LEFT) ?></span>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="showcase__controls">
        <div class="showcase__dots">
          <?php foreach ($slides as $i => $sl): ?>
            <button type="button" class="showcase__dot<?= $i === 0 ? ' is-active' : '' ?>" data-slider-dot="<?= $i ?>" aria-label="Go to slide <?= $i + 1 ?>: <?= e((string) $sl['title']) ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>><span></span></button>
          <?php endforeach; ?>
        </div>
        <button type="button" class="showcase__pause" data-slider-pause aria-label="Pause slides"><?= icon('pause', 'icon icon-sm') ?><?= icon('play', 'icon icon-sm') ?></button>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- 13. Final CTA -->
<?= cta_banner() ?>

<?php require INC . '/layout/footer.php'; ?>
