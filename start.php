<?php
/**
 * Run the website on your own computer.
 *
 *   php start.php            (or double-click start-windows.bat / start-mac.command)
 *
 * Checks your PHP, turns on the extensions the site needs, starts PHP's
 * built-in web server on a free port and opens your browser.
 * Press Ctrl+C in this window to stop the server.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Run this from a terminal: php start.php');
}
chdir(__DIR__);

$line = str_repeat('─', 60);
echo "\n$line\n  Enoma Digital Technologies · local website\n$line\n";

if (PHP_VERSION_ID < 80100) {
    echo "\n  Your PHP is version " . PHP_VERSION . ". This site needs PHP 8.1 or newer.\n  See LOCAL-SETUP.md for how to install a newer PHP.\n\n";
    exit(1);
}

// Extensions the site uses. Missing ones are switched on for this run.
$needed = ['mbstring' => 'required', 'openssl' => 'email & HTTPS', 'curl' => 'AI assistant, Resend & news', 'gd' => 'image resizing', 'fileinfo' => 'uploads', 'exif' => 'photo rotation'];
$flags = [];
$missing = array_keys(array_filter($needed, static fn ($why, $ext) => !extension_loaded($ext), ARRAY_FILTER_USE_BOTH));
if ($missing) {
    $extDir = dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . 'ext';
    if (is_dir($extDir)) {
        $flags[] = '-d ' . escapeshellarg('extension_dir=' . $extDir);
    }
    foreach ($missing as $ext) {
        $flags[] = '-d extension=' . $ext;
    }
}
// Check that the extensions really load with those flags.
$check = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . implode(' ', $flags) . ' -r ' . escapeshellarg('echo json_encode(array_values(array_filter(' . var_export(array_keys($needed), true) . ', fn($e) => !extension_loaded($e))));') . ' 2>&1');
$stillMissing = json_decode(trim((string) preg_replace('/^.*?(\[.*\])\s*$/s', '$1', (string) $check)), true) ?? [];
if (in_array('mbstring', $stillMissing, true)) {
    echo "\n  PHP is missing the \"mbstring\" extension, which the site needs.\n  See LOCAL-SETUP.md (\"Missing extensions\") for a 1-minute fix.\n\n";
    exit(1);
}
foreach ($stillMissing as $ext) {
    echo "  Note: PHP extension \"$ext\" isn't available, so " . $needed[$ext] . " won't work locally.\n";
}

// Larger uploads, like on the real site.
$flags[] = '-d upload_max_filesize=25M';
$flags[] = '-d post_max_size=30M';
$flags[] = '-d memory_limit=256M';

// Find a free port.
$port = 0;
foreach (range(8000, 8020) as $p) {
    $sock = @fsockopen('127.0.0.1', $p, $errno, $errstr, 0.2);
    if ($sock) {
        fclose($sock);
        continue;
    }
    $port = $p;
    break;
}
if (!$port) {
    echo "\n  Ports 8000–8020 are all busy. Close other local servers and try again.\n\n";
    exit(1);
}
$url = "http://localhost:$port";
putenv("ENOMA_BASE_URL=$url");

// Make sure the writable folders exist.
foreach (['storage', 'assets/uploads'] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

// Admin setup code (first run only).
$usersFile = __DIR__ . '/storage/admin/users.json';
$codeFile = __DIR__ . '/storage/admin/setup-code.txt';
$adminNote = "  Admin:    $url/admin";
if (!is_file($usersFile)) {
    if (!is_file($codeFile)) {
        @mkdir(dirname($codeFile), 0750, true);
        file_put_contents($codeFile, strtoupper(bin2hex(random_bytes(4))) . "\n");
    }
    $adminNote .= "\n  Setup code for your first admin login: " . trim((string) file_get_contents($codeFile));
}

echo "\n  Website:  $url\n$adminNote\n\n  Leave this window open while you use the site.\n  Press Ctrl+C to stop the server.\n$line\n\n";

// Open the browser shortly after the server starts.
$open = match (PHP_OS_FAMILY) {
    'Windows' => 'start "" ' . escapeshellarg($url),
    'Darwin'  => 'open ' . escapeshellarg($url),
    default   => 'xdg-open ' . escapeshellarg($url) . ' >/dev/null 2>&1',
};
if (getenv('ENOMA_NO_BROWSER') !== '1') {
    PHP_OS_FAMILY === 'Windows'
        ? pclose(popen('cmd /c "timeout /t 1 >nul & ' . $open . '"', 'r'))
        : exec('(sleep 1; ' . $open . ') > /dev/null 2>&1 &');
}

passthru(escapeshellarg(PHP_BINARY) . ' ' . implode(' ', $flags) . ' -S ' . escapeshellarg("localhost:$port") . ' router.php', $exit);
exit((int) $exit);
