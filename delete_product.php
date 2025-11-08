<?php
session_start();
require_once 'classes/FileDatabaseUtil.php';

$message = "";
$messageType = "";
$products = FileDatabaseUtil::getAllProductsSorted();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    
    if (FileDatabaseUtil::deleteProduct($id)) {
        $message = "Product deleted successfully!";
        $messageType = "success";
        $products = FileDatabaseUtil::getAllProductsSorted(); // Refresh list
    } else {
        $message = "Product ID does not exist.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product - VENTO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            <h1 class="nav-title">Delete Product</h1>
            <div class="nav-actions">
                <a href="main_menu.php" class="btn btn-secondary btn-small">Back to Menu</a>
                <a href="#" onclick="window.close(); return false;" class="btn btn-danger btn-small">Exit</a>
            </div>
        </div>

        <div class="card">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="card-header">
                <h2 class="card-title">Delete Product</h2>
            </div>

            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    No products found.
                </div>
            <?php else: ?>
                <div class="table-container mb-3">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product->getId()); ?></td>
                                    <td><?php echo htmlspecialchars($product->getProductName()); ?></td>
                                    <td>$<?php echo number_format($product->getPrice(), 2); ?></td>
                                    <td><?php echo htmlspecialchars($product->getQuantity()); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this product?');">
                    <div class="form-group">
                        <label class="form-label" for="delete_id">Enter Product ID to Delete</label>
                        <input type="number" class="form-input" id="delete_id" name="delete_id" 
                               required min="0" placeholder="Enter product ID">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-danger">Delete Product</button>
                        <a href="main_menu.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

