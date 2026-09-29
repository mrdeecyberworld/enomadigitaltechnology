<?php
/**
 * Uploaded images: automatic fitting and optimization.
 *
 * Every upload is:
 *  - checked to be a real JPG, PNG, WebP or GIF image
 *  - rotated upright using the camera's orientation data (phone photos)
 *  - scaled down to at most MEDIA_MAX_EDGE pixels and re-compressed
 *  - stripped of hidden metadata (such as GPS location in phone photos)
 *  - saved in extra sizes (MEDIA_SIZES) so pages load the smallest one that looks sharp
 *
 * On the site, images fill their frame (cropping to fit) around a focus point
 * you choose in Admin → Media library.
 */

declare(strict_types=1);

const MEDIA_MAX_BYTES = 25 * 1024 * 1024;
const MEDIA_MAX_EDGE = 2400;
const MEDIA_SIZES = [480, 960, 1600];
const MEDIA_JPEG_QUALITY = 82;
const MEDIA_FOCUS = ['top-left', 'top', 'top-right', 'left', 'center', 'right', 'bottom-left', 'bottom', 'bottom-right'];

function media_upload_dir(): string
{
    $dir = SITE_ROOT . '/assets/uploads';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir;
}

function media_meta_file(string $name): string
{
    return storage_dir('media') . '/' . basename($name) . '.json';
}

function media_meta(string $name): array
{
    $meta = json_read(media_meta_file($name), []);
    return is_array($meta) ? $meta + ['focus' => 'center', 'variants' => []] : ['focus' => 'center', 'variants' => []];
}

/** Largest file size PHP on this server accepts, in bytes. */
function media_server_limit(): int
{
    $toBytes = static function (string $v): int {
        $v = trim($v);
        $n = (int) $v;
        return match (strtolower(substr($v, -1))) { 'g' => $n << 30, 'm' => $n << 20, 'k' => $n << 10, default => $n };
    };
    $limits = array_filter([$toBytes((string) ini_get('upload_max_filesize')), $toBytes((string) ini_get('post_max_size')), MEDIA_MAX_BYTES]);
    return $limits ? min($limits) : MEDIA_MAX_BYTES;
}

/**
 * Validate, fit and store an uploaded image.
 * @return array{0: ?string, 1: ?string} [path relative to site root, error message]
 */
function media_store_upload(array $upload): array
{
    $err = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        return [null, 'That image is larger than your hosting allows (' . round(media_server_limit() / 1048576) . ' MB). See the README for how to raise the limit, or use a smaller photo.'];
    }
    if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($upload['tmp_name'] ?? ''))) {
        return [null, 'The upload didn’t finish. Please try again.'];
    }
    if ((int) $upload['size'] > MEDIA_MAX_BYTES) {
        return [null, 'That image is larger than 25 MB. Please use a smaller photo.'];
    }
    $info = @getimagesize($upload['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$info || !isset($types[$info[2]])) {
        $isHeic = preg_match('/\.(heic|heif)$/i', (string) ($upload['name'] ?? ''));
        return [null, $isHeic
            ? 'iPhone HEIC photos aren’t supported by web browsers. On your iPhone, go to Settings → Camera → Formats → Most Compatible, or share the photo as JPG, then upload it again.'
            : 'Please upload a JPG, PNG, WebP or GIF image.'];
    }

    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(pathinfo((string) $upload['name'], PATHINFO_FILENAME))), '-') ?: 'image';
    $stem = substr($base, 0, 40) . '-' . bin2hex(random_bytes(3));
    return media_process($upload['tmp_name'], $stem, $info, (int) $upload['size']);
}

/**
 * Fit an image file into the uploads folder.
 * @return array{0: ?string, 1: ?string}
 */
