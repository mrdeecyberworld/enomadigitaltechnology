<?php
/** /ads.txt: tells ad buyers which sellers may show ads on this site (Google AdSense). */
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
header_remove('Content-Security-Policy');
header('Content-Type: text/plain; charset=utf-8');
$client = adsense_client();
if ($client !== '') {
    echo 'google.com, ' . substr($client, 3) . ", DIRECT, f08c47fec0942fa0\n";
} else {
    echo "# No ad sellers authorised.\n";
}
