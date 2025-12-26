<?php 
$pageTitle = 'Rewards & Games';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$restaurantId = $_SESSION['restaurant_id'] ?? 1;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $nameEn = trim($_POST['name_en'] ?? '');
        $nameBn = trim($_POST['name_bn'] ?? '');
        $descEn = trim($_POST['description_en'] ?? '');
        $descBn = trim($_POST['description_bn'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');
        $rewardType = $_POST['reward_type'] ?? 'discount';
        $rewardValue = (int)($_POST['reward_value'] ?? 0);
        $probability = (float)($_POST['probability'] ?? 0.1);
        $expiresIn = (int)($_POST['expires_in'] ?? 15);
        
        $stmt = $db->prepare("INSERT INTO games_rewards (restaurant_id, name_en, name_bn, description_en, description_bn, image_url, reward_type, reward_value, probability, expires_in) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$restaurantId, $nameEn, $nameBn, $descEn, $descBn, $imageUrl, $rewardType, $rewardValue, $probability, $expiresIn]);
        $message = 'Reward added successfully';
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $nameEn = trim($_POST['name_en'] ?? '');
        $nameBn = trim($_POST['name_bn'] ?? '');
        $descEn = trim($_POST['description_en'] ?? '');
        $descBn = trim($_POST['description_bn'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');
        $rewardType = $_POST['reward_type'] ?? 'discount';
        $rewardValue = (int)($_POST['reward_value'] ?? 0);
        $probability = (float)($_POST['probability'] ?? 0.1);
        $expiresIn = (int)($_POST['expires_in'] ?? 15);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE games_rewards SET name_en = ?, name_bn = ?, description_en = ?, description_bn = ?, image_url = ?, reward_type = ?, reward_value = ?, probability = ?, expires_in = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
        $stmt->execute([$nameEn, $nameBn, $descEn, $descBn, $imageUrl, $rewardType, $rewardValue, $probability, $expiresIn, $isActive, $id, $restaurantId]);
        $message = 'Reward updated successfully';
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM games_rewards WHERE id = ? AND restaurant_id = ?");
        $stmt->execute([$id, $restaurantId]);
        $message = 'Reward deleted successfully';
    }
}

$stmt = $db->prepare("SELECT * FROM games_rewards WHERE restaurant_id = ? ORDER BY probability DESC");
$stmt->execute([$restaurantId]);
$rewards = $stmt->fetchAll();
?>

<div class="top-bar">
    <h1 class="page-title">Rewards & Games</h1>
    <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#addModal">
        <i class="fas fa-plus me-2"></i>Add Reward
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-dismissible fade show"><?php echo $message; ?><button type="button" class="btn-close" data-mdb-dismiss="alert"></button></div>
<?php endif; ?>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Probability:</strong> Set the chance of winning each reward (0 to 1). Total should add up to 1 or less.
</div>

<div class="row g-4">
    <?php foreach ($rewards as $reward): ?>
    <div class="col-md-4">
        <div class="card h-100 <?php echo !$reward['is_active'] ? 'border-secondary' : ''; ?>">
            <?php if ($reward['image_url']): ?>
            <img src="<?php echo htmlspecialchars($reward['image_url']); ?>" class="card-img-top" style="height: 150px; object-fit: cover;" alt="">
            <?php else: ?>
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                <span class="material-icons-outlined" style="font-size: 48px; color: #ccc;">emoji_events</span>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($reward['name_en']); ?></h5>
                    <span class="badge <?php echo $reward['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                        <?php echo $reward['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
                <p class="card-text text-muted small"><?php echo htmlspecialchars($reward['description_en']); ?></p>
                <div class="d-flex gap-2 flex-wrap mb-3">
                    <span class="badge bg-primary"><?php echo ucfirst($reward['reward_type']); ?></span>
                    <?php if ($reward['reward_value']): ?>
                    <span class="badge bg-info">Value: <?php echo $reward['reward_value']; ?>%</span>
                    <?php endif; ?>
                    <span class="badge bg-warning text-dark">Prob: <?php echo ($reward['probability'] * 100); ?>%</span>
                    <span class="badge bg-secondary">Expires: <?php echo $reward['expires_in']; ?> mins</span>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <button class="btn btn-sm btn-outline-primary" onclick='editReward(<?php echo json_encode($reward); ?>)'>
                    <i class="fas fa-edit me-1"></i>Edit
                </button>
                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this reward?')">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $reward['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>Delete</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($rewards)): ?>
    <div class="col-12">
        <div class="empty-state">
            <span class="material-icons-outlined">emoji_events</span>
            <p>No rewards added yet</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add Reward</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name (English)</label>
                            <input type="text" name="name_en" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name (Bengali)</label>
                            <input type="text" name="name_bn" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description (English)</label>
                            <textarea name="description_en" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description (Bengali)</label>
                            <textarea name="description_bn" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="url" name="image_url" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Reward Type</label>
                            <select name="reward_type" class="form-select">
                                <option value="discount">Discount</option>
                                <option value="free_item">Free Item</option>
                                <option value="points">Points</option>
                                <option value="coupon">Coupon</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Value (%)</label>
                            <input type="number" name="reward_value" class="form-control" value="10" min="0" max="100">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Expires In (mins)</label>
                            <input type="number" name="expires_in" class="form-control" value="15" min="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Probability (0-1)</label>
                        <input type="number" name="probability" class="form-control" value="0.1" min="0" max="1" step="0.01">
                        <small class="text-muted">e.g., 0.1 = 10% chance of winning</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Reward</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name (English)</label>
                            <input type="text" name="name_en" id="edit_name_en" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name (Bengali)</label>
                            <input type="text" name="name_bn" id="edit_name_bn" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description (English)</label>
                            <textarea name="description_en" id="edit_description_en" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Description (Bengali)</label>
                            <textarea name="description_bn" id="edit_description_bn" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="url" name="image_url" id="edit_image_url" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Reward Type</label>
                            <select name="reward_type" id="edit_reward_type" class="form-select">
                                <option value="discount">Discount</option>
                                <option value="free_item">Free Item</option>
                                <option value="points">Points</option>
                                <option value="coupon">Coupon</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Value (%)</label>
                            <input type="number" name="reward_value" id="edit_reward_value" class="form-control" min="0" max="100">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Expires In (mins)</label>
                            <input type="number" name="expires_in" id="edit_expires_in" class="form-control" min="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Probability (0-1)</label>
                        <input type="number" name="probability" id="edit_probability" class="form-control" min="0" max="1" step="0.01">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="edit_is_active" class="form-check-input">
                        <label class="form-check-label" for="edit_is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$pageScripts = <<<'SCRIPT'
<script>
function editReward(reward) {
    document.getElementById('edit_id').value = reward.id;
    document.getElementById('edit_name_en').value = reward.name_en;
    document.getElementById('edit_name_bn').value = reward.name_bn || '';
    document.getElementById('edit_description_en').value = reward.description_en || '';
    document.getElementById('edit_description_bn').value = reward.description_bn || '';
    document.getElementById('edit_image_url').value = reward.image_url || '';
    document.getElementById('edit_reward_type').value = reward.reward_type;
    document.getElementById('edit_reward_value').value = reward.reward_value;
    document.getElementById('edit_probability').value = reward.probability;
    document.getElementById('edit_expires_in').value = reward.expires_in;
    document.getElementById('edit_is_active').checked = reward.is_active;
    new mdb.Modal(document.getElementById('editModal')).show();
}
</script>
SCRIPT;
require_once __DIR__ . '/includes/footer.php'; 
?>
