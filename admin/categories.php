<?php 
$pageTitle = 'Categories';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$restaurantId = $_SESSION['restaurant_id'] ?? 1;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $id = preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['id'] ?? ''));
        $nameEn = trim($_POST['name_en'] ?? '');
        $nameBn = trim($_POST['name_bn'] ?? '');
        $icon = trim($_POST['icon'] ?? 'category');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        
        if ($id && $nameEn) {
            try {
                $stmt = $db->prepare("INSERT INTO categories (id, restaurant_id, name_en, name_bn, icon, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $restaurantId, $nameEn, $nameBn, $icon, $sortOrder]);
                $message = 'Category added successfully';
            } catch (Exception $e) {
                $error = 'Category ID already exists';
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $nameEn = trim($_POST['name_en'] ?? '');
        $nameBn = trim($_POST['name_bn'] ?? '');
        $icon = trim($_POST['icon'] ?? 'category');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE categories SET name_en = ?, name_bn = ?, icon = ?, sort_order = ?, is_active = ? WHERE id = ? AND restaurant_id = ?");
        $stmt->execute([$nameEn, $nameBn, $icon, $sortOrder, $isActive, $id, $restaurantId]);
        $message = 'Category updated successfully';
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ? AND restaurant_id = ?");
        $stmt->execute([$id, $restaurantId]);
        $message = 'Category deleted successfully';
    }
}

$stmt = $db->prepare("SELECT * FROM categories WHERE restaurant_id = ? ORDER BY sort_order ASC");
$stmt->execute([$restaurantId]);
$categories = $stmt->fetchAll();
?>

<div class="top-bar">
    <h1 class="page-title">Categories</h1>
    <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#addModal">
        <i class="fas fa-plus me-2"></i>Add Category
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-dismissible fade show"><?php echo $message; ?><button type="button" class="btn-close" data-mdb-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-mdb-dismiss="alert"></button></div>
<?php endif; ?>

<div class="table-card">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Icon</th>
                <th>Name (EN)</th>
                <th>Name (BN)</th>
                <th>Sort Order</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><code><?php echo htmlspecialchars($cat['id']); ?></code></td>
                <td><span class="material-icons-outlined"><?php echo htmlspecialchars($cat['icon']); ?></span></td>
                <td><?php echo htmlspecialchars($cat['name_en']); ?></td>
                <td><?php echo htmlspecialchars($cat['name_bn']); ?></td>
                <td><?php echo $cat['sort_order']; ?></td>
                <td>
                    <span class="badge <?php echo $cat['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                        <?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick='editCategory(<?php echo json_encode($cat); ?>)'>
                        <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this category?')">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
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
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category ID (lowercase, no spaces)</label>
                        <input type="text" name="id" class="form-control" required pattern="[a-z0-9_]+">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name (English)</label>
                        <input type="text" name="name_en" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name (Bengali)</label>
                        <input type="text" name="name_bn" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Material Icon name)</label>
                        <input type="text" name="icon" class="form-control" value="category">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
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
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name (English)</label>
                        <input type="text" name="name_en" id="edit_name_en" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name (Bengali)</label>
                        <input type="text" name="name_bn" id="edit_name_bn" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <input type="text" name="icon" id="edit_icon" class="form-control">
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
function editCategory(cat) {
    document.getElementById('edit_id').value = cat.id;
    document.getElementById('edit_name_en').value = cat.name_en;
    document.getElementById('edit_name_bn').value = cat.name_bn || '';
    document.getElementById('edit_icon').value = cat.icon;
    document.getElementById('edit_sort_order').value = cat.sort_order;
    document.getElementById('edit_is_active').checked = cat.is_active;
    new mdb.Modal(document.getElementById('editModal')).show();
}
</script>
SCRIPT;
require_once __DIR__ . '/includes/footer.php'; 
?>
