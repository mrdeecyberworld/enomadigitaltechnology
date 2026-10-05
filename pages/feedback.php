<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$path = page_url('feedback');
$crumbs = [['Home', '/'], ['Client Feedback', $path]];
$formId = 'feedback-form';
$state = handle_feedback_form($formId);
$v = $state['values'];
$errors = $state['errors'];
$val = static fn (string $k): string => e((string) ($v[$k] ?? ''));
$invalid = static fn (string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="fb-' . $k . '-error"' : '';
$err = static fn (string $k): string => isset($errors[$k])
    ? '<p class="field__error" id="fb-' . $k . '-error">' . icon('circle-alert', 'icon icon-xs') . e($errors[$k]) . '</p>'
    : '';

$page = [
    'title'       => 'Client Feedback | ' . site('name'),
    'description' => 'Worked with ' . site('name') . '? Share your experience. Every review is read before it appears on the website.',
    'path'        => $path,
    'schema'      => [schema_breadcrumbs($crumbs)],
];
require INC . '/layout/header.php';

echo page_hero('Share Your Experience', [
    'eyebrow' => 'Client feedback',
    'text'    => 'Worked with us? Your honest feedback helps us improve and helps other people decide if we are the right fit.',
    'crumbs'  => $crumbs,
]);
?>

<section class="section section--overlap">
  <div class="container form-layout form-layout--page">
    <aside class="form-layout__intro" aria-labelledby="fb-side-heading">
      <h2 class="side-heading" id="fb-side-heading">How it works</h2>
      <ul class="contact-points contact-points--lg">
        <li><span class="contact-points__icon"><?= icon('message-square-text', 'icon icon-sm') ?></span><span>Tell us how the project or support went, in your own words.</span></li>
        <li><span class="contact-points__icon"><?= icon('shield-check', 'icon icon-sm') ?></span><span>We read every review first. Your email address is never published.</span></li>
        <li><span class="contact-points__icon"><?= icon('badge-check', 'icon icon-sm') ?></span><span>With your permission, we may share your words on our website.</span></li>
      </ul>
      <div class="side-links">
        <p class="side-links__label">Need help instead?</p>
        <a href="<?= e(page_url('contact')) ?>">Contact Us<?= icon('arrow-right', 'icon icon-xs') ?></a>
      </div>
    </aside>

    <div class="form-card form-card--raised">
      <h2 class="form-card__title">Leave a review</h2>
      <form class="form" id="<?= e($formId) ?>" method="post" action="<?= e(current_path()) ?>#<?= e($formId) ?>" novalidate data-validate>
        <?php if ($state['status']): ?>
          <div class="form-status form-status--<?= e($state['status']) ?>" role="<?= $state['status'] === 'error' ? 'alert' : 'status' ?>" tabindex="-1" data-focus-on-load>
            <?= icon($state['status'] === 'success' ? 'circle-check-big' : 'circle-alert', 'icon') ?>
            <p><?= e($state['message']) ?></p>
          </div>
        <?php endif; ?>

        <input type="hidden" name="form_id" value="<?= e($formId) ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="started" value="<?= time() ?>">
        <div class="hp-field" aria-hidden="true">
          <label for="website-fb">Leave this field empty</label>
          <input type="text" id="website-fb" name="website" tabindex="-1" autocomplete="off">
        </div>

        <p class="form__required-note">Fields marked <span aria-hidden="true">*</span><span class="sr-only">with an asterisk</span> are required.</p>

        <fieldset class="rating-input"<?= $invalid('rating') ?>>
          <legend>Your rating <span class="req" aria-hidden="true">*</span></legend>
          <div class="rating-input__stars">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" id="fb-rating-<?= $i ?>" name="rating" value="<?= $i ?>"<?= (string) ($v['rating'] ?? '') === (string) $i ? ' checked' : '' ?>>
              <label for="fb-rating-<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>"><span aria-hidden="true">★</span><span class="sr-only"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></span></label>
            <?php endfor; ?>
          </div>
          <?= $err('rating') ?>
        </fieldset>

        <div class="form__grid">
          <div class="field field--full">
            <label for="fb-quote">Your feedback <span class="req" aria-hidden="true">*</span></label>
            <textarea id="fb-quote" name="quote" rows="5" required minlength="20" maxlength="1500"<?= $invalid('quote') ?>><?= $val('quote') ?></textarea>
            <?= $err('quote') ?>
          </div>
          <div class="field">
            <label for="fb-name">Name <span class="req" aria-hidden="true">*</span></label>
            <input type="text" id="fb-name" name="name" autocomplete="name" required maxlength="100" value="<?= $val('name') ?>"<?= $invalid('name') ?>>
            <?= $err('name') ?>
          </div>
          <div class="field">
            <label for="fb-email">Email <span class="req" aria-hidden="true">*</span> <span class="optional">(never published)</span></label>
            <input type="email" id="fb-email" name="email" autocomplete="email" required maxlength="254" value="<?= $val('email') ?>"<?= $invalid('email') ?>>
            <?= $err('email') ?>
          </div>
          <div class="field">
            <label for="fb-role">Role and company <span class="optional">(optional)</span></label>
            <input type="text" id="fb-role" name="role" autocomplete="organization-title" maxlength="120" placeholder="e.g. Owner, Bright Bakery" value="<?= $val('role') ?>">
          </div>
          <div class="field">
            <label for="fb-service">Service you used <span class="optional">(optional)</span></label>
            <div class="select-wrap">
              <select id="fb-service" name="service"<?= $invalid('service') ?>>
                <option value="">Choose a service</option>
                <?php foreach (array_diff_key(service_options(), ['not-sure' => 1]) as $slug => $label): ?>
                  <option value="<?= e($slug) ?>"<?= ($v['service'] ?? '') === $slug ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
              <?= icon('chevron-down', 'icon icon-sm select-wrap__icon') ?>
            </div>
            <?= $err('service') ?>
          </div>
          <div class="field field--full">
            <label class="check">
              <input type="checkbox" name="consent" value="1"<?= !empty($v['consent']) ? ' checked' : '' ?>>
              <span>You may publish my feedback, name and role on the <?= e(site('name')) ?> website.</span>
            </label>
          </div>
        </div>

        <div class="form__footer">
          <button type="submit" class="btn btn-primary btn-lg"><span>Send Feedback</span><?= icon('send', 'icon btn-icon') ?></button>
          <p class="form__privacy">We use your email only to follow up on your feedback. See our <a href="<?= e(page_url('privacy')) ?>">Privacy Policy</a>.</p>
        </div>
      </form>
    </div>
  </div>
</section>

<?php require INC . '/layout/footer.php'; ?>
