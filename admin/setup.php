<?php
/**
 * First-run setup: create the admin account.
 * Requires the one-time setup code written to storage/admin/setup-code.txt,
 * which only someone with hosting (File Manager / FTP) access can read.
 */
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';

if (admin_is_setup()) {
    redirect('/admin/login');
}

$codeFile = storage_dir('admin') . '/setup-code.txt';
if (!is_file($codeFile)) {
    file_put_contents($codeFile, strtoupper(bin2hex(random_bytes(4))) . "\n", LOCK_EX);
}
$code = trim((string) file_get_contents($codeFile));
$errors = [];
$username = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (login_attempts() >= 5) {
        $errors[] = 'Too many attempts. Wait 15 minutes and try again.';
    } elseif (!hash_equals($code, strtoupper(trim((string) ($_POST['code'] ?? ''))))) {
        login_attempts(true);
        $errors[] = 'The setup code is not correct.';
    }
    if (!preg_match('/^[A-Za-z0-9._@-]{3,60}$/', $username)) {
        $errors[] = 'Username must be 3–60 characters: letters, numbers, dots, dashes or @.';
    }
    if (mb_strlen($password) < 10) {
        $errors[] = 'Password must be at least 10 characters.';
    } elseif ($password !== ($_POST['password2'] ?? '')) {
        $errors[] = 'The two passwords do not match.';
    }
    if (!$errors) {
        json_write(admin_users_file(), [
            'username'      => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at'    => date('c'),
        ]);
        @unlink($codeFile);
        admin_login($username);
        flash('Your admin account is ready. Welcome!');
        redirect('/admin/');
    }
}

auth_page('Set up your admin account', function () use ($errors, $username) { ?>
  <p class="muted">To prove you own this website, enter the setup code saved on your server at
    <code>storage/admin/setup-code.txt</code>. Open it with your hosting File Manager or FTP.</p>
  <?php foreach ($errors as $err): ?><div class="notice notice--error" role="alert"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post" class="stack">
    <?= csrf_field() ?>
    <div class="field"><label for="code">Setup code</label><input id="code" name="code" required autocomplete="off" spellcheck="false"></div>
    <div class="field"><label for="username">Username</label><input id="username" name="username" value="<?= e($username) ?>" required autocomplete="username"></div>
    <div class="field"><label for="password">Password</label><input type="password" id="password" name="password" required minlength="10" autocomplete="new-password"><p class="hint">At least 10 characters. A passphrase is best.</p></div>
    <div class="field"><label for="password2">Confirm password</label><input type="password" id="password2" name="password2" required minlength="10" autocomplete="new-password"></div>
    <button class="btn btn--primary btn--block" type="submit">Create account</button>
  </form>
<?php });
