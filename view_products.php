<?php
session_start();
require_once 'auth.php';
requireLogin();
require_once 'classes/ProductDatabaseUtil.php';

$products = ProductDatabaseUtil::getAllProductsSorted();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products - VENTO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            <h1 class="nav-title">View Products</h1>
            <div class="nav-actions">
                <a href="main_menu.php" class="btn btn-secondary btn-small">Back to Menu</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">All Products</h2>
            </div>

            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    No products found. <a href="add_product.php">Add your first product</a>
                </div>
            <?php else: ?>
                <div class="search-container">
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

                <div class="table-container">
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
                                <tr>
                                    <td><?php echo htmlspecialchars($product->getId()); ?></td>
                                    <td><?php echo htmlspecialchars($product->getProductName()); ?></td>
                                    <td>₱<?php echo number_format($product->getPrice(), 2); ?></td>
                                    <td><?php echo htmlspecialchars($product->getQuantity()); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const allProducts = <?php echo json_encode(array_map(function($p) { 
            return [
                'id' => $p->getId(),
                'name' => $p->getProductName(),
                'price' => $p->getPrice(),
                'quantity' => $p->getQuantity()
            ];
        }, $products)); ?>;
        
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
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>

