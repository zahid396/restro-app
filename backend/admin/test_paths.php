<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

echo "Testing from: " . __DIR__ . "<br>";
echo "Looking for api config at: " . realpath(__DIR__ . '/../api/includes/config.php') . "<br>";
echo "Looking for api db at: " . realpath(__DIR__ . '/../api/includes/db.php') . "<br>";

if (file_exists(__DIR__ . '/../api/includes/config.php')) {
    echo "✓ config.php EXISTS<br>";
} else {
    echo "✗ config.php NOT FOUND<br>";
}

if (file_exists(__DIR__ . '/../api/includes/db.php')) {
    echo "✓ db.php EXISTS<br>";
} else {
    echo "✗ db.php NOT FOUND<br>";
}

echo "<br>Now trying to include config.php...<br>";
try {
    require_once __DIR__ . '/../api/includes/config.php';
    echo "✓ config.php included successfully<br>";
} catch (Exception $e) {
    echo "✗ Error including config.php: " . $e->getMessage() . "<br>";
}

echo "<br>Now trying to include db.php...<br>";
try {
    require_once __DIR__ . '/../api/includes/db.php';
    echo "✓ db.php included successfully<br>";
} catch (Exception $e) {
    echo "✗ Error including db.php: " . $e->getMessage() . "<br>";
}

echo "<br>All tests complete!";
?>
