<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
$json = static function (array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // A request bigger than post_max_size arrives with empty $_POST/$_FILES.
    if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $msg = 'That upload is larger than your hosting allows (' . round(media_server_limit() / 1048576) . ' MB).';
        $wantsJson ? $json(['error' => $msg], 413) : flash($msg, 'error');
        redirect('/admin/media');
    }
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $wantsJson ? $json(['error' => 'Your session expired. Reload the page and try again.'], 400) : print('Session expired.');
        exit;
    }
    $action = (string) ($_POST['action'] ?? 'upload');

    if ($action === 'delete') {
        media_delete((string) ($_POST['name'] ?? '')) ? flash('Image deleted. Any page still using it will show the placeholder design.') : flash('That image was already removed.', 'error');
        redirect('/admin/media');
    }

    if ($action === 'focus') {
        $name = basename((string) ($_POST['name'] ?? ''));
        $focus = (string) ($_POST['focus'] ?? 'center');
        if (is_file(media_upload_dir() . '/' . $name) && in_array($focus, MEDIA_FOCUS, true)) {
            $meta = media_meta($name);
            $meta['focus'] = $focus;
            json_write(media_meta_file($name), $meta);
            $wantsJson ? $json(['ok' => true, 'focus' => $focus]) : flash('Focus point saved.');
        }
        redirect('/admin/media');
    }

    // Upload: one file ("file") or several ("files[]").
    $uploads = [];
    if (!empty($_FILES['file']['name'])) {
        $uploads[] = $_FILES['file'];
    }
    if (!empty($_FILES['files']['name']) && is_array($_FILES['files']['name'])) {
        foreach ($_FILES['files']['name'] as $i => $n) {
            $uploads[] = ['name' => $n, 'type' => $_FILES['files']['type'][$i], 'tmp_name' => $_FILES['files']['tmp_name'][$i], 'error' => $_FILES['files']['error'][$i], 'size' => $_FILES['files']['size'][$i]];
        }
    }
    $done = [];
    $errors = [];
    foreach (array_slice($uploads, 0, 20) as $u) {
        [$path, $error] = media_store_upload($u);
        $error ? $errors[] = ($u['name'] ?? 'Image') . ': ' . $error : $done[] = $path;
    }
    if ($wantsJson) {
        if (!$done) {
            $json(['error' => $errors[0] ?? 'Choose an image to upload.'], 422);
        }
        $json(['path' => $done[0], 'url' => '/' . $done[0], 'paths' => $done, 'errors' => $errors]);
    }
    if ($done) {
        flash(count($done) === 1 ? 'Image uploaded and optimized.' : count($done) . ' images uploaded and optimized.');
    }
    foreach ($errors as $e) {
        flash($e, 'error');
    }
    if (!$done && !$errors) {
        flash('Choose an image to upload.', 'error');
    }
    redirect('/admin/media');
}

if (($_GET['format'] ?? '') === 'json') {
    $json(array_map(static fn ($m) => $m + ['url' => $m['thumb']], media_list()));
}

$items = media_list();
$limitMb = (int) round(media_server_limit() / 1048576);
$focusLabels = ['top-left' => 'Top left', 'top' => 'Top', 'top-right' => 'Top right', 'left' => 'Left', 'center' => 'Center', 'right' => 'Right', 'bottom-left' => 'Bottom left', 'bottom' => 'Bottom', 'bottom-right' => 'Bottom right'];

admin_header('Media library', 'media');
?>
<header class="page-head">
  <div>
    <h1>Media library</h1>
    <p class="muted">Upload any photo. It is automatically turned upright, resized, compressed and made in several sizes, then cropped to fit each spot on the site. Pick a focus point to choose which part stays in view.</p>
  </div>
</header>

<form method="post" enctype="multipart/form-data" class="dropzone" data-dropzone>
  <?= csrf_field() ?>
  <input type="file" id="media-files" name="files[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple class="sr-only" data-drop-input>
  <label for="media-files" class="dropzone__inner">
    <span class="dropzone__icon"><?= icon('hard-drive', 'icon') ?></span>
    <strong>Drag photos here, or click to choose</strong>
    <span class="muted small">JPG, PNG, WebP or GIF · up to <?= $limitMb ?> MB each · several at once is fine</span>
  </label>
  <div class="dropzone__progress" data-drop-status role="status" aria-live="polite"></div>
  <noscript><button class="btn btn--primary" type="submit">Upload</button></noscript>
</form>

<?php if (!$items): ?>
  <div class="panel empty"><?= icon('hard-drive', 'icon') ?><p>No images uploaded yet.</p></div>
<?php else: ?>
  <div class="media-grid media-grid--page">
    <?php foreach ($items as $it): ?>
      <figure class="media-card">
        <div class="focus-picker" data-focus-picker data-name="<?= e($it['name']) ?>">
          <img src="<?= e($it['thumb']) ?>" alt="" loading="lazy" class="<?= $it['focus'] !== 'center' ? 'focus-' . e($it['focus']) : '' ?>" data-focus-img>
          <div class="focus-picker__grid" role="radiogroup" aria-label="Focus point for <?= e($it['name']) ?>">
            <?php foreach (MEDIA_FOCUS as $f): ?>
              <button type="button" role="radio" aria-checked="<?= $it['focus'] === $f ? 'true' : 'false' ?>" aria-label="<?= e($focusLabels[$f]) ?>" data-focus="<?= e($f) ?>"><span></span></button>
            <?php endforeach; ?>
          </div>
        </div>
        <figcaption>
          <span class="small" title="<?= e($it['name']) ?>"><?= e($it['name']) ?></span>
          <span class="muted small"><?= (int) $it['w'] ?>×<?= (int) $it['h'] ?> · <?= (int) $it['kb'] ?> KB<?php if ($it['original_kb'] > $it['kb'] + 5): ?> <span class="saved">(was <?= $it['original_kb'] >= 1024 ? round($it['original_kb'] / 1024, 1) . ' MB' : (int) $it['original_kb'] . ' KB' ?>)</span><?php endif; ?></span>
          <span class="muted small">Focus: <strong data-focus-label><?= e($focusLabels[$it['focus']] ?? 'Center') ?></strong> · click the photo to change</span>
          <form method="post" data-confirm="Delete this image? Pages using it will show the placeholder design."><?= csrf_field() ?><input type="hidden" name="name" value="<?= e($it['name']) ?>"><button class="btn btn--danger btn--sm" name="action" value="delete">Delete</button></form>
        </figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
  <script type="application/json" data-focus-labels><?= json_encode($focusLabels) ?></script>
<?php endif; ?>
<?php admin_footer();
