<?php
/**
 * Selling software and courses before (and after) online payment is set up.
 *
 * - Product files (software, course material) are uploaded in Admin → Product files
 *   and stored privately in the storage folder, never in a public web folder.
 * - Visitors order from the product page; orders appear in Admin → Orders.
 * - Once paid, you create a private download link for the order (it expires and
 *   allows a limited number of downloads) and email it to your customer.
 * - Later, add a checkout link to a product and the Buy button goes there instead.
 */

declare(strict_types=1);

/** File types that can be sold as downloads. */
const PRODUCT_FILE_TYPES = [
    'zip', '7z', 'rar', 'tar', 'gz', 'pdf', 'epub', 'exe', 'msi', 'dmg', 'pkg', 'apk', 'deb', 'rpm', 'appimage', 'iso',
    'mp4', 'mov', 'mp3', 'm4a', 'docx', 'xlsx', 'pptx', 'csv', 'txt', 'md', 'json', 'png', 'jpg', 'jpeg',
];

const ORDER_STATUSES = [
    'new'       => 'New',
    'awaiting'  => 'Waiting for payment',
    'paid'      => 'Paid',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled',
];

/* ---------- Product files (private) ---------- */

function product_files_dir(): string
{
    $dir = storage_dir('product-files');
    // Belt and braces: block direct web access even if storage sits inside public_html.
    if (!is_file($dir . '/.htaccess')) {
        @file_put_contents($dir . '/.htaccess', "Require all denied\n");
    }
    return $dir;
}

/** Uploaded product files: filename => size in bytes. */
function product_files(): array
{
    $out = [];
    foreach ((array) @scandir(product_files_dir()) as $name) {
        $path = product_files_dir() . '/' . $name;
        if ($name !== '' && $name[0] !== '.' && is_file($path)) {
            $out[$name] = (int) filesize($path);
        }
    }
    ksort($out, SORT_NATURAL | SORT_FLAG_CASE);
    return $out;
}

function product_file_path(string $name): ?string
{
    $name = basename($name);
    $path = product_files_dir() . '/' . $name;
    return $name !== '' && $name[0] !== '.' && is_file($path) ? $path : null;
}

function format_bytes(int $bytes): string
{
    foreach (['GB' => 1 << 30, 'MB' => 1 << 20, 'KB' => 1 << 10] as $unit => $size) {
        if ($bytes >= $size) {
            return round($bytes / $size, 1) . ' ' . $unit;
        }
    }
    return $bytes . ' bytes';
}

/**
 * Store an uploaded product file.
 * @return array{0: ?string, 1: ?string} [stored filename, error message]
 */
function product_file_store(array $upload): array
{
    $err = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        return [null, 'That file is larger than your hosting allows (' . format_bytes(media_server_limit_raw()) . '). Upload it to Google Drive or Dropbox and paste its link on the product instead.'];
    }
    if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($upload['tmp_name'] ?? ''))) {
        return [null, 'The upload did not finish. Please try again.'];
    }
    $original = (string) ($upload['name'] ?? 'file');
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($ext, PRODUCT_FILE_TYPES, true)) {
        return [null, 'That file type (.' . $ext . ') is not allowed. Put it in a .zip file and upload that instead.'];
    }
    $base = trim((string) preg_replace('/[^A-Za-z0-9._-]+/', '-', pathinfo($original, PATHINFO_FILENAME)), '-.') ?: 'file';
    $name = $base . '.' . $ext;
    for ($i = 2; is_file(product_files_dir() . '/' . $name); $i++) {
        $name = $base . '-' . $i . '.' . $ext;
    }
    if (!move_uploaded_file((string) $upload['tmp_name'], product_files_dir() . '/' . $name)) {
        return [null, 'The file could not be saved. Check that the storage folder is writable.'];
    }
    @chmod(product_files_dir() . '/' . $name, 0640);
    return [$name, null];
}

/** Upload limit from PHP settings only (product files are not resized like photos). */
function media_server_limit_raw(): int
{
    $toBytes = static function (string $v): int {
        $v = trim($v);
        $n = (int) $v;
        return match (strtolower(substr($v, -1))) { 'g' => $n << 30, 'm' => $n << 20, 'k' => $n << 10, default => $n };
    };
    $limits = array_filter([$toBytes((string) ini_get('upload_max_filesize')), $toBytes((string) ini_get('post_max_size'))]);
    return $limits ? min($limits) : 2 << 20;
}

/* ---------- Orders ---------- */

