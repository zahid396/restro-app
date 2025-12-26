<?php
require_once __DIR__ . '/config.php';

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        if (empty(DB_NAME) || empty(DB_USER)) {
            $errorMsg = 'Database not configured. Set MYSQL_URL or MYSQL_HOST/MYSQL_DATABASE/MYSQL_USER/MYSQL_PASSWORD environment variables.';
            if (function_exists('jsonResponse')) {
                jsonResponse(['error' => $errorMsg], 500);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $errorMsg]);
            }
            exit;
        }
        
        try {
            if (DB_DRIVER === 'mysql') {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];
            } else {
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ];
            }
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (function_exists('jsonResponse')) {
                jsonResponse(['error' => 'Database connection failed: ' . $e->getMessage()], 500);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
            }
            exit;
        }
    }
    return $pdo;
}
