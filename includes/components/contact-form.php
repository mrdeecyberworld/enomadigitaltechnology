<?php
/**
 * Contact / quote / consultation / service inquiry form.
 *
 * Usage on a page (before any output):
 *   $formState = handle_form('quote-form', 'quote');
 * Then in the markup:
 *   echo contact_form('quote-form', 'quote', $formState);
 */

declare(strict_types=1);

function contact_form(string $formId, string $type, array $state, array $opts = []): string
{
    $v = $state['values'] ?? [];
    $errors = $state['errors'] ?? [];
    $status = $state['status'] ?? null;
    $showBudget = $opts['budget'] ?? in_array($type, ['quote', 'inquiry', 'consultation'], true);
    $submit = $opts['submit'] ?? match ($type) {
        'quote'        => 'Request a Quote',
        'consultation' => 'Request a Consultation',
        'inquiry'      => 'Send Inquiry',
        default        => 'Send Message',
    };
    $messageLabel = $opts['message_label'] ?? match ($type) {
        'quote'        => 'Tell us about your project',
        'consultation' => 'What would you like to discuss?',
        default        => 'How can we help?',
    };
    $val = static fn (string $k): string => e($v[$k] ?? '');
    $invalid = static fn (string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="' . $k . '-' . $formId . '-error"' : '';
    $err = static fn (string $k): string => isset($errors[$k])
        ? '<p class="field__error" id="' . $k . '-' . $formId . '-error">' . icon('circle-alert', 'icon icon-xs') . e($errors[$k]) . '</p>'
        : '';
    $fid = static fn (string $k): string => $k . '-' . $formId;

    ob_start(); ?>
    <form class="form" id="<?= e($formId) ?>" method="post" action="<?= e(current_path()) ?>#<?= e($formId) ?>" novalidate data-validate>
      <?php if ($status): ?>
        <div class="form-status form-status--<?= e($status) ?>" role="<?= $status === 'error' ? 'alert' : 'status' ?>" tabindex="-1" data-focus-on-load>
          <?= icon($status === 'success' ? 'circle-check-big' : 'circle-alert', 'icon') ?>
          <p><?= e($state['message']) ?></p>
        </div>
      <?php endif; ?>

      <?php if (cfg('forms.delivery') !== 'mail' || !cfg('forms.to')): ?>
        <?= setup_notice('Submissions are saved to Admin → Messages. To also receive them by email, turn on email delivery in Admin → Settings.') ?>
      <?php endif; ?>

      <input type="hidden" name="form_id" value="<?= e($formId) ?>">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="started" value="<?= time() ?>">
      <div class="hp-field" aria-hidden="true">
        <label for="website-<?= e($formId) ?>">Leave this field empty</label>
        <input type="text" id="website-<?= e($formId) ?>" name="website" tabindex="-1" autocomplete="off">
      </div>

      <p class="form__required-note">Fields marked <span aria-hidden="true">*</span><span class="sr-only">with an asterisk</span> are required.</p>

      <div class="form__grid">
        <div class="field">
          <label for="<?= $fid('name') ?>">Name <span class="req" aria-hidden="true">*</span></label>
          <input type="text" id="<?= $fid('name') ?>" name="name" autocomplete="name" required maxlength="100" value="<?= $val('name') ?>"<?= $invalid('name') ?>>
          <?= $err('name') ?>
        </div>
        <div class="field">
          <label for="<?= $fid('email') ?>">Email <span class="req" aria-hidden="true">*</span></label>
          <input type="email" id="<?= $fid('email') ?>" name="email" autocomplete="email" required maxlength="254" value="<?= $val('email') ?>"<?= $invalid('email') ?>>
          <?= $err('email') ?>
        </div>
        <div class="field">
          <label for="<?= $fid('phone') ?>">Phone <span class="optional">(optional)</span></label>
          <input type="tel" id="<?= $fid('phone') ?>" name="phone" autocomplete="tel" maxlength="30" value="<?= $val('phone') ?>"<?= $invalid('phone') ?>>
          <?= $err('phone') ?>
        </div>
        <div class="field">
          <label for="<?= $fid('company') ?>">Company or organization <span class="optional">(optional)</span></label>
          <input type="text" id="<?= $fid('company') ?>" name="company" autocomplete="organization" maxlength="120" value="<?= $val('company') ?>">
        </div>
        <div class="field">
          <label for="<?= $fid('service') ?>">Service <span class="req" aria-hidden="true">*</span></label>
          <div class="select-wrap">
            <select id="<?= $fid('service') ?>" name="service" required<?= $invalid('service') ?>>
              <option value="">Choose a service</option>
              <?php foreach (service_options() as $slug => $label): ?>
                <option value="<?= e($slug) ?>"<?= ($v['service'] ?? '') === $slug ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
            <?= icon('chevron-down', 'icon icon-sm select-wrap__icon') ?>
          </div>
          <?= $err('service') ?>
        </div>
        <?php if ($showBudget): ?>
        <div class="field">
          <label for="<?= $fid('budget') ?>">Budget range <span class="optional">(optional)</span></label>
          <div class="select-wrap">
            <select id="<?= $fid('budget') ?>" name="budget"<?= $invalid('budget') ?>>
              <option value="">Select a range</option>
              <?php foreach (BUDGET_RANGES as $range): ?>
                <option value="<?= e($range) ?>"<?= ($v['budget'] ?? '') === $range ? ' selected' : '' ?>><?= e($range) ?></option>
              <?php endforeach; ?>
            </select>
            <?= icon('chevron-down', 'icon icon-sm select-wrap__icon') ?>
          </div>
          <?= $err('budget') ?>
        </div>
        <?php endif; ?>
        <div class="field field--full">
          <label for="<?= $fid('message') ?>"><?= e($messageLabel) ?> <span class="req" aria-hidden="true">*</span></label>
          <textarea id="<?= $fid('message') ?>" name="message" rows="5" required minlength="10" maxlength="5000"<?= $invalid('message') ?>><?= $val('message') ?></textarea>
          <?= $err('message') ?>
        </div>
      </div>

      <div class="form__footer">
        <button type="submit" class="btn btn-primary btn-lg"><span><?= e($submit) ?></span><?= icon('send', 'icon btn-icon') ?></button>
        <p class="form__privacy">We use your details only to respond to your request. See our <a href="/privacy-policy">Privacy Policy</a>.</p>
      </div>
    </form>
    <?php
    return (string) ob_get_clean();
}
