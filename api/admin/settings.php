<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/auth.php';

$token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$payload = verifyToken($token);
if (!$payload) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$restaurantId = $payload['restaurant_id'];
$adminId = $payload['admin_id'];
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch();
    
    $adminStmt = $db->prepare("SELECT id, name, username, last_login FROM admin_users WHERE id = ?");
    $adminStmt->execute([$adminId]);
    $admin = $adminStmt->fetch();
    
    jsonResponse([
        'success' => true,
        'restaurant' => $restaurant,
        'admin' => $admin
    ]);
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'update_restaurant';
    
    if ($action === 'update_restaurant') {
        $nameEn = trim($input['name_en'] ?? '');
        $nameBn = trim($input['name_bn'] ?? '');
        $taglineEn = trim($input['tagline_en'] ?? '');
        $taglineBn = trim($input['tagline_bn'] ?? '');
        
        $stmt = $db->prepare("UPDATE restaurants SET name_en = ?, name_bn = ?, tagline_en = ?, tagline_bn = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$nameEn, $nameBn, $taglineEn, $taglineBn, $restaurantId]);
        jsonResponse(['success' => true, 'message' => 'Restaurant settings updated']);
    }
    
    if ($action === 'update_password') {
        $currentPass = $input['current_password'] ?? '';
        $newPass = $input['new_password'] ?? '';
        $confirmPass = $input['confirm_password'] ?? '';
        
        if ($newPass !== $confirmPass) {
            jsonResponse(['error' => 'New passwords do not match'], 400);
        }
        
        if (strlen($newPass) < 6) {
            jsonResponse(['error' => 'Password must be at least 6 characters'], 400);
        }
        
        $adminStmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
        $adminStmt->execute([$adminId]);
        $adminUser = $adminStmt->fetch();
        
        if (!password_verify($currentPass, $adminUser['password_hash'])) {
            jsonResponse(['error' => 'Current password is incorrect'], 400);
        }
        
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $updateStmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
        $updateStmt->execute([$newHash, $adminId]);
        jsonResponse(['success' => true, 'message' => 'Password updated successfully']);
    }
    
    jsonResponse(['error' => 'Invalid action'], 400);
}

jsonResponse(['error' => 'Invalid method'], 405);
