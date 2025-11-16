<?php
session_start();
require_once 'auth.php';
requireLogin();
require_once 'classes/FileDatabaseUtil.php';

$message = "";
$messageType = "";
$products = FileDatabaseUtil::getAllProductsSorted();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    
    if (ProductDatabaseUtil::deleteProduct($id)) {
        $message = "Product deleted successfully!";
        $messageType = "success";
        $products = ProductDatabaseUtil::getAllProductsSorted(); // Refresh list
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
                <div class="search-container mb-3">
                    <div class="search-box">
                        <input type="text" id="searchInput" class="search-input" placeholder="Search by ID, Name, Price, or Quantity..." onkeyup="filterProducts()">
                        <button type="button" class="search-btn" onclick="filterProducts()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="search-info">
                        <span id="resultCount"><?php echo count($products); ?></span> product(s) found
                    </div>
                </div>

                <div class="table-container mb-3" style="max-height: 400px; overflow-y: auto;">
                    <table id="productsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <?php foreach ($products as $product): ?>
                                <tr class="clickable-row" onclick="selectProduct(<?php echo htmlspecialchars($product->getId()); ?>, this)">
                                    <td><?php echo htmlspecialchars($product->getId()); ?></td>
                                    <td><?php echo htmlspecialchars($product->getProductName()); ?></td>
                                    <td>₱<?php echo number_format($product->getPrice(), 2); ?></td>
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

    <script>
        const allProducts = <?php echo !empty($products) ? json_encode(array_map(function($p) { 
            return [
                'id' => $p->getId(),
                'name' => $p->getProductName(),
                'price' => $p->getPrice(),
                'quantity' => $p->getQuantity()
            ];
        }, $products)) : '[]'; ?>;
        
        function filterProducts() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const tbody = document.getElementById('productsTableBody');
            const resultCount = document.getElementById('resultCount');
            let count = 0;
            
            tbody.innerHTML = '';
            
            allProducts.forEach(product => {
                const id = product.id.toString();
                const name = product.name.toLowerCase();
                const price = product.price.toString();
                const quantity = product.quantity.toString();
                
                if (id.includes(searchTerm) || 
                    name.includes(searchTerm) || 
                    price.includes(searchTerm) || 
                    quantity.includes(searchTerm)) {
                    
                    const row = tbody.insertRow();
                    row.className = 'clickable-row';
                    row.onclick = function() { selectProduct(product.id, this); };
                    row.innerHTML = `
                        <td>${product.id}</td>
                        <td>${escapeHtml(product.name)}</td>
                        <td>₱${parseFloat(product.price).toFixed(2)}</td>
                        <td>${product.quantity}</td>
                    `;
                    count++;
                }
            });
            
            resultCount.textContent = count;
            
            if (count === 0 && searchTerm !== '') {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 2rem; color: var(--gray-500);">No products found matching your search</td></tr>';
            }
        }
        
        function selectProduct(productId, rowElement) {
            document.getElementById('delete_id').value = productId;
            // Highlight the selected row
            document.querySelectorAll('.clickable-row').forEach(row => {
                row.classList.remove('selected');
            });
            if (rowElement) {
                rowElement.classList.add('selected');
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>

    <style>
        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .clickable-row:hover {
            background-color: var(--gray-100);
        }
        
        .clickable-row.selected {
            background-color: var(--primary-light);
            font-weight: 600;
        }
    </style>
</body>
</html>

