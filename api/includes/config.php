<?php
$database_url = getenv('DATABASE_URL');
$mysql_url = getenv('MYSQL_URL');

if ($mysql_url) {
    $parsed = parse_url($mysql_url);
    define('DB_DRIVER', 'mysql');
    define('DB_PORT', $parsed['port'] ?? 3306);
} elseif ($database_url) {
    $parsed = parse_url($database_url);
    if (strpos($database_url, 'mysql') !== false) {
        define('DB_DRIVER', 'mysql');
        define('DB_PORT', $parsed['port'] ?? 3306);
    } else {
        define('DB_DRIVER', 'pgsql');
        define('DB_PORT', $parsed['port'] ?? 5432);
    }
} else {
    $parsed = [];
    define('DB_DRIVER', 'mysql');
    define('DB_PORT', 3306);
}

define('DB_HOST', $parsed['host'] ?? getenv('MYSQL_HOST') ?? 'localhost');
define('DB_NAME', ltrim($parsed['path'] ?? '', '/') ?: getenv('MYSQL_DATABASE') ?? '');
define('DB_USER', $parsed['user'] ?? getenv('MYSQL_USER') ?? '');
define('DB_PASS', $parsed['pass'] ?? getenv('MYSQL_PASSWORD') ?? '');

define('DEFAULT_RESTAURANT_ID', 1);
define('DEFAULT_LANGUAGE', 'en');
