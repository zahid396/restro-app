<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

cors();
$input = getInput();

$orderId = isset($input['order_id']) ? (int)$input['order_id'] : null;
$menuItemId = (int)($input['menu_item_id'] ?? 0);
$rating = (int)($input['rating'] ?? 0);
$comment = sanitize($input['comment'] ?? '');
$userName = sanitize($input['user_name'] ?? 'Anonymous');
$avatar = sanitize($input['avatar'] ?? '👤');

if ($rating < 1 || $rating > 5) {
    jsonResponse(['error' => 'Rating must be between 1 and 5'], 400);
}

$db = getDB();

$stmt = $db->prepare("
    INSERT INTO reviews (order_id, menu_item_id, user_name, avatar, rating, comment) 
    VALUES (?, ?, ?, ?, ?, ?) RETURNING id, created_at
");
$stmt->execute([$orderId, $menuItemId ?: null, $userName, $avatar, $rating, $comment]);
$review = $stmt->fetch();

if ($menuItemId > 0) {
    $updateStmt = $db->prepare("UPDATE menu_items SET comments_count = comments_count + 1 WHERE id = ?");
    $updateStmt->execute([$menuItemId]);
}

jsonResponse([
    'success' => true,
    'review_id' => $review['id'],
    'created_at' => $review['created_at']
]);
