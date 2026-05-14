<?php
require_once 'config/database.php';

$item_id = isset($_GET['id']) ? $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT i.*, u.fullname, u.email, u.phone 
                       FROM items i 
                       JOIN users u ON i.user_id = u.id 
                       WHERE i.id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    redirect('index.php');
}

// Check if user has already claimed this item
$has_claimed = false;
if(isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM claims WHERE item_id = ? AND claimant_id = ?");
    $stmt->execute([$item_id, $_SESSION['user_id']]);
    $has_claimed = $stmt->rowCount() > 0;
}

// Handle claim submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['claim_item']) && isLoggedIn()) {
    $message = $_POST['message'];
    $claimant_id = $_SESSION['user_id'];
    
    // Check if already claimed
    $stmt = $pdo->prepare("SELECT * FROM claims WHERE item_id = ? AND claimant_id = ?");
    $stmt->execute([$item_id, $claimant_id]);
    if($stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO claims (item_id, claimant_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$item_id, $claimant_id, $message]);
        $_SESSION['success'] = "Claim request submitted successfully! The owner will review your claim.";
    } else {
        $_SESSION['error'] = "You have already claimed this item!";
    }
    redirect("view_item.php?id=$item_id");
}
?>
<?php include 'includes/header.php'; ?>

<div class="item-details">
    <h2>📌 <?php echo htmlspecialchars($item['title']); ?></h2>
    
    <?php if($item['image_path'] && file_exists($item['image_path'])): ?>
        <div style="text-align: center;">
            <img src="<?php echo $item['image_path']; ?>" alt="Item Image">
        </div>
    <?php endif; ?>
    
    <p><strong>📂 Category:</strong> 
        <span style="color: <?php echo $item['category'] == 'lost' ? '#e74c3c' : '#27ae60'; ?>; font-weight: bold;">
            <?php echo ucfirst($item['category']); ?>
        </span>
    </p>
    <p><strong>🏷️ Item Type:</strong> <?php echo htmlspecialchars($item['item_type']); ?></p>
    <p><strong>📝 Description:</strong></p>
    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;">
        <?php echo nl2br(htmlspecialchars($item['description'])); ?>
    </div>
    <p><strong>📍 Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
    <p><strong>📅 Date Lost/Found:</strong> <?php echo date('F d, Y', strtotime($item['date_lost_found'])); ?></p>
    <p><strong>👤 Posted by:</strong> <?php echo htmlspecialchars($item['fullname']); ?></p>
    <p><strong>📞 Contact Phone:</strong> <?php echo htmlspecialchars($item['contact_phone']) ?: 'Not provided'; ?></p>
    <p><strong>📧 Contact Email:</strong> <?php echo htmlspecialchars($item['contact_email']) ?: 'Not provided'; ?></p>
    <p><strong>📊 Status:</strong> 
        <span style="background: <?php echo $item['status'] == 'open' ? '#d4edda' : '#f8d7da'; ?>; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
            <?php echo ucfirst($item['status']); ?>
        </span>
    </p>
    
    <!-- CLAIM BUTTON - This is where the claim function appears -->
    <?php if(isLoggedIn()): ?>
        <?php if($_SESSION['user_id'] != $item['user_id']): ?>
            <?php if($item['status'] == 'open'): ?>
                <?php if(!$has_claimed): ?>
                    <div style="margin-top: 30px; padding: 20px; background: #e8f8f5; border-radius: 10px; text-align: center;">
                        <h3 style="color: #27ae60; margin-bottom: 15px;">🔔 Is this your item?</h3>
                        <p style="margin-bottom: 15px;">If this item belongs to you, submit a claim request. The owner will review your claim and contact you.</p>
                        <button onclick="document.getElementById('claimForm').style.display='block'" class="btn btn-success" style="font-size: 16px; padding: 12px 30px;">
                            📝 Claim This Item
                        </button>
                    </div>
                    
                    <div id="claimForm" style="display: none; margin-top: 20px; padding: 25px; background: #fff3cd; border-radius: 10px; border: 2px solid #ffc107;">
                        <h3 style="margin-bottom: 15px; color: #856404;">Submit Claim Request</h3>
                        <form method="POST">
                            <div class="form-group">
                                <label style="font-weight: bold;">Why do you believe this item belongs to you? *</label>
                                <textarea name="message" required rows="6" placeholder="Please provide detailed information to prove ownership. Include things like:
• When and where you lost the item
• Distinguishing features or marks
• Any identifying information (serial numbers, engravings, etc.)
• Photos or receipts as proof (you can mention them here)"
                                style="width: 100%; padding: 12px; border: 2px solid #ffc107; border-radius: 8px;"></textarea>
                                <small style="color: #856404;">⚠️ Be as detailed as possible. The owner will use this information to verify ownership.</small>
                            </div>
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <button type="submit" name="claim_item" class="btn btn-success">✅ Submit Claim</button>
                                <button type="button" onclick="document.getElementById('claimForm').style.display='none'" class="btn btn-danger">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" style="margin-top: 20px; text-align: center;">
                        <strong>⏳ Claim Pending</strong><br>
                        You have already submitted a claim for this item. The owner will review it shortly.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-error" style="margin-top: 20px; text-align: center;">
                    <strong>❌ Item Unavailable</strong><br>
                    This item has already been claimed/resolved and is no longer available.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-success" style="margin-top: 20px; text-align: center;">
                <strong>📦 This is your item</strong><br>
                You posted this item. Check your dashboard for any claims from others.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info" style="margin-top: 20px; text-align: center;">
            <strong>🔐 Login to Claim</strong><br>
            Please <a href="login.php" style="color: #3498db;">login</a> or <a href="register.php" style="color: #3498db;">register</a> to claim this item.
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 30px; display: flex; gap: 10px;">
        <a href="index.php" class="btn">← Back to Home</a>
        <?php if(isLoggedIn() && $_SESSION['user_id'] == $item['user_id']): ?>
            <a href="my_items.php" class="btn">📦 My Items</a>
        <?php endif; ?>
    </div>
</div>

<style>
    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #17a2b8;
    }
</style>

<?php include 'includes/footer.php'; ?>