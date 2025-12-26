<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = getDB();
$restaurantId = $_SESSION['restaurant_id'] ?? 1;

$stmt = $db->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    WHERE o.restaurant_id = ? AND o.status NOT IN ('delivered', 'cancelled')
    ORDER BY o.created_at DESC
");
$stmt->execute([$restaurantId]);
$orders = $stmt->fetchAll();

$count = count($orders);

$formattedOrders = [];
foreach ($orders as $order) {
    $formattedOrders[] = [
        'id' => $order['id'],
        'table_number' => $order['table_number'],
        'status' => $order['status'],
        'total' => $order['total_amount'],
        'item_count' => $order['item_count'],
        'created_at' => $order['created_at'],
        'time_ago' => timeAgo($order['created_at'])
    ];
}

echo json_encode([
    'count' => $count,
    'orders' => $formattedOrders
]);