function media_process(string $source, string $stem, array $info, int $originalBytes): array
{
    $dir = media_upload_dir();
    $type = $info[2];
    $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'][$type];
    $name = $stem . '.' . $ext;
    $dest = $dir . '/' . $name;

    // GIFs may be animated, and without GD we can't process: store as-is.
    if ($type === IMAGETYPE_GIF || !function_exists('imagecreatetruecolor')) {
        if (!@copy($source, $dest)) {
            return [null, 'Could not save the image. Check that assets/uploads is writable.'];
        }
        @chmod($dest, 0644);
        json_write(media_meta_file($name), ['w' => $info[0], 'h' => $info[1], 'focus' => 'center', 'variants' => [], 'original_bytes' => $originalBytes, 'bytes' => filesize($dest), 'uploaded_at' => date('c')]);
        return ['assets/uploads/' . $name, null];
    }

    @ini_set('memory_limit', '512M');
    @set_time_limit(60);
    if ($info[0] * $info[1] > 60_000_000) {
        return [null, 'That image has too many pixels to process (over 60 megapixels). Please use a smaller version.'];
    }
    $img = match ($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
        IMAGETYPE_PNG  => @imagecreatefrompng($source),
        IMAGETYPE_WEBP => @imagecreatefromwebp($source),
    };
    if (!$img) {
        return [null, 'That image could not be read. It may be damaged; try exporting it again.'];
    }

    // Rotate phone photos upright.
    if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $exif = @exif_read_data($source);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $img = media_orient($img, $orientation);
    }

    $hasAlpha = $type !== IMAGETYPE_JPEG && media_has_alpha($img);
    // Photos without transparency are saved as JPG for the best size.
    if (!$hasAlpha && $type === IMAGETYPE_PNG) {
        $ext = 'jpg';
        $name = $stem . '.jpg';
        $dest = $dir . '/' . $name;
    }

    $w = imagesx($img);
    $h = imagesy($img);
    $main = media_resize($img, MEDIA_MAX_EDGE);
    if (!media_save($main, $dest, $ext)) {
        imagedestroy($img);
        return [null, 'Could not save the image. Check that assets/uploads is writable.'];
    }
    $mw = imagesx($main);
    $mh = imagesy($main);
    if ($main !== $img) {
        imagedestroy($main);
    }

    $variants = [];
    foreach (MEDIA_SIZES as $size) {
        if ($size >= $mw) {
            continue;
        }
        $v = media_resize($img, $size, true);
        if (media_save($v, $dir . '/' . $stem . '-' . $size . '.' . $ext, $ext)) {
            $variants[] = $size;
        }
        imagedestroy($v);
    }
    imagedestroy($img);

    json_write(media_meta_file($name), [
        'w' => $mw, 'h' => $mh, 'original_w' => $w, 'original_h' => $h,
        'focus' => 'center', 'variants' => $variants,
        'original_bytes' => $originalBytes, 'bytes' => filesize($dest), 'uploaded_at' => date('c'),
    ]);
    return ['assets/uploads/' . $name, null];
}

