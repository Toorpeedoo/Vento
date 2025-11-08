<?php
require_once 'Product.php';

/**
 * File-based database utility class
 * Stores products in a text file with format: ID|ProductName|Price|Quantity
 */
class FileDatabaseUtil {
    private static $DB_FILE = "data/products.txt";
    
    /**
     * Get the database file path
     */
    private static function getDbFile() {
        $file = __DIR__ . "/../" . self::$DB_FILE;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $file;
    }
    
    /**
     * Read all products from file
     */
    public static function getAllProducts() {
        $products = [];
        $file = self::getDbFile();
        
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
     * Write all products to file
     */
    private static function writeAllProducts($products) {
        $file = self::getDbFile();
        $content = "";
        
        foreach ($products as $product) {
            $content .= $product->toFileString() . "\n";
        }
        
        return file_put_contents($file, $content) !== false;
    }
    
    /**
     * Add a new product
     */
    public static function addProduct($product) {
        if ($product === null) {
            return false;
        }
        
        $products = self::getAllProducts();
        
        // Check if ID already exists
        foreach ($products as $p) {
            if ($p->getId() == $product->getId()) {
                return false; // ID already exists
            }
        }
        
        $products[] = $product;
        return self::writeAllProducts($products);
    }
    
    /**
     * Update an existing product
     */
    public static function updateProduct($product) {
        if ($product === null) {
            return false;
        }
        
        $products = self::getAllProducts();
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
        
        return self::writeAllProducts($products);
    }
    
    /**
     * Delete a product by ID
     */
    public static function deleteProduct($id) {
        $products = self::getAllProducts();
        $filtered = array_filter($products, function($p) use ($id) {
            return $p->getId() != $id;
        });
        
        if (count($filtered) == count($products)) {
            return false; // Product not found
        }
        
        return self::writeAllProducts(array_values($filtered));
    }
    
    /**
     * Get a product by ID
     */
    public static function getProduct($id) {
        $products = self::getAllProducts();
        foreach ($products as $product) {
            if ($product->getId() == $id) {
                return $product;
            }
        }
        return null;
    }
    
    /**
     * Check if a product ID exists
     */
    public static function productExists($id) {
        return self::getProduct($id) !== null;
    }
    
    /**
     * Get all products sorted by ID
     */
    public static function getAllProductsSorted() {
        $products = self::getAllProducts();
        usort($products, function($a, $b) {
            return $a->getId() - $b->getId();
        });
        return $products;
    }
    
    /**
     * Update product quantity (add)
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
     * Update product quantity (subtract)
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
}

