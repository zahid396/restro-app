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
require_once __DIR__ . '/auth.php';

$token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$payload = verifyToken($token);
if (!$payload) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$restaurantId = $payload['restaurant_id'];
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM restaurant_tables WHERE restaurant_id = ? ORDER BY table_number");
    $stmt->execute([$restaurantId]);
    $tables = $stmt->fetchAll();
    jsonResponse(['success' => true, 'tables' => $tables]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $tableNumber = (int)($input['table_number'] ?? 0);
    $capacity = (int)($input['capacity'] ?? 4);
    
    if ($tableNumber <= 0) {
        jsonResponse(['error' => 'Valid table number required'], 400);
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO restaurant_tables (restaurant_id, table_number, capacity, status) VALUES (?, ?, ?, 'available')");
        $stmt->execute([$restaurantId, $tableNumber, $capacity]);
        jsonResponse(['success' => true, 'message' => 'Table added', 'id' => $db->lastInsertId()]);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Table number already exists'], 400);
    }
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $capacity = (int)($input['capacity'] ?? 4);
    $status = $input['status'] ?? 'available';
    
    $validStatuses = ['available', 'busy', 'reserved'];
    if (!in_array($status, $validStatuses)) {
        $status = 'available';
    }
    
    $stmt = $db->prepare("UPDATE restaurant_tables SET capacity = ?, status = ? WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$capacity, $status, $id, $restaurantId]);
    jsonResponse(['success' => true, 'message' => 'Table updated']);
}

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    $stmt = $db->prepare("DELETE FROM restaurant_tables WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$id, $restaurantId]);
    jsonResponse(['success' => true, 'message' => 'Table deleted']);
}

jsonResponse(['error' => 'Invalid method'], 405);
