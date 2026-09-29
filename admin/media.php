<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

const MEDIA_MAX_BYTES = 8 * 1024 * 1024;
$uploadDir = SITE_ROOT . '/assets/uploads';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

function media_list(string $dir): array
{
    $files = glob($dir . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];
    usort($files, static fn ($a, $b) => filemtime($b) <=> filemtime($a));
    return array_map(static function (string $f) {
        $size = @getimagesize($f) ?: [0, 0];
        return ['path' => 'assets/uploads/' . basename($f), 'url' => '/assets/uploads/' . basename($f), 'name' => basename($f), 'w' => $size[0], 'h' => $size[1], 'kb' => (int) round(filesize($f) / 1024)];
    }, $files);
}

/** Validate and store an uploaded image. Returns [path|null, error|null]. */
function media_store(array $upload, string $dir): array
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [null, 'The upload failed. Try a smaller image (max 8 MB).'];
    }
    if ($upload['size'] > MEDIA_MAX_BYTES) {
        return [null, 'That image is larger than 8 MB.'];
    }
    $info = @getimagesize($upload['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$info || !isset($types[$info[2]])) {
        return [null, 'Please upload a JPG, PNG, WebP or GIF image.'];
    }
    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(pathinfo((string) $upload['name'], PATHINFO_FILENAME))), '-') ?: 'image';
    $name = substr($base, 0, 40) . '-' . bin2hex(random_bytes(3)) . '.' . $types[$info[2]];
    if (!move_uploaded_file($upload['tmp_name'], $dir . '/' . $name)) {
        return [null, 'Could not save the image. Check that assets/uploads is writable.'];
    }
    @chmod($dir . '/' . $name, 0644);
    return ['assets/uploads/' . $name, null];
}

$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        http_response_code(400);
        $wantsJson ? print(json_encode(['error' => 'Session expired. Reload the page.'])) : print('Session expired.');
        exit;
    }
    if (($_POST['action'] ?? '') === 'delete') {
        $name = basename((string) ($_POST['name'] ?? ''));
        $file = $uploadDir . '/' . $name;
        if ($name !== '' && is_file($file) && preg_match('/\.(jpe?g|png|webp|gif)$/i', $name)) {
            unlink($file);
            flash('Image deleted. Any page still pointing to it will show no image.');
        }
        redirect('/admin/media');
    }
    [$path, $error] = media_store($_FILES['file'] ?? [], $uploadDir);
    if ($wantsJson) {
        header('Content-Type: application/json');
        echo json_encode($error ? ['error' => $error] : ['path' => $path, 'url' => '/' . $path]);
        exit;
    }
    $error ? flash($error, 'error') : flash('Image uploaded.');
    redirect('/admin/media');
}

if (($_GET['format'] ?? '') === 'json') {
    header('Content-Type: application/json');
    echo json_encode(media_list($uploadDir));
    exit;
}

$items = media_list($uploadDir);
admin_header('Media library', 'media');
?>
<header class="page-head">
  <div>
    <h1>Media library</h1>
    <p class="muted">Upload photos and images, then choose them anywhere an image can be set (Photos, Services, founder photo).</p>
  </div>
</header>

<form method="post" enctype="multipart/form-data" class="panel upload-panel">
  <?= csrf_field() ?>
  <label for="media-file"><strong>Upload an image</strong><span class="muted small">JPG, PNG, WebP or GIF, up to 8 MB. For best quality and speed, use images about 1600 px wide.</span></label>
  <input type="file" id="media-file" name="file" accept="image/jpeg,image/png,image/webp,image/gif" required>
  <button class="btn btn--primary" type="submit">Upload</button>
</form>

<?php if (!$items): ?>
  <div class="panel empty"><?= icon('hard-drive', 'icon') ?><p>No images uploaded yet.</p></div>
<?php else: ?>
  <div class="media-grid media-grid--page">
    <?php foreach ($items as $it): ?>
      <figure class="media-card">
        <img src="<?= e($it['url']) ?>" alt="" loading="lazy">
        <figcaption>
          <span class="small" title="<?= e($it['name']) ?>"><?= e($it['name']) ?></span>
          <span class="muted small"><?= (int) $it['w'] ?>×<?= (int) $it['h'] ?> · <?= (int) $it['kb'] ?> KB</span>
          <input class="copy" readonly value="<?= e($it['path']) ?>" aria-label="Image path" data-select-on-focus>
          <form method="post" data-confirm="Delete this image? Pages using it will show no image."><?= csrf_field() ?><input type="hidden" name="name" value="<?= e($it['name']) ?>"><button class="btn btn--danger btn--sm" name="action" value="delete">Delete</button></form>
        </figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php admin_footer();
