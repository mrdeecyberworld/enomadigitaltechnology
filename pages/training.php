<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$trainingKey = service_key((string) ($_GET['slug'] ?? 'training'));
$svc = service($trainingKey);
if ($svc === null) {
    require __DIR__ . '/404.php';
    exit;
}
$training = content('training');
$formId = 'training-form';
$formState = handle_form($formId, 'inquiry', $trainingKey);
$crumbs = [['Home', '/'], ['Services', '/services'], [$svc['short_label'] ?: $svc['name'], service_path($trainingKey)]];

$page = [
    'title'       => $svc['meta_title'],
    'description' => $svc['meta_description'],
    'path'        => service_path($trainingKey),
    'schema'      => [schema_service($trainingKey, $svc), schema_breadcrumbs($crumbs), schema_faq($svc['faqs'])],
];
require INC . '/layout/header.php';

echo page_hero('Technology Education for Everyone', [
    'eyebrow' => $svc['eyebrow'],
    'text'    => $svc['intro'],
    'crumbs'  => $crumbs,
    'image'   => 'training',
    'actions' => true,
    'keyword_h1' => true,
]);
?>

<section class="section" aria-labelledby="audiences-heading">
  <div class="container">
    <?= section_header('Who we train', 'Training that meets people where they are', 'Sessions are adapted to each audience, with examples that feel relevant and a pace that builds real confidence.', ['id' => 'audiences-heading']) ?>
    <div class="audience-grid">
      <?php foreach ($training['audiences'] as $a): ?>
        <div class="feature-card feature-card--light reveal">
          <span class="feature-card__icon"><?= icon($a['icon']) ?></span>
          <h3 class="feature-card__title"><?= e($a['label']) ?></h3>
          <p class="feature-card__text"><?= e($a['text']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--dark" aria-labelledby="topics-heading">
  <div class="section__bg" aria-hidden="true"></div>
  <div class="container">
    <?= section_header('Topics', 'What you can learn', 'Popular topics for individuals, teams and classrooms. Programs can combine several topics.', ['id' => 'topics-heading']) ?>
    <ul class="topic-grid topic-grid--dark">
      <?php foreach ($training['topics'] as $t): ?>
        <li class="reveal"><?= icon($t['icon'], 'icon') ?><span><?= e($t['label']) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="section" id="programs" aria-labelledby="programs-heading">
  <div class="container">
    <?= section_header('Training programs', 'Formats that fit your group', 'All programs are delivered live online. Contact us to discuss your audience, goals and schedule.', ['id' => 'programs-heading']) ?>
    <div class="detail-grid detail-grid--2">
      <?php foreach ($training['formats'] as $f): ?>
        <div class="detail-card reveal">
          <span class="detail-card__icon"><?= icon('graduation-cap', 'icon icon-sm') ?></span>
          <h3 class="detail-card__title"><?= e($f['title']) ?></h3>
          <p class="detail-card__text"><?= e($f['text']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <h3 class="mini-heading mini-heading--center">Every program includes</h3>
    <?= check_list(['Plain-language explanations', 'Real-world examples', 'Practical takeaways', 'Time for questions'], 'check-list--inline reveal') ?>
  </div>
</section>

<section class="section section--muted" aria-labelledby="training-faq-heading">
  <div class="container container--narrow">
    <?= section_header('FAQ', 'Training questions', null, ['id' => 'training-faq-heading']) ?>
    <?= faq_list($svc['faqs']) ?>
  </div>
</section>

<section class="section" aria-labelledby="training-form-heading">
  <div class="container form-layout">
    <div class="form-layout__intro">
      <?= section_header('Plan a session', 'Ask about training', 'Tell us who the training is for, roughly how many people, and the topics you are interested in.', ['align' => 'left', 'id' => 'training-form-heading']) ?>
      <ul class="contact-points">
        <li><?= icon('users', 'icon icon-sm') ?><span>Groups, teams, classrooms and individuals</span></li>
        <li><?= icon('globe', 'icon icon-sm') ?><span>Delivered live online across the U.S.</span></li>
      </ul>
    </div>
    <div class="form-card">
      <?= contact_form($formId, 'inquiry', $formState, ['message_label' => 'Who is the training for, and what topics interest you?']) ?>
    </div>
  </div>
</section>

<?= cta_banner('Ready to build confidence with technology?', 'Let’s design a training session that fits your group.') ?>

<?php require INC . '/layout/footer.php'; ?>
