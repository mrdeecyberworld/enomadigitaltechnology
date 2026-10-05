<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

$dir = feedback_dir();
$safeId = static fn (string $id): string => preg_replace('/[^A-Za-z0-9-]/', '', $id);
$testimonialsFile = content_override_file('testimonials');
$loadTestimonials = static function () use ($testimonialsFile): array {
    $list = json_read($testimonialsFile);
    return is_array($list) ? array_values($list) : content_default('testimonials');
};
// A published review is matched to its testimonial by name and words.
$sameAs = static fn (array $t, array $f): bool => ($t['name'] ?? '') === $f['name'] && ($t['quote'] ?? '') === $f['quote'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    $id = $safeId((string) ($_POST['id'] ?? ''));
    $action = (string) ($_POST['action'] ?? '');
    $file = $dir . '/' . $id . '.json';
    $f = $id !== '' ? json_read($file) : null;
    if (!is_array($f)) {
        flash('That feedback no longer exists.', 'error');
        redirect('/admin/feedback');
    }
    $list = $loadTestimonials();
    $without = array_values(array_filter($list, static fn ($t) => !$sameAs($t, $f)));

    if ($action === 'publish') {
        if (empty($f['consent'])) {
            flash('This client did not give permission to publish their feedback, so it stays private.', 'error');
            redirect('/admin/feedback');
        }
        $without[] = array_filter([
            'quote'   => $f['quote'],
            'name'    => $f['name'],
            'role'    => $f['role'] ?? '',
            'service' => $f['service'] ?? '',
            'rating'  => (string) ($f['rating'] ?? ''),
        ], static fn ($v) => $v !== '');
        $f['status'] = 'published';
        $ok = json_write($testimonialsFile, $without) && json_write($file, $f);
        flash($ok ? 'Published. It now shows in What Clients Say on the homepage and About page.' : 'Could not save. Check that the storage folder is writable.', $ok ? 'success' : 'error');
    } elseif ($action === 'unpublish' || $action === 'archive') {
        $f['status'] = $action === 'archive' ? 'archived' : 'pending';
        $ok = ($without === $list || json_write($testimonialsFile, $without)) && json_write($file, $f);
        flash($ok ? ($action === 'archive' ? 'Kept private.' : 'Removed from the website.') : 'Could not save.', $ok ? 'success' : 'error');
    } elseif ($action === 'delete') {
        if ($without !== $list) {
            json_write($testimonialsFile, $without);
        }
        store_delete($file);
        flash('Deleted.');
    }
    redirect('/admin/feedback');
}

$all = feedback_all();
$labels = ['pending' => 'Waiting for review', 'published' => 'On the website', 'archived' => 'Kept private'];
$filter = isset($labels[$_GET['status'] ?? '']) ? (string) $_GET['status'] : '';
$list = $filter !== '' ? array_filter($all, static fn ($f) => ($f['status'] ?? '') === $filter) : $all;

admin_header('Client feedback', 'feedback');
?>
<header class="page-head">
  <div>
    <h1>Client feedback</h1>
    <p class="muted">Reviews clients send from <a href="<?= e(page_url('feedback')) ?>" target="_blank" rel="noopener"><?= e(site('domain') . page_url('feedback')) ?></a>. Nothing appears on the website until you click Publish.</p>
  </div>
  <a class="btn btn--light" href="/admin/edit?section=testimonials"><?= icon('quote', 'icon icon-sm') ?> Edit testimonials</a>
</header>

<nav class="chips" aria-label="Filter feedback">
  <a href="/admin/feedback"<?= $filter === '' ? ' aria-current="page"' : '' ?>>All (<?= count($all) ?>)</a>
  <?php foreach ($labels as $key => $label): $n = count(array_filter($all, static fn ($f) => ($f['status'] ?? '') === $key)); ?>
    <a href="/admin/feedback?status=<?= e($key) ?>"<?= $filter === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?> (<?= $n ?>)</a>
  <?php endforeach; ?>
</nav>

<?php if (!$list): ?>
  <div class="panel empty"><?= icon('quote', 'icon') ?><p>No feedback yet. Send happy clients the link <strong><?= e(rtrim((string) cfg('base_url'), '/') . page_url('feedback')) ?></strong> and their reviews will appear here for you to approve.</p></div>
<?php else: ?>
  <div class="feedback-list">
    <?php foreach ($list as $f): $status = (string) ($f['status'] ?? 'pending'); ?>
      <article class="panel feedback-item feedback-item--<?= e($status) ?>">
        <header class="feedback-item__head">
          <div>
            <h2><?= e($f['name']) ?></h2>
            <p class="muted small"><?= e(implode(' · ', array_filter([$f['role'] ?? '', $f['service'] ?? '']))) ?></p>
          </div>
          <div class="feedback-item__meta">
            <?= rating_stars((int) ($f['rating'] ?? 0)) ?>
            <span class="pill pill--<?= e($status) ?>"><?= e($labels[$status] ?? $status) ?></span>
          </div>
        </header>
        <blockquote class="feedback-item__quote"><?= nl2br(e($f['quote'])) ?></blockquote>
        <p class="muted small">
          <a href="mailto:<?= e($f['email']) ?>"><?= e($f['email']) ?></a> ·
          <?= e(date('M j, Y g:i a', strtotime($f['created_at']))) ?> ·
          <?= !empty($f['consent']) ? 'Gave permission to publish' : '<strong>Did not give permission to publish</strong>' ?>
        </p>
        <div class="actions">
          <?php if ($status !== 'published' && !empty($f['consent'])): ?>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($f['id']) ?>"><button class="btn btn--primary" name="action" value="publish"><?= icon('check', 'icon icon-sm') ?> Publish on website</button></form>
          <?php endif; ?>
          <?php if ($status === 'published'): ?>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($f['id']) ?>"><button class="btn btn--light" name="action" value="unpublish">Remove from website</button></form>
          <?php elseif ($status === 'pending'): ?>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($f['id']) ?>"><button class="btn btn--light" name="action" value="archive">Keep private</button></form>
          <?php endif; ?>
          <form method="post" data-confirm="Delete this feedback permanently?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($f['id']) ?>"><button class="btn btn--danger" name="action" value="delete">Delete</button></form>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php admin_footer();
