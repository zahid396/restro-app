<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

cors();
$itemId = (int)($_GET['id'] ?? 0);

if ($itemId <= 0) {
    jsonResponse(['error' => 'Invalid item ID'], 400);
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    jsonResponse(['error' => 'Item not found'], 404);
}

$reviewStmt = $db->prepare("SELECT * FROM reviews WHERE menu_item_id = ? ORDER BY created_at DESC LIMIT 10");
$reviewStmt->execute([$itemId]);
$reviews = $reviewStmt->fetchAll();

$comboStmt = $db->prepare("SELECT * FROM combos WHERE trigger_item_id = ?");
$comboStmt->execute([$itemId]);
$combo = $comboStmt->fetch();

$reviewList = [];
foreach ($reviews as $r) {
    $reviewList[] = [
        'id' => $r['id'],
        'user' => $r['user_name'],
        'avatar' => $r['avatar'],
        'rating' => (int)$r['rating'],
        'comment' => $r['comment'],
        'date' => $r['created_at']
    ];
}

$response = [
    'id' => $item['id'],
    'name' => [
        'en' => $item['name_en'],
        'bn' => $item['name_bn']
    ],
    'description' => [
        'en' => $item['description_en'],
        'bn' => $item['description_bn']
    ],
    'price' => (int)$item['price'],
    'category' => $item['category_id'],
    'image' => $item['image_url'],
    'weight' => $item['weight'],
    'rating' => (float)$item['rating'],
    'likes' => (int)$item['likes'],
    'comments' => (int)$item['comments_count'],
    'tags' => json_decode($item['tags'] ?? '[]', true),
    'mood' => json_decode($item['mood'] ?? '[]', true),
    'taste' => json_decode($item['taste'] ?? '[]', true),
    'trending' => (bool)$item['is_trending'],
    'allergens' => $item['allergens'],
    'reviews' => $reviewList
];

if ($combo) {
    $response['combo'] = [
        'suggestItemIds' => json_decode($combo['suggested_item_ids'], true),
        'message' => [
            'en' => $combo['message_en'],
            'bn' => $combo['message_bn']
        ],
        'discount' => (int)$combo['discount_amount']
    ];
}

jsonResponse($response);
