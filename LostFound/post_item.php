<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $item_type = $_POST['item_type'];
    $location = $_POST['location'];
    $date_lost_found = $_POST['date_lost_found'];
    $contact_phone = $_POST['contact_phone'];
    $contact_email = $_POST['contact_email'];
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $image_path = $upload_dir . time() . '_' . basename($filename);
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO items (user_id, title, description, category, item_type, location, date_lost_found, contact_phone, contact_email, image_path) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $description, $category, $item_type, $location, $date_lost_found, $contact_phone, $contact_email, $image_path]);
    
    $_SESSION['success'] = "Item posted successfully!";
    redirect('dashboard.php');
}
?>
<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h2>📝 Post a New Item</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Category *</label>
            <select name="category" required>
                <option value="lost">🔴 Lost Item</option>
                <option value="found">🟢 Found Item</option>
            </select>
        </div>
        <div class="form-group">
            <label>Title *</label>
            <input type="text" name="title" placeholder="e.g., Lost Wallet, Found Phone" required>
        </div>
        <div class="form-group">
            <label>Item Type *</label>
            <input type="text" name="item_type" placeholder="e.g., Wallet, Phone, Book, Keys" required>
        </div>
        <div class="form-group">
            <label>Description *</label>
            <textarea name="description" placeholder="Provide detailed description of the item..." required></textarea>
        </div>
        <div class="form-group">
            <label>Location *</label>
            <input type="text" name="location" placeholder="Where was it lost/found?" required>
        </div>
        <div class="form-group">
            <label>Date Lost/Found *</label>
            <input type="date" name="date_lost_found" required>
        </div>
        <div class="form-group">
            <label>Contact Phone</label>
            <input type="text" name="contact_phone" placeholder="Your phone number">
        </div>
        <div class="form-group">
            <label>Contact Email</label>
            <input type="email" name="contact_email" placeholder="Your email address">
        </div>
        <div class="form-group">
            <label>Upload Image (optional)</label>
            <input type="file" name="image" accept="image/*">
            <small style="color: #7f8c8d;">Accepted formats: JPG, PNG, GIF (Max 2MB)</small>
        </div>
        <button type="submit" class="btn">✅ Post Item</button>
        <a href="dashboard.php" class="btn btn-danger" style="margin-left: 10px;">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>