<?php 
$pageTitle = 'Menu Items';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$restaurantId = $_SESSION['restaurant_id'] ?? 1;

$message = '';
$error = '';

$categoriesStmt = $db->prepare("SELECT * FROM categories WHERE restaurant_id = ? ORDER BY sort_order");
$categoriesStmt->execute([$restaurantId]);
$categories = $categoriesStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $id = $action === 'edit' ? (int)$_POST['id'] : null;
        $categoryId = $_POST['category_id'] ?? '';
        $nameEn = trim($_POST['name_en'] ?? '');
        $nameBn = trim($_POST['name_bn'] ?? '');
        $descEn = trim($_POST['description_en'] ?? '');
        $descBn = trim($_POST['description_bn'] ?? '');
        $price = (int)($_POST['price'] ?? 0);
        $imageUrl = trim($_POST['image_url'] ?? '');
        $weight = trim($_POST['weight'] ?? '');
        $allergens = trim($_POST['allergens'] ?? '');
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;
        $isTrending = isset($_POST['is_trending']) ? 1 : 0;
        $tags = $_POST['tags'] ?? '';
        $mood = $_POST['mood'] ?? '';
        
        $tagsJson = json_encode(array_filter(array_map('trim', explode(',', $tags))));
        $moodJson = json_encode(array_filter(array_map('trim', explode(',', $mood))));
        
        if ($action === 'add') {
            $stmt = $db->prepare("INSERT INTO menu_items (restaurant_id, category_id, name_en, name_bn, description_en, description_bn, price, image_url, weight, allergens, is_available, is_trending, tags, mood) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$restaurantId, $categoryId, $nameEn, $nameBn, $descEn, $descBn, $price, $imageUrl, $weight, $allergens, $isAvailable, $isTrending, $tagsJson, $moodJson]);
            $message = 'Menu item added successfully';
        } else {
            $stmt = $db->prepare("UPDATE menu_items SET category_id = ?, name_en = ?, name_bn = ?, description_en = ?, description_bn = ?, price = ?, image_url = ?, weight = ?, allergens = ?, is_available = ?, is_trending = ?, tags = ?, mood = ?, updated_at = NOW() WHERE id = ? AND restaurant_id = ?");
            $stmt->execute([$categoryId, $nameEn, $nameBn, $descEn, $descBn, $price, $imageUrl, $weight, $allergens, $isAvailable, $isTrending, $tagsJson, $moodJson, $id, $restaurantId]);
            $message = 'Menu item updated successfully';
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM menu_items WHERE id = ? AND restaurant_id = ?");
        $stmt->execute([$id, $restaurantId]);
        $message = 'Menu item deleted successfully';
    }
}

$categoryFilter = $_GET['category'] ?? '';
$sql = "SELECT mi.*, c.name_en as category_name FROM menu_items mi LEFT JOIN categories c ON mi.category_id = c.id WHERE mi.restaurant_id = ?";
$params = [$restaurantId];
if ($categoryFilter) {
    $sql .= " AND mi.category_id = ?";
    $params[] = $categoryFilter;
}
$sql .= " ORDER BY mi.category_id, mi.name_en";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>

<div class="top-bar">
    <h1 class="page-title">Menu Items</h1>
    <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#addModal">
        <i class="fas fa-plus me-2"></i>Add Item
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-dismissible fade show"><?php echo $message; ?><button type="button" class="btn-close" data-mdb-dismiss="alert"></button></div>
<?php endif; ?>

<div class="table-card p-3 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Filter by Category</label>
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter === $cat['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name_en']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="table-card">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Rating</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <?php if ($item['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                    <?php else: ?>
                    <div style="width: 50px; height: 50px; background: #f1f1f1; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-icons-outlined text-muted">image</span>
                    </div>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?php echo htmlspecialchars($item['name_en']); ?></strong>
                    <?php if ($item['is_trending']): ?><span class="badge bg-warning ms-1">Trending</span><?php endif; ?>
                    <br><small class="text-muted"><?php echo htmlspecialchars($item['name_bn']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($item['category_name'] ?? '-'); ?></td>
                <td><strong><?php echo formatPrice($item['price']); ?></strong></td>
                <td>
                    <span class="text-warning">★</span> <?php echo number_format($item['rating'], 1); ?>
                    <small class="text-muted">(<?php echo $item['likes']; ?> likes)</small>
                </td>
                <td>
                    <span class="badge <?php echo $item['is_available'] ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick='editItem(<?php echo json_encode($item); ?>)'>
                        <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this item?')">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add Menu Item</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name_en']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price (৳)</label>
                            <input type="number" name="price" class="form-control" required>
                        </div>
                    </div>
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
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Weight/Size</label>
                            <input type="text" name="weight" class="form-control" placeholder="e.g., 250g">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tags (comma separated)</label>
                            <input type="text" name="tags" class="form-control" placeholder="spicy, popular">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mood (comma separated)</label>
                            <input type="text" name="mood" class="form-control" placeholder="spicy, comfort">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Allergens</label>
                        <input type="text" name="allergens" class="form-control" placeholder="e.g., Contains nuts, dairy">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="is_available" class="form-check-input" checked>
                                <label class="form-check-label">Available</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="is_trending" class="form-check-input">
                                <label class="form-check-label">Trending</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Item</button>
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
                    <h5 class="modal-title">Edit Menu Item</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="edit_category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name_en']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price (৳)</label>
                            <input type="number" name="price" id="edit_price" class="form-control" required>
                        </div>
                    </div>
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
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" id="edit_image_url" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Weight/Size</label>
                            <input type="text" name="weight" id="edit_weight" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tags (comma separated)</label>
                            <input type="text" name="tags" id="edit_tags" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mood (comma separated)</label>
                            <input type="text" name="mood" id="edit_mood" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Allergens</label>
                        <input type="text" name="allergens" id="edit_allergens" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="is_available" id="edit_is_available" class="form-check-input">
                                <label class="form-check-label">Available</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="is_trending" id="edit_is_trending" class="form-check-input">
                                <label class="form-check-label">Trending</label>
                            </div>
                        </div>
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
function editItem(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_category_id').value = item.category_id;
    document.getElementById('edit_name_en').value = item.name_en;
    document.getElementById('edit_name_bn').value = item.name_bn || '';
    document.getElementById('edit_description_en').value = item.description_en || '';
    document.getElementById('edit_description_bn').value = item.description_bn || '';
    document.getElementById('edit_price').value = item.price;
    document.getElementById('edit_image_url').value = item.image_url || '';
    document.getElementById('edit_weight').value = item.weight || '';
    document.getElementById('edit_allergens').value = item.allergens || '';
    document.getElementById('edit_is_available').checked = item.is_available;
    document.getElementById('edit_is_trending').checked = item.is_trending;
    
    const tags = item.tags ? JSON.parse(item.tags) : [];
    const mood = item.mood ? JSON.parse(item.mood) : [];
    document.getElementById('edit_tags').value = tags.join(', ');
    document.getElementById('edit_mood').value = mood.join(', ');
    
    new mdb.Modal(document.getElementById('editModal')).show();
}
</script>
SCRIPT;
require_once __DIR__ . '/includes/footer.php'; 
?>
