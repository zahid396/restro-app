<?php
function generateToken($adminId, $restaurantId) {
    $payload = [
        'admin_id' => $adminId,
        'restaurant_id' => $restaurantId,
        'exp' => time() + 86400
    ];
    return base64_encode(json_encode($payload) . '.' . hash('sha256', json_encode($payload) . 'admin_secret_key'));
}

function verifyToken($token) {
    if (empty($token)) return null;
    $token = str_replace('Bearer ', '', $token);
    $parts = explode('.', base64_decode($token));
    if (count($parts) !== 2) return null;
    $payload = json_decode($parts[0], true);
    if (!$payload) return null;
    $expectedHash = hash('sha256', $parts[0] . 'admin_secret_key');
    if (!hash_equals($expectedHash, $parts[1])) return null;
    if (isset($payload['exp']) && $payload['exp'] < time()) return null;
    return $payload;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function requireAuth() {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $payload = verifyToken($token);
    if (!$payload) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    return $payload;
}
