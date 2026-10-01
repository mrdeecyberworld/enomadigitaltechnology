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

    // Address changes: validate, then remember old → new so old links keep working.
    $moves = [];
    $problems = [];
    if ($key === 'routes') {
        $current = routes();
        $seen = [];
        foreach ($data as $pageKey => $slug) {
            $slug = trim(strtolower((string) $slug), '/ ');
            $data[$pageKey] = $slug;
            if ($slug === ($current[$pageKey] ?? '')) {
                $seen[$slug] = true;
                continue;
            }
            $problem = slug_problem($slug, 'page:' . $pageKey) ?? (isset($seen[$slug]) ? '“' . $slug . '” is used twice.' : null);
            if ($problem) {
                $problems[] = $problem;
            }
            $seen[$slug] = true;
            $moves[] = ['/' . $current[$pageKey], '/' . $slug, in_array($pageKey, ['blog', 'resources'], true)];
        }
    }
    if ($key === 'services') {
        foreach ($data as $slug => &$svc) {
            $orig = (string) ($svc['_original'] ?? '');
            unset($svc['_original']);
            if ($orig === $slug) {
                continue;
            }
            $problem = slug_problem($slug, 'service:' . $orig);
            if ($problem && !isset(content('services')[$slug])) {
                $problems[] = $problem;
            }
            if ($orig !== '') {
                $moves[] = ['/' . $orig, '/' . $slug, false];
            } else {
                $moves[] = ['', '/' . $slug, false]; // brand-new address
            }
        }
        unset($svc);
    }
    if ($key === 'resources') {
        $base = page_url('resources') . '/';
        foreach ($data as &$g) {
            $g['slug'] = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower((string) (($g['slug'] ?? '') ?: ($g['title'] ?? 'guide')))), '-') ?: 'guide';
            $orig = (string) ($g['_original'] ?? '');
            unset($g['_original']);
            if ($orig !== '' && $orig !== $g['slug']) {
                $moves[] = [$base . $orig, $base . $g['slug'], false];
            }
        }
        unset($g);
    }
    if ($problems) {
        foreach (array_unique($problems) as $msg) {
            flash($msg, 'error');
        }
        flash('Nothing was saved. Fix the addresses above and try again.', 'error');
        redirect('/admin/edit?section=' . $key);
    }

    if (json_write($file, $data)) {
        foreach ($moves as [$from, $to, $isSection]) {
            if ($from === '') {
                clear_redirect($to);
                continue;
            }
            record_redirect($from, $to);
            if ($isSection) {
                record_redirect($from . '/', $to . '/');
            }
        }
        $note = array_filter($moves, static fn ($m) => $m[0] !== '') ? ' Old addresses now redirect to the new ones.' : '';
        flash($section['title'] . ' saved. Changes are live on the website.' . $note);
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
