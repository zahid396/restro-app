<?php 
$pageTitle = 'Restaurant Settings';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$restaurantId = $_SESSION['restaurant_id'] ?? 1;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_restaurant') {
        $nameEn = trim($_POST['name_en'] ?? '');
        $nameBn = trim($_POST['name_bn'] ?? '');
        $taglineEn = trim($_POST['tagline_en'] ?? '');
        $taglineBn = trim($_POST['tagline_bn'] ?? '');
        
        $stmt = $db->prepare("UPDATE restaurants SET name_en = ?, name_bn = ?, tagline_en = ?, tagline_bn = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$nameEn, $nameBn, $taglineEn, $taglineBn, $restaurantId]);
        $message = 'Restaurant settings updated successfully';
    } elseif ($action === 'update_password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';
        
        if ($newPass !== $confirmPass) {
            $message = 'New passwords do not match';
        } elseif (strlen($newPass) < 6) {
            $message = 'Password must be at least 6 characters';
        } else {
            $adminStmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
            $adminStmt->execute([$_SESSION['admin_id']]);
            $adminUser = $adminStmt->fetch();
            
            if (password_verify($currentPass, $adminUser['password_hash'])) {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $updateStmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                $updateStmt->execute([$newHash, $_SESSION['admin_id']]);
                $message = 'Password updated successfully';
            } else {
                $message = 'Current password is incorrect';
            }
        }
    }
}

$stmt = $db->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$restaurantId]);
$restaurant = $stmt->fetch();
?>

<div class="top-bar">
    <h1 class="page-title">Settings</h1>
</div>

<?php if ($message): ?>
<div class="alert alert-info alert-dismissible fade show"><?php echo $message; ?><button type="button" class="btn-close" data-mdb-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="table-card p-4">
            <h5 class="mb-4 fw-bold">Restaurant Information</h5>
            <form method="POST">
                <input type="hidden" name="action" value="update_restaurant">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Restaurant Name (English)</label>
                        <input type="text" name="name_en" class="form-control" value="<?php echo htmlspecialchars($restaurant['name_en'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Restaurant Name (Bengali)</label>
                        <input type="text" name="name_bn" class="form-control" value="<?php echo htmlspecialchars($restaurant['name_bn'] ?? ''); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tagline (English)</label>
                        <input type="text" name="tagline_en" class="form-control" value="<?php echo htmlspecialchars($restaurant['tagline_en'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tagline (Bengali)</label>
                        <input type="text" name="tagline_bn" class="form-control" value="<?php echo htmlspecialchars($restaurant['tagline_bn'] ?? ''); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </form>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="table-card p-4">
            <h5 class="mb-4 fw-bold">Change Password</h5>
            <form method="POST">
                <input type="hidden" name="action" value="update_password">
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn btn-warning w-100">
                    <i class="fas fa-key me-2"></i>Update Password
                </button>
            </form>
        </div>
        
        <div class="table-card p-4 mt-4">
            <h5 class="mb-3 fw-bold">Admin Info</h5>
            <p class="mb-1"><strong>Username:</strong> <?php echo htmlspecialchars($admin['username']); ?></p>
            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($admin['name']); ?></p>
            <p class="mb-0"><strong>Last Login:</strong> <?php echo $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'N/A'; ?></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
