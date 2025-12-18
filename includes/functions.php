<?php
/**
 * Functions Library for ISU Lost & Found System
 * 
 * This file contains all utility functions used throughout the application including:
 * - Session management and authentication helpers
 * - CSRF token generation and validation
 * - Input sanitization and output escaping
 * - File upload handling
 * - Flash message system
 * - Date formatting and text utilities
 * 
 * @package ISU_Lost_Found
 * @since 1.0.0
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Initialize database
$db = Database::getInstance();

// Build absolute paths for assets and internal links
function app_url($path = '')
{
    $base = rtrim(APP_BASE_URL, '/');
    if ($path === '' || $path === null) {
        return $base . '/';
    }
    return $base . '/' . ltrim($path, '/');
}

// CSRF token generation
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Check if user is logged in
function is_logged_in()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function is_admin()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Redirect if not logged in
function require_login()
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Redirect if not admin
function require_admin()
{
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}

// Get current user
function get_logged_in_user()
{
    if (!is_logged_in()) {
        return null;
    }
    global $db;
    return $db->getUserById($_SESSION['user_id']);
}

// Sanitize output
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Format date
function format_date($date)
{
    return date('M d, Y', strtotime($date));
}

// Truncate text
function truncate($text, $limit = 120)
{
    $text = strip_tags($text);
    return strlen($text) <= $limit ? $text : substr($text, 0, $limit) . '…';
}

// Status chip HTML
function status_chip($status)
{
    $map = [
        'active' => 'status-active',
        'claimed' => 'status-success'
    ];

    $class = $map[$status] ?? 'status-muted';
    return '<span class="status-chip ' . $class . '">' . ucfirst($status) . '</span>';
}

// Get category label
function get_category_label($key, $categories)
{
    foreach ($categories as $cat) {
        if (is_array($cat) && isset($cat['name'])) {
            if ($cat['name'] === $key || strtolower(str_replace(['&', ' '], ['', '_'], $cat['name'])) === $key) {
                return $cat['name'];
            }
        }
    }
    return ucwords(str_replace('_', ' ', $key));
}

// Upload image helper
function upload_image($file, $folder = 'lost')
{
    $upload_dir = __DIR__ . '/../assets/uploads/' . $folder . '/';
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB

    // Validate file
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File too large (max 2MB)'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);

    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'path' => 'assets/uploads/' . $folder . '/' . $filename];
    }

    return ['success' => false, 'error' => 'Upload failed'];
}

// Set flash message
function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Get and clear flash message
function get_flash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Get user IP
function get_client_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

// Validate ISU email
function is_isu_email($email)
{
    return substr(strtolower($email), -11) === '@isu.edu.ph';
}

// Validate password strength
function is_strong_password($password)
{
    return strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]/', $password);
}
