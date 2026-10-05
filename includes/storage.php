<?php
/**
 * Small JSON file store used by the admin panel (no database needed).
 * Everything lives under storage/, which is blocked from the web.
 */

declare(strict_types=1);

function storage_dir(string $sub = ''): string
{
    $dir = dirname(__DIR__) . '/storage' . ($sub !== '' ? '/' . trim($sub, '/') : '');
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    return $dir;
}

/*
 * Optional MySQL database (Admin → Database). When it is connected, every JSON
 * document under storage/ (content edits, messages, blog posts, admin login,
 * settings) is kept in one database table instead of a file, with the same
 * path as its key. Temporary data (cache/, ratelimit/) stays in files.
 * The connection details live in storage/database.json (never in the database).
 */

const DB_CONFIG_FILE = 'database.json';

/** Connection settings, or null when the site uses files. */
function db_config(): ?array
{
    static $cfg = false;
    if ($cfg === false) {
        $file = storage_dir() . '/' . DB_CONFIG_FILE;
        $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
        $cfg = is_array($data) && !empty($data['enabled']) ? $data : null;
    }
    return $cfg;
}

/** PDO connection for $cfg (or the saved settings); creates the table if needed. */
function db_connect(?array $cfg = null): PDO
{
    $cfg ??= db_config();
    if (!$cfg) {
        throw new RuntimeException('No database is configured.');
    }
    $dsn = (string) (getenv('ENOMA_DB_TEST_DSN') ?: '');
    if ($dsn === '') {
        if (!extension_loaded('pdo_mysql')) {
            throw new RuntimeException('PHP on this server is missing the pdo_mysql extension (cPanel → Select PHP Version → Extensions).');
        }
        $dsn = 'mysql:host=' . ($cfg['host'] ?: 'localhost') . (!empty($cfg['port']) ? ';port=' . (int) $cfg['port'] : '') . ';dbname=' . $cfg['name'] . ';charset=utf8mb4';
    }
    $pdo = new PDO($dsn, (string) ($cfg['user'] ?? ''), (string) ($cfg['password'] ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    $table = db_table($cfg);
    $isMysql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
    $pdo->exec('CREATE TABLE IF NOT EXISTS ' . $table . ' (path VARCHAR(191) NOT NULL PRIMARY KEY, data LONGTEXT NOT NULL, updated_at INTEGER NOT NULL)'
        . ($isMysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : ''));
    return $pdo;
}

function db_table(?array $cfg = null): string
{
    $cfg ??= db_config();
    $prefix = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($cfg['prefix'] ?? 'enoma_'));
    return '`' . $prefix . 'store`';
}

/** The shared connection while the database is in use (null when using files). */
function db(): ?PDO
{
    static $pdo = null;
    if ($pdo === null && db_config()) {
        try {
            $pdo = db_connect();
        } catch (Throwable $e) {
            throw new StorageUnavailable($e->getMessage(), 0, $e);
        }
    }
    return $pdo;
}

/** Thrown when the database is configured but cannot be reached. */
class StorageUnavailable extends RuntimeException
{
}

/** Database key for a storage file (path relative to storage/), or null if it stays a file. */
function store_key(string $file): ?string
{
    $root = storage_dir() . '/';
    if (!str_starts_with($file, $root) || !str_ends_with($file, '.json')) {
        return null;
    }
    $rel = substr($file, strlen($root));
    if ($rel === DB_CONFIG_FILE || str_contains($rel, '..') || preg_match('#^(cache|ratelimit)/#', $rel)) {
        return null;
    }
    return $rel;
}

/** Whether this storage path lives in the database right now. */
function store_in_db(string $file): ?string
{
    $key = store_key($file);
    return $key !== null && db() ? $key : null;
}

function json_read(string $file, mixed $default = null): mixed
{
    if ($key = store_in_db($file)) {
        $q = db()->prepare('SELECT data FROM ' . db_table() . ' WHERE path = ?');
        $q->execute([$key]);
        $raw = $q->fetchColumn();
        $data = $raw === false ? null : json_decode((string) $raw, true);
        return $data ?? $default;
    }
    if (!is_file($file)) {
        return $default;
    }
    $data = json_decode((string) file_get_contents($file), true);
    return $data ?? $default;
}

/** Atomic write: write to a temp file, then rename over the target (or one database row). */
function json_write(string $file, mixed $data): bool
{
    if ($key = store_in_db($file)) {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $json !== false && db()->prepare('REPLACE INTO ' . db_table() . ' (path, data, updated_at) VALUES (?, ?, ?)')->execute([$key, $json, time()]);
    }
    $dir = dirname($file);
    if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
        return false;
    }
    $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false || file_put_contents($tmp, $json, LOCK_EX) === false) {
        return false;
    }
    return rename($tmp, $file);
}

/** Path of the admin-edited copy of a content section. */
function content_override_file(string $name): string
{
    return storage_dir('content') . '/' . $name . '.json';
}

/** Built-in default content shipped in includes/content/{name}.php. */
function content_default(string $name): array
{
    $file = dirname(__DIR__) . '/includes/content/' . $name . '.php';
    return is_file($file) ? (array) require $file : [];
}

/** Whether a stored document exists. */
function store_exists(string $file): bool
{
    if ($key = store_in_db($file)) {
        $q = db()->prepare('SELECT 1 FROM ' . db_table() . ' WHERE path = ?');
        $q->execute([$key]);
        return (bool) $q->fetchColumn();
    }
    return is_file($file);
}

function store_delete(string $file): bool
{
    if ($key = store_in_db($file)) {
        return db()->prepare('DELETE FROM ' . db_table() . ' WHERE path = ?')->execute([$key]);
    }
    return is_file($file) && unlink($file);
}

/** Last change time of a stored document (0 if missing). */
function store_mtime(string $file): int
{
    if ($key = store_in_db($file)) {
        $q = db()->prepare('SELECT updated_at FROM ' . db_table() . ' WHERE path = ?');
        $q->execute([$key]);
        return (int) $q->fetchColumn();
    }
    return is_file($file) ? (int) filemtime($file) : 0;
}

/** Paths of the JSON documents in a storage folder, sorted by name (like glob). */
function store_list(string $sub): array
{
    $dir = storage_dir($sub);
    if (($prefix = store_key($dir . '/x.json')) !== null && db()) {
        $base = substr($prefix, 0, -strlen('x.json'));
        $q = db()->prepare('SELECT path FROM ' . db_table() . ' WHERE path LIKE ? ORDER BY path');
        $q->execute([$base . '%']);
        $out = [];
        foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $path) {
            // Only documents directly in this folder (LIKE treats _ as a wildcard, so check exactly).
            if (str_starts_with($path, $base) && str_ends_with($path, '.json') && !str_contains(substr($path, strlen($base)), '/')) {
                $out[] = storage_dir() . '/' . $path;
            }
        }
        return $out;
    }
    return glob($dir . '/*.json') ?: [];
}

/** Every storage document that can move to the database, as relative path => full file path. */
function store_files_on_disk(): array
{
    $root = storage_dir();
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        $path = str_replace('\\', '/', $f->getPathname());
        if (($key = store_key($path)) !== null && !str_contains($key, '.tmp')) {
            $out[$key] = $path;
        }
    }
    ksort($out);
    return $out;
}
