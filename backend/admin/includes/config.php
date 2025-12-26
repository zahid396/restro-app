<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

require_once __DIR__ . '/../../api/includes/config.php';
require_once __DIR__ . '/../../api/includes/db.php';

define('SESSION_TIMEOUT', 3600);

function isLoggedIn() {
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        return false;
    }
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        return false;
    }
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}

function verifyCsrf() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCsrfToken($token)) {
        http_response_code(403);
        die('Invalid security token. Please refresh the page and try again.');
    }
}

function getAdminUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ? AND is_active = true");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

function formatPrice($price) {
    return '৳' . number_format($price);
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return date('M j, Y', $time);
}
