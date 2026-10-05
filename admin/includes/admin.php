<?php
/**
 * Admin bootstrap: authentication, CSRF, flash messages and layout.
 */

declare(strict_types=1);

require dirname(__DIR__, 2) . '/includes/bootstrap.php';
require __DIR__ . '/schemas.php';
require __DIR__ . '/fields.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

const ADMIN_IDLE_TIMEOUT = 7200; // seconds

function admin_users_file(): string
{
    return storage_dir('admin') . '/users.json';
}

function admin_user(): ?array
{
    return json_read(admin_users_file());
}

function admin_is_setup(): bool
{
    $u = admin_user();
    return is_array($u) && !empty($u['password_hash']);
}

function admin_logged_in(): bool
{
    start_session();
    if (empty($_SESSION['admin_user'])) {
        return false;
    }
    if (time() - (int) ($_SESSION['admin_seen'] ?? 0) > ADMIN_IDLE_TIMEOUT) {
        unset($_SESSION['admin_user']);
        return false;
    }
    $_SESSION['admin_seen'] = time();
    return true;
}

/** Pages call this first. $forPasswordChange lets the Account page open while a temporary password is in use. */
function require_admin(bool $forPasswordChange = false): void
{
    if (!admin_is_setup()) {
        header('Location: /admin/setup');
        exit;
    }
    if (!admin_logged_in()) {
        header('Location: /admin/login');
        exit;
    }
    // A temporary password must be replaced before anything else.
    if (!$forPasswordChange && !empty(admin_user()['must_change_password'])) {
        header('Location: /admin/account');
        exit;
    }
}

function admin_login(string $username): void
{
    start_session();
    session_regenerate_id(true);
    $_SESSION['admin_user'] = $username;
    $_SESSION['admin_seen'] = time();
}

/** Failed-login throttle: 5 failures per 15 minutes per IP. */
function login_attempts(bool $record = false): int
{
    $file = storage_dir('admin') . '/login-attempts.json';
    $key = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $all = json_read($file, []);
    $now = time();
    $mine = array_values(array_filter($all[$key] ?? [], static fn ($t) => $t > $now - 900));
    if ($record) {
        $mine[] = $now;
    }
    $all[$key] = $mine;
    if ($record) {
        json_write($file, $all);
    }
    return count($mine);
}

function admin_csrf_check(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !csrf_valid($_POST['csrf'] ?? null)) {
        http_response_code(400);
        exit('Your session expired. Go back, reload the page and try again.');
    }
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function flash(string $message, string $type = 'success'): void
{
    start_session();
    $_SESSION['flash'][] = [$type, $message];
}

