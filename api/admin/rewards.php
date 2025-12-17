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
    $stmt = $db->prepare("SELECT * FROM games_rewards WHERE restaurant_id = ? ORDER BY probability DESC");
    $stmt->execute([$restaurantId]);
    $rewards = $stmt->fetchAll();
    jsonResponse(['success' => true, 'rewards' => $rewards]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nameEn = trim($input['name_en'] ?? '');
    $nameBn = trim($input['name_bn'] ?? '');
    $descEn = trim($input['description_en'] ?? '');
    $descBn = trim($input['description_bn'] ?? '');
    $imageUrl = trim($input['image_url'] ?? '');
    $rewardType = $input['reward_type'] ?? 'discount';
    $rewardValue = (int)($input['reward_value'] ?? 0);
    $probability = (float)($input['probability'] ?? 0.1);
    $expiresIn = (int)($input['expires_in'] ?? 15);
    
    $stmt = $db->prepare("INSERT INTO games_rewards (restaurant_id, name_en, name_bn, description_en, description_bn, image_url, reward_type, reward_value, probability, expires_in) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$restaurantId, $nameEn, $nameBn, $descEn, $descBn, $imageUrl, $rewardType, $rewardValue, $probability, $expiresIn]);
    jsonResponse(['success' => true, 'message' => 'Reward added', 'id' => $db->lastInsertId()]);
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $nameEn = trim($input['name_en'] ?? '');
    $nameBn = trim($input['name_bn'] ?? '');
    $descEn = trim($input['description_en'] ?? '');
    $descBn = trim($input['description_bn'] ?? '');
    $imageUrl = trim($input['image_url'] ?? '');
    $rewardType = $input['reward_type'] ?? 'discount';
    $rewardValue = (int)($input['reward_value'] ?? 0);
    $probability = (float)($input['probability'] ?? 0.1);
    $expiresIn = (int)($input['expires_in'] ?? 15);
    $isActive = isset($input['is_active']) ? ($input['is_active'] ? 1 : 0) : 1;
    
    $stmt = $db->prepare("UPDATE games_rewards SET name_en = ?, name_bn = ?, description_en = ?, description_bn = ?, image_url = ?, reward_type = ?, reward_value = ?, probability = ?, expires_in = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$nameEn, $nameBn, $descEn, $descBn, $imageUrl, $rewardType, $rewardValue, $probability, $expiresIn, $isActive, $id, $restaurantId]);
    jsonResponse(['success' => true, 'message' => 'Reward updated']);
}

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    $stmt = $db->prepare("DELETE FROM games_rewards WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$id, $restaurantId]);
    jsonResponse(['success' => true, 'message' => 'Reward deleted']);
}

jsonResponse(['error' => 'Invalid method'], 405);
