<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>Database Connection Test</h2>";

require_once __DIR__ . '/../api/includes/config.php';
require_once __DIR__ . '/../api/includes/db.php';

echo "✓ Config and DB files included successfully<br><br>";

echo "Database Settings:<br>";
echo "- Driver: " . DB_DRIVER . "<br>";
echo "- Host: " . DB_HOST . "<br>";
echo "- Port: " . DB_PORT . "<br>";
echo "- Database: " . DB_NAME . "<br>";
echo "- User: " . DB_USER . "<br><br>";

try {
    $db = getDB();
    echo "✓ Database connection successful!<br><br>";
    
    // Test query
    $stmt = $db->query("SELECT COUNT(*) as count FROM restaurants");
    $result = $stmt->fetch();
    echo "Number of restaurants in database: " . $result['count'] . "<br>";
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>";
    echo "Error details: " . $e->getTraceAsString() . "<br>";
}
?>
