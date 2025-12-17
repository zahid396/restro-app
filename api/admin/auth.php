<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

function generateToken($adminId, $restaurantId) {
    $payload = [
        'admin_id' => $adminId,
        'restaurant_id' => $restaurantId,
        'exp' => time() + 86400
    ];
    return base64_encode(json_encode($payload) . '.' . hash('sha256', json_encode($payload) . 'admin_secret_key'));
}

function verifyToken($token) {
    if (empty($token)) return null;
    $token = str_replace('Bearer ', '', $token);
    $parts = explode('.', base64_decode($token));
    if (count($parts) !== 2) return null;
    $payload = json_decode($parts[0], true);
    if (!$payload) return null;
    $expectedHash = hash('sha256', $parts[0] . 'admin_secret_key');
    if (!hash_equals($expectedHash, $parts[1])) return null;
    if (isset($payload['exp']) && $payload['exp'] < time()) return null;
    return $payload;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        jsonResponse(['error' => 'Username and password required'], 400);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        jsonResponse(['error' => 'Invalid username or password'], 401);
    }
    
    $updateStmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$admin['id']]);
    
    $token = generateToken($admin['id'], $admin['restaurant_id']);
    
    jsonResponse([
        'success' => true,
        'token' => $token,
        'admin' => [
            'id' => $admin['id'],
            'name' => $admin['name'],
            'username' => $admin['username'],
            'restaurant_id' => $admin['restaurant_id']
        ]
    ]);
}

if ($method === 'GET' && $action === 'verify') {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $payload = verifyToken($token);
    
    if (!$payload) {
        jsonResponse(['error' => 'Invalid or expired token'], 401);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, username, restaurant_id FROM admin_users WHERE id = ? AND is_active = 1");
    $stmt->execute([$payload['admin_id']]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        jsonResponse(['error' => 'Admin not found'], 401);
    }
    
    jsonResponse([
        'success' => true,
        'admin' => $admin
    ]);
}

if ($method === 'POST' && $action === 'logout') {
    jsonResponse(['success' => true, 'message' => 'Logged out']);
}

jsonResponse(['error' => 'Invalid action'], 400);
