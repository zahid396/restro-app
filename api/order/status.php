<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

cors();
$input = getInput();

$orderId = (int)($input['order_id'] ?? 0);
$newStatus = sanitize($input['status'] ?? '');

$validStatuses = ['received', 'cooking', 'plating', 'delivered'];

if ($orderId <= 0) {
    jsonResponse(['error' => 'Invalid order ID'], 400);
}

if (!in_array($newStatus, $validStatuses)) {
    jsonResponse(['error' => 'Invalid status'], 400);
}

$db = getDB();

$orderStmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch();

if (!$order) {
    jsonResponse(['error' => 'Order not found'], 404);
}

$updateStmt = $db->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
$updateStmt->execute([$newStatus, $orderId]);

if ($newStatus === 'delivered') {
    $tableStmt = $db->prepare("UPDATE restaurant_tables SET status = 'available' WHERE id = ?");
    $tableStmt->execute([$order['table_id']]);
}

jsonResponse([
    'success' => true,
    'order_id' => $orderId,
    'status' => $newStatus
]);
