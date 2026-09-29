<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

$dir = storage_dir('submissions');
$safeId = static fn (string $id): string => preg_replace('/[^A-Za-z0-9-]/', '', $id);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    $ids = array_map($safeId, (array) ($_POST['ids'] ?? []));
    $action = (string) ($_POST['action'] ?? '');
    foreach ($ids as $id) {
        $file = $dir . '/' . $id . '.json';
        if (!is_file($file)) {
            continue;
        }
        if ($action === 'delete') {
            unlink($file);
        } elseif ($action === 'read' || $action === 'unread') {
            $m = json_read($file, []);
            $m['read'] = $action === 'read';
            json_write($file, $m);
        }
    }
    flash($action === 'delete' ? 'Deleted.' : 'Updated.');
    redirect('/admin/messages');
}

$all = [];
foreach (glob($dir . '/*.json') ?: [] as $file) {
    $all[] = json_read($file, []);
}
usort($all, static fn ($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

// CSV export
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="enoma-messages-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date', 'Form', 'Name', 'Email', 'Phone', 'Company', 'Service', 'Budget', 'Message', 'Page']);
    foreach ($all as $m) {
        $row = [$m['created_at'], $m['type_label'], $m['name'], $m['email'], $m['phone'], $m['company'], $m['service'], $m['budget'], $m['message'], $m['page']];
        // Neutralize spreadsheet formulas.
        fputcsv($out, array_map(static fn ($v) => preg_match('/^[=+\-@]/', (string) $v) ? "'" . $v : (string) $v, $row));
    }
    exit;
}

// Single message view
if (!empty($_GET['id'])) {
    $id = $safeId((string) $_GET['id']);
    $file = $dir . '/' . $id . '.json';
    $m = json_read($file);
    if (!$m) {
        flash('That message no longer exists.', 'error');
        redirect('/admin/messages');
    }
    if (empty($m['read'])) {
        $m['read'] = true;
        json_write($file, $m);
    }
    admin_header('Message from ' . $m['name'], 'messages');
    $subject = 'Re: your ' . strtolower($m['type_label']) . ' – ' . site('name');
    ?>
    <p><a class="link" href="/admin/messages">← All messages</a></p>
    <article class="panel message">
      <header>
        <h1><?= e($m['name']) ?></h1>
        <p class="muted"><?= e($m['type_label']) ?> · <?= e(date('l, F j, Y \a\t g:i a', strtotime($m['created_at']))) ?></p>
      </header>
      <dl class="meta">
        <dt>Email</dt><dd><a href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode($subject) ?>"><?= e($m['email']) ?></a></dd>
        <?php foreach (['phone' => 'Phone', 'company' => 'Company', 'service' => 'Service', 'budget' => 'Budget', 'page' => 'Sent from page'] as $k => $label): ?>
          <?php if (!empty($m[$k])): ?><dt><?= e($label) ?></dt><dd><?= e($m[$k]) ?></dd><?php endif; ?>
        <?php endforeach; ?>
      </dl>
      <div class="message__body"><?= nl2br(e($m['message'])) ?></div>
      <div class="actions">
        <a class="btn btn--primary" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode($subject) ?>"><?= icon('mail', 'icon icon-sm') ?> Reply by email</a>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="ids[]" value="<?= e($m['id']) ?>"><button class="btn btn--light" name="action" value="unread">Mark as unread</button></form>
        <form method="post" data-confirm="Delete this message permanently?"><?= csrf_field() ?><input type="hidden" name="ids[]" value="<?= e($m['id']) ?>"><button class="btn btn--danger" name="action" value="delete">Delete</button></form>
      </div>
    </article>
    <?php
    admin_footer();
    exit;
}

$filter = (string) ($_GET['type'] ?? '');
$list = $filter !== '' ? array_filter($all, static fn ($m) => ($m['type'] ?? '') === $filter) : $all;

admin_header('Messages', 'messages');
?>
<header class="page-head">
  <div>
    <h1>Messages</h1>
    <p class="muted">Every contact, quote, consultation and service inquiry form submission. Saved privately on your server.</p>
  </div>
  <?php if ($all): ?><a class="btn btn--light" href="/admin/messages?export=csv"><?= icon('file-text', 'icon icon-sm') ?> Export CSV</a><?php endif; ?>
</header>

<nav class="chips" aria-label="Filter messages">
  <a href="/admin/messages"<?= $filter === '' ? ' aria-current="page"' : '' ?>>All (<?= count($all) ?>)</a>
  <?php foreach (FORM_TYPES as $type => $label): $n = count(array_filter($all, static fn ($m) => ($m['type'] ?? '') === $type)); ?>
    <a href="/admin/messages?type=<?= e($type) ?>"<?= $filter === $type ? ' aria-current="page"' : '' ?>><?= e($label) ?> (<?= $n ?>)</a>
  <?php endforeach; ?>
</nav>

<?php if (!$list): ?>
  <div class="panel empty"><?= icon('mail', 'icon') ?><p>No messages yet. When someone fills in a form on your website, it appears here.</p></div>
<?php else: ?>
<form method="post" class="panel table-panel">
  <?= csrf_field() ?>
  <div class="bulk">
    <label class="check"><input type="checkbox" data-check-all> Select all</label>
    <button class="btn btn--light btn--sm" name="action" value="read">Mark read</button>
    <button class="btn btn--light btn--sm" name="action" value="unread">Mark unread</button>
    <button class="btn btn--danger btn--sm" name="action" value="delete" data-confirm-click="Delete the selected messages permanently?">Delete</button>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th scope="col"><span class="sr-only">Select</span></th><th scope="col">From</th><th scope="col">Form</th><th scope="col">Service</th><th scope="col">Received</th></tr></thead>
      <tbody>
        <?php foreach ($list as $m): ?>
          <tr class="<?= empty($m['read']) ? 'is-unread' : '' ?>">
            <td><input type="checkbox" name="ids[]" value="<?= e($m['id']) ?>" aria-label="Select message from <?= e($m['name']) ?>"></td>
            <td><a href="/admin/messages?id=<?= e($m['id']) ?>"><strong><?= e($m['name']) ?></strong></a><br><span class="muted small"><?= e($m['email']) ?></span></td>
            <td><?= e($m['type_label']) ?></td>
            <td><?= e($m['service']) ?></td>
            <td class="nowrap"><?= e(date('M j, Y g:i a', strtotime($m['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</form>
<?php endif; ?>
<?php admin_footer();
