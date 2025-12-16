<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

cors();
$input = getInput();

$tableNumber = (int)($input['table'] ?? 0);
$items = $input['items'] ?? [];
$paymentMethod = sanitize($input['payment_method'] ?? 'cash');

if ($tableNumber <= 0) {
    jsonResponse(['error' => 'Invalid table number'], 400);
}

if (empty($items)) {
    jsonResponse(['error' => 'No items in order'], 400);
}

$db = getDB();
$restaurantId = getRestaurantId();

$tableStmt = $db->prepare("SELECT id FROM restaurant_tables WHERE table_number = ? AND restaurant_id = ?");
$tableStmt->execute([$tableNumber, $restaurantId]);
$table = $tableStmt->fetch();

if (!$table) {
    $tableStmt = $db->prepare("INSERT INTO restaurant_tables (restaurant_id, table_number, status) VALUES (?, ?, 'busy')");
    $tableStmt->execute([$restaurantId, $tableNumber]);
    $tableId = $db->lastInsertId();
    $table = ['id' => $tableId];
} else {
    $updateStmt = $db->prepare("UPDATE restaurant_tables SET status = 'busy' WHERE id = ?");
    $updateStmt->execute([$table['id']]);
}

$totalAmount = 0;
$orderItems = [];

foreach ($items as $item) {
    $itemId = (int)($item['id'] ?? 0);
    $qty = (int)($item['qty'] ?? 1);
    
    if ($itemId <= 0 || $qty <= 0) continue;
    
    $itemStmt = $db->prepare("SELECT id, price, name_en FROM menu_items WHERE id = ? AND is_available = true");
    $itemStmt->execute([$itemId]);
    $menuItem = $itemStmt->fetch();
    
    if ($menuItem) {
        $orderItems[] = [
            'menu_item_id' => $menuItem['id'],
            'quantity' => $qty,
            'unit_price' => $menuItem['price'],
            'name' => $menuItem['name_en']
        ];
        $totalAmount += $menuItem['price'] * $qty;
    }
}

if (empty($orderItems)) {
    jsonResponse(['error' => 'No valid items found'], 400);
}

$vat = round($totalAmount * 0.05);
$service = round($totalAmount * 0.05);
$grandTotal = $totalAmount + $vat + $service;

$orderStmt = $db->prepare("
    INSERT INTO orders (restaurant_id, table_id, table_number, status, total_amount, payment_method, created_at) 
    VALUES (?, ?, ?, 'received', ?, ?, NOW())
");
$orderStmt->execute([$restaurantId, $table['id'], $tableNumber, $grandTotal, $paymentMethod]);
$orderId = $db->lastInsertId();
$order = ['id' => $orderId, 'created_at' => date('Y-m-d H:i:s')];

foreach ($orderItems as $oi) {
    $oiStmt = $db->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    $oiStmt->execute([$order['id'], $oi['menu_item_id'], $oi['quantity'], $oi['unit_price']]);
}

jsonResponse([
    'success' => true,
    'order_id' => $order['id'],
    'table_number' => $tableNumber,
    'status' => 'received',
    'subtotal' => $totalAmount,
    'vat' => $vat,
    'service' => $service,
    'total' => $grandTotal,
    'payment_method' => $paymentMethod,
    'items' => $orderItems,
    'created_at' => $order['created_at'],
    'eta_minutes' => 12
]);
