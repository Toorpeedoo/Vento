<?php
session_start();
require_once 'auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Menu - VENTO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            <h1 class="nav-title">Update Menu</h1>
            <div class="nav-actions">
                <a href="main_menu.php" class="btn btn-secondary btn-small">Back to Menu</a>
                <a href="#" onclick="window.close(); return false;" class="btn btn-danger btn-small">Exit</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Update Options</h2>
            </div>
            
            <div class="menu-grid">
                <a href="update_product.php" class="menu-card">
                    <h3 class="menu-card-title">Update Information</h3>
                    <p class="menu-card-description">Update product name, price, and quantity</p>
                </a>
                
                <a href="add_quantity.php" class="menu-card">
                    <h3 class="menu-card-title">Add Quantity</h3>
                    <p class="menu-card-description">Increase product quantity</p>
                </a>
                
                <a href="subtract_quantity.php" class="menu-card">
                    <h3 class="menu-card-title">Subtract Quantity</h3>
                    <p class="menu-card-description">Decrease product quantity</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>

