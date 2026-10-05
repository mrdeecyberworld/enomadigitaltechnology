<?php
/**
 * Real photos, stored on your own website.
 *
 * Each photo slot (includes/content/images.php, Admin → Photos) names a photo
 * on Unsplash (free to use under the Unsplash License, https://unsplash.com/license).
 * "Save photos to this website" in Admin → Photos (and the local start script)
 * downloads each one once, fits it with the same pipeline as uploads, and the
 * site then serves it from assets/uploads/stock-{slot}.jpg: faster, private
 * for visitors and independent of Unsplash. Until then photos load from
 * Unsplash's image CDN.
 */

declare(strict_types=1);

const STOCK_PREFIX = 'stock-';

/** Saved copy of a slot's photo (relative to assets/), if it is the slot's current photo. */
function stock_local(string $key, string $id): ?string
{
    $key = stock_safe_key($key);
    if ($id === '' || $key === '') {
        return null;
    }
    $rel = 'uploads/' . STOCK_PREFIX . $key . '.jpg';
    if (!is_file(SITE_ROOT . '/assets/' . $rel)) {
        return null;
    }
    return (media_meta(basename($rel))['stock_id'] ?? '') === $id ? $rel : null;
}

/**
 * Slots that use an Unsplash photo, and whether each one is saved on the site.
 * @return array<string, bool>
 */
function stock_slots(): array
{
    $out = [];
    foreach (content('images') as $key => $img) {
        $id = (string) ($img['id'] ?? '');
        if ($id !== '' && empty($img['file'])) {
            $out[(string) $key] = stock_local((string) $key, $id) !== null;
        }
    }
    return $out;
}

/** Download a file. @return array{0: ?string, 1: ?string} [bytes, error] */
function stock_fetch(string $url): array
{
    $max = 20 * 1024 * 1024;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 3,
            CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 45,
            CURLOPT_USERAGENT => 'EnomaWebsite/1.0 (+photo download)',
            CURLOPT_PROTOCOLS => getenv('ENOMA_PHOTO_SOURCE') ? CURLPROTO_HTTP | CURLPROTO_HTTPS : CURLPROTO_HTTPS,
        ]);
        $data = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($data === false) {
            return [null, 'no connection (' . $err . ')'];
        }
    } else {
        $ctx = stream_context_create(['http' => ['timeout' => 45, 'user_agent' => 'EnomaWebsite/1.0', 'ignore_errors' => true]]);
        $data = @file_get_contents($url, false, $ctx, 0, $max + 1);
        if ($data === false) {
            return [null, 'no connection'];
        }
        $code = preg_match('#^HTTP/\S+ (\d{3})#', (string) ($http_response_header[0] ?? ''), $m) ? (int) $m[1] : 0;
    }
    if ($code === 404) {
        return [null, 'Unsplash has no photo with this ID'];
    }
    if ($code !== 200) {
        return [null, 'Unsplash answered with error ' . $code];
    }
    if (strlen((string) $data) > $max) {
        return [null, 'the file is too large'];
    }
    return [(string) $data, null];
}

/** Slot keys become file names: letters, digits and dashes only. */
function stock_safe_key(string $key): string
{
    return preg_replace('/[^a-z0-9-]/', '', strtolower($key)) ?? '';
}

/** Download one slot's photo and store it on the site. Returns an error message, or null when saved. */
function stock_download(string $key, string $id): ?string
{
    $key = stock_safe_key($key);
    if ($key === '') {
        return 'invalid photo slot name';
    }
    if (!preg_match('/^[0-9]+-[0-9a-f]+$/', $id)) {
        return 'the photo ID doesn’t look like an Unsplash ID (for example 1498050108023-c5249f4df085)';
    }
    // ENOMA_PHOTO_SOURCE points at a mirror of images.unsplash.com (used for testing).
    $source = rtrim((string) (getenv('ENOMA_PHOTO_SOURCE') ?: 'https://images.unsplash.com'), '/');
    [$data, $err] = stock_fetch($source . '/photo-' . $id . '?fm=jpg&q=82&w=2400&fit=max');
    if ($err !== null) {
        return $err;
    }
    $tmp = tempnam(sys_get_temp_dir(), 'enoma');
    file_put_contents($tmp, $data);
    $info = @getimagesize($tmp);
    if (!$info || $info[2] !== IMAGETYPE_JPEG) {
        @unlink($tmp);
        return 'the download was not a photo';
    }
    [$path, $error] = media_process($tmp, STOCK_PREFIX . $key, $info, strlen($data));
    @unlink($tmp);
    if ($error !== null) {
        return $error;
    }
    $name = basename((string) $path);
    $meta = media_meta($name);
    $meta['stock_id'] = $id;
    $meta['source'] = 'Unsplash';
    json_write(media_meta_file($name), $meta);
    return null;
}

/**
 * Save every slot's photo that isn't on the site yet.
 * $progress is called as fn(string $key, int $done, int $total, ?string $error).
 * Stops early when there is no internet connection, or when $seconds (if set)
 * have passed, so a web request stays within the host's time limit; 'remaining'
 * then counts the photos left for the next run.
 * @return array{saved: int, already: int, failed: array<string, string>, offline: bool, remaining: int}
 */
function stock_download_all(?callable $progress = null, ?int $seconds = null): array
{
    @set_time_limit(600);
    $started = microtime(true);
    $images = content('images');
    $todo = array_keys(array_filter(stock_slots(), static fn (bool $saved) => !$saved));
    $result = ['saved' => 0, 'already' => count(stock_slots()) - count($todo), 'failed' => [], 'offline' => false, 'remaining' => 0];
    foreach ($todo as $i => $key) {
        if ($seconds !== null && $i > 0 && microtime(true) - $started > $seconds) {
            $result['remaining'] = count($todo) - $i;
            break;
        }
        $err = stock_download($key, (string) $images[$key]['id']);
        if ($err === null) {
            $result['saved']++;
        } else {
            $result['failed'][$key] = $err;
        }
        if ($progress) {
            $progress($key, $i + 1, count($todo), $err);
        }
        if ($err !== null && str_starts_with($err, 'no connection') && $result['saved'] === 0) {
            $result['offline'] = true;
            break;
        }
    }
    return $result;
}
