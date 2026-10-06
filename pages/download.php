<?php
/**
 * Private download link: /download/{token}
 * Links are created in Admin → Orders. Each one expires and allows a limited
 * number of downloads. Files are streamed from private storage.
 */
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$token = preg_replace('/[^a-f0-9]/', '', (string) ($_GET['token'] ?? ''));
$link = strlen($token) === 40 ? json_read(download_file($token)) : null;
$problem = null;
$path = null;
if (!is_array($link)) {
    $problem = 'This download link is not valid. Please check that you copied the whole link from your email.';
} elseif (time() > (int) $link['expires']) {
    $problem = 'This download link has expired.';
} elseif ((int) $link['count'] >= (int) $link['max_downloads']) {
    $problem = 'This download link has reached its download limit.';
} elseif ($link['file'] !== '' && !($path = product_file_path((string) $link['file']))) {
    $problem = 'This file is no longer available.';
} elseif ($link['file'] === '' && $link['url'] === '') {
    $problem = 'This file is no longer available.';
}

if ($problem === null) {
    $link['count'] = (int) $link['count'] + 1;
    $link['last_download'] = date('c');
    json_write(download_file($token), $link);
    if ($path !== null) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', basename($path)) . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        @set_time_limit(0);
        readfile($path);
        exit;
    }
    header('Location: ' . $link['url'], true, 302);
    exit;
}

http_response_code(410);
$page = ['title' => 'Download unavailable | ' . site('name'), 'description' => '', 'path' => '/download', 'noindex' => true];
require INC . '/layout/header.php';
echo page_hero('Download unavailable', ['eyebrow' => 'Your purchase', 'text' => $problem . ' Contact us and we will send you a new link.']);
?>
<section class="section">
  <div class="container container--narrow section-foot">
    <?= button('Contact Us', page_url('contact'), 'primary', 'arrow-right') ?>
  </div>
</section>
<?php require INC . '/layout/footer.php'; ?>
