<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    if($password !== $confirm_password) {
        $errors[] = "Passwords do not match!";
    }
    
    if(strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters!";
    }
    
    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullname, $email, $phone, $hashed_password]);
            $_SESSION['success'] = "Registration successful! Please login.";
            redirect('login.php');
        } catch(PDOException $e) {
            $errors[] = "Email already exists!";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h2>📝 Create an Account</h2>
    <?php if(!empty($errors)): ?>
        <?php foreach($errors as $error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="fullname" required value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Phone Number *</label>
            <input type="text" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
        </div>
        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required>
            <small>Minimum 6 characters</small>
        </div>
        <div class="form-group">
            <label>Confirm Password *</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn">Register</button>
        <p style="margin-top: 15px; text-align: center;">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>