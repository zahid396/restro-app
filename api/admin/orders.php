<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
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
    $statusFilter = $_GET['status'] ?? '';
    $dateFilter = $_GET['date'] ?? date('Y-m-d');
    
    $sql = "SELECT o.*, 
            (SELECT GROUP_CONCAT(CONCAT(mi.name_en, ' x', oi.quantity) SEPARATOR ', ') 
             FROM order_items oi 
             JOIN menu_items mi ON oi.menu_item_id = mi.id 
             WHERE oi.order_id = o.id) as items_summary
            FROM orders o 
            WHERE o.restaurant_id = ?";
    $params = [$restaurantId];
    
    if ($statusFilter) {
        $sql .= " AND o.status = ?";
        $params[] = $statusFilter;
    }
    
    if ($dateFilter) {
        $sql .= " AND DATE(o.created_at) = ?";
        $params[] = $dateFilter;
    }
    
    $sql .= " ORDER BY o.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'orders' => $orders]);
}

if ($method === 'PUT' || $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $orderId = (int)($input['order_id'] ?? 0);
    $newStatus = $input['status'] ?? '';
    
    $validStatuses = ['received', 'cooking', 'ready', 'delivered', 'cancelled'];
    
    if ($orderId <= 0 || !in_array($newStatus, $validStatuses)) {
        jsonResponse(['error' => 'Invalid parameters'], 400);
    }
    
    $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$newStatus, $orderId, $restaurantId]);
    
    if ($newStatus === 'delivered') {
        $tableStmt = $db->prepare("
            UPDATE restaurant_tables SET status = 'available' 
            WHERE id = (SELECT table_id FROM orders WHERE id = ?)
        ");
        $tableStmt->execute([$orderId]);
    }
    
    jsonResponse(['success' => true, 'status' => $newStatus]);
}

jsonResponse(['error' => 'Invalid method'], 405);
