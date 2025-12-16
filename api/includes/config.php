<?php
$database_url = getenv('DATABASE_URL');
$parsed = parse_url($database_url);

define('DB_HOST', $parsed['host'] ?? 'localhost');
define('DB_PORT', $parsed['port'] ?? 5432);
define('DB_NAME', ltrim($parsed['path'] ?? '', '/'));
define('DB_USER', $parsed['user'] ?? '');
define('DB_PASS', $parsed['pass'] ?? '');

define('DEFAULT_RESTAURANT_ID', 1);
define('DEFAULT_LANGUAGE', 'en');