function orders_all(): array
{
    $all = [];
    foreach (store_list('orders') as $file) {
        $o = json_read($file, []);
        if (!empty($o['id'])) {
            $all[] = $o;
        }
    }
    usort($all, static fn ($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
    return $all;
}

function order_file(string $id): string
{
    return storage_dir('orders') . '/' . preg_replace('/[^A-Za-z0-9-]/', '', $id) . '.json';
}

function order_find(string $id): ?array
{
    $o = json_read(order_file($id));
    return is_array($o) && !empty($o['id']) ? $o : null;
}

function order_save(array $o): bool
{
    return json_write(order_file((string) $o['id']), $o);
}

function orders_new_count(): int
{
    return count(array_filter(orders_all(), static fn ($o) => ($o['status'] ?? '') === 'new'));
}

/** Whether a product has something to deliver (an uploaded file or a download link). */
function product_has_delivery(array $p): bool
{
    return (($p['file'] ?? '') !== '' && product_file_path((string) $p['file'])) || trim((string) ($p['download_url'] ?? '')) !== '';
}

/** Order form on a product page (used while the product has no checkout link). */
function handle_order_form(string $formId, array $product): array
{
    start_session();
    $state = ['values' => [], 'errors' => [], 'status' => null, 'message' => ''];
    if (isset($_SESSION['form_result'][$formId])) {
        $state = array_replace($state, $_SESSION['form_result'][$formId]);
        unset($_SESSION['form_result'][$formId]);
        return $state;
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['form_id'] ?? '') !== $formId) {
        return $state;
    }

    $in = static fn (string $k, int $max): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
    $v = ['name' => $in('name', 100), 'email' => $in('email', 254), 'country' => $in('country', 80), 'note' => $in('note', 2000)];
    $errors = [];
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $errors['_form'] = 'Your session expired. Please submit the form again.';
    }
    $startedAt = (int) ($_POST['started'] ?? 0);
    $isBot = ($_POST['website'] ?? '') !== '' || $startedAt === 0 || (time() - $startedAt) < 3;
    if ($v['name'] === '') {
        $errors['name'] = 'Please enter your name.';
    }
    if ($v['email'] === '') {
        $errors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address, like name@example.com.';
    }
    if ($errors) {
        return array_replace($state, ['values' => $v, 'errors' => $errors, 'status' => 'error', 'message' => $errors['_form'] ?? 'Please correct the highlighted fields and try again.']);
    }

    $ok = true;
    if (!$isBot) {
        $id = date('Ymd-His') . '-' . bin2hex(random_bytes(3));
        $order = [
            'id'           => $id,
            'status'       => 'new',
            'product_slug' => $product['slug'],
            'product_name' => $product['name'],
            'product_type' => $product['type'],
            'price'        => shop_price($product),
            'name'         => $v['name'],
            'email'        => $v['email'],
            'country'      => $v['country'],
            'note'         => $v['note'],
            'created_at'   => date('c'),
            'downloads'    => [],
        ];
        $ok = order_save($order);
        if ($ok && mail_enabled()) {
            $base = rtrim((string) cfg('base_url'), '/');
            send_mail([
                'to'       => mail_settings()['to'],
                'subject'  => 'New order: ' . $product['name'] . ' from ' . $v['name'],
                'text'     => "New order from your website.\n\nProduct: " . $product['name'] . ($order['price'] !== '' ? ' (' . $order['price'] . ')' : '')
                    . "\nName: " . $v['name'] . "\nEmail: " . $v['email'] . ($v['country'] !== '' ? "\nCountry: " . $v['country'] : '')
                    . ($v['note'] !== '' ? "\n\nNote:\n" . $v['note'] : '')
                    . "\n\nOpen the order: " . $base . '/admin/orders?id=' . $id
                    . "\nReply to this email to send " . $v['name'] . ' payment details.',
                'reply_to' => $v['email'],
            ]);
            send_mail([
                'to'       => $v['email'],
                'subject'  => 'We received your order: ' . $product['name'],
                'text'     => 'Hi ' . $v['name'] . ",\n\nThank you for ordering " . $product['name'] . ' from ' . site('name') . ".\n\n"
                    . "We will email you shortly with how to pay. Once your payment is confirmed, you will receive your access or download link.\n\nBest regards,\n" . site('name'),
                'reply_to' => mail_settings()['to'],
            ]);
        }
    }

    $result = $ok
        ? ['status' => 'success', 'message' => 'Thank you, ' . $v['name'] . '! Your order has been received. We will email you at ' . $v['email'] . ' with how to pay, then send your access or download link.']
        : ['status' => 'error', 'message' => 'Sorry, your order could not be saved right now. Please try again later or contact us.'];
    $_SESSION['form_result'][$formId] = $result + ['values' => $ok ? [] : $v];
    header('Location: ' . strtok($_SERVER['REQUEST_URI'] ?? '/', '?') . '#' . $formId, true, 303);
    exit;
}

/* ---------- Private download links ---------- */

function download_file(string $token): string
{
    return storage_dir('download-links') . '/' . preg_replace('/[^a-f0-9]/', '', $token) . '.json';
}

/** Create a download link for an order. Returns the full URL. */
function download_link_create(array &$order, array $product, int $days = 7, int $maxDownloads = 5): ?string
{
    $token = bin2hex(random_bytes(20));
    $ok = json_write(download_file($token), [
        'token'         => $token,
        'order_id'      => $order['id'],
        'product_slug'  => $product['slug'],
        'file'          => (string) ($product['file'] ?? ''),
        'url'           => trim((string) ($product['download_url'] ?? '')),
        'expires'       => time() + $days * 86400,
        'max_downloads' => $maxDownloads,
        'count'         => 0,
        'created_at'    => date('c'),
    ]);
    if (!$ok) {
        return null;
    }
    $url = rtrim((string) cfg('base_url'), '/') . '/download/' . $token;
    $order['downloads'][] = ['token' => $token, 'url' => $url, 'expires' => date('c', time() + $days * 86400), 'max' => $maxDownloads];
    return $url;
}
