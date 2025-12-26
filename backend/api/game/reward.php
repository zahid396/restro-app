<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

cors();
$restaurantId = getRestaurantId();

$db = getDB();
$stmt = $db->prepare("SELECT * FROM games_rewards WHERE restaurant_id = ? ORDER BY probability DESC");
$stmt->execute([$restaurantId]);
$rewards = $stmt->fetchAll();

if (empty($rewards)) {
    jsonResponse(['error' => 'No rewards available'], 404);
}

$rand = mt_rand(0, 100) / 100;
$cumulative = 0;
$selectedReward = $rewards[0];

foreach ($rewards as $reward) {
    $cumulative += (float)$reward['probability'];
    if ($rand <= $cumulative) {
        $selectedReward = $reward;
        break;
    }
}

jsonResponse([
    'id' => $selectedReward['id'],
    'name' => [
        'en' => $selectedReward['name_en'],
        'bn' => $selectedReward['name_bn']
    ],
    'description' => [
        'en' => $selectedReward['description_en'],
        'bn' => $selectedReward['description_bn']
    ],
    'image' => $selectedReward['image_url'],
    'reward_type' => $selectedReward['reward_type'],
    'reward_value' => (int)$selectedReward['reward_value'],
    'expires_in' => (int)$selectedReward['expires_in']
]);
