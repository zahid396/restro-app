<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/auth.php';

$token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$payload = verifyToken($token);
if (!$payload) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$restaurantId = $payload['restaurant_id'];
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $categoryFilter = $_GET['category'] ?? '';
    $sql = "SELECT mi.*, c.name_en as category_name FROM menu_items mi LEFT JOIN categories c ON mi.category_id = c.id WHERE mi.restaurant_id = ?";
    $params = [$restaurantId];
    
    if ($categoryFilter) {
        $sql .= " AND mi.category_id = ?";
        $params[] = $categoryFilter;
    }
    $sql .= " ORDER BY mi.category_id, mi.name_en";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
    
    $catStmt = $db->prepare("SELECT * FROM categories WHERE restaurant_id = ? ORDER BY sort_order");
    $catStmt->execute([$restaurantId]);
    $categories = $catStmt->fetchAll();
    
    jsonResponse(['success' => true, 'items' => $items, 'categories' => $categories]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $categoryId = $input['category_id'] ?? '';
    $nameEn = trim($input['name_en'] ?? '');
    $nameBn = trim($input['name_bn'] ?? '');
    $descEn = trim($input['description_en'] ?? '');
    $descBn = trim($input['description_bn'] ?? '');
    $price = (int)($input['price'] ?? 0);
    $imageUrl = trim($input['image_url'] ?? '');
    $weight = trim($input['weight'] ?? '');
    $allergens = trim($input['allergens'] ?? '');
    $isAvailable = isset($input['is_available']) ? ($input['is_available'] ? 1 : 0) : 1;
    $isTrending = isset($input['is_trending']) ? ($input['is_trending'] ? 1 : 0) : 0;
    $tags = $input['tags'] ?? '';
    $mood = $input['mood'] ?? '';
    
    $tagsJson = json_encode(array_filter(array_map('trim', explode(',', $tags))));
    $moodJson = json_encode(array_filter(array_map('trim', explode(',', $mood))));
    
    $stmt = $db->prepare("INSERT INTO menu_items (restaurant_id, category_id, name_en, name_bn, description_en, description_bn, price, image_url, weight, allergens, is_available, is_trending, tags, mood) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$restaurantId, $categoryId, $nameEn, $nameBn, $descEn, $descBn, $price, $imageUrl, $weight, $allergens, $isAvailable, $isTrending, $tagsJson, $moodJson]);
    jsonResponse(['success' => true, 'message' => 'Menu item added', 'id' => $db->lastInsertId()]);
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $categoryId = $input['category_id'] ?? '';
    $nameEn = trim($input['name_en'] ?? '');
    $nameBn = trim($input['name_bn'] ?? '');
    $descEn = trim($input['description_en'] ?? '');
    $descBn = trim($input['description_bn'] ?? '');
    $price = (int)($input['price'] ?? 0);
    $imageUrl = trim($input['image_url'] ?? '');
    $weight = trim($input['weight'] ?? '');
    $allergens = trim($input['allergens'] ?? '');
    $isAvailable = isset($input['is_available']) ? ($input['is_available'] ? 1 : 0) : 1;
    $isTrending = isset($input['is_trending']) ? ($input['is_trending'] ? 1 : 0) : 0;
    $tags = $input['tags'] ?? '';
    $mood = $input['mood'] ?? '';
    
    $tagsJson = json_encode(array_filter(array_map('trim', explode(',', $tags))));
    $moodJson = json_encode(array_filter(array_map('trim', explode(',', $mood))));
    
    $stmt = $db->prepare("UPDATE menu_items SET category_id = ?, name_en = ?, name_bn = ?, description_en = ?, description_bn = ?, price = ?, image_url = ?, weight = ?, allergens = ?, is_available = ?, is_trending = ?, tags = ?, mood = ?, updated_at = NOW() WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$categoryId, $nameEn, $nameBn, $descEn, $descBn, $price, $imageUrl, $weight, $allergens, $isAvailable, $isTrending, $tagsJson, $moodJson, $id, $restaurantId]);
    jsonResponse(['success' => true, 'message' => 'Menu item updated']);
}

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    $stmt = $db->prepare("DELETE FROM menu_items WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$id, $restaurantId]);
    jsonResponse(['success' => true, 'message' => 'Menu item deleted']);
}

jsonResponse(['error' => 'Invalid method'], 405);
