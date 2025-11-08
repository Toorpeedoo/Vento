<?php
session_start();
require_once 'classes/FileDatabaseUtil.php';

$products = FileDatabaseUtil::getAllProductsSorted();
$message = "";
$messageType = "";
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'update_info') {
        // Update product information
        $id = isset($_POST['id']) ? trim($_POST['id']) : '';
        $productName = isset($_POST['productName']) ? trim($_POST['productName']) : '';
        $price = isset($_POST['price']) ? trim($_POST['price']) : '';
        $quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';
        
        if (empty($id) || empty($productName) || empty($price) || empty($quantity)) {
            $message = "All fields must be filled out.";
            $messageType = "error";
            $activeTab = 'info';
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
                
                if (!FileDatabaseUtil::productExists($id)) {
                    $message = "This ID does not exist.";
                    $messageType = "error";
                    $activeTab = 'info';
                } else {
                    $product = new Product($id, $productName, $price, $quantity);
                    if (FileDatabaseUtil::updateProduct($product)) {
                        $message = "Product updated successfully!";
                        $messageType = "success";
                        $activeTab = 'info';
                        $_POST = array();
                        $products = FileDatabaseUtil::getAllProductsSorted(); // Refresh list
                    } else {
                        $message = "Error: Failed to update product.";
                        $messageType = "error";
                        $activeTab = 'info';
                    }
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                $messageType = "error";
                $activeTab = 'info';
            }
        }
    } elseif ($action === 'add_quantity') {
        // Add quantity
        $id = isset($_POST['id']) ? trim($_POST['id']) : '';
        $quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';
        
        if (empty($id) || empty($quantity)) {
            $message = "All fields must be filled out.";
            $messageType = "error";
            $activeTab = 'add';
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
                
                if (FileDatabaseUtil::addQuantity($id, $quantity)) {
                    $message = "Quantity added successfully!";
                    $messageType = "success";
                    $activeTab = 'add';
                    $_POST = array();
                    $products = FileDatabaseUtil::getAllProductsSorted(); // Refresh list
                } else {
                    $message = "Product not found.";
                    $messageType = "error";
                    $activeTab = 'add';
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                $messageType = "error";
                $activeTab = 'add';
            }
        }
    } elseif ($action === 'subtract_quantity') {
        // Subtract quantity
        $id = isset($_POST['id']) ? trim($_POST['id']) : '';
        $quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';
        
        if (empty($id) || empty($quantity)) {
            $message = "All fields must be filled out.";
            $messageType = "error";
            $activeTab = 'subtract';
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
                
                if (FileDatabaseUtil::subtractQuantity($id, $quantity)) {
                    $message = "Quantity subtracted successfully!";
                    $messageType = "success";
                    $activeTab = 'subtract';
                    $_POST = array();
                    $products = FileDatabaseUtil::getAllProductsSorted(); // Refresh list
                } else {
                    $product = FileDatabaseUtil::getProduct($id);
                    if ($product === null) {
                        $message = "Product not found.";
                    } else {
                        $message = "Insufficient quantity in stock. Current quantity: " . $product->getQuantity();
                    }
                    $messageType = "error";
                    $activeTab = 'subtract';
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                $messageType = "error";
                $activeTab = 'subtract';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product - VENTO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            <h1 class="nav-title">Update Product</h1>
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

            <?php if (!empty($products)): ?>
                <div class="products-preview">
                    <div class="preview-header">
                        <h3 class="preview-title">Existing Products (Click to fill form)</h3>
                        <div class="search-box-small">
                            <input type="text" id="searchInputUpdate" class="search-input-small" placeholder="Search products..." onkeyup="filterProductsUpdate()">
                            <button type="button" class="search-btn-small" onclick="filterProductsUpdate()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="products-scroll">
                        <table class="preview-table" id="previewTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                                <?php foreach ($products as $product): ?>
                                    <tr onclick="fillForm(<?php echo $product->getId(); ?>, '<?php echo htmlspecialchars($product->getProductName(), ENT_QUOTES); ?>', <?php echo $product->getPrice(); ?>, <?php echo $product->getQuantity(); ?>);">
                                        <td><?php echo htmlspecialchars($product->getId()); ?></td>
                                        <td><?php echo htmlspecialchars($product->getProductName()); ?></td>
                                        <td>₱<?php echo number_format($product->getPrice(), 2); ?></td>
                                        <td><?php echo htmlspecialchars($product->getQuantity()); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn <?php echo $activeTab === 'info' ? 'active' : ''; ?>" onclick="switchTab('info')">
                    Update Information
                </button>
                <button class="tab-btn <?php echo $activeTab === 'add' ? 'active' : ''; ?>" onclick="switchTab('add')">
                    Add Quantity
                </button>
                <button class="tab-btn <?php echo $activeTab === 'subtract' ? 'active' : ''; ?>" onclick="switchTab('subtract')">
                    Subtract Quantity
                </button>
            </div>

            <!-- Tab Content: Update Information -->
            <div id="tab-info" class="tab-content <?php echo $activeTab === 'info' ? 'active' : ''; ?>">
                <form method="POST" action="" id="updateForm">
                    <input type="hidden" name="action" value="update_info">
                    
                    <div class="form-group">
                        <label class="form-label" for="id">Product ID to Update</label>
                        <input type="number" class="form-input" id="id" name="id" 
                               value="<?php echo isset($_POST['id']) && $_POST['action'] === 'update_info' ? htmlspecialchars($_POST['id']) : ''; ?>" 
                               required min="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="productName">New Product Name</label>
                        <input type="text" class="form-input" id="productName" name="productName" 
                               value="<?php echo isset($_POST['productName']) && $_POST['action'] === 'update_info' ? htmlspecialchars($_POST['productName']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="price">New Price</label>
                        <input type="number" step="0.01" class="form-input" id="price" name="price" 
                               value="<?php echo isset($_POST['price']) && $_POST['action'] === 'update_info' ? htmlspecialchars($_POST['price']) : ''; ?>" 
                               required min="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="quantity">New Quantity</label>
                        <input type="number" class="form-input" id="quantity" name="quantity" 
                               value="<?php echo isset($_POST['quantity']) && $_POST['action'] === 'update_info' ? htmlspecialchars($_POST['quantity']) : ''; ?>" 
                               required min="0">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Product</button>
                        <button type="reset" class="btn btn-secondary">Clear</button>
                    </div>
                </form>
            </div>

            <!-- Tab Content: Add Quantity -->
            <div id="tab-add" class="tab-content <?php echo $activeTab === 'add' ? 'active' : ''; ?>">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_quantity">
                    
                    <div class="form-group">
                        <label class="form-label" for="add_id">Product ID</label>
                        <input type="number" class="form-input" id="add_id" name="id" 
                               value="<?php echo isset($_POST['id']) && $_POST['action'] === 'add_quantity' ? htmlspecialchars($_POST['id']) : ''; ?>" 
                               required min="0" placeholder="Enter product ID">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="add_quantity">Quantity to Add</label>
                        <input type="number" class="form-input" id="add_quantity" name="quantity" 
                               value="<?php echo isset($_POST['quantity']) && $_POST['action'] === 'add_quantity' ? htmlspecialchars($_POST['quantity']) : ''; ?>" 
                               required min="0" placeholder="Enter quantity">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary">Add Quantity</button>
                        <button type="reset" class="btn btn-secondary">Clear</button>
                    </div>
                </form>
            </div>

            <!-- Tab Content: Subtract Quantity -->
            <div id="tab-subtract" class="tab-content <?php echo $activeTab === 'subtract' ? 'active' : ''; ?>">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="subtract_quantity">
                    
                    <div class="form-group">
                        <label class="form-label" for="sub_id">Product ID</label>
                        <input type="number" class="form-input" id="sub_id" name="id" 
                               value="<?php echo isset($_POST['id']) && $_POST['action'] === 'subtract_quantity' ? htmlspecialchars($_POST['id']) : ''; ?>" 
                               required min="0" placeholder="Enter product ID">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sub_quantity">Quantity to Subtract</label>
                        <input type="number" class="form-input" id="sub_quantity" name="quantity" 
                               value="<?php echo isset($_POST['quantity']) && $_POST['action'] === 'subtract_quantity' ? htmlspecialchars($_POST['quantity']) : ''; ?>" 
                               required min="0" placeholder="Enter quantity">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary">Subtract Quantity</button>
                        <button type="reset" class="btn btn-secondary">Clear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const allProductsUpdate = <?php echo json_encode(array_map(function($p) { 
            return [
                'id' => $p->getId(),
                'name' => $p->getProductName(),
                'price' => $p->getPrice(),
                'quantity' => $p->getQuantity()
            ];
        }, $products)); ?>;
        
        function filterProductsUpdate() {
            const searchTerm = document.getElementById('searchInputUpdate').value.toLowerCase();
            const tbody = document.getElementById('previewTableBody');
            
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            allProductsUpdate.forEach(product => {
                const id = product.id.toString();
                const name = product.name.toLowerCase();
                const price = product.price.toString();
                const quantity = product.quantity.toString();
                
                if (id.includes(searchTerm) || 
                    name.includes(searchTerm) || 
                    price.includes(searchTerm) || 
                    quantity.includes(searchTerm)) {
                    
                    const row = tbody.insertRow();
                    row.onclick = function() {
                        fillForm(product.id, product.name, product.price, product.quantity);
                    };
                    row.innerHTML = `
                        <td>${product.id}</td>
                        <td>${escapeHtml(product.name)}</td>
                        <td>₱${parseFloat(product.price).toFixed(2)}</td>
                        <td>${product.quantity}</td>
                    `;
                }
            });
            
            if (tbody.children.length === 0 && searchTerm !== '') {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 1rem; color: var(--gray-500); font-size: 0.9rem;">No products found</td></tr>';
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
            
            // Update URL without reload
            window.history.pushState({}, '', '?tab=' + tabName);
        }
        
        function fillForm(id, name, price, quantity) {
            // Fill the update info form
            document.getElementById('id').value = id;
            document.getElementById('productName').value = name;
            document.getElementById('price').value = price;
            document.getElementById('quantity').value = quantity;
            
            // Also fill add/subtract forms
            document.getElementById('add_id').value = id;
            document.getElementById('sub_id').value = id;
            
            // Switch to info tab
            switchTab('info');
            document.querySelectorAll('.tab-btn')[0].classList.add('active');
            
            // Scroll to form
            document.getElementById('updateForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    </script>
</body>
</html>
