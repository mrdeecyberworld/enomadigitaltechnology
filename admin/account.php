<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

$user = admin_user();
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    $username = trim((string) ($_POST['username'] ?? ''));
    $current = (string) ($_POST['current'] ?? '');
    $new = (string) ($_POST['password'] ?? '');
    if (!password_verify($current, $user['password_hash'])) {
        flash('Your current password is not correct.', 'error');
    } elseif (!preg_match('/^[A-Za-z0-9._@-]{3,60}$/', $username)) {
        flash('Username must be 3–60 characters: letters, numbers, dots, dashes or @.', 'error');
    } elseif ($new !== '' && mb_strlen($new) < 10) {
        flash('New password must be at least 10 characters.', 'error');
    } elseif ($new !== '' && $new !== ($_POST['password2'] ?? '')) {
        flash('The new passwords do not match.', 'error');
    } else {
        $user['username'] = $username;
        if ($new !== '') {
            $user['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
        }
        json_write(admin_users_file(), $user);
        admin_login($username);
        flash('Account updated.');
    }
    redirect('/admin/account');
}

admin_header('Account', 'account');
?>
<header class="page-head"><div><h1>Account</h1><p class="muted">Change your admin username or password.</p></div></header>
<form method="post" class="panel stack narrow">
  <?= csrf_field() ?>
  <div class="field"><label for="username">Username</label><input id="username" name="username" value="<?= e($user['username']) ?>" required autocomplete="username"></div>
  <div class="field"><label for="password">New password</label><input type="password" id="password" name="password" minlength="10" autocomplete="new-password"><p class="hint">Leave blank to keep your current password.</p></div>
  <div class="field"><label for="password2">Confirm new password</label><input type="password" id="password2" name="password2" minlength="10" autocomplete="new-password"></div>
  <div class="field"><label for="current">Current password</label><input type="password" id="current" name="current" required autocomplete="current-password"><p class="hint">Required to save any change.</p></div>
  <button class="btn btn--primary" type="submit">Save account</button>
</form>
<?php admin_footer();
