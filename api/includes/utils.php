<?php
function cors() {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function getInput() {
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?? [];
}

function sanitize($value) {
    if (is_string($value)) {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
    return $value;
}

function getLang() {
    return $_GET['lang'] ?? DEFAULT_LANGUAGE;
}

function getRestaurantId() {
    return (int)($_GET['restaurant_id'] ?? DEFAULT_RESTAURANT_ID);
}

function localizedField($row, $field, $lang = null) {
    $lang = $lang ?? getLang();
    $localField = $field . '_' . $lang;
    $fallback = $field . '_en';
    return $row[$localField] ?? $row[$fallback] ?? '';
}
