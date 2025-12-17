<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, DELETE, OPTIONS');
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
    $stmt = $db->prepare("
        SELECT r.*, o.table_number, mi.name_en as item_name
        FROM reviews r
        LEFT JOIN orders o ON r.order_id = o.id
        LEFT JOIN menu_items mi ON r.menu_item_id = mi.id
        WHERE o.restaurant_id = ? OR r.order_id IS NULL
        ORDER BY r.created_at DESC
        LIMIT 100
    ");
    $stmt->execute([$restaurantId]);
    $reviews = $stmt->fetchAll();
    
    $statsStmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            AVG(rating) as avg_rating,
            COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
            COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive
        FROM reviews r
        LEFT JOIN orders o ON r.order_id = o.id
        WHERE o.restaurant_id = ? OR r.order_id IS NULL
    ");
    $statsStmt->execute([$restaurantId]);
    $stats = $statsStmt->fetch();
    
    jsonResponse([
        'success' => true,
        'reviews' => $reviews,
        'stats' => [
            'total' => (int)$stats['total'],
            'avg_rating' => round((float)$stats['avg_rating'], 1),
            'five_star' => (int)$stats['five_star'],
            'positive' => (int)$stats['positive']
        ]
    ]);
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $action = $input['action'] ?? '';
    
    if ($action === 'toggle_visibility') {
        $stmt = $db->prepare("UPDATE reviews SET is_visible = NOT is_visible WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['success' => true, 'message' => 'Visibility toggled']);
    }
    
    jsonResponse(['error' => 'Invalid action'], 400);
}

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Review deleted']);
}

jsonResponse(['error' => 'Invalid method'], 405);
