<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.use_strict_mode', 1);
session_start();

require_once __DIR__ . '/../api/includes/db.php';

$error = '';
$maxAttempts = 5;
$lockoutTime = 900;

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

function getClientIP() {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function checkLoginAttempts($db, $ip) {
    global $maxAttempts, $lockoutTime;
    $stmt = $db->prepare("SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND) AND success = 0");
    $stmt->execute([$ip, $lockoutTime]);
    $result = $stmt->fetch();
    return $result['attempts'] < $maxAttempts;
}

function recordLoginAttempt($db, $ip, $username, $success) {
    $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, username, success, attempt_time) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$ip, $username, $success ? 1 : 0]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $db = getDB();
    $clientIP = getClientIP();
    
    if (!checkLoginAttempts($db, $clientIP)) {
        $error = 'Too many failed attempts. Please try again in 15 minutes.';
    } elseif ($username && $password) {
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            recordLoginAttempt($db, $clientIP, $username, true);
            session_regenerate_id(true);
            
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['restaurant_id'] = $user['restaurant_id'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['login_time'] = time();
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $updateStmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            header('Location: index.php');
            exit;
        } else {
            recordLoginAttempt($db, $clientIP, $username, false);
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Please enter username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Restaurant</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 { margin: 0 0 10px; font-weight: 700; color: #1e1e2e; }
        .login-header p { color: #6b7280; margin: 0; }
        .login-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
        }
        .form-outline { margin-bottom: 20px; }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="login-icon">🍽️</div>
            <h2>Admin Login</h2>
            <p>Sign in to manage your restaurant</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-outline mb-4">
                <input type="text" name="username" id="username" class="form-control form-control-lg" required>
                <label class="form-label" for="username">Username</label>
            </div>
            <div class="form-outline mb-4">
                <input type="password" name="password" id="password" class="form-control form-control-lg" required>
                <label class="form-label" for="password">Password</label>
            </div>
            <button type="submit" class="btn btn-primary btn-login btn-lg">Sign In</button>
        </form>
        
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.umd.min.js"></script>
</body>
</html>
