<?php
require_once 'config/database.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT i.*, u.fullname, u.email, u.phone 
        FROM items i 
        JOIN users u ON i.user_id = u.id 
        WHERE i.status = 'open'";

$params = [];

if (!empty($search)) {
    $sql .= " AND (i.title LIKE ? OR i.description LIKE ? OR i.item_type LIKE ? OR i.location LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($category)) {
    $sql .= " AND i.category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY i.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<h2>Lost & Found Items</h2>

<!-- Professional Search Bar Section -->
<div class="search-section">
    <div class="search-container">
        <form method="GET" class="search-form">
            <div class="search-input-wrapper">
                <span class="search-icon">🔍</span>
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Search by title, description, item type, or location..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <?php if(!empty($search)): ?>
                    <a href="index.php" class="clear-search" title="Clear search">✕</a>
                <?php endif; ?>
            </div>
            
            <div class="filter-wrapper">
                <select name="category" class="category-filter">
                    <option value="">📋 All Items</option>
                    <option value="lost" <?php echo $category == 'lost' ? 'selected' : ''; ?>>⚠️ Lost Items</option>
                    <option value="found" <?php echo $category == 'found' ? 'selected' : ''; ?>>✓ Found Items</option>
                </select>
            </div>
            
            <button type="submit" class="search-btn">
                <span>Search</span>
            </button>
        </form>
    </div>
    
    <!-- Search results info -->
    <?php if(!empty($search) || !empty($category)): ?>
        <div class="search-info">
            Found <strong><?php echo count($items); ?></strong> result(s)
            <?php if(!empty($search)): ?>
                for "<strong><?php echo htmlspecialchars($search); ?></strong>"
            <?php endif; ?>
            <a href="index.php" class="clear-filters">Clear all filters</a>
        </div>
    <?php endif; ?>
</div>

<div class="items-grid">
    <?php if(count($items) > 0): ?>
        <?php foreach($items as $item): ?>
            <div class="item-card">
                <?php if($item['image_path'] && file_exists($item['image_path'])): ?>
                    <img src="<?php echo $item['image_path']; ?>" alt="Item Image">
                <?php else: ?>
                    <img src="https://via.placeholder.com/300x200?text=No+Image" alt="No Image">
                <?php endif; ?>
                <div class="item-info">
                    <div class="item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                    <span class="item-category category-<?php echo $item['category']; ?>">
                        <?php echo ucfirst($item['category']); ?>
                    </span>
                    <div><strong>Type:</strong> <?php echo htmlspecialchars($item['item_type']); ?></div>
                    <div><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></div>
                    <div class="item-meta">
                        Posted by: <?php echo htmlspecialchars($item['fullname']); ?><br>
                        Date: <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                    </div>
                    <a href="view_item.php?id=<?php echo $item['id']; ?>" class="btn" style="margin-top: 10px;">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-results">
            <p>No items found matching your criteria.</p>
            <a href="index.php" class="btn">View all items</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>