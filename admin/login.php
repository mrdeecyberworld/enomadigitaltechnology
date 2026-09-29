<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';

if (!admin_is_setup()) {
    redirect('/admin/setup');
}
if (admin_logged_in()) {
    redirect('/admin/');
}

$error = '';
$username = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    $username = trim((string) ($_POST['username'] ?? ''));
    $user = admin_user();
    if (login_attempts() >= 5) {
        $error = 'Too many failed attempts. Wait 15 minutes and try again.';
    } elseif (hash_equals((string) $user['username'], $username) && password_verify((string) ($_POST['password'] ?? ''), (string) $user['password_hash'])) {
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $user['password_hash'] = password_hash((string) $_POST['password'], PASSWORD_DEFAULT);
            json_write(admin_users_file(), $user);
        }
        admin_login($username);
        redirect('/admin/');
    } else {
        login_attempts(true);
        $error = 'That username and password don’t match.';
    }
}

auth_page('Log in', function () use ($error, $username) { ?>
  <?php if ($error): ?><div class="notice notice--error" role="alert"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="stack">
    <?= csrf_field() ?>
    <div class="field"><label for="username">Username</label><input id="username" name="username" value="<?= e($username) ?>" required autocomplete="username" autofocus></div>
    <div class="field"><label for="password">Password</label><input type="password" id="password" name="password" required autocomplete="current-password"></div>
    <button class="btn btn--primary btn--block" type="submit">Log in</button>
  </form>
  <p class="muted small">Forgot your password? Delete <code>storage/admin/users.json</code> with your hosting File Manager, then visit this page again to set up a new account.</p>
<?php });