function media_orient(GdImage $img, int $o): GdImage
{
    if ($o <= 1 || $o > 8) {
        return $img;
    }
    if (in_array($o, [2, 4, 5, 7], true)) {
        imageflip($img, $o === 4 ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
    }
    $angle = match ($o) { 3, 4 => 180, 5, 6 => -90, 7, 8 => 90, default => 0 };
    if ($angle !== 0) {
        $rotated = imagerotate($img, $angle, 0);
        if ($rotated) {
            imagedestroy($img);
            $img = $rotated;
        }
    }
    return $img;
}

function media_has_alpha(GdImage $img): bool
{
    $w = imagesx($img);
    $h = imagesy($img);
    $step = max(1, (int) floor(min($w, $h) / 40));
    for ($x = 0; $x < $w; $x += $step) {
        for ($y = 0; $y < $h; $y += $step) {
            if ((imagecolorat($img, $x, $y) >> 24) & 0x7F) {
                return true;
            }
        }
    }
    return false;
}

/** Scale so the longest edge (or the width, when $byWidth) is at most $max. */
function media_resize(GdImage $img, int $max, bool $byWidth = false): GdImage
{
    $w = imagesx($img);
    $h = imagesy($img);
    $scale = $byWidth ? $max / $w : $max / max($w, $h);
    if ($scale >= 1) {
        return $img;
    }
    $nw = max(1, (int) round($w * $scale));
    $nh = max(1, (int) round($h * $scale));
    $out = imagecreatetruecolor($nw, $nh);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    return $out;
}

function media_save(GdImage $img, string $path, string $ext): bool
{
    $ok = match ($ext) {
        'jpg'  => (static function () use ($img, $path) {
            // Flatten any transparency onto white for JPG.
            $flat = imagecreatetruecolor(imagesx($img), imagesy($img));
            imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
            imagecopy($flat, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
            imageinterlace($flat, true);
            $r = imagejpeg($flat, $path, MEDIA_JPEG_QUALITY);
            imagedestroy($flat);
            return $r;
        })(),
        'png'  => (static function () use ($img, $path) { imagesavealpha($img, true); return imagepng($img, $path, 8); })(),
        'webp' => imagewebp($img, $path, MEDIA_JPEG_QUALITY),
        default => false,
    };
    if ($ok) {
        @chmod($path, 0644);
    }
    return (bool) $ok;
}

/** Delete an upload with its extra sizes and settings. */
function media_delete(string $name): bool
{
    $name = basename($name);
    $file = media_upload_dir() . '/' . $name;
    if ($name === '' || !is_file($file) || !preg_match('/\.(jpe?g|png|webp|gif)$/i', $name)) {
        return false;
    }
    $meta = media_meta($name);
    $stem = pathinfo($name, PATHINFO_FILENAME);
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    foreach ((array) $meta['variants'] as $size) {
        @unlink(media_upload_dir() . '/' . $stem . '-' . (int) $size . '.' . $ext);
    }
    @unlink(media_meta_file($name));
    return unlink($file);
}

/** srcset for an uploaded image, using the extra sizes made at upload. */
function media_srcset(string $relPath): string
{
    $name = basename($relPath);
    $meta = media_meta($name);
    if (!$meta['variants']) {
        return '';
    }
    $stem = pathinfo($name, PATHINFO_FILENAME);
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $parts = [];
    foreach ($meta['variants'] as $size) {
        $parts[] = asset('uploads/' . $stem . '-' . (int) $size . '.' . $ext) . ' ' . (int) $size . 'w';
    }
    if (!empty($meta['w'])) {
        $parts[] = asset('uploads/' . $name) . ' ' . (int) $meta['w'] . 'w';
    }
    return implode(', ', $parts);
}

/** CSS class that keeps the chosen part of the photo in view when it is cropped to fit. */
function media_focus_class(string $relPath): string
{
    if (!str_contains($relPath, 'uploads/')) {
        return '';
    }
    $focus = media_meta(basename($relPath))['focus'] ?? 'center';
    return in_array($focus, MEDIA_FOCUS, true) && $focus !== 'center' ? 'focus-' . $focus : '';
}

/** Uploaded images (main files only), newest first. */
function media_list(): array
{
    $files = glob(media_upload_dir() . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];
    $files = array_filter($files, static fn ($f) => !preg_match('/-(' . implode('|', MEDIA_SIZES) . ')\.[a-z]+$/', $f) || !is_file(preg_replace('/-\d+(\.[a-z]+)$/', '$1', $f)));
    usort($files, static fn ($a, $b) => filemtime($b) <=> filemtime($a));
    return array_values(array_map(static function (string $f) {
        $name = basename($f);
        $meta = media_meta($name);
        $size = !empty($meta['w']) ? [$meta['w'], $meta['h']] : (@getimagesize($f) ?: [0, 0]);
        $thumb = $meta['variants'] ? 'uploads/' . pathinfo($name, PATHINFO_FILENAME) . '-' . min($meta['variants']) . '.' . pathinfo($name, PATHINFO_EXTENSION) : 'uploads/' . $name;
        return [
            'path' => 'assets/uploads/' . $name, 'url' => '/assets/uploads/' . $name, 'thumb' => '/assets/' . $thumb,
            'name' => $name, 'w' => (int) $size[0], 'h' => (int) $size[1], 'kb' => (int) round(filesize($f) / 1024),
            'original_kb' => (int) round(((int) ($meta['original_bytes'] ?? filesize($f))) / 1024), 'focus' => $meta['focus'],
        ];
    }, $files));
}
