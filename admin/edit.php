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
    if ($key === 'images' && isset($_POST['save_photos'])) {
        // Stay well inside the 30-second limit many shared hosts set; the rest is saved on the next click.
        $r = stock_download_all(null, 18);
        $more = $r['remaining'] ? ' ' . $r['remaining'] . ' more to go: click “Save photos to this website” again to continue.' : '';
        if ($r['offline']) {
            flash('This server couldn’t reach Unsplash, so no photos were saved. Photos keep loading from Unsplash in the meantime. Try again later, or ask your host whether outgoing connections are allowed.', 'error');
        } elseif ($r['failed']) {
            $list = [];
            foreach ($r['failed'] as $slot => $why) {
                $list[] = (image_usage()[$slot] ?? $slot) . ': ' . $why;
            }
            flash('Saved ' . $r['saved'] . ' photo' . ($r['saved'] === 1 ? '' : 's') . '. Not saved: ' . implode('; ', $list) . '.' . $more, 'error');
        } else {
            flash($r['saved'] ? 'Saved ' . $r['saved'] . ' photo' . ($r['saved'] === 1 ? '' : 's') . ' to your website.' . ($more ?: ' They now load from your own site.') : 'All photos are already saved on your website.');
        }
        redirect('/admin/edit?section=images');
    }
    if (isset($_POST['reset'])) {
        if (($_POST['confirm_reset'] ?? '') === 'yes') {
            store_delete($file);
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
    if ($key === 'images') {
        // Photo links pasted from unsplash.com become photo IDs; a link that can't be read keeps the previous photo.
        $problems = [];
        foreach ($data as $slot => $img) {
            if (!is_array($img) || !isset($img['id'])) {
                continue;
            }
            [$id, $why] = stock_resolve_id((string) $img['id']);
            if ($why !== null) {
                $problems[] = (image_usage()[$slot] ?? $slot) . ': ' . $why;
                $id = (string) ($old[$slot]['id'] ?? '');
            }
            $data[$slot]['id'] = $id;
        }
        if ($problems) {
            flash('Some photos were not changed. ' . implode('; ', $problems) . '.', 'error');
        }
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
            $moves[] = ['/' . $current[$pageKey], '/' . $slug, in_array($pageKey, ['blog', 'resources', 'shop'], true)];
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
    if ($key === 'shop') {
        $base = page_url('shop') . '/';
        $used = [];
        foreach ($data['products'] as &$p) {
            $p['slug'] = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower((string) (($p['slug'] ?? '') ?: ($p['name'] ?? 'product')))), '-') ?: 'product';
            while (isset($used[$p['slug']])) {
                $p['slug'] .= '-2';
            }
            $used[$p['slug']] = true;
            $orig = (string) ($p['_original'] ?? '');
            unset($p['_original']);
            if ($orig !== '' && $orig !== $p['slug']) {
                $moves[] = [$base . $orig, $base . $p['slug'], false];
            }
        }
        unset($p);
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

$hasOverride = store_exists($file);
admin_header($section['title'], 'edit:' . $key);
?>
<header class="page-head">
  <div>
    <h1><?= e($section['title']) ?></h1>
    <p class="muted"><?= e($section['intro']) ?></p>
  </div>
</header>

<?php if ($key === 'shop'): ?>
  <section class="panel steps-panel">
    <ol class="steps">
      <li><strong>Upload the file</strong> your customers receive (software, course files, e-book).<br><a class="btn btn--light btn--sm" href="/admin/files"><?= icon('download', 'icon icon-sm') ?> Upload courses &amp; software</a></li>
      <li><strong>Add the product below</strong>: click “Add a course or tool”, choose the type (Course, Software…), set the price, choose the file and set it to Published.</li>
      <li><strong>Save.</strong> It gets its own page and appears in Courses &amp; Tools. Orders arrive in <a href="/admin/orders">Orders</a>.</li>
    </ol>
  </section>
<?php endif; ?>

<?php if ($key === 'images'): ?>
  <?php $stockSlots = stock_slots(); $stockSaved = count(array_filter($stockSlots)); ?>
  <section class="panel photo-store">
    <div>
      <h2>Photos stored on your website</h2>
      <p class="muted">
        <strong><?= $stockSaved ?> of <?= count($stockSlots) ?></strong> Unsplash photos are saved on this website.
        Saved photos load faster, keep visitors' browsing private and don't depend on Unsplash.
        <?php if ($stockSaved < count($stockSlots)): ?>The others load from Unsplash until you save them.<?php endif; ?>
        After you change a photo, click save again.
      </p>
    </div>
    <?php if ($stockSaved < count($stockSlots)): ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="save_photos" value="1">
      <button type="submit" class="btn btn--primary" data-busy-label="Saving photos… this can take a minute"><?= icon('download', 'icon icon-sm') ?> Save photos to this website</button>
    </form>
    <?php endif; ?>
    <ul class="photo-grid" aria-label="Every photo on the site">
      <?php foreach (content('images') as $slot => $img): ?>
        <?php
          $slot = (string) $slot;
          $state = !empty($img['file']) ? ['Your upload', 'ok'] : (!empty($stockSlots[$slot]) ? ['Saved on your site', 'ok'] : (empty($img['id']) ? ['No photo', 'warn'] : ['Loads from Unsplash', 'muted']));
        ?>
        <li class="photo-grid__item">
          <div class="photo-grid__img"><?= photo($slot, '200px', ['alt' => '']) ?></div>
          <span class="photo-grid__label"><?= e(image_usage()[$slot] ?? ucfirst($slot)) ?></span>
          <span class="photo-grid__state photo-grid__state--<?= $state[1] ?>"><?= e($state[0]) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
<?php endif; ?>

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
