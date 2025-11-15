<?php
require_once 'Product.php';

/**
 * File-based database utility class
 * Stores products in a text file with format: ID|ProductName|Price|Quantity
 * Each user has their own product database file: data/products_{username}.txt
 */
class FileDatabaseUtil {
    private static $DB_FILE_TEMPLATE = "data/products_%s.txt";
    
    /**
     * Get the current username from session
     * @return string|null The username or null if not available
     */
    private static function getCurrentUsername() {
        // Check if session is started and username is set
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
            return $_SESSION['username'];
        }
        
        // Try to use getCurrentUsername function if auth.php is included
        if (function_exists('getCurrentUsername')) {
            return getCurrentUsername();
        }
        
        return null;
    }
    
    /**
     * Sanitize username for use in filename
     * @param string $username The username to sanitize
     * @return string The sanitized username
     */
    public static function sanitizeUsername($username) {
        // Remove any characters that could be problematic in filenames
        // Allow alphanumeric, underscore, hyphen, and dot
        $sanitized = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $username);
        // Limit length to prevent extremely long filenames
        return substr($sanitized, 0, 100);
    }
    
    /**
     * Get the database file path for a specific user
     * @param string|null $username The username (if null, uses current session username)
     * @return string The file path
     */
    private static function getDbFile($username = null) {
        if ($username === null) {
            $username = self::getCurrentUsername();
        }
        
        if ($username === null) {
            throw new Exception("Username is required to access product database.");
        }
        
        $sanitizedUsername = self::sanitizeUsername($username);
        $dbFile = sprintf(self::$DB_FILE_TEMPLATE, $sanitizedUsername);
        $file = __DIR__ . "/../" . $dbFile;
        $dir = dirname($file);
        
        // Ensure the data directory exists
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        return $file;
    }
    
    /**
     * Read all products from file for the current user
     * @param string|null $username Optional username (uses current session if not provided)
     */
    public static function getAllProducts($username = null) {
        $products = [];
        try {
            $file = self::getDbFile($username);
        } catch (Exception $e) {
            return $products; // Return empty array if username not available
        }
        
        if (!file_exists($file)) {
            return $products;
        }
        
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $product = Product::fromFileString($line);
            if ($product !== null) {
                $products[] = $product;
            }
        }
        
        return $products;
    }
    
    /**
     * Write all products to file for the current user
     * @param array $products The products to write
     * @param string|null $username Optional username (uses current session if not provided)
     */
    private static function writeAllProducts($products, $username = null) {
        try {
            $file = self::getDbFile($username);
        } catch (Exception $e) {
            return false; // Cannot write if username not available
        }
        
        $content = "";
        
        foreach ($products as $product) {
            $content .= $product->toFileString() . "\n";
        }
        
        return file_put_contents($file, $content) !== false;
    }
    
    /**
     * Add a new product for the current user
     */
    public static function addProduct($product) {
        if ($product === null) {
            return false;
        }
        
        try {
            $username = self::getCurrentUsername();
            $products = self::getAllProducts($username);
            
            // Check if ID already exists
            foreach ($products as $p) {
                if ($p->getId() == $product->getId()) {
                    return false; // ID already exists
                }
            }
            
            $products[] = $product;
            return self::writeAllProducts($products, $username);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Update an existing product for the current user
     */
    public static function updateProduct($product) {
        if ($product === null) {
            return false;
        }
        
        try {
            $username = self::getCurrentUsername();
            $products = self::getAllProducts($username);
            $found = false;
            
            for ($i = 0; $i < count($products); $i++) {
                if ($products[$i]->getId() == $product->getId()) {
                    $products[$i] = $product;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                return false;
            }
            
            return self::writeAllProducts($products, $username);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Delete a product by ID for the current user
     */
    public static function deleteProduct($id) {
        try {
            $username = self::getCurrentUsername();
            $products = self::getAllProducts($username);
            $filtered = array_filter($products, function($p) use ($id) {
                return $p->getId() != $id;
            });
            
            if (count($filtered) == count($products)) {
                return false; // Product not found
            }
            
            return self::writeAllProducts(array_values($filtered), $username);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get a product by ID for the current user
     */
    public static function getProduct($id) {
        try {
            $username = self::getCurrentUsername();
            $products = self::getAllProducts($username);
            foreach ($products as $product) {
                if ($product->getId() == $id) {
                    return $product;
                }
            }
        } catch (Exception $e) {
            // Return null if username not available
        }
        return null;
    }
    
    /**
     * Check if a product ID exists for the current user
     */
    public static function productExists($id) {
        return self::getProduct($id) !== null;
    }
    
    /**
     * Get all products sorted by ID for the current user
     */
    public static function getAllProductsSorted() {
        try {
            $username = self::getCurrentUsername();
            $products = self::getAllProducts($username);
            usort($products, function($a, $b) {
                return $a->getId() - $b->getId();
            });
            return $products;
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Update product quantity (add) for the current user
     */
    public static function addQuantity($id, $quantityToAdd) {
        $product = self::getProduct($id);
        if ($product === null) {
            return false;
        }
        
        $product->setQuantity($product->getQuantity() + $quantityToAdd);
        return self::updateProduct($product);
    }
    
    /**
     * Update product quantity (subtract) for the current user
     */
    public static function subtractQuantity($id, $quantityToSubtract) {
        $product = self::getProduct($id);
        if ($product === null) {
            return false;
        }
        
        if ($product->getQuantity() < $quantityToSubtract) {
            return false; // Insufficient quantity
        }
        
        $product->setQuantity($product->getQuantity() - $quantityToSubtract);
        return self::updateProduct($product);
    }
    
    /**
     * Get product count for a specific user without loading all products
     * Optimized for performance - just counts lines instead of parsing all products
     * @param string $username The username to count products for
     * @return int The number of products
     */
    public static function getProductCount($username) {
        try {
            $file = self::getDbFile($username);
        } catch (Exception $e) {
            return 0;
        }
        
        if (!file_exists($file)) {
            return 0;
        }
        
        // Count non-empty lines without parsing products
        $count = 0;
        $handle = fopen($file, 'r');
        
        if ($handle === false) {
            return 0;
        }
        
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (!empty($line)) {
                $count++;
            }
        }
        
        fclose($handle);
        return $count;
    }
    
    /**
     * Delete all products for a specific user (delete user's product database file)
     * @param string $username The username whose products should be deleted
     * @return bool True if successful, false otherwise
     */
    public static function deleteUserProducts($username) {
        try {
            $sanitizedUsername = self::sanitizeUsername($username);
            $dbFile = sprintf(self::$DB_FILE_TEMPLATE, $sanitizedUsername);
            $file = __DIR__ . "/../" . $dbFile;
            
            // Check if file exists
            if (file_exists($file)) {
                // Delete the file
                return unlink($file);
            }
            
            // File doesn't exist, consider it successful (nothing to delete)
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

