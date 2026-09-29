<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    start_session();
    unset($_SESSION['admin_user'], $_SESSION['admin_seen']);
    session_regenerate_id(true);
    flash('You are logged out.');
}
redirect('/admin/login');
