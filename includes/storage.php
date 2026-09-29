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

function json_read(string $file, mixed $default = null): mixed
{
    if (!is_file($file)) {
        return $default;
    }
    $data = json_decode((string) file_get_contents($file), true);
    return $data ?? $default;
}

/** Atomic write: write to a temp file, then rename over the target. */
function json_write(string $file, mixed $data): bool
{
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
