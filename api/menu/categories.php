<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

cors();
$lang = getLang();
$restaurantId = getRestaurantId();

$db = getDB();
$stmt = $db->prepare("SELECT * FROM categories WHERE restaurant_id = ? ORDER BY sort_order ASC");
$stmt->execute([$restaurantId]);
$categories = $stmt->fetchAll();

$result = [];
foreach ($categories as $cat) {
    $result[] = [
        'id' => $cat['id'],
        'name' => [
            'en' => $cat['name_en'],
            'bn' => $cat['name_bn']
        ],
        'icon' => $cat['icon']
    ];
}

jsonResponse(['categories' => $result]);
