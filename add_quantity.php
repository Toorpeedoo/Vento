<?php
session_start();
require_once 'auth.php';
requireLogin();
require_once 'classes/Product.php';
require_once 'classes/ProductDatabaseUtil.php';

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';
    
    if (empty($id) || empty($quantity)) {
        $message = "All fields must be filled out.";
        $messageType = "error";
    } else {
        try {
            if (!is_numeric($id) || !is_numeric($quantity)) {
                throw new Exception("ID and quantity must be valid numbers.");
            }
            
            $id = (int)$id;
            $quantity = (int)$quantity;
            
            if ($id < 0 || $quantity < 0) {
                throw new Exception("ID and quantity must be positive numbers.");
            }
            
            if (ProductDatabaseUtil::addQuantity($id, $quantity)) {
                $message = "Quantity added successfully!";
                $messageType = "success";
                $_POST = array();
            } else {
                $message = "Product not found.";
                $messageType = "error";
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
    <title>Add Quantity - VENTO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            <h1 class="nav-title">Add Quantity</h1>
            <div class="nav-actions">
                <a href="update_menu.php" class="btn btn-secondary btn-small">Back</a>
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
                           required min="0" placeholder="Enter product ID">
                </div>

                <div class="form-group">
                    <label class="form-label" for="quantity">Quantity to Add</label>
                    <input type="number" class="form-input" id="quantity" name="quantity" 
                           value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>" 
                           required min="0" placeholder="Enter quantity">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">Add Quantity</button>
                    <a href="update_menu.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

