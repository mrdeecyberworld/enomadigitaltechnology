<?php
/**
 * Create (or reset) the admin login from cPanel → Terminal:
 *   php ~/public_html/includes/cli/create-admin.php            (username: admin)
 *   php ~/public_html/includes/cli/create-admin.php myname     (choose the username)
 *   php ~/public_html/includes/cli/create-admin.php myname --reset   (replace an existing login)
 * A strong random password is generated on your server and shown only here.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}
require dirname(__DIR__, 2) . '/admin/includes/admin.php'; // loads the site too

$args = array_slice($argv, 1);
$reset = in_array('--reset', $args, true);
$names = array_values(array_filter($args, static fn ($a) => $a !== '--reset'));
$username = $names[0] ?? 'admin';

if (!preg_match('/^[A-Za-z0-9._@-]{3,60}$/', $username)) {
    fwrite(STDERR, "Usernames can use letters, numbers and . _ @ - (3 to 60 characters).\n");
    exit(1);
}
if (admin_is_setup() && !$reset) {
    $current = (string) (admin_user()['username'] ?? '');
    echo "An admin login already exists (username: $current).\n";
    echo "To replace it with a new one, run the same command with --reset at the end.\n";
    exit(1);
}

// 4 groups of 4 characters, without look-alikes (0/O, 1/l/I).
$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
$groups = [];
for ($g = 0; $g < 4; $g++) {
    $part = '';
    for ($i = 0; $i < 4; $i++) {
        $part .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    $groups[] = $part;
}
$password = implode('-', $groups);

$saved = json_write(admin_users_file(), [
    'username'      => $username,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'created_at'    => date('c'),
]);
if (!$saved) {
    fwrite(STDERR, "Could not save the login. Check that " . storage_display_path('admin') . " is writable.\n");
    exit(1);
}
@unlink(storage_dir('admin') . '/setup-code.txt');
@unlink(storage_dir('admin') . '/login-attempts.json');

$site = rtrim((string) cfg('base_url'), '/');
echo "\n  Admin login created\n";
echo "  ───────────────────────────────\n";
echo "  Address:   $site/admin\n";
echo "  Username:  $username\n";
echo "  Password:  $password\n";
echo "  ───────────────────────────────\n";
echo "  Save these in a password manager now; the password is not stored anywhere readable.\n";
echo "  You can change it any time in Admin → Account.\n\n";
