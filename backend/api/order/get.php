<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

cors();
$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    jsonResponse(['error' => 'Invalid order ID'], 400);
}

$db = getDB();

$orderStmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch();

if (!$order) {
    jsonResponse(['error' => 'Order not found'], 404);
}

$itemsStmt = $db->prepare("
    SELECT oi.*, mi.name_en, mi.name_bn, mi.image_url 
    FROM order_items oi 
    JOIN menu_items mi ON oi.menu_item_id = mi.id 
    WHERE oi.order_id = ?
");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();

$splitStmt = $db->prepare("SELECT * FROM bill_splits WHERE order_id = ? ORDER BY created_at DESC LIMIT 1");
$splitStmt->execute([$orderId]);
$split = $splitStmt->fetch();

$orderItems = [];
foreach ($items as $item) {
    $orderItems[] = [
        'id' => $item['menu_item_id'],
        'name' => [
            'en' => $item['name_en'],
            'bn' => $item['name_bn']
        ],
        'image' => $item['image_url'],
        'quantity' => (int)$item['quantity'],
        'unit_price' => (int)$item['unit_price'],
        'subtotal' => (int)$item['quantity'] * (int)$item['unit_price']
    ];
}

$statusTimeline = [
    'received' => ['completed' => true, 'current' => false],
    'cooking' => ['completed' => false, 'current' => false],
    'plating' => ['completed' => false, 'current' => false],
    'delivered' => ['completed' => false, 'current' => false]
];

$statusOrder = ['received', 'cooking', 'plating', 'delivered'];
$currentIndex = array_search($order['status'], $statusOrder);

foreach ($statusOrder as $i => $status) {
    if ($i < $currentIndex) {
        $statusTimeline[$status] = ['completed' => true, 'current' => false];
    } elseif ($i === $currentIndex) {
        $statusTimeline[$status] = ['completed' => false, 'current' => true];
    }
}

$response = [
    'id' => $order['id'],
    'table_number' => $order['table_number'],
    'status' => $order['status'],
    'total' => (int)$order['total_amount'],
    'payment_method' => $order['payment_method'],
    'items' => $orderItems,
    'timeline' => $statusTimeline,
    'created_at' => $order['created_at'],
    'eta_minutes' => 12
];

if ($split) {
    $response['split'] = [
        'member_count' => (int)$split['member_count'],
        'per_person' => (int)$split['per_person_amount']
    ];
}

jsonResponse($response);
