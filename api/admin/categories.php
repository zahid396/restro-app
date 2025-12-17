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
require_once __DIR__ . '/auth_helpers.php';

$payload = requireAuth();
$restaurantId = $payload['restaurant_id'];
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM categories WHERE restaurant_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$restaurantId]);
    $categories = $stmt->fetchAll();
    jsonResponse(['success' => true, 'categories' => $categories]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = preg_replace('/[^a-z0-9_]/', '', strtolower($input['id'] ?? ''));
    $nameEn = trim($input['name_en'] ?? '');
    $nameBn = trim($input['name_bn'] ?? '');
    $icon = trim($input['icon'] ?? 'category');
    $sortOrder = (int)($input['sort_order'] ?? 0);
    
    if (empty($id) || empty($nameEn)) {
        jsonResponse(['error' => 'ID and name are required'], 400);
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO categories (id, restaurant_id, name_en, name_bn, icon, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $restaurantId, $nameEn, $nameBn, $icon, $sortOrder]);
        jsonResponse(['success' => true, 'message' => 'Category added']);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Category ID already exists'], 400);
    }
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';
    $nameEn = trim($input['name_en'] ?? '');
    $nameBn = trim($input['name_bn'] ?? '');
    $icon = trim($input['icon'] ?? 'category');
    $sortOrder = (int)($input['sort_order'] ?? 0);
    $isActive = isset($input['is_active']) ? ($input['is_active'] ? 1 : 0) : 1;
    
    $stmt = $db->prepare("UPDATE categories SET name_en = ?, name_bn = ?, icon = ?, sort_order = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$nameEn, $nameBn, $icon, $sortOrder, $isActive, $id, $restaurantId]);
    jsonResponse(['success' => true, 'message' => 'Category updated']);
}

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';
    
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$id, $restaurantId]);
    jsonResponse(['success' => true, 'message' => 'Category deleted']);
}

jsonResponse(['error' => 'Invalid method'], 405);
