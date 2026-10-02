<?php
/**
 * Save the site's photos onto this website (run by start.php; the same as
 * "Save photos to this website" in Admin → Photos). Usage: php includes/cli/download-photos.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}
require dirname(__DIR__) . '/bootstrap.php';

$missing = count(array_filter(stock_slots(), static fn (bool $saved) => !$saved));
if ($missing === 0) {
    exit(0);
}
echo "  Saving $missing photos to your website (one time only)...\n";
$result = stock_download_all(static function (string $key, int $done, int $total, ?string $error): void {
    echo '    ' . str_pad("$done/$total", 6) . ($error === null ? "$key" : "$key: not saved ($error)") . "\n";
});
if ($result['offline']) {
    echo "  No internet connection right now. Photos will load from Unsplash when you're online,\n  and will be saved the next time you start the site.\n";
} else {
    echo '  Photos saved: ' . $result['saved'] . ($result['failed'] ? ', not saved: ' . count($result['failed']) : '') . "\n";
}
echo "\n";
