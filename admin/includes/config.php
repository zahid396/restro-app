<?php
session_start();

require_once __DIR__ . '/../../api/includes/config.php';
require_once __DIR__ . '/../../api/includes/db.php';

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
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
