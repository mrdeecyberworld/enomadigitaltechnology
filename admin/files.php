<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

/** Products that use each file. */
$usedBy = [];
foreach (shop_products(true) as $p) {
    if (($p['file'] ?? '') !== '') {
        $usedBy[$p['file']][] = $p['name'];
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'upload') {
        if (empty($_FILES['file']) || ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            // An upload larger than post_max_size arrives with no file at all.
            flash(!empty($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > media_server_limit_raw()
                ? 'That file is larger than your hosting allows (' . format_bytes(media_server_limit_raw()) . '). Upload it to Google Drive or Dropbox and paste its link on the product instead.'
                : 'Choose a file to upload.', 'error');
        } else {
            [$name, $error] = product_file_store($_FILES['file']);
            flash($error ?? 'Uploaded ' . $name . '. Now choose it on a product in Courses & Tools → “File customers receive”.', $error ? 'error' : 'success');
        }
    } elseif ($action === 'delete') {
        $path = product_file_path((string) ($_POST['name'] ?? ''));
        if ($path && @unlink($path)) {
            flash('Deleted.');
        } else {
            flash('That file could not be deleted.', 'error');
        }
    }
    redirect('/admin/files');
}

$files = product_files();
admin_header('Product files', 'files');
?>
<header class="page-head">
  <div>
    <h1>Product files</h1>
    <p class="muted">Software, course material and other files you sell. They are stored privately: nobody can download them without a download link you create for a paid order in <a href="/admin/orders">Orders</a>.</p>
  </div>
</header>

<form class="panel" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="upload">
  <div class="field">
    <label for="file">Upload a file</label>
    <p class="hint">Largest file your hosting accepts right now: <strong><?= e(format_bytes(media_server_limit_raw())) ?></strong>. For bigger files (large software or video courses), upload them to Google Drive, Dropbox or OneDrive and paste the share link on the product instead. Allowed: <?= e(implode(', ', array_map(static fn ($t) => '.' . $t, PRODUCT_FILE_TYPES))) ?>.</p>
    <input type="file" id="file" name="file" required>
  </div>
  <div class="actions"><button class="btn btn--primary" type="submit"><?= icon('download', 'icon icon-sm') ?> Upload</button></div>
</form>

<?php if (!$files): ?>
  <div class="panel empty"><?= icon('package', 'icon') ?><p>No product files yet. Upload your software or course files above, then choose one on a product in <a href="/admin/edit?section=shop">Courses &amp; Tools</a>.</p></div>
<?php else: ?>
  <div class="panel table-panel">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th scope="col">File</th><th scope="col">Size</th><th scope="col">Used by</th><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
        <tbody>
          <?php foreach ($files as $name => $size): ?>
            <tr>
              <td><strong><?= e($name) ?></strong></td>
              <td class="nowrap"><?= e(format_bytes($size)) ?></td>
              <td><?= isset($usedBy[$name]) ? e(implode(', ', $usedBy[$name])) : '<span class="muted">Not used yet</span>' ?></td>
              <td><form method="post" data-confirm="Delete <?= e($name) ?> permanently? Download links for it will stop working."><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="name" value="<?= e($name) ?>"><button class="btn btn--danger btn--sm">Delete</button></form></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
<?php admin_footer();
