<?php 
$pageTitle = 'Payment Methods';
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
        $methodType = $_POST['method_type'] ?? 'other';
        $accountNumber = trim($_POST['account_number'] ?? '');
        $accountName = trim($_POST['account_name'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $icon = trim($_POST['icon'] ?? 'payments');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        
        $stmt = $db->prepare("INSERT INTO payment_methods (restaurant_id, name_en, name_bn, method_type, account_number, account_name, instructions, icon, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$restaurantId, $nameEn, $nameBn, $methodType, $accountNumber, $accountName, $instructions, $icon, $sortOrder]);
        $message = 'Payment method added successfully';
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $nameEn = trim($_POST['name_en'] ?? '');
        $nameBn = trim($_POST['name_bn'] ?? '');
        $methodType = $_POST['method_type'] ?? 'other';
        $accountNumber = trim($_POST['account_number'] ?? '');
        $accountName = trim($_POST['account_name'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $icon = trim($_POST['icon'] ?? 'payments');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE payment_methods SET name_en = ?, name_bn = ?, method_type = ?, account_number = ?, account_name = ?, instructions = ?, icon = ?, sort_order = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
        $stmt->execute([$nameEn, $nameBn, $methodType, $accountNumber, $accountName, $instructions, $icon, $sortOrder, $isActive, $id, $restaurantId]);
        $message = 'Payment method updated successfully';
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM payment_methods WHERE id = ? AND restaurant_id = ?");
        $stmt->execute([$id, $restaurantId]);
        $message = 'Payment method deleted successfully';
    }
}

$stmt = $db->prepare("SELECT * FROM payment_methods WHERE restaurant_id = ? ORDER BY sort_order");
$stmt->execute([$restaurantId]);
$methods = $stmt->fetchAll();
?>

<div class="top-bar">
    <h1 class="page-title">Payment Methods</h1>
    <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#addModal">
        <i class="fas fa-plus me-2"></i>Add Payment Method
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-dismissible fade show"><?php echo $message; ?><button type="button" class="btn-close" data-mdb-dismiss="alert"></button></div>
<?php endif; ?>

<div class="table-card">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Icon</th>
                <th>Name</th>
                <th>Type</th>
                <th>Account Number</th>
                <th>Account Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($methods as $method): ?>
            <tr>
                <td><span class="material-icons-outlined"><?php echo htmlspecialchars($method['icon']); ?></span></td>
                <td>
                    <strong><?php echo htmlspecialchars($method['name_en']); ?></strong>
                    <br><small class="text-muted"><?php echo htmlspecialchars($method['name_bn']); ?></small>
                </td>
                <td><span class="badge bg-info"><?php echo ucfirst($method['method_type']); ?></span></td>
                <td><?php echo htmlspecialchars($method['account_number'] ?: '-'); ?></td>
                <td><?php echo htmlspecialchars($method['account_name'] ?: '-'); ?></td>
                <td>
                    <span class="badge <?php echo $method['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                        <?php echo $method['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick='editMethod(<?php echo json_encode($method); ?>)'>
                        <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this payment method?')">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $method['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add Payment Method</h5>
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
                            <label class="form-label">Type</label>
                            <select name="method_type" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile Payment</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon</label>
                            <input type="text" name="icon" class="form-control" value="payments">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" class="form-control" placeholder="e.g., 01712345678">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="account_name" class="form-control" placeholder="Account holder name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instructions</label>
                        <textarea name="instructions" class="form-control" rows="2" placeholder="Payment instructions for customers"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Payment Method</h5>
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
                            <label class="form-label">Type</label>
                            <select name="method_type" id="edit_method_type" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile Payment</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon</label>
                            <input type="text" name="icon" id="edit_icon" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" id="edit_account_number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="account_name" id="edit_account_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instructions</label>
                        <textarea name="instructions" id="edit_instructions" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
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
function editMethod(method) {
    document.getElementById('edit_id').value = method.id;
    document.getElementById('edit_name_en').value = method.name_en;
    document.getElementById('edit_name_bn').value = method.name_bn || '';
    document.getElementById('edit_method_type').value = method.method_type;
    document.getElementById('edit_account_number').value = method.account_number || '';
    document.getElementById('edit_account_name').value = method.account_name || '';
    document.getElementById('edit_instructions').value = method.instructions || '';
    document.getElementById('edit_icon').value = method.icon;
    document.getElementById('edit_sort_order').value = method.sort_order;
    document.getElementById('edit_is_active').checked = method.is_active;
    new mdb.Modal(document.getElementById('editModal')).show();
}
</script>
SCRIPT;
require_once __DIR__ . '/includes/footer.php'; 
?>
