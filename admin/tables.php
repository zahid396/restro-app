<?php 
$pageTitle = 'Tables & QR Codes';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$restaurantId = $_SESSION['restaurant_id'] ?? 1;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $tableNumber = (int)($_POST['table_number'] ?? 0);
        $capacity = (int)($_POST['capacity'] ?? 4);
        
        if ($tableNumber > 0) {
            try {
                $stmt = $db->prepare("INSERT INTO restaurant_tables (restaurant_id, table_number, capacity, status) VALUES (?, ?, ?, 'available')");
                $stmt->execute([$restaurantId, $tableNumber, $capacity]);
                $message = 'Table added successfully';
            } catch (Exception $e) {
                $message = 'Table number already exists';
            }
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $capacity = (int)($_POST['capacity'] ?? 4);
        $status = $_POST['status'] ?? 'available';
        
        $stmt = $db->prepare("UPDATE restaurant_tables SET capacity = ?, status = ? WHERE id = ? AND restaurant_id = ?");
        $stmt->execute([$capacity, $status, $id, $restaurantId]);
        $message = 'Table updated successfully';
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM restaurant_tables WHERE id = ? AND restaurant_id = ?");
        $stmt->execute([$id, $restaurantId]);
        $message = 'Table deleted successfully';
    }
}

$stmt = $db->prepare("SELECT * FROM restaurant_tables WHERE restaurant_id = ? ORDER BY table_number");
$stmt->execute([$restaurantId]);
$tables = $stmt->fetchAll();

$baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
?>

<div class="top-bar">
    <h1 class="page-title">Tables & QR Codes</h1>
    <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#addModal">
        <i class="fas fa-plus me-2"></i>Add Table
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-dismissible fade show"><?php echo $message; ?><button type="button" class="btn-close" data-mdb-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <?php foreach ($tables as $table): ?>
    <div class="col-md-4 col-lg-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <div id="qr-<?php echo $table['id']; ?>" class="d-flex justify-content-center"></div>
                </div>
                <h5 class="card-title">Table <?php echo $table['table_number']; ?></h5>
                <p class="card-text text-muted">
                    <i class="fas fa-users me-1"></i> <?php echo $table['capacity']; ?> seats
                </p>
                <span class="badge mb-3 <?php echo $table['status'] === 'available' ? 'bg-success' : ($table['status'] === 'busy' ? 'bg-danger' : 'bg-warning'); ?>">
                    <?php echo ucfirst($table['status']); ?>
                </span>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-sm btn-outline-primary" onclick="downloadQR(<?php echo $table['id']; ?>, <?php echo $table['table_number']; ?>)">
                        <i class="fas fa-download"></i> QR
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick='editTable(<?php echo json_encode($table); ?>)'>
                        <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this table?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $table['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($tables)): ?>
    <div class="col-12">
        <div class="empty-state">
            <span class="material-icons-outlined">table_restaurant</span>
            <p>No tables added yet</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add Table</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Table Number</label>
                        <input type="number" name="table_number" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacity (seats)</label>
                        <input type="number" name="capacity" class="form-control" value="4" min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Table</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Table</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Capacity (seats)</label>
                        <input type="number" name="capacity" id="edit_capacity" class="form-control" min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="available">Available</option>
                            <option value="busy">Busy</option>
                            <option value="reserved">Reserved</option>
                        </select>
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

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<?php 
$pageScripts = <<<SCRIPT
<script>
const baseUrl = '{$baseUrl}';

document.addEventListener('DOMContentLoaded', function() {
    const tables = document.querySelectorAll('[id^="qr-"]');
    tables.forEach(container => {
        const id = container.id.split('-')[1];
        const tableNum = container.closest('.card').querySelector('.card-title').textContent.replace('Table ', '');
        const url = baseUrl + '/?table=' + tableNum;
        
        QRCode.toCanvas(document.createElement('canvas'), url, { width: 120, margin: 1 }, function(err, canvas) {
            if (!err) {
                container.appendChild(canvas);
            }
        });
    });
});

function downloadQR(id, tableNum) {
    const url = baseUrl + '/?table=' + tableNum;
    QRCode.toDataURL(url, { width: 300, margin: 2 }, function(err, dataUrl) {
        if (!err) {
            const link = document.createElement('a');
            link.download = 'table-' + tableNum + '-qr.png';
            link.href = dataUrl;
            link.click();
        }
    });
}

function editTable(table) {
    document.getElementById('edit_id').value = table.id;
    document.getElementById('edit_capacity').value = table.capacity;
    document.getElementById('edit_status').value = table.status;
    new mdb.Modal(document.getElementById('editModal')).show();
}
</script>
SCRIPT;
require_once __DIR__ . '/includes/footer.php'; 
?>
