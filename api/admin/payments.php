<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/auth_helpers.php';

$payload = requireAuth();
$restaurantId = $payload['restaurant_id'];
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM payment_methods WHERE restaurant_id = ? ORDER BY sort_order");
    $stmt->execute([$restaurantId]);
    $methods = $stmt->fetchAll();
    jsonResponse(['success' => true, 'payments' => $methods]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nameEn = trim($input['name_en'] ?? '');
    $nameBn = trim($input['name_bn'] ?? '');
    $methodType = $input['method_type'] ?? 'other';
    $accountNumber = trim($input['account_number'] ?? '');
    $accountName = trim($input['account_name'] ?? '');
    $instructions = trim($input['instructions'] ?? '');
    $icon = trim($input['icon'] ?? 'payments');
    $sortOrder = (int)($input['sort_order'] ?? 0);
    
    $stmt = $db->prepare("INSERT INTO payment_methods (restaurant_id, name_en, name_bn, method_type, account_number, account_name, instructions, icon, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$restaurantId, $nameEn, $nameBn, $methodType, $accountNumber, $accountName, $instructions, $icon, $sortOrder]);
    jsonResponse(['success' => true, 'message' => 'Payment method added', 'id' => $db->lastInsertId()]);
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $nameEn = trim($input['name_en'] ?? '');
    $nameBn = trim($input['name_bn'] ?? '');
    $methodType = $input['method_type'] ?? 'other';
    $accountNumber = trim($input['account_number'] ?? '');
    $accountName = trim($input['account_name'] ?? '');
    $instructions = trim($input['instructions'] ?? '');
    $icon = trim($input['icon'] ?? 'payments');
    $sortOrder = (int)($input['sort_order'] ?? 0);
    $isActive = isset($input['is_active']) ? ($input['is_active'] ? 1 : 0) : 1;
    
    $stmt = $db->prepare("UPDATE payment_methods SET name_en = ?, name_bn = ?, method_type = ?, account_number = ?, account_name = ?, instructions = ?, icon = ?, sort_order = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$nameEn, $nameBn, $methodType, $accountNumber, $accountName, $instructions, $icon, $sortOrder, $isActive, $id, $restaurantId]);
    jsonResponse(['success' => true, 'message' => 'Payment method updated']);
}

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    $stmt = $db->prepare("DELETE FROM payment_methods WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$id, $restaurantId]);
    jsonResponse(['success' => true, 'message' => 'Payment method deleted']);
}

jsonResponse(['error' => 'Invalid method'], 405);
