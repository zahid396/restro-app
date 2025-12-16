<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

cors();
$input = getInput();

$orderId = (int)($input['order_id'] ?? 0);
$members = (int)($input['members'] ?? 1);

if ($orderId <= 0) {
    jsonResponse(['error' => 'Invalid order ID'], 400);
}

if ($members < 1) {
    $members = 1;
}

$db = getDB();

$orderStmt = $db->prepare("SELECT total_amount FROM orders WHERE id = ?");
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch();

if (!$order) {
    jsonResponse(['error' => 'Order not found'], 404);
}

$perPerson = ceil($order['total_amount'] / $members);

$existingStmt = $db->prepare("SELECT id FROM bill_splits WHERE order_id = ?");
$existingStmt->execute([$orderId]);
$existing = $existingStmt->fetch();

if ($existing) {
    $updateStmt = $db->prepare("UPDATE bill_splits SET member_count = ?, per_person_amount = ? WHERE order_id = ?");
    $updateStmt->execute([$members, $perPerson, $orderId]);
} else {
    $insertStmt = $db->prepare("INSERT INTO bill_splits (order_id, member_count, per_person_amount) VALUES (?, ?, ?)");
    $insertStmt->execute([$orderId, $members, $perPerson]);
}

jsonResponse([
    'success' => true,
    'order_id' => $orderId,
    'total' => (int)$order['total_amount'],
    'members' => $members,
    'per_person' => $perPerson
]);
