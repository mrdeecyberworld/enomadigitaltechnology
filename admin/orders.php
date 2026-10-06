<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    $id = (string) ($_POST['id'] ?? '');
    $order = order_find($id);
    $action = (string) ($_POST['action'] ?? '');
    if (!$order) {
        flash('That order no longer exists.', 'error');
        redirect('/admin/orders');
    }
    if ($action === 'status' && isset(ORDER_STATUSES[$_POST['status'] ?? ''])) {
        $order['status'] = (string) $_POST['status'];
        $order['updated_at'] = date('c');
        order_save($order);
        flash('Status updated.');
    } elseif ($action === 'link') {
        $product = shop_find((string) $order['product_slug'], true);
        if (!$product || !product_has_delivery($product)) {
            flash('This product has no file or download link yet. Add one in Courses & Tools (“File customers receive” or “Download or access link”).', 'error');
        } else {
            $days = max(1, min(90, (int) ($_POST['days'] ?? 7)));
            $max = max(1, min(50, (int) ($_POST['max'] ?? 5)));
            $url = download_link_create($order, $product, $days, $max);
            if ($url) {
                if (in_array($order['status'], ['new', 'awaiting'], true)) {
                    $order['status'] = 'paid';
                }
                order_save($order);
                flash('Download link created. Copy it below and email it to ' . $order['name'] . '.');
            } else {
                flash('The link could not be saved. Check that the storage folder is writable.', 'error');
            }
        }
    } elseif ($action === 'delete') {
        store_delete(order_file($id));
        flash('Order deleted.');
        redirect('/admin/orders');
    }
    redirect('/admin/orders?id=' . rawurlencode($id));
}

