<?php 
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$restaurantId = $_SESSION['restaurant_id'] ?? 1;

$todayStart = date('Y-m-d 00:00:00');
$orderStats = $db->prepare("
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total_amount), 0) as total_revenue,
        COUNT(CASE WHEN status NOT IN ('delivered', 'cancelled') THEN 1 END) as pending_orders
    FROM orders 
    WHERE restaurant_id = ? AND created_at >= ?
");
$orderStats->execute([$restaurantId, $todayStart]);
$stats = $orderStats->fetch();

$tableStats = $db->prepare("SELECT COUNT(*) as total, COUNT(CASE WHEN status = 'busy' THEN 1 END) as busy FROM restaurant_tables WHERE restaurant_id = ?");
$tableStats->execute([$restaurantId]);
$tables = $tableStats->fetch();

$pendingOrders = $db->prepare("
    SELECT o.*, 
           (SELECT GROUP_CONCAT(CONCAT(mi.name_en, ' x', oi.quantity) SEPARATOR ', ') 
            FROM order_items oi 
            JOIN menu_items mi ON oi.menu_item_id = mi.id 
            WHERE oi.order_id = o.id) as items_summary
    FROM orders o 
    WHERE o.restaurant_id = ? AND o.status NOT IN ('delivered', 'cancelled')
    ORDER BY o.created_at DESC
    LIMIT 10
");
$pendingOrders->execute([$restaurantId]);
$pending = $pendingOrders->fetchAll();
?>

<div class="top-bar">
    <h1 class="page-title">Dashboard</h1>
    <div class="d-flex align-items-center gap-3">
        <div class="notification-bell" onclick="window.location.href='orders.php'">
            <span class="material-icons-outlined" style="font-size: 28px;">notifications</span>
            <span class="notification-badge" id="pendingOrderCount"><?php echo $stats['pending_orders']; ?></span>
        </div>
        <span class="text-muted">Welcome, <?php echo htmlspecialchars($admin['name']); ?></span>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card-stat">
            <div class="icon" style="background: #dbeafe; color: #2563eb;">
                <span class="material-icons-outlined">receipt_long</span>
            </div>
            <h3><?php echo $stats['total_orders']; ?></h3>
            <p>Today's Orders</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat">
            <div class="icon" style="background: #d1fae5; color: #059669;">
                <span class="material-icons-outlined">payments</span>
            </div>
            <h3><?php echo formatPrice($stats['total_revenue']); ?></h3>
            <p>Today's Revenue</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat">
            <div class="icon" style="background: #fef3c7; color: #d97706;">
                <span class="material-icons-outlined">pending_actions</span>
            </div>
            <h3 class="<?php echo $stats['pending_orders'] > 0 ? 'pulse text-warning' : ''; ?>">
                <?php echo $stats['pending_orders']; ?>
            </h3>
            <p>Pending Orders</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat">
            <div class="icon" style="background: #ede9fe; color: #7c3aed;">
                <span class="material-icons-outlined">table_restaurant</span>
            </div>
            <h3><?php echo $tables['busy']; ?>/<?php echo $tables['total']; ?></h3>
            <p>Tables Occupied</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="table-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold">Pending Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            
            <div id="pendingOrdersList">
                <?php if (empty($pending)): ?>
                <div class="empty-state">
                    <span class="material-icons-outlined">check_circle</span>
                    <p>No pending orders</p>
                </div>
                <?php else: ?>
                <?php foreach ($pending as $order): ?>
                <div class="order-card status-<?php echo $order['status']; ?>">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong class="d-block">Order #<?php echo $order['id']; ?></strong>
                            <small class="text-muted">Table <?php echo $order['table_number']; ?> • <?php echo timeAgo($order['created_at']); ?></small>
                        </div>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    <p class="small text-muted mt-2 mb-2"><?php echo htmlspecialchars($order['items_summary'] ?? 'No items'); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                        <div class="btn-group btn-group-sm">
                            <?php if ($order['status'] === 'received'): ?>
                            <button class="btn btn-outline-primary btn-action" onclick="updateStatus(<?php echo $order['id']; ?>, 'cooking')">
                                Start Cooking
                            </button>
                            <?php elseif ($order['status'] === 'cooking'): ?>
                            <button class="btn btn-outline-info btn-action" onclick="updateStatus(<?php echo $order['id']; ?>, 'ready')">
                                Mark Ready
                            </button>
                            <?php elseif ($order['status'] === 'ready'): ?>
                            <button class="btn btn-success btn-action" onclick="updateStatus(<?php echo $order['id']; ?>, 'delivered')">
                                <i class="fas fa-check me-1"></i> Confirm Delivery
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="table-card p-4">
            <h5 class="mb-3 fw-bold">Quick Actions</h5>
            <div class="d-grid gap-2">
                <a href="menu-items.php?action=add" class="btn btn-outline-primary">
                    <span class="material-icons-outlined me-2">add_circle</span> Add Menu Item
                </a>
                <a href="tables.php?action=add" class="btn btn-outline-secondary">
                    <span class="material-icons-outlined me-2">table_restaurant</span> Add Table
                </a>
                <a href="categories.php" class="btn btn-outline-info">
                    <span class="material-icons-outlined me-2">category</span> Manage Categories
                </a>
                <a href="rewards.php" class="btn btn-outline-warning">
                    <span class="material-icons-outlined me-2">emoji_events</span> Manage Rewards
                </a>
            </div>
        </div>
        
        <div class="table-card p-4 mt-4">
            <h5 class="mb-3 fw-bold">Order Status Legend</h5>
            <div class="d-flex flex-column gap-2">
                <span class="status-badge status-received">Received - New order</span>
                <span class="status-badge status-cooking">Cooking - In preparation</span>
                <span class="status-badge status-ready">Ready - Awaiting delivery confirmation</span>
                <span class="status-badge status-delivered">Delivered - Completed</span>
            </div>
        </div>
    </div>
</div>

<?php 
$pageScripts = <<<SCRIPT
<script>
async function updateStatus(orderId, status) {
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

function updatePendingOrdersList(orders) {
    // Real-time update handled by page reload for now
}
</script>
SCRIPT;
require_once __DIR__ . '/includes/footer.php'; 
?>
