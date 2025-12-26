<?php
function cors() {
    $allowedOrigins = ['*'];
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
        header('Access-Control-Allow-Credentials: true');
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
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
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE && !empty($json)) {
        jsonResponse(['error' => 'Invalid JSON input'], 400);
    }
    return $data ?? [];
}

function sanitize($value) {
    if (is_string($value)) {
        $value = trim($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        return $value;
    }
    if (is_array($value)) {
        return array_map('sanitize', $value);
    }
    return $value;
}

function validateRequired($input, $fields) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($input[$field]) || (is_string($input[$field]) && trim($input[$field]) === '')) {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        jsonResponse(['error' => 'Missing required fields: ' . implode(', ', $missing)], 400);
    }
    return true;
}

function validateInt($value, $min = null, $max = null) {
    $value = filter_var($value, FILTER_VALIDATE_INT);
    if ($value === false) return false;
    if ($min !== null && $value < $min) return false;
    if ($max !== null && $value > $max) return false;
    return $value;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function getLang() {
    $lang = $_GET['lang'] ?? DEFAULT_LANGUAGE;
    return in_array($lang, ['en', 'bn']) ? $lang : DEFAULT_LANGUAGE;
}

function getRestaurantId() {
    $id = (int)($_GET['restaurant_id'] ?? DEFAULT_RESTAURANT_ID);
    return $id > 0 ? $id : DEFAULT_RESTAURANT_ID;
}

function localizedField($row, $field, $lang = null) {
    $lang = $lang ?? getLang();
    $localField = $field . '_' . $lang;
    $fallback = $field . '_en';
    return $row[$localField] ?? $row[$fallback] ?? '';
}

function logError($message, $context = []) {
    $logEntry = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($context)) {
        $logEntry .= ' - ' . json_encode($context);
    }
    error_log($logEntry);
}
