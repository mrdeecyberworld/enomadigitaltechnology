<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$home = content('home');
$f = cfg('founder', []);
$crumbs = [['Home', '/'], ['About', '/about']];

// Founder profile from the CV (Admin → About me). Only filled-in parts are shown.
$profile = content('profile');
$filled = static fn ($rows, string $key) => array_values(array_filter((array) $rows, static fn ($r) => is_array($r) && trim((string) ($r[$key] ?? '')) !== ''));
$experience = $filled($profile['experience'] ?? [], 'role');
$education = $filled($profile['education'] ?? [], 'qualification');
$certifications = array_values(array_filter(array_map('trim', (array) ($profile['certifications'] ?? []))));
$skills = array_values(array_filter(array_map('trim', (array) ($profile['skills'] ?? []))));
$story = trim((string) ($profile['story'] ?? ''));
$inspiration = trim((string) ($profile['inspiration'] ?? ''));
$quote = trim((string) ($profile['quote'] ?? ''));
$hasCv = $experience || $education || $certifications || $skills;
$person = null;
if (founder_ready()) {
    $person = array_filter([
        '@type'     => 'Person',
        'name'      => $f['name'],
        'jobTitle'  => ($f['title'] ?? '') ?: 'Founder',
        'worksFor'  => ['@id' => abs_url('/#organization')],
        'knowsAbout' => $skills ?: null,
        'alumniOf'  => array_values(array_filter(array_map(static fn ($e) => trim((string) ($e['institution'] ?? '')) !== '' ? ['@type' => 'EducationalOrganization', 'name' => $e['institution']] : null, $education))) ?: null,
        'hasCredential' => $certifications ? array_map(static fn ($c) => ['@type' => 'EducationalOccupationalCredential', 'name' => $c], $certifications) : null,
    ]);
}

$page = [
    'title'       => page_text('about', 'meta_title', 'About | ' . site('name')),
    'description' => page_text('about', 'meta_description', site('description')),
    'path'        => '/about',
    'schema'      => array_values(array_filter([schema_breadcrumbs($crumbs), ['@type' => 'AboutPage', 'url' => abs_url('/about'), 'name' => 'About ' . site('name'), 'about' => ['@id' => abs_url('/#organization')]], $person])),
];
require INC . '/layout/header.php';

echo page_hero(page_text('about', 'heading', 'About'), [
    'eyebrow' => page_text('about', 'eyebrow'),
    'text'    => page_text('about', 'intro'),
    'crumbs'  => $crumbs,
    'image'   => 'about',
]);
?>

<section class="section" aria-labelledby="mission-heading">
  <div class="container split">
    <div class="split__content">
      <?= section_header('Our purpose', 'Making technology accessible, practical and secure', null, ['align' => 'left', 'id' => 'mission-heading']) ?>
      <?php foreach ($home['about']['paragraphs'] as $p): ?>
        <p class="prose-p reveal"><?= e($p) ?></p>
      <?php endforeach; ?>
      <?php if (page_text('about', 'body') !== ''): ?><div class="prose-p reveal"><?= simple_format(page_text('about', 'body')) ?></div><?php endif; ?>
    </div>
    <div class="split__media reveal">
      <div class="media-frame media-frame--tall">
        <?= photo('team', '(min-width: 1024px) 45vw, 100vw') ?>
      </div>
    </div>
  </div>
</section>

<section class="section section--dark" aria-labelledby="values-heading">
  <div class="section__bg" aria-hidden="true"></div>
  <div class="container">
    <?= section_header($home['why']['eyebrow'], 'What guides our work', $home['why']['text'], ['id' => 'values-heading']) ?>
    <div class="why__grid why__grid--4">
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

<?php if (founder_ready() || viewer_is_admin()): ?>
<section class="section" aria-labelledby="founder-heading">
  <div class="container split split--center">
    <div class="split__media reveal">
      <?= founder_card() ?>
    </div>
    <div class="split__content">
      <?= section_header('Leadership', 'Meet the founder', null, ['align' => 'left', 'id' => 'founder-heading']) ?>
      <?php if (!empty($f['bio'])): ?>
        <p class="prose-p reveal"><?= e($f['bio']) ?></p>
      <?php else: ?>
        <p class="prose-p reveal"><?= placeholder('Founder biography: add a short, factual introduction, including background, areas of focus and why Enoma Digital Technologies was started.') ?></p>
        <?= setup_notice('Add your name, short bio and photo in Admin → Settings (Founder), and your story and CV in Admin → About me.') ?>
      <?php endif; ?>
      <div class="btn-row">
        <?= button('Book a Consultation', '/book-a-consultation', 'primary', 'calendar-check') ?>
        <?= button('Contact Us', '/contact', 'ghost', 'arrow-right') ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($story !== '' || $inspiration !== '' || $quote !== '' || viewer_is_admin()): ?>
