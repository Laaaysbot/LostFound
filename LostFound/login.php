<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['success'] = "Welcome back, " . $user['fullname'] . "!";
        redirect('dashboard.php');
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h2>🔐 Login to Your Account</h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
        <p style="margin-top: 15px; text-align: center;">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>