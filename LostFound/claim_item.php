<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$claim_id = isset($_GET['id']) ? $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT c.*, i.title, i.description, i.user_id as owner_id, 
                              u.fullname as claimant_name, u.email as claimant_email, u.phone as claimant_phone,
                              owner.fullname as owner_name, owner.email as owner_email
                       FROM claims c 
                       JOIN items i ON c.item_id = i.id 
                       JOIN users u ON c.claimant_id = u.id 
                       JOIN users owner ON i.user_id = owner.id
                       WHERE c.id = ?");
$stmt->execute([$claim_id]);
$claim = $stmt->fetch();

if (!$claim || $claim['owner_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Unauthorized access!";
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        // Update claim status
        $stmt = $pdo->prepare("UPDATE claims SET status = 'approved' WHERE id = ?");
        $stmt->execute([$claim_id]);
        
        // Update item status to claimed
        $stmt = $pdo->prepare("UPDATE items SET status = 'claimed' WHERE id = ?");
        $stmt->execute([$claim['item_id']]);
        
        $_SESSION['success'] = "✅ Claim approved! You can now contact the claimant using their email or phone number.";
    } elseif ($action == 'reject') {
        $stmt = $pdo->prepare("UPDATE claims SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$claim_id]);
        $_SESSION['success'] = "❌ Claim rejected.";
    }
    
    redirect('dashboard.php');
}
?>
<?php include 'includes/header.php'; ?>

<div class="form-container" style="max-width: 800px;">
    <h2>🔍 Review Claim Request</h2>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #2c3e50;">Item Information</h3>
        <p><strong>📦 Item:</strong> <?php echo htmlspecialchars($claim['title']); ?></p>
        <p><strong>📝 Description:</strong> <?php echo nl2br(htmlspecialchars($claim['description'])); ?></p>
    </div>
    
    <div style="background: #e8f8f5; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #27ae60;">Claimant Information</h3>
        <p><strong>👤 Name:</strong> <?php echo htmlspecialchars($claim['claimant_name']); ?></p>
        <p><strong>📧 Email:</strong> <?php echo htmlspecialchars($claim['claimant_email']); ?></p>
        <p><strong>📞 Phone:</strong> <?php echo htmlspecialchars($claim['claimant_phone']); ?></p>
        <p><strong>📅 Claim Date:</strong> <?php echo date('F d, Y g:i A', strtotime($claim['created_at'])); ?></p>
    </div>
    
    <div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
        <h3 style="margin-bottom: 15px; color: #856404;">💬 Claimant's Message</h3>
        <div style="background: white; padding: 15px; border-radius: 8px;">
            <?php echo nl2br(htmlspecialchars($claim['message'])); ?>
        </div>
    </div>
    
    <form method="POST">
        <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px;">
            <button type="submit" name="action" value="approve" class="btn btn-success" onclick="return confirm('Approve this claim? The claimant will be able to contact you.')" style="padding: 12px 30px;">
                ✅ Approve Claim
            </button>
            <button type="submit" name="action" value="reject" class="btn btn-danger" onclick="return confirm('Reject this claim? This action cannot be undone.')" style="padding: 12px 30px;">
                ❌ Reject Claim
            </button>
            <a href="dashboard.php" class="btn" style="background: #95a5a6;">← Back to Dashboard</a>
        </div>
    </form>
    
    <div style="margin-top: 30px; padding: 15px; background: #d1ecf1; border-radius: 8px;">
        <p style="margin: 0; font-size: 14px;"><strong>💡 Tip:</strong> Before approving, make sure to:</p>
        <ul style="margin-top: 10px; margin-left: 20px; font-size: 14px;">
            <li>Review the claimant's message carefully</li>
            <li>Look for specific details only the owner would know</li>
            <li>Contact the claimant if you need more information</li>
            <li>Once approved, you can arrange item pickup using their contact info</li>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>