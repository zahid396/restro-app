<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCsrfToken($csrfToken)) {
    echo json_encode(['error' => 'Invalid security token']);
    exit;
}
$orderId = (int)($input['order_id'] ?? 0);
$newStatus = $input['status'] ?? '';

$validStatuses = ['received', 'cooking', 'ready', 'delivered', 'cancelled'];

if ($orderId <= 0 || !in_array($newStatus, $validStatuses)) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$db = getDB();
$restaurantId = $_SESSION['restaurant_id'] ?? 1;

$stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ? AND restaurant_id = ?");
$stmt->execute([$newStatus, $orderId, $restaurantId]);

if ($newStatus === 'delivered') {
    $tableStmt = $db->prepare("
        UPDATE restaurant_tables SET status = 'available' 
        WHERE id = (SELECT table_id FROM orders WHERE id = ?)
    ");
    $tableStmt->execute([$orderId]);
}

echo json_encode(['success' => true, 'status' => $newStatus]);
