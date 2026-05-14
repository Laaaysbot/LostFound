<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

// Handle delete
if (isset($_GET['delete'])) {
    $item_id = $_GET['delete'];
    
    // Get image path to delete
    $stmt = $pdo->prepare("SELECT image_path FROM items WHERE id = ? AND user_id = ?");
    $stmt->execute([$item_id, $user_id]);
    $item = $stmt->fetch();
    
    if($item && $item['image_path'] && file_exists($item['image_path'])) {
        unlink($item['image_path']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ? AND user_id = ?");
    $stmt->execute([$item_id, $user_id]);
    $_SESSION['success'] = "Item deleted successfully!";
    redirect('my_items.php');
}
?>
<?php include 'includes/header.php'; ?>

<h2>📦 My Posted Items</h2>
<p style="margin-bottom: 20px; color: #7f8c8d;">Manage all the items you've posted.</p>

<?php if(count($items) > 0): ?>
    <div class="items-grid">
        <?php foreach($items as $item): ?>
            <div class="item-card">
                <?php if($item['image_path'] && file_exists($item['image_path'])): ?>
                    <img src="<?php echo $item['image_path']; ?>" alt="Item Image">
                <?php else: ?>
                    <img src="https://via.placeholder.com/320x200?text=No+Image" alt="No Image">
                <?php endif; ?>
                <div class="item-info">
                    <div class="item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                    <span class="item-category category-<?php echo $item['category']; ?>">
                        <?php echo ucfirst($item['category']); ?>
                    </span>
                    <div><strong>Type:</strong> <?php echo htmlspecialchars($item['item_type']); ?></div>
                    <div><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></div>
                    <div><strong>Status:</strong> 
                        <span style="color: <?php echo $item['status'] == 'open' ? '#27ae60' : '#e74c3c'; ?>">
                            <?php echo ucfirst($item['status']); ?>
                        </span>
                    </div>
                    <div class="item-meta">
                        Posted: <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                    </div>
                    <div style="margin-top: 15px;">
                        <a href="view_item.php?id=<?php echo $item['id']; ?>" class="btn">View</a>
                        <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this item? This action cannot be undone.')">Delete</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-results">
        <p>📭 You haven't posted any items yet.</p>
        <a href="post_item.php" class="btn btn-success">➕ Post Your First Item</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>