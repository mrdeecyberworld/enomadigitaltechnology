<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

$file = storage_dir('content') . '/settings.json';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    $theme = ($_POST['theme'] ?? '') === 'light' ? 'light' : 'dark';
    $palette = (string) ($_POST['palette'] ?? 'midnight');
    $accent = strtolower(trim((string) ($_POST['accent'] ?? '')));
    if (!isset(palettes()[$palette])) {
        $palette = 'midnight';
    }
    if (!empty($_POST['use_custom']) && preg_match('/^#[0-9a-f]{6}$/', $accent)) {
        // keep
    } else {
        $accent = '';
    }
    $all = json_read($file, []);
    $all['appearance'] = ['theme' => $theme, 'palette' => $palette, 'accent' => $accent];
    unset($all['site_theme']);
    json_write($file, $all) ? flash('Colors saved. Your website has the new look.') : flash('Could not save. Check that the storage folder is writable.', 'error');
    redirect('/admin/appearance');
}

$look = appearance();
admin_header('Appearance', 'appearance');
?>
<header class="page-head">
  <div>
    <h1>Appearance</h1>
    <p class="muted">Choose your website’s colors. Changes apply to every page as soon as you save. Text contrast is checked automatically so everything stays readable.</p>
  </div>
  <a class="btn btn--light" href="/" target="_blank" rel="noopener"><?= icon('arrow-up-right', 'icon icon-sm') ?> View website</a>
</header>

<form method="post" class="stack" data-editor>
  <?= csrf_field() ?>

  <section class="panel">
    <h2>Background</h2>
    <div class="choice-row" role="radiogroup" aria-label="Background">
      <label class="choice"><input type="radio" name="theme" value="dark"<?= $look['theme'] === 'dark' ? ' checked' : '' ?>><span class="choice__preview choice__preview--dark"><span></span><span></span><span></span></span><strong>Dark premium</strong><span class="muted small">Deep, rich backgrounds with light text.</span></label>
      <label class="choice"><input type="radio" name="theme" value="light"<?= $look['theme'] === 'light' ? ' checked' : '' ?>><span class="choice__preview choice__preview--light"><span></span><span></span><span></span></span><strong>Light</strong><span class="muted small">White backgrounds with dark text.</span></label>
    </div>
  </section>

  <section class="panel">
    <h2>Color palette</h2>
    <div class="palette-grid" role="radiogroup" aria-label="Color palette">
      <?php foreach (palettes() as $key => $p): [$bg, $surface, $accent, $accent2] = $p['swatch']; ?>
        <label class="palette">
          <input type="radio" name="palette" value="<?= e($key) ?>"<?= $look['palette'] === $key ? ' checked' : '' ?>>
          <span class="palette__art" data-bg="<?= e($bg) ?>" data-surface="<?= e($surface) ?>" data-accent="<?= e($accent) ?>" data-accent2="<?= e($accent2) ?>">
            <svg viewBox="0 0 240 120" aria-hidden="true">
              <defs><linearGradient id="pg-<?= e($key) ?>" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="<?= e($accent) ?>"/><stop offset="1" stop-color="<?= e($accent2) ?>"/></linearGradient>
              <radialGradient id="pr-<?= e($key) ?>" cx=".85" cy=".1" r=".8"><stop offset="0" stop-color="<?= e($accent) ?>" stop-opacity=".55"/><stop offset="1" stop-color="<?= e($accent) ?>" stop-opacity="0"/></radialGradient></defs>
              <rect width="240" height="120" fill="<?= e($bg) ?>"/><rect width="240" height="120" fill="url(#pr-<?= e($key) ?>)"/>
              <rect x="16" y="18" width="92" height="10" rx="5" fill="#ffffff" fill-opacity=".9"/>
              <rect x="16" y="34" width="70" height="10" rx="5" fill="url(#pg-<?= e($key) ?>)"/>
              <rect x="16" y="56" width="110" height="5" rx="2.5" fill="#ffffff" fill-opacity=".35"/>
              <rect x="16" y="66" width="90" height="5" rx="2.5" fill="#ffffff" fill-opacity=".35"/>
              <rect x="16" y="84" width="54" height="18" rx="6" fill="<?= e($accent) ?>"/>
              <rect x="148" y="22" width="76" height="80" rx="10" fill="<?= e($surface) ?>" stroke="#ffffff" stroke-opacity=".12"/>
              <rect x="160" y="36" width="20" height="20" rx="6" fill="<?= e($accent) ?>" fill-opacity=".35"/>
              <rect x="160" y="66" width="50" height="5" rx="2.5" fill="#ffffff" fill-opacity=".5"/>
              <rect x="160" y="78" width="38" height="5" rx="2.5" fill="#ffffff" fill-opacity=".3"/>
            </svg>
          </span>
          <span class="palette__text"><strong><?= e($p['name']) ?></strong><span class="muted small"><?= e($p['note']) ?></span></span>
        </label>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="panel">
    <h2>Custom accent color <span class="muted small">(optional)</span></h2>
    <p class="muted small">Use your own brand color for buttons, links and highlights instead of the palette’s. Lighter and darker shades are created automatically, and button text stays readable.</p>
    <div class="accent-row">
      <label class="check"><input type="checkbox" name="use_custom" value="1" data-use-custom<?= $look['accent'] !== '' ? ' checked' : '' ?>> Use a custom accent color</label>
      <label for="accent" class="sr-only">Accent color</label>
      <input type="color" id="accent" name="accent" value="<?= e($look['accent'] ?: palettes()[$look['palette']]['swatch'][2]) ?>" data-accent-input>
      <code data-accent-hex><?= e($look['accent'] ?: palettes()[$look['palette']]['swatch'][2]) ?></code>
    </div>
  </section>

  <div class="savebar">
    <span class="muted small" data-dirty-note>All changes saved.</span>
    <button type="submit" class="btn btn--primary"><?= icon('check', 'icon icon-sm') ?> Save colors</button>
  </div>
</form>
<?php admin_footer();
