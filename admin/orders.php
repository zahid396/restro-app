<?php 
$pageTitle = 'Orders';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$restaurantId = $_SESSION['restaurant_id'] ?? 1;

$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? date('Y-m-d');

$sql = "SELECT o.*, 
        (SELECT GROUP_CONCAT(CONCAT(mi.name_en, ' x', oi.quantity) SEPARATOR ', ') 
         FROM order_items oi 
         JOIN menu_items mi ON oi.menu_item_id = mi.id 
         WHERE oi.order_id = o.id) as items_summary
        FROM orders o 
        WHERE o.restaurant_id = ?";
$params = [$restaurantId];

if ($statusFilter) {
    $sql .= " AND o.status = ?";
    $params[] = $statusFilter;
}

if ($dateFilter) {
    $sql .= " AND DATE(o.created_at) = ?";
    $params[] = $dateFilter;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="top-bar">
    <h1 class="page-title">Orders Management</h1>
    <div class="notification-bell">
        <span class="material-icons-outlined" style="font-size: 28px;">notifications</span>
        <span class="notification-badge" id="pendingOrderCount">0</span>
    </div>
</div>

<div class="table-card p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="received" <?php echo $statusFilter === 'received' ? 'selected' : ''; ?>>Received</option>
                <option value="cooking" <?php echo $statusFilter === 'cooking' ? 'selected' : ''; ?>>Cooking</option>
                <option value="ready" <?php echo $statusFilter === 'ready' ? 'selected' : ''; ?>>Ready</option>
                <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="<?php echo $dateFilter; ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="orders.php" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>
</div>

<div class="table-card">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Table</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">No orders found</td>
            </tr>
            <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><strong>#<?php echo $order['id']; ?></strong></td>
                <td>Table <?php echo $order['table_number']; ?></td>
                <td class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($order['items_summary']); ?>">
                    <?php echo htmlspecialchars($order['items_summary'] ?? '-'); ?>
                </td>
                <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                <td>
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </td>
                <td><?php echo timeAgo($order['created_at']); ?></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <?php if ($order['status'] === 'received'): ?>
                        <button class="btn btn-primary btn-action" onclick="updateStatus(<?php echo $order['id']; ?>, 'cooking')">
                            <i class="fas fa-fire"></i> Cook
                        </button>
                        <?php elseif ($order['status'] === 'cooking'): ?>
                        <button class="btn btn-info btn-action" onclick="updateStatus(<?php echo $order['id']; ?>, 'ready')">
                            <i class="fas fa-bell"></i> Ready
                        </button>
                        <?php elseif ($order['status'] === 'ready'): ?>
                        <button class="btn btn-success btn-action" onclick="updateStatus(<?php echo $order['id']; ?>, 'delivered')">
                            <i class="fas fa-check"></i> Deliver
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                        <button class="btn btn-outline-danger btn-action" onclick="updateStatus(<?php echo $order['id']; ?>, 'cancelled')">
                            <i class="fas fa-times"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
$pageScripts = <<<SCRIPT
<script>
async function updateStatus(orderId, status) {
    const confirmMsg = status === 'cancelled' ? 'Cancel this order?' : 'Update order status?';
    if (!confirm(confirmMsg)) return;
    
    try {
        const response = await fetch('api/update-order-status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ order_id: orderId, status: status, csrf_token: CSRF_TOKEN })
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to update'));
        }
    } catch (error) {
        alert('Error updating order status');
    }
}
</script>
SCRIPT;
require_once __DIR__ . '/includes/footer.php'; 
?>
