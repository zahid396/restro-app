<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

cors();
$tableNumber = (int)($_GET['id'] ?? 0);
$restaurantId = getRestaurantId();

if ($tableNumber <= 0) {
    jsonResponse(['error' => 'Invalid table number'], 400);
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM restaurant_tables WHERE table_number = ? AND restaurant_id = ?");
$stmt->execute([$tableNumber, $restaurantId]);
$table = $stmt->fetch();

if (!$table) {
    $stmt = $db->prepare("INSERT INTO restaurant_tables (restaurant_id, table_number, status) VALUES (?, ?, 'free') RETURNING *");
    $stmt->execute([$restaurantId, $tableNumber]);
    $table = $stmt->fetch();
}

jsonResponse([
    'id' => $table['id'],
    'table_number' => $table['table_number'],
    'status' => $table['status'],
    'restaurant_id' => $table['restaurant_id']
]);
