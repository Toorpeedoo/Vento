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
    <title>Main Menu - VENTO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            <h1 class="nav-title">VENTO Inventory System</h1>
            <div class="nav-actions">
                <span style="color: var(--gray-700); margin-right: 1rem;">Welcome, <?php echo htmlspecialchars(getCurrentUsername()); ?>!</span>
                <?php if (isAdmin()): ?>
                    <a href="admin_dashboard.php" class="btn btn-warning btn-small">Admin Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-danger btn-small">Logout</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Main Menu</h2>
            </div>
            
            <div class="menu-grid">
                <a href="add_product.php" class="menu-card">
                    <h3 class="menu-card-title">Add Product</h3>
                    <p class="menu-card-description">Add a new product to the inventory</p>
                </a>
                
                <a href="update_product.php" class="menu-card">
                    <h3 class="menu-card-title">Update Product</h3>
                    <p class="menu-card-description">Update product information, add or subtract quantity</p>
                </a>
                
                <a href="view_products.php" class="menu-card">
                    <h3 class="menu-card-title">View Products</h3>
                    <p class="menu-card-description">View all products in inventory</p>
                </a>
                
                <a href="delete_product.php" class="menu-card">
                    <h3 class="menu-card-title">Delete Product</h3>
                    <p class="menu-card-description">Remove a product from inventory</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>

