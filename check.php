<?php
/**
 * Website health check: open https://yourdomain.com/check.php to see whether
 * the server is set up correctly for this website. Shows no private data.
 * Deliberately plain PHP with no dependencies, so it works even when the rest
 * of the site doesn't.
 */
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

$here = __DIR__;
$docRoot = rtrim((string) realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$perm = static fn (string $f): string => file_exists($f) ? substr(sprintf('%o', fileperms($f)), -3) : '—';
$ht = $here . '/.htaccess';
$htText = is_readable($ht) ? (string) file_get_contents($ht) : '';
$private = dirname($here) . '/enoma-storage';
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$host = preg_replace('/[^A-Za-z0-9.:-]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));

// Ask this same server for a clean address that only works when .htaccess rewrites are active.
$rewrite = 'not tested';
$url = ($https ? 'https' : 'http') . '://' . $host . '/enoma-rewrite-test';
if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_FOLLOWLOCATION => true]);
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $rewrite = trim($body) === 'rewrite-ok' ? 'yes' : ($code === 0 ? 'could not test automatically: use the link below' : 'no (HTTP ' . $code . ')');
}
$modules = function_exists('apache_get_modules') ? apache_get_modules() : null;

// Admin login status (no names or passwords are shown).
$users = is_file($private . '/admin/users.json') ? json_decode((string) file_get_contents($private . '/admin/users.json'), true) : null;
$adminState = is_array($users) && !empty($users['password_hash'])
    ? (!empty($users['must_change_password']) ? 'exists (temporary password: you will be asked to choose a new one)' : 'exists')
    : (is_file($private . '/admin/setup-code.txt') ? 'not created yet (setup code is waiting)' : 'not created yet');

// Can PHP keep visitors signed in? Saves a counter in a session; reload to see it go up.
$savePath = (string) session_save_path();
$saveDir = str_contains($savePath, ';') ? substr($savePath, strrpos($savePath, ';') + 1) : $savePath;
$saveDirOk = $saveDir !== '' && is_dir($saveDir) && is_writable($saveDir);
session_name('enoma_check');
@session_start();
$_SESSION['n'] = (int) ($_SESSION['n'] ?? 0) + 1;
$visits = $_SESSION['n'];
session_write_close();

$rewriteOk = $rewrite === 'yes' ? true : (str_starts_with($rewrite, 'no') ? false : null);
$rows = [
    ['PHP is running', 'yes', true],
    ['PHP version', PHP_VERSION, version_compare(PHP_VERSION, '8.1.0', '>=')],
    ['Web server', (string) ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown'), null],
    ['PHP mode', PHP_SAPI, null],
    ['Website folder', $here, null],
    ['Domain points to this folder', $docRoot === $here ? 'yes' : 'NO: the domain uses ' . ($docRoot ?: 'unknown'), $docRoot === $here],
    ['.htaccess present', file_exists($ht) ? 'yes (permissions ' . $perm($ht) . ')' : 'NO', file_exists($ht)],
    ['.htaccess is the website\'s own', str_contains($htText, 'Enoma') ? 'yes' : 'NO', str_contains($htText, 'Enoma')],
    ['Clean page addresses work (.htaccess active)', $rewrite, $rewriteOk],
    ['mod_rewrite listed', $modules === null ? 'not visible in this PHP mode' : (in_array('mod_rewrite', $modules, true) ? 'yes' : 'NO'), $modules === null ? null : in_array('mod_rewrite', $modules, true)],
    ['index.php permissions', $perm($here . '/index.php'), in_array($perm($here . '/index.php'), ['644', '664', '640', '755'], true)],
    ['Private data folder (enoma-storage)', is_dir($private) ? (is_writable($private) ? 'yes, writable' : 'yes, NOT writable') : 'not created (using public_html/storage)', is_dir($private) ? is_writable($private) : null],
    ['Uploads folder writable', is_writable($here . '/assets/uploads') ? 'yes' : 'NO', is_writable($here . '/assets/uploads')],
    ['Extensions', implode(', ', array_map(static fn ($e) => $e . (extension_loaded($e) ? ' ✓' : ' ✗'), ['mbstring', 'gd', 'curl', 'openssl', 'fileinfo', 'exif', 'pdo_mysql'])), extension_loaded('mbstring')],
    ['HTTPS', $https ? 'yes' : 'no', $https ? true : null],
    ['Admin login', $adminState, is_array($users) ? true : null],
    ['PHP session folder writable', $saveDirOk ? 'yes' : 'NO (' . ($saveDir ?: 'not set') . '): the website uses its own folder instead', $saveDirOk ? true : null],
    ['Sign-in test (reload this page)', 'count: ' . $visits . ($visits > 1 ? ', sign-ins are kept ✓' : ', reload: it should become 2'), $visits > 1 ? true : null],
];
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Website check</title>
<style>body{font:15px/1.5 system-ui,sans-serif;margin:0;padding:24px 16px;background:#0b1322;color:#e9eef7}main{max-width:820px;margin:0 auto}h1{font-size:1.4rem}table{width:100%;border-collapse:collapse;background:#111c30;border-radius:12px;overflow:hidden}td{padding:10px 12px;border-top:1px solid #1f2d47;vertical-align:top;overflow-wrap:anywhere}td:first-child{color:#a9b6ca;width:38%}.ok{color:#5fd6a4}.bad{color:#ff9b91;font-weight:600}p{color:#a9b6ca}</style></head>
<body><main><h1>Website check</h1><p>Take a screenshot of this page and send it to your developer. It shows no passwords or private data.</p>
<table><?php foreach ($rows as [$label, $value, $ok]): ?><tr><td><?= htmlspecialchars($label) ?></td><td class="<?= $ok === true ? 'ok' : ($ok === false ? 'bad' : '') ?>"><?= htmlspecialchars((string) $value) ?></td></tr><?php endforeach; ?></table>
<p>Manual test for clean page addresses: <a href="/enoma-rewrite-test" style="color:#8fb4ff">open this link</a>. It should show only the word <code>rewrite-ok</code>.</p>
<p>Checked <?= htmlspecialchars(gmdate('Y-m-d H:i')) ?> UTC.</p></main></body></html>
