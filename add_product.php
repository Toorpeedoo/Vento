<?php
session_start();
require_once 'auth.php';
requireLogin();
require_once 'classes/Product.php';
require_once 'classes/FileDatabaseUtil.php';

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $productName = isset($_POST['productName']) ? trim($_POST['productName']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';
    
    if (empty($id) || empty($productName) || empty($price) || empty($quantity)) {
        $message = "All fields must be filled out.";
        $messageType = "error";
    } else {
        try {
            if (!is_numeric($id) || !is_numeric($price) || !is_numeric($quantity)) {
                throw new Exception("ID, Price, and Quantity must be valid numbers.");
            }
            
            $id = (int)$id;
            $price = (float)$price;
            $quantity = (int)$quantity;
            
            if ($id < 0 || $price < 0 || $quantity < 0) {
                throw new Exception("Values must be positive numbers.");
            }
            
            if (FileDatabaseUtil::productExists($id)) {
                $message = "Error: ID already exists. Please use a unique ID.";
                $messageType = "error";
            } else {
                $product = new Product($id, $productName, $price, $quantity);
                if (FileDatabaseUtil::addProduct($product)) {
                    $message = "Product added successfully!";
                    $messageType = "success";
                    // Clear form
                    $_POST = array();
                } else {
                    $message = "Error: Failed to add product.";
                    $messageType = "error";
                }
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - VENTO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            <h1 class="nav-title">Add Product</h1>
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

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="id">Product ID</label>
                    <input type="number" class="form-input" id="id" name="id" 
                           value="<?php echo isset($_POST['id']) ? htmlspecialchars($_POST['id']) : ''; ?>" 
                           required min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="productName">Product Name</label>
                    <input type="text" class="form-input" id="productName" name="productName" 
                           value="<?php echo isset($_POST['productName']) ? htmlspecialchars($_POST['productName']) : ''; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="price">Price</label>
                    <input type="number" step="0.01" class="form-input" id="price" name="price" 
                           value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" 
                           required min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="quantity">Quantity</label>
                    <input type="number" class="form-input" id="quantity" name="quantity" 
                           value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>" 
                           required min="0">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">Add Product</button>
                    <a href="main_menu.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

