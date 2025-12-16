<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

cors();
$lang = getLang();
$restaurantId = getRestaurantId();
$category = $_GET['category'] ?? null;
$mood = $_GET['mood'] ?? null;
$trending = isset($_GET['trending']);

$db = getDB();

$sql = "SELECT * FROM menu_items WHERE restaurant_id = ? AND is_available = true";
$params = [$restaurantId];

if ($category) {
    $sql .= " AND category_id = ?";
    $params[] = $category;
}

if ($trending) {
    $sql .= " AND is_trending = true";
}

$sql .= " ORDER BY is_trending DESC, likes DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

if ($mood) {
    $items = array_filter($items, function($item) use ($mood) {
        $moods = json_decode($item['mood'] ?? '[]', true);
        return in_array($mood, $moods);
    });
    $items = array_values($items);
}

$result = [];
foreach ($items as $item) {
    $result[] = [
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
        'allergens' => $item['allergens']
    ];
}

jsonResponse(['items' => $result]);