<section class="section section--muted founder-story" aria-label="The founder’s story">
  <div class="container container--narrow">
    <?php if ($story === '' && $inspiration === '' && viewer_is_admin()): ?>
      <?= setup_notice('Add your story and what inspires you, plus your experience, education, certifications and skills from your CV, in Admin → About me. Visitors only see what you fill in.') ?>
    <?php endif; ?>
    <?php if ($story !== ''): ?>
      <h2 class="section-title reveal"><?= e((string) $profile['story_heading'] ?: 'My story') ?></h2>
      <div class="prose founder-story__text reveal"><?= simple_format($story) ?></div>
    <?php endif; ?>
    <?php if ($quote !== ''): ?>
      <figure class="founder-quote reveal">
        <blockquote><p><?= e($quote) ?></p></blockquote>
        <?php if (trim((string) $profile['quote_source']) !== ''): ?><figcaption><?= e((string) $profile['quote_source']) ?></figcaption><?php endif; ?>
      </figure>
    <?php endif; ?>
    <?php if ($inspiration !== ''): ?>
      <h2 class="section-title reveal"><?= e((string) $profile['inspiration_heading'] ?: 'What inspires me') ?></h2>
      <div class="prose founder-story__text reveal"><?= simple_format($inspiration) ?></div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($hasCv): ?>
<section class="section" aria-labelledby="cv-heading">
  <div class="container">
    <?= section_header('Background', 'Experience and qualifications', null, ['id' => 'cv-heading']) ?>
    <div class="cv">
      <?php if ($experience): ?>
        <div class="cv__main">
          <h3 class="cv__heading"><?= icon('briefcase', 'icon icon-sm') ?> Experience</h3>
          <ol class="timeline">
            <?php foreach ($experience as $job): ?>
              <li class="timeline__item reveal">
                <div class="timeline__head">
                  <h4 class="timeline__role"><?= e($job['role']) ?></h4>
                  <?php if (trim((string) ($job['period'] ?? '')) !== ''): ?><span class="timeline__period"><?= e($job['period']) ?></span><?php endif; ?>
                </div>
                <?php if (trim((string) ($job['organization'] ?? '')) !== ''): ?><p class="timeline__org"><?= e($job['organization']) ?></p><?php endif; ?>
                <?php if (trim((string) ($job['summary'] ?? '')) !== ''): ?><p class="timeline__summary"><?= e($job['summary']) ?></p><?php endif; ?>
                <?php $hl = array_values(array_filter(array_map('trim', (array) ($job['highlights'] ?? [])))); ?>
                <?php if ($hl): ?><ul class="timeline__highlights"><?php foreach ($hl as $h): ?><li><?= e($h) ?></li><?php endforeach; ?></ul><?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>
      <?php endif; ?>
      <?php if ($education || $certifications || $skills): ?>
        <aside class="cv__side">
          <?php if ($education): ?>
            <div class="cv__block reveal">
              <h3 class="cv__heading"><?= icon('graduation-cap', 'icon icon-sm') ?> Education</h3>
              <ul class="cv__list">
                <?php foreach ($education as $ed): ?>
                  <li><strong><?= e($ed['qualification']) ?></strong><?php if (trim((string) ($ed['institution'] ?? '')) !== ''): ?><span><?= e($ed['institution']) ?><?= trim((string) ($ed['year'] ?? '')) !== '' ? ' · ' . e($ed['year']) : '' ?></span><?php endif; ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
          <?php if ($certifications): ?>
            <div class="cv__block reveal">
              <h3 class="cv__heading"><?= icon('badge-check', 'icon icon-sm') ?> Certifications</h3>
              <ul class="cv__list"><?php foreach ($certifications as $c): ?><li><?= e($c) ?></li><?php endforeach; ?></ul>
            </div>
          <?php endif; ?>
          <?php if ($skills): ?>
            <div class="cv__block reveal">
              <h3 class="cv__heading"><?= icon('wrench', 'icon icon-sm') ?> Skills</h3>
              <ul class="cv__chips"><?php foreach ($skills as $sk): ?><li><?= e($sk) ?></li><?php endforeach; ?></ul>
            </div>
          <?php endif; ?>
        </aside>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section--muted" aria-labelledby="how-heading">
  <div class="container">
    <?= section_header('How we work', 'Remote-first, clear and collaborative', null, ['id' => 'how-heading']) ?>
    <div class="detail-grid">
      <div class="detail-card reveal"><span class="detail-card__icon"><?= icon('globe', 'icon icon-sm') ?></span><h3 class="detail-card__title">Remote services across the U.S.</h3><p class="detail-card__text">We deliver our services remotely using secure tools, which keeps things convenient and flexible for clients wherever they are.</p></div>
      <div class="detail-card reveal"><span class="detail-card__icon"><?= icon('message-square-text', 'icon icon-sm') ?></span><h3 class="detail-card__title">Plain-language communication</h3><p class="detail-card__text">You will always know what we are doing and why, without jargon.</p></div>
      <div class="detail-card reveal"><span class="detail-card__icon"><?= icon('lock', 'icon icon-sm') ?></span><h3 class="detail-card__title">Respect for your data</h3><p class="detail-card__text">Remote access happens only with your permission, and we only request the access a task requires.</p></div>
    </div>
  </div>
</section>

<?= testimonials_section() ?>

<?= cta_banner() ?>

<?php require INC . '/layout/footer.php'; ?>
