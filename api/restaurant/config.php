<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

cors();
$lang = getLang();
$restaurantId = getRestaurantId();

$db = getDB();
$stmt = $db->prepare("SELECT * FROM restaurants WHERE id = ? AND is_active = true");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    jsonResponse(['error' => 'Restaurant not found'], 404);
}

jsonResponse([
    'id' => $restaurant['id'],
    'name' => [
        'en' => $restaurant['name_en'],
        'bn' => $restaurant['name_bn']
    ],
    'tagline' => [
        'en' => $restaurant['tagline_en'],
        'bn' => $restaurant['tagline_bn']
    ],
    'branding' => json_decode($restaurant['branding_config'] ?? '{}', true)
]);
