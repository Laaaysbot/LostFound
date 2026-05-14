<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get counts for statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM items WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_items = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending FROM claims c JOIN items i ON c.item_id = i.id WHERE i.user_id = ? AND c.status = 'pending'");
$stmt->execute([$user_id]);
$pending_claims_count = $stmt->fetch()['pending'];

$stmt = $pdo->prepare("SELECT COUNT(*) as open FROM items WHERE user_id = ? AND status = 'open'");
$stmt->execute([$user_id]);
$open_items = $stmt->fetch()['open'];

// Get recent items posted by the user
$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$my_recent_items = $stmt->fetchAll();

// Get pending claims on items the logged-in user owns
$stmt = $pdo->prepare("SELECT c.*, i.title, i.id as item_id, u.fullname as claimant_name, u.email as claimant_email, u.phone as claimant_phone
                       FROM claims c 
                       JOIN items i ON c.item_id = i.id 
                       JOIN users u ON c.claimant_id = u.id 
                       WHERE i.user_id = ? AND c.status = 'pending'
                       ORDER BY c.created_at DESC");
$stmt->execute([$user_id]);
$pending_claims = $stmt->fetchAll();

// Get all recent items from all users (for feed)
$stmt = $pdo->prepare("SELECT i.*, u.fullname FROM items i JOIN users u ON i.user_id = u.id WHERE i.status = 'open' ORDER BY i.created_at DESC LIMIT 6");
$stmt->execute();
$recent_items = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<!-- Welcome Section -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h2 style="margin-bottom: 10px;">Welcome back, <?php echo htmlspecialchars($_SESSION['fullname']); ?>! 👋</h2>
    <p style="opacity: 0.9;">Here's what's happening with your lost and found items.</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card primary">
        <h3>Total Items Posted</h3>
        <div class="stat-number"><?php echo $total_items; ?></div>
        <a href="my_items.php" style="font-size: 14px; color: #3498db; text-decoration: none;">View all →</a>
    </div>
    <div class="stat-card danger">
        <h3>Pending Claims</h3>
        <div class="stat-number"><?php echo $pending_claims_count; ?></div>
        <?php if($pending_claims_count > 0): ?>
            <span style="font-size: 13px; color: #e74c3c;">⚠️ Requires attention!</span>
        <?php else: ?>
            <span style="font-size: 13px; color: #7f8c8d;">No pending claims</span>
        <?php endif; ?>
    </div>
    <div class="stat-card success">
        <h3>Active Items</h3>
        <div class="stat-number"><?php echo $open_items; ?></div>
        <a href="post_item.php" style="font-size: 14px; color: #27ae60; text-decoration: none;">Post new →</a>
    </div>
</div>

<!-- Pending Claims Section - THIS IS THE CLAIM FUNCTION FOR OWNERS -->
<h3 style="margin-top: 30px; margin-bottom: 20px;">📋 Pending Claims on Your Items</h3>
<?php if(count($pending_claims) > 0): ?>
    <div class="items-grid">
        <?php foreach($pending_claims as $claim): ?>
            <div class="item-card" style="border-left: 4px solid #ffc107;">
                <div class="item-info">
                    <div class="item-title">🎯 <?php echo htmlspecialchars($claim['title']); ?></div>
                    <p><strong>👤 Claimant:</strong> <?php echo htmlspecialchars($claim['claimant_name']); ?></p>
                    <p><strong>📧 Claimant Email:</strong> <?php echo htmlspecialchars($claim['claimant_email']); ?></p>
                    <p><strong>📞 Claimant Phone:</strong> <?php echo htmlspecialchars($claim['claimant_phone']); ?></p>
                    <p><strong>💬 Message:</strong></p>
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;">
                        <?php echo nl2br(htmlspecialchars(substr($claim['message'], 0, 150))); ?>
                        <?php if(strlen($claim['message']) > 150): ?>...<?php endif; ?>
                    </div>
                    <p><strong>📅 Claim Date:</strong> <?php echo date('F d, Y g:i A', strtotime($claim['created_at'])); ?></p>
                    <a href="claim_item.php?id=<?php echo $claim['id']; ?>" class="btn" style="margin-top: 10px; background: #ffc107; color: #333;">
                        🔍 Review & Respond
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div style="background: #f8f9fa; padding: 30px; text-align: center; border-radius: 10px; margin-bottom: 30px;">
        <p style="color: #7f8c8d;">📭 No pending claims on your items.</p>
        <p style="font-size: 14px; color: #95a5a6;">When someone claims one of your items, it will appear here.</p>
    </div>
<?php endif; ?>

<!-- Your Recent Items -->
<h3 style="margin-top: 30px; margin-bottom: 20px;">📦 Your Recent Items</h3>
<?php if(count($my_recent_items) > 0): ?>
    <div class="items-grid">
        <?php foreach($my_recent_items as $item): ?>
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
                        <span style="color: <?php echo $item['status'] == 'open' ? '#27ae60' : '#e74c3c'; ?>; font-weight: bold;">
                            <?php echo ucfirst($item['status']); ?>
                        </span>
                    </div>
                    <div class="item-meta">
                        Posted: <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                    </div>
                    <a href="view_item.php?id=<?php echo $item['id']; ?>" class="btn" style="margin-top: 10px;">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if(count($my_recent_items) >= 5): ?>
        <div style="text-align: center; margin-top: 20px;">
            <a href="my_items.php" class="btn">View All My Items →</a>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div style="background: #f8f9fa; padding: 30px; text-align: center; border-radius: 10px; margin-bottom: 30px;">
        <p>You haven't posted any items yet.</p>
        <a href="post_item.php" class="btn btn-success" style="margin-top: 10px;">➕ Post Your First Item</a>
    </div>
<?php endif; ?>

<!-- Recently Added Items Feed -->
<h3 style="margin-top: 30px; margin-bottom: 20px;">🆕 Recently Added Items</h3>
<div class="items-grid">
    <?php foreach($recent_items as $item): ?>
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
                <div class="item-meta">
                    Posted by: <?php echo htmlspecialchars($item['fullname']); ?><br>
                    Date: <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                </div>
                <a href="view_item.php?id=<?php echo $item['id']; ?>" class="btn" style="margin-top: 10px;">View Details</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>