<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

$sections = admin_sections();
$key = (string) ($_GET['section'] ?? '');
if (!isset($sections[$key])) {
    http_response_code(404);
    admin_header('Not found');
    echo '<h1>Section not found</h1><p><a href="/admin/">Back to the dashboard</a></p>';
    admin_footer();
    exit;
}
$section = $sections[$key];
$file = admin_section_file($key);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    if (isset($_POST['reset'])) {
        if (($_POST['confirm_reset'] ?? '') === 'yes') {
            @unlink($file);
            flash($section['title'] . ' was reset to the original content.');
        } else {
            flash('Tick the confirmation box to reset this section.', 'error');
        }
        redirect('/admin/edit?section=' . $key);
    }
    $old = $key === 'settings' ? json_read($file, []) : content($key);
    if ($key === 'services') {
        $old = []; // rebuilt from the submitted list
    }
    $data = field_parse($section['schema'], $_POST['data'] ?? [], $old);
    if ($key === 'settings') {
        $data['ai']['rate_limit'] = max(1, (int) ($data['ai']['rate_limit'] ?? 20));
        $data['base_url'] = rtrim((string) ($data['base_url'] ?? ''), '/') ?: cfg('base_url');
    }
    if ($key === 'services' && !$data) {
        flash('Keep at least one service.', 'error');
        redirect('/admin/edit?section=services');
    }
    if (json_write($file, $data)) {
        flash($section['title'] . ' saved. Changes are live on the website.');
    } else {
        flash('Could not save. Check that the storage folder is writable.', 'error');
    }
    redirect('/admin/edit?section=' . $key);
}

$hasOverride = is_file($file);
admin_header($section['title'], 'edit:' . $key);
?>
<header class="page-head">
  <div>
    <h1><?= e($section['title']) ?></h1>
    <p class="muted"><?= e($section['intro']) ?></p>
  </div>
</header>

<form method="post" class="editor" data-editor>
  <?= csrf_field() ?>
  <?= field_render($section['schema'], 'data', admin_section_data($key)) ?>
  <div class="savebar">
    <span class="muted small" data-dirty-note>All changes saved.</span>
    <button type="submit" class="btn btn--primary"><?= icon('check', 'icon icon-sm') ?> Save changes</button>
  </div>
</form>

<?php if ($hasOverride && $key !== 'settings'): ?>
<details class="panel danger">
  <summary>Reset this section to the original content</summary>
  <form method="post" class="stack">
    <?= csrf_field() ?>
    <p class="muted">This removes every change made here to <?= e($section['title']) ?> and restores the text the site launched with. Tip: download a backup first.</p>
    <label class="check"><input type="checkbox" name="confirm_reset" value="yes"> Yes, reset <?= e($section['title']) ?></label>
    <button type="submit" name="reset" value="1" class="btn btn--danger">Reset section</button>
  </form>
</details>
<?php endif; ?>
<?php admin_footer();
