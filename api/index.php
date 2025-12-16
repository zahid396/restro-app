<?php
require_once __DIR__ . '/includes/utils.php';
cors();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$uri = preg_replace('#^/api#', '', $uri);
$uri = rtrim($uri, '/');

$routes = [
    'GET' => [
        '/restaurant/config' => 'restaurant/config.php',
        '/table/{id}' => 'restaurant/table.php',
        '/menu/categories' => 'menu/categories.php',
        '/menu/items' => 'menu/items.php',
        '/menu/item/{id}' => 'menu/item.php',
        '/order/{id}' => 'order/get.php',
        '/game/reward' => 'game/reward.php',
    ],
    'POST' => [
        '/order/create' => 'order/create.php',
        '/order/status' => 'order/status.php',
        '/order/split' => 'order/split.php',
        '/review/submit' => 'review/submit.php',
    ]
];

function matchRoute($uri, $pattern) {
    $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern);
    $pattern = '#^' . $pattern . '$#';
    if (preg_match($pattern, $uri, $matches)) {
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }
    return false;
}

if (isset($routes[$method])) {
    foreach ($routes[$method] as $pattern => $file) {
        $params = matchRoute($uri, $pattern);
        if ($params !== false) {
            $_GET = array_merge($_GET, $params);
            require __DIR__ . '/' . $file;
            exit;
        }
    }
}

if ($uri === '' || $uri === '/') {
    jsonResponse([
        'api' => 'Restaurant Backend API',
        'version' => '1.0.0',
        'endpoints' => [
            'GET /api/restaurant/config',
            'GET /api/table/{tableNumber}',
            'GET /api/menu/categories',
            'GET /api/menu/items',
            'GET /api/menu/item/{id}',
            'POST /api/order/create',
            'GET /api/order/{orderId}',
            'POST /api/order/status',
            'POST /api/order/split',
            'GET /api/game/reward',
            'POST /api/review/submit'
        ]
    ]);
}

jsonResponse(['error' => 'Not Found', 'uri' => $uri], 404);