// Single order
if (!empty($_GET['id'])) {
    $o = order_find((string) $_GET['id']);
    if (!$o) {
        flash('That order no longer exists.', 'error');
        redirect('/admin/orders');
    }
    $product = shop_find((string) $o['product_slug'], true);
    $hasDelivery = $product && product_has_delivery($product);
    $isCourse = in_array($o['product_type'] ?? '', ['Course', 'Workshop'], true);
    $payBody = 'Hi ' . $o['name'] . ",\n\nThank you for your order of " . $o['product_name'] . ($o['price'] !== '' ? ' (' . $o['price'] . ')' : '') . ".\n\nHere is how to pay:\n[add your payment details here]\n\nOnce your payment is confirmed, I will send you your " . ($isCourse ? 'course access' : 'download link') . ".\n\nBest regards,\n" . site('name');
    $lastLink = $o['downloads'] ? end($o['downloads']) : null;
    $sendBody = $lastLink ? 'Hi ' . $o['name'] . ",\n\nThank you for your payment. Here is your " . ($isCourse ? 'course access' : 'download') . ' link for ' . $o['product_name'] . ":\n\n" . $lastLink['url'] . "\n\nThe link works until " . date('F j, Y', strtotime($lastLink['expires'])) . ' and can be used ' . $lastLink['max'] . " times.\n\nBest regards,\n" . site('name') : '';
    admin_header('Order from ' . $o['name'], 'orders');
    ?>
    <p><a class="link" href="/admin/orders">← All orders</a></p>
    <article class="panel message">
      <header>
        <h1><?= e($o['product_name']) ?></h1>
        <p class="muted">Order <?= e($o['id']) ?> · <?= e(date('l, F j, Y \a\t g:i a', strtotime($o['created_at']))) ?></p>
      </header>
      <dl class="meta">
        <dt>Customer</dt><dd><?= e($o['name']) ?></dd>
        <dt>Email</dt><dd><a href="mailto:<?= e($o['email']) ?>"><?= e($o['email']) ?></a></dd>
        <?php if ($o['country'] !== ''): ?><dt>Country</dt><dd><?= e($o['country']) ?></dd><?php endif; ?>
        <dt>Price</dt><dd><?= e($o['price'] !== '' ? $o['price'] : '—') ?></dd>
        <dt>Status</dt><dd><span class="pill pill--order-<?= e($o['status']) ?>"><?= e(ORDER_STATUSES[$o['status']] ?? $o['status']) ?></span></dd>
      </dl>
      <?php if ($o['note'] !== ''): ?><div class="message__body"><?= nl2br(e($o['note'])) ?></div><?php endif; ?>

      <h2 class="order-step">1. Send payment details</h2>
      <p class="muted">Email your customer how to pay (bank transfer, PayPal, mobile money…). Opens your email app with a ready-made message.</p>
      <div class="actions">
        <a class="btn btn--primary" href="mailto:<?= e($o['email']) ?>?subject=<?= rawurlencode('Payment for your order: ' . $o['product_name']) ?>&amp;body=<?= rawurlencode($payBody) ?>"><?= icon('mail', 'icon icon-sm') ?> Email payment details</a>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($o['id']) ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="status" value="awaiting"><button class="btn btn--light">Mark as waiting for payment</button></form>
      </div>

      <h2 class="order-step">2. After payment: send the <?= $isCourse ? 'course access' : 'download' ?> link</h2>
      <?php if (!$hasDelivery): ?>
        <p class="notice">This product has no file or link to deliver yet. Upload it in <a href="/admin/files">Product files</a> (or paste a Google Drive / Dropbox / course link), then choose it on the product in <a href="/admin/edit?section=shop">Courses &amp; Tools</a>.</p>
      <?php else: ?>
        <form method="post" class="inline-form">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($o['id']) ?>"><input type="hidden" name="action" value="link">
          <label>Works for <input type="number" name="days" value="7" min="1" max="90" class="input-sm"> days</label>
          <label>up to <input type="number" name="max" value="5" min="1" max="50" class="input-sm"> downloads</label>
          <button class="btn btn--primary"><?= icon('link-2', 'icon icon-sm') ?> Create private link</button>
        </form>
      <?php endif; ?>
      <?php if ($o['downloads']): ?>
        <ul class="link-list">
          <?php foreach (array_reverse($o['downloads']) as $d): ?>
            <li><input type="text" readonly value="<?= e($d['url']) ?>" aria-label="Download link"><span class="muted small">Until <?= e(date('M j, Y', strtotime($d['expires']))) ?> · <?= (int) $d['max'] ?> downloads</span></li>
          <?php endforeach; ?>
        </ul>
        <div class="actions">
          <a class="btn btn--primary" href="mailto:<?= e($o['email']) ?>?subject=<?= rawurlencode('Your ' . ($isCourse ? 'course access' : 'download') . ': ' . $o['product_name']) ?>&amp;body=<?= rawurlencode($sendBody) ?>"><?= icon('send', 'icon icon-sm') ?> Email the link to <?= e($o['name']) ?></a>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($o['id']) ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="status" value="delivered"><button class="btn btn--light">Mark as delivered</button></form>
        </div>
      <?php endif; ?>

      <h2 class="order-step">Status</h2>
      <form method="post" class="inline-form">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($o['id']) ?>"><input type="hidden" name="action" value="status">
        <label for="status" class="sr-only">Status</label>
        <select id="status" name="status"><?php foreach (ORDER_STATUSES as $k => $label): ?><option value="<?= e($k) ?>"<?= $o['status'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select>
        <button class="btn btn--light">Update</button>
      </form>
      <form method="post" data-confirm="Delete this order permanently?" class="actions"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($o['id']) ?>"><button class="btn btn--danger" name="action" value="delete">Delete order</button></form>
    </article>
    <?php
    admin_footer();
    exit;
}

$all = orders_all();
$filter = isset(ORDER_STATUSES[$_GET['status'] ?? '']) ? (string) $_GET['status'] : '';
$list = $filter !== '' ? array_filter($all, static fn ($o) => $o['status'] === $filter) : $all;
admin_header('Orders', 'orders');
?>
<header class="page-head">
  <div>
    <h1>Orders</h1>
    <p class="muted">Orders for your courses, software and tools. While a product has no checkout link, customers order here and you email them how to pay. After payment, create a private download link from the order.</p>
  </div>
</header>

<nav class="chips" aria-label="Filter orders">
  <a href="/admin/orders"<?= $filter === '' ? ' aria-current="page"' : '' ?>>All (<?= count($all) ?>)</a>
  <?php foreach (ORDER_STATUSES as $k => $label): $n = count(array_filter($all, static fn ($o) => $o['status'] === $k)); ?>
    <a href="/admin/orders?status=<?= e($k) ?>"<?= $filter === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?> (<?= $n ?>)</a>
  <?php endforeach; ?>
</nav>

<?php if (!$list): ?>
  <div class="panel empty"><?= icon('shopping-bag', 'icon') ?><p>No orders yet. Add your products in <a href="/admin/edit?section=shop">Courses &amp; Tools</a>; customers can order from each product page.</p></div>
<?php else: ?>
  <div class="panel table-panel">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th scope="col">Customer</th><th scope="col">Product</th><th scope="col">Price</th><th scope="col">Status</th><th scope="col">Received</th></tr></thead>
        <tbody>
          <?php foreach ($list as $o): ?>
            <tr class="<?= $o['status'] === 'new' ? 'is-unread' : '' ?>">
              <td><a href="/admin/orders?id=<?= e($o['id']) ?>"><strong><?= e($o['name']) ?></strong></a><br><span class="muted small"><?= e($o['email']) ?></span></td>
              <td><?= e($o['product_name']) ?></td>
              <td><?= e($o['price']) ?></td>
              <td><span class="pill pill--order-<?= e($o['status']) ?>"><?= e(ORDER_STATUSES[$o['status']] ?? $o['status']) ?></span></td>
              <td class="nowrap"><?= e(date('M j, Y g:i a', strtotime($o['created_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
<?php admin_footer();
