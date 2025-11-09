<?php
session_start();
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VENTO - Inventory Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="splash-container">
        <div class="splash-content">
            <div class="splash-left">
                <h1 class="splash-title">VENTO</h1>
                <p class="splash-subtitle">Your go-to solution</p>
                <p class="splash-description">for efficient Inventory Management</p>
            </div>
            <div class="splash-right">
                <div class="button-group">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <a href="admin_dashboard.php" class="btn btn-primary btn-large">Admin Dashboard</a>
                        <?php else: ?>
                            <a href="main_menu.php" class="btn btn-primary btn-large">Continue to Menu</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-secondary">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-large">Login</a>
                        <a href="signup.php" class="btn btn-secondary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

