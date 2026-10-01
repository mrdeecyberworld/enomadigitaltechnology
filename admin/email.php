<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

$file = storage_dir('content') . '/settings.json';
$f = static fn (string $key, string $label, string $type = 'text', array $extra = []): array => ['key' => $key, 'label' => $label, 'type' => $type] + $extra;
$saved = mail_settings();
$schema = ['type' => 'group', 'fields' => [
    $f('method', 'How should emails be sent?', 'select', ['options' => [
        'none'   => 'Off: only save messages in the admin inbox',
        'smtp'   => 'SMTP (Gmail, Outlook / Microsoft 365, Zoho, your web host’s email)',
        'resend' => 'Resend API (resend.com)',
        'php'    => 'PHP mail() (your web host’s built-in mail)',
    ]]),
    $f('to', 'Send form submissions to', 'text', ['hint' => 'Your inbox. Separate several addresses with commas.']),
    $f('from_email', 'From address', 'email', ['hint' => 'Use an address on your own domain (e.g. no-reply@enomadigitaltech.com). With SMTP, Gmail and Outlook usually require this to be the account you log in with. With Resend, the domain must be verified in Resend.']),
    $f('from_name', 'From name', 'text', ['hint' => 'e.g. Enoma Digital Technologies']),
    $f('smtp', 'SMTP server', 'group', ['hint' => 'Choose a preset to fill in the usual settings, then add your username and password.', 'fields' => [
        $f('host', 'Server address', 'text', ['hint' => 'e.g. smtp.gmail.com, smtp.office365.com, mail.yourdomain.com']),
        $f('port', 'Port', 'text', ['hint' => 'Usually 587 (TLS) or 465 (SSL).']),
        $f('encryption', 'Security', 'select', ['options' => ['tls' => 'TLS / STARTTLS (port 587)', 'ssl' => 'SSL (port 465)', 'none' => 'None (not recommended)']]),
        $f('username', 'Username', 'text', ['hint' => 'Usually your full email address.']),
        $f('password', 'Password', 'password', ['is_set' => ($saved['smtp']['password'] ?? '') !== '', 'hint' => 'For Gmail or Outlook with two-step verification, create an “app password” and use it here.']),
    ]]),
    $f('resend_api_key', 'Resend API key', 'password', ['is_set' => ($saved['resend_api_key'] ?? '') !== '', 'hint' => 'Create one at resend.com → API Keys (starts with “re_”). Verify your domain in Resend first.']),
    $f('auto_reply', 'Send an automatic “we received your message” email to the visitor', 'bool'),
    $f('auto_reply_subject', 'Automatic reply subject'),
    $f('auto_reply_body', 'Automatic reply message', 'textarea', ['rows' => 6, 'hint' => 'Use {name} for the visitor’s name and {company} for your business name.']),
]];

$testResult = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    $all = json_read($file, []);
    $data = field_parse($schema, $_POST['data'] ?? [], (array) ($all['mail'] ?? []));
    $data['to'] = implode(', ', array_filter(array_map('trim', explode(',', (string) $data['to'])), static fn ($a) => filter_var($a, FILTER_VALIDATE_EMAIL)));
    $data['smtp']['port'] = (string) max(1, min(65535, (int) $data['smtp']['port'] ?: 587));
    $all['mail'] = $data;
    if (!json_write($file, $all)) {
        flash('Could not save. Check that the storage folder is writable.', 'error');
        redirect('/admin/email');
    }

    if (($_POST['action'] ?? '') === 'test') {
        $GLOBALS['config']['mail'] = array_replace_recursive((array) cfg('mail', []), $data);
        $m = mail_settings();
        $to = trim((string) ($_POST['test_to'] ?? '')) ?: $m['to'];
        if ($m['method'] === 'none') {
            flash('Choose a sending method first, then send a test.', 'error');
        } else {
            $r = send_mail([
                'to' => $to,
                'subject' => 'Test email from your website',
                'text' => "Good news: email from your website is working.\n\nSent with: " . strtoupper($m['method']) . "\nFrom: {$m['from_name']} <{$m['from_email']}>\nTime: " . date('M j, Y g:i a T'),
            ], $m);
            $r['ok'] ? flash('Settings saved and a test email was sent to ' . $to . '. Check your inbox (and spam folder).') : flash('Settings saved, but the test email failed: ' . $r['error'], 'error');
        }
    } else {
        flash('Email settings saved.');
    }
    redirect('/admin/email');
}

$current = $saved;
$current['smtp']['password'] = '';
$current['resend_api_key'] = '';
admin_header('Email', 'email');
?>
<header class="page-head">
  <div>
    <h1>Email</h1>
    <p class="muted">Choose how your website sends email. Every form submission is always saved in <a href="/admin/messages">Messages</a>; email is an extra copy in your inbox.</p>
  </div>
  <span class="pill pill--<?= mail_enabled() ? 'ok' : 'off' ?>"><?= mail_enabled() ? 'Sending with ' . e(strtoupper($saved['method'])) : 'Email is off' ?></span>
</header>

<form method="post" class="editor" data-editor data-email-form>
  <?= csrf_field() ?>
  <div class="panel presets" data-presets>
    <h2>Quick setup</h2>
    <p class="muted small">Pick your email provider to fill in the server settings.</p>
    <div class="chips">
      <button type="button" class="btn btn--light btn--sm" data-preset='{"method":"smtp","host":"smtp.gmail.com","port":"587","encryption":"tls"}'>Gmail / Google Workspace</button>
      <button type="button" class="btn btn--light btn--sm" data-preset='{"method":"smtp","host":"smtp.office365.com","port":"587","encryption":"tls"}'>Outlook / Microsoft 365</button>
      <button type="button" class="btn btn--light btn--sm" data-preset='{"method":"smtp","host":"smtp.zoho.com","port":"465","encryption":"ssl"}'>Zoho Mail</button>
      <button type="button" class="btn btn--light btn--sm" data-preset='{"method":"smtp","host":"mail.<?= e(preg_replace('/^www\./', '', (string) parse_url((string) cfg('base_url'), PHP_URL_HOST))) ?>","port":"465","encryption":"ssl"}'>cPanel / web host email</button>
      <button type="button" class="btn btn--light btn--sm" data-preset='{"method":"resend"}'>Resend</button>
    </div>
  </div>
  <?= field_render($schema, 'data', $current) ?>

  <div class="panel test-panel">
    <h2>Send a test email</h2>
    <p class="muted small">Saves these settings, then sends a test so you can confirm everything works.</p>
    <div class="test-row">
      <label for="test_to" class="sr-only">Send test to</label>
      <input type="email" id="test_to" name="test_to" placeholder="<?= e($saved['to'] ?: 'you@example.com') ?>">
      <button type="submit" name="action" value="test" class="btn btn--light"><?= icon('send', 'icon icon-sm') ?> Save &amp; send test</button>
    </div>
  </div>

  <div class="savebar">
    <span class="muted small" data-dirty-note>All changes saved.</span>
    <button type="submit" class="btn btn--primary"><?= icon('check', 'icon icon-sm') ?> Save email settings</button>
  </div>
</form>
<?php admin_footer();
