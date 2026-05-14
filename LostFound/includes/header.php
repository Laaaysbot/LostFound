<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Additional clean styles for header */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Styles */
        .header {
            background: #2c3e50;
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .nav {
            text-align: center;
            margin-top: 10px;
        }

        .nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .nav a:hover {
            background: #34495e;
            transform: translateY(-2px);
        }

        /* Alert Styles */
        .alert {
            padding: 12px 20px;
            margin: 10px 0;
            border-radius: 8px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Footer Styles */
        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }

        /* Responsive Navigation */
        @media (max-width: 768px) {
            .nav a {
                margin: 5px;
                padding: 6px 12px;
                font-size: 14px;
            }
            
            .header h1 {
                font-size: 22px;
            }
            
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>📱 Lost & Found System</h1>
            <div class="nav">
                <a href="index.php">🏠 Home</a>
                <?php if(isset($_SESSION['user_id']) && isLoggedIn()): ?>
                    <a href="dashboard.php">📊 Dashboard</a>
                    <a href="post_item.php">➕ Post Item</a>
                    <a href="my_items.php">📦 My Items</a>
                    <a href="logout.php">🚪 Logout (<?php echo htmlspecialchars($_SESSION['fullname']); ?>)</a>
                <?php else: ?>
                    <a href="login.php">🔐 Login</a>
                    <a href="register.php">📝 Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="container">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>