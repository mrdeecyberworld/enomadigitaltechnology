<?php
/**
 * Serves every service page, e.g. /cybersecurity -> service.php?slug=cybersecurity
 * (routed by .htaccess). Services are managed in Admin → Services.
 */
declare(strict_types=1);

$serviceSlug = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_GET['slug'] ?? '')));
require __DIR__ . '/includes/templates/service-page.php';
