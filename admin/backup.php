<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

$sections = array_keys(admin_sections());

if (($_GET['download'] ?? '') === '1') {
    $export = ['exported_at' => date('c'), 'site' => site('domain'), 'sections' => []];
    foreach ($sections as $key) {
        $data = json_read(admin_section_file($key));
        if ($data !== null) {
            if ($key === 'settings') {
                unset($data['ai']['api_key']); // never put secrets in a download
            }
            $export['sections'][$key] = $data;
        }
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="enoma-content-backup-' . date('Y-m-d') . '.json"');
    echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    $raw = is_uploaded_file($_FILES['backup']['tmp_name'] ?? '') ? file_get_contents($_FILES['backup']['tmp_name']) : '';
    $data = json_decode((string) $raw, true);
    if (!is_array($data) || !is_array($data['sections'] ?? null)) {
        flash('That file is not an Enoma content backup.', 'error');
        redirect('/admin/backup');
    }
    $restored = 0;
    foreach ($data['sections'] as $key => $value) {
        if (in_array($key, $sections, true) && is_array($value)) {
            if ($key === 'settings') {
                $current = json_read(admin_section_file('settings'), []);
                $value['ai']['api_key'] = $current['ai']['api_key'] ?? '';
            }
            json_write(admin_section_file($key), $value);
            $restored++;
        }
    }
    flash('Restored ' . $restored . ' section' . ($restored === 1 ? '' : 's') . ' from the backup.');
    redirect('/admin/backup');
}

admin_header('Backup', 'backup');
?>
<header class="page-head"><div><h1>Backup</h1><p class="muted">Download a copy of all your edited content and settings, or restore from a previous download.</p></div></header>
<div class="cols">
  <section class="panel stack">
    <h2>Download a backup</h2>
    <p class="muted">Includes every section you have edited in this admin. Your AI API key is left out for safety. Messages and uploaded images are not included; back those up with your hosting provider’s backup tool.</p>
    <a class="btn btn--primary" href="/admin/backup?download=1"><?= icon('database', 'icon icon-sm') ?> Download backup</a>
  </section>
  <form method="post" enctype="multipart/form-data" class="panel stack" data-confirm="Restore this backup? It replaces the current content of every section in the file.">
    <h2>Restore a backup</h2>
    <?= csrf_field() ?>
    <div class="field"><label for="backup">Backup file (.json)</label><input type="file" id="backup" name="backup" accept="application/json,.json" required></div>
    <button class="btn btn--light" type="submit">Restore</button>
  </form>
</div>
<?php admin_footer();
