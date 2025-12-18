<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    $db->logActivity($_SESSION['user_id'], 'user_logout', 'User logged out', get_client_ip());
    session_destroy();
    setcookie('remember_token', '', time() - 3600, '/');
}

set_flash('info', 'You have been logged out.');
header('Location: index.php');
exit;
