<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../api/includes/config.php';
require_once __DIR__ . '/../api/includes/db.php';

echo "<h2>Admin User Check and Reset</h2>";

try {
    $db = getDB();
    
    // Check existing admin users
    echo "<h3>Current Admin Users:</h3>";
    $stmt = $db->query("SELECT id, username, name, email, is_active, last_login FROM admin_users");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "<p style='color: orange;'>⚠️ No admin users found in database!</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Active</th><th>Last Login</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email'] ?? 'N/A') . "</td>";
            echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . ($user['last_login'] ?? 'Never') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Create/reset admin user with known password
    echo "<h3>Resetting Admin Credentials:</h3>";
    
    $username = 'admin';
    $password = 'password';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin user exists
    $stmt = $db->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing user
        $stmt = $db->prepare("UPDATE admin_users SET password_hash = ?, is_active = 1 WHERE username = ?");
        $stmt->execute([$password_hash, $username]);
        echo "<p style='color: green;'>✅ Updated existing admin user</p>";
    } else {
        // Create new admin user
        $stmt = $db->prepare("INSERT INTO admin_users (restaurant_id, username, password_hash, name, is_active) VALUES (1, ?, ?, 'Admin User', 1)");
        $stmt->execute([$username, $password_hash]);
        echo "<p style='color: green;'>✅ Created new admin user</p>";
    }
    
    echo "<hr>";
    echo "<h3>✅ Admin Credentials Reset Successfully!</h3>";
    echo "<p><strong>Username:</strong> " . $username . "</p>";
    echo "<p><strong>Password:</strong> " . $password . "</p>";
    echo "<p><strong>Password Hash:</strong> " . substr($password_hash, 0, 50) . "...</p>";
    
    // Verify the password works
    echo "<h3>Verification:</h3>";
    if (password_verify($password, $password_hash)) {
        echo "<p style='color: green;'>✅ Password verification successful!</p>";
    } else {
        echo "<p style='color: red;'>❌ Password verification failed!</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='login.php' style='background: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
