<?php 
$pageTitle = 'Reviews';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$restaurantId = $_SESSION['restaurant_id'] ?? 1;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'toggle_visibility') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE reviews SET is_visible = NOT is_visible WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Review visibility updated';
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Review deleted successfully';
    }
}

$stmt = $db->prepare("
    SELECT r.*, o.table_number, mi.name_en as item_name
    FROM reviews r
    LEFT JOIN orders o ON r.order_id = o.id
    LEFT JOIN menu_items mi ON r.menu_item_id = mi.id
    WHERE o.restaurant_id = ? OR r.order_id IS NULL
    ORDER BY r.created_at DESC
    LIMIT 100
");
$stmt->execute([$restaurantId]);
$reviews = $stmt->fetchAll();

$statsStmt = $db->prepare("
    SELECT 
        COUNT(*) as total,
        AVG(rating) as avg_rating,
        COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
        COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive
    FROM reviews r
    LEFT JOIN orders o ON r.order_id = o.id
    WHERE o.restaurant_id = ? OR r.order_id IS NULL
");
$statsStmt->execute([$restaurantId]);
$stats = $statsStmt->fetch();
?>

<div class="top-bar">
    <h1 class="page-title">Customer Reviews</h1>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-dismissible fade show"><?php echo $message; ?><button type="button" class="btn-close" data-mdb-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card-stat">
            <div class="icon" style="background: #dbeafe; color: #2563eb;">
                <span class="material-icons-outlined">rate_review</span>
            </div>
            <h3><?php echo $stats['total']; ?></h3>
            <p>Total Reviews</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat">
            <div class="icon" style="background: #fef3c7; color: #d97706;">
                <span class="material-icons-outlined">star</span>
            </div>
            <h3><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
            <p>Average Rating</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat">
            <div class="icon" style="background: #d1fae5; color: #059669;">
                <span class="material-icons-outlined">thumb_up</span>
            </div>
            <h3><?php echo $stats['positive']; ?></h3>
            <p>Positive (4-5 stars)</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat">
            <div class="icon" style="background: #ede9fe; color: #7c3aed;">
                <span class="material-icons-outlined">auto_awesome</span>
            </div>
            <h3><?php echo $stats['five_star']; ?></h3>
            <p>5-Star Reviews</p>
        </div>
    </div>
</div>

<div class="table-card">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Item/Order</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reviews)): ?>
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">No reviews yet</td>
            </tr>
            <?php else: ?>
            <?php foreach ($reviews as $review): ?>
            <tr class="<?php echo !$review['is_visible'] ? 'table-secondary' : ''; ?>">
                <td>
                    <span class="me-2"><?php echo $review['avatar']; ?></span>
                    <?php echo htmlspecialchars($review['user_name']); ?>
                </td>
                <td>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>">★</span>
                    <?php endfor; ?>
                </td>
                <td class="text-truncate" style="max-width: 200px;">
                    <?php echo htmlspecialchars($review['comment'] ?: '-'); ?>
                </td>
                <td>
                    <?php if ($review['item_name']): ?>
                    <small class="text-muted"><?php echo htmlspecialchars($review['item_name']); ?></small>
                    <?php elseif ($review['order_id']): ?>
                    <small class="text-muted">Order #<?php echo $review['order_id']; ?></small>
                    <?php else: ?>
                    <small class="text-muted">-</small>
                    <?php endif; ?>
                </td>
                <td><small><?php echo timeAgo($review['created_at']); ?></small></td>
                <td>
                    <span class="badge <?php echo $review['is_visible'] ? 'bg-success' : 'bg-secondary'; ?>">
                        <?php echo $review['is_visible'] ? 'Visible' : 'Hidden'; ?>
                    </span>
                </td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="toggle_visibility">
                        <input type="hidden" name="id" value="<?php echo $review['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Toggle visibility">
                            <i class="fas fa-eye<?php echo $review['is_visible'] ? '-slash' : ''; ?>"></i>
                        </button>
                    </form>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this review?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $review['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
