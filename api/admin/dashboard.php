<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

$todayStart = date('Y-m-d 00:00:00');

$orderStats = $db->prepare("
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total_amount), 0) as total_revenue,
        COUNT(CASE WHEN status NOT IN ('delivered', 'cancelled') THEN 1 END) as pending_orders
    FROM orders 
    WHERE restaurant_id = ? AND created_at >= ?
");
$orderStats->execute([$restaurantId, $todayStart]);
$stats = $orderStats->fetch();

$tableStats = $db->prepare("SELECT COUNT(*) as total, COUNT(CASE WHEN status = 'busy' THEN 1 END) as busy FROM restaurant_tables WHERE restaurant_id = ?");
$tableStats->execute([$restaurantId]);
$tables = $tableStats->fetch();

$pendingOrders = $db->prepare("
    SELECT o.*, 
           (SELECT GROUP_CONCAT(CONCAT(mi.name_en, ' x', oi.quantity) SEPARATOR ', ') 
            FROM order_items oi 
            JOIN menu_items mi ON oi.menu_item_id = mi.id 
            WHERE oi.order_id = o.id) as items_summary
    FROM orders o 
    WHERE o.restaurant_id = ? AND o.status NOT IN ('delivered', 'cancelled')
    ORDER BY o.created_at DESC
    LIMIT 10
");
$pendingOrders->execute([$restaurantId]);
$pending = $pendingOrders->fetchAll();

jsonResponse([
    'success' => true,
    'stats' => [
        'total_orders' => (int)$stats['total_orders'],
        'total_revenue' => (int)$stats['total_revenue'],
        'pending_orders' => (int)$stats['pending_orders'],
        'tables_total' => (int)$tables['total'],
        'tables_busy' => (int)$tables['busy']
    ],
    'pending_orders' => $pending
]);