function flashes(): string
{
    start_session();
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as [$type, $message]) {
        $html .= '<div class="notice notice--' . e($type) . '" role="' . ($type === 'error' ? 'alert' : 'status') . '">' . e($message) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

function unread_count(): int
{
    $n = 0;
    foreach (store_list('submissions') as $file) {
        $m = json_read($file, []);
        if (empty($m['read'])) {
            $n++;
        }
    }
    return $n;
}

function admin_header(string $title, string $active = ''): void
{
    $unread = unread_count();
    $nav = [
        ['Dashboard', '/admin/', 'dashboard', 'layout-template'],
        ['Messages', '/admin/messages', 'messages', 'mail'],
        ['Blog posts', '/admin/blog', 'blog', 'file-text'],
        ['Tech & security news', '/admin/news', 'news', 'sparkles'],
    ];
    $content = [];
    foreach (admin_sections() as $key => $section) {
        if ($key !== 'settings') {
            $content[] = [$section['title'], '/admin/edit?section=' . $key, 'edit:' . $key, $section['icon']];
        }
    }
    $tools = [
        ['Media library', '/admin/media', 'media', 'hard-drive'],
        ['Email', '/admin/email', 'email', 'mail'],
        ['Appearance', '/admin/appearance', 'appearance', 'sparkles'],
        ['Settings', '/admin/edit?section=settings', 'edit:settings', 'wrench'],
        ['Account', '/admin/account', 'account', 'user'],
        ['Backup', '/admin/backup', 'backup', 'database'],
    ];
    $link = static function (array $item) use ($active, $unread): string {
        [$label, $href, $key, $ic] = $item;
        $badge = $key === 'messages' && $unread ? '<span class="badge">' . $unread . '</span>' : '';
        return '<li><a href="' . e($href) . '"' . ($active === $key ? ' aria-current="page"' : '') . '>' . icon($ic, 'icon icon-sm') . '<span>' . e($label) . '</span>' . $badge . '</a></li>';
    };
    ?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> · Enoma Admin</title>
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="<?= e('/admin/assets/admin.css?v=' . filemtime(__DIR__ . '/../assets/admin.css')) ?>">
  <script src="<?= e('/admin/assets/admin.js?v=' . filemtime(__DIR__ . '/../assets/admin.js')) ?>" defer></script>
</head>
<body>
<a class="skip" href="#content">Skip to content</a>
<div class="shell">
  <aside class="sidebar" data-sidebar>
    <div class="sidebar__top">
      <a class="brand" href="/admin/"><?= logo_mark('brand__mark') ?><span><strong>ENOMA</strong><small>Site admin</small></span></a>
      <button type="button" class="menu-btn" data-menu-btn aria-expanded="false" aria-controls="admin-nav">Menu</button>
    </div>
    <nav id="admin-nav" class="sidebar__nav" aria-label="Admin">
      <ul><?= implode('', array_map($link, $nav)) ?></ul>
      <p class="sidebar__label">Content</p>
      <ul><?= implode('', array_map($link, $content)) ?></ul>
      <p class="sidebar__label">Site</p>
      <ul><?= implode('', array_map($link, $tools)) ?></ul>
      <div class="sidebar__foot">
        <a href="/" target="_blank" rel="noopener"><?= icon('arrow-up-right', 'icon icon-sm') ?><span>View website</span></a>
        <form method="post" action="/admin/logout"><?= csrf_field() ?><button type="submit"><?= icon('arrow-right', 'icon icon-sm') ?><span>Log out</span></button></form>
      </div>
    </nav>
  </aside>
  <main id="content" class="main" tabindex="-1">
    <?= flashes() ?>
<?php
}

function admin_footer(): void
{
    ?>
  </main>
</div>
<div hidden data-icon-sprite><?php foreach (icon_names() as $n): ?><span data-name="<?= e($n) ?>"><?= icon($n) ?></span><?php endforeach; ?></div>
<div class="modal" data-media-modal hidden>
  <div class="modal__panel" role="dialog" aria-modal="true" aria-labelledby="media-modal-title">
    <div class="modal__head"><h2 id="media-modal-title">Choose an image</h2><button type="button" class="tool" data-modal-close aria-label="Close"><?= icon('x', 'icon icon-sm') ?></button></div>
    <form class="upload" data-modal-upload enctype="multipart/form-data">
      <?= csrf_field() ?>
      <label class="btn btn--light">Upload new image<input type="file" name="file" accept="image/jpeg,image/png,image/webp,image/gif" hidden data-modal-file></label>
      <span class="hint">JPG, PNG, WebP or GIF, up to 8 MB.</span>
    </form>
    <div class="media-grid" data-modal-grid><p class="hint">Loading…</p></div>
  </div>
</div>
</body>
</html>
<?php
}

/** Simple centered page for login and setup. */
function auth_page(string $title, callable $body): void
{
    ?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> · Enoma Admin</title>
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="<?= e('/admin/assets/admin.css?v=' . filemtime(__DIR__ . '/../assets/admin.css')) ?>">
</head>
<body class="auth">
  <main class="auth__card">
    <div class="auth__brand"><?= logo_mark('brand__mark') ?><span><strong>ENOMA</strong><small>Digital Technologies · Site admin</small></span></div>
    <h1><?= e($title) ?></h1>
    <?= flashes() ?>
    <?php $body(); ?>
  </main>
</body>
</html>
<?php
}
