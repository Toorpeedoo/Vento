<?php
require_once 'Product.php';
require_once 'MongoDBConnection.php';

/**
 * MongoDB-based Product Database Utility
 * Each product is stored with username field for user isolation
 */
class ProductDatabaseUtil {
    private const COLLECTION = "products";
    
    /**
     * Get the current username from session
     */
    private static function getCurrentUsername() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
            return $_SESSION['username'];
        }
        
        if (function_exists('getCurrentUsername')) {
            return getCurrentUsername();
        }
        
        return null;
    }
    
    /**
     * Get all products for the current user
     */
    public static function getAllProducts($username = null) {
        if ($username === null) {
            $username = self::getCurrentUsername();
        }
        
        if ($username === null) {
            return [];
        }
        
        $documents = MongoDBConnection::find(self::COLLECTION, ['username' => $username]);
        $products = [];
        
        foreach ($documents as $doc) {
            $product = new Product(
                $doc->id,
                $doc->productName,
                $doc->price,
                $doc->quantity
            );
            $products[] = $product;
        }
        
        return $products;
    }
    
    /**
     * Add a new product
     */
    public static function addProduct($product) {
        if ($product === null) {
            return false;
        }
        
        $username = self::getCurrentUsername();
        if ($username === null) {
            return false;
        }
        
        // Check if product ID already exists for this user
        if (self::productExists($product->getId())) {
            return false;
        }
        
        $document = [
            'id' => $product->getId(),
            'productName' => $product->getName(),
            'price' => $product->getPrice(),
            'quantity' => $product->getQuantity(),
            'username' => $username,
            'createdAt' => new MongoDB\BSON\UTCDateTime()
        ];
        
        return MongoDBConnection::insertOne(self::COLLECTION, $document);
    }
    
    /**
     * Update an existing product
     */
    public static function updateProduct($product) {
        if ($product === null) {
            return false;
        }
        
        $username = self::getCurrentUsername();
        if ($username === null) {
            return false;
        }
        
        $filter = [
            'id' => $product->getId(),
            'username' => $username
        ];
        
        $update = [
            '$set' => [
                'productName' => $product->getName(),
                'price' => $product->getPrice(),
                'quantity' => $product->getQuantity(),
                'updatedAt' => new MongoDB\BSON\UTCDateTime()
            ]
        ];
        
        return MongoDBConnection::updateOne(self::COLLECTION, $filter, $update);
    }
    
    /**
     * Delete a product by ID
     */
    public static function deleteProduct($id) {
        $username = self::getCurrentUsername();
        if ($username === null) {
            return false;
        }
        
        $filter = [
            'id' => $id,
            'username' => $username
        ];
        
        return MongoDBConnection::deleteOne(self::COLLECTION, $filter);
    }
    
    /**
     * Get a product by ID
     */
    public static function getProduct($id) {
        $username = self::getCurrentUsername();
        if ($username === null) {
            return null;
        }
        
        $filter = [
            'id' => $id,
            'username' => $username
        ];
        
        $doc = MongoDBConnection::findOne(self::COLLECTION, $filter);
        
        if ($doc === null) {
            return null;
        }
        
        return new Product(
            $doc->id,
            $doc->productName,
            $doc->price,
            $doc->quantity
        );
    }
    
    /**
     * Check if a product ID exists for current user
     */
    public static function productExists($id) {
        return self::getProduct($id) !== null;
    }
    
    /**
     * Get all products sorted by ID
     */
    public static function getAllProductsSorted() {
        $username = self::getCurrentUsername();
        if ($username === null) {
            return [];
        }
        
        $documents = MongoDBConnection::find(
            self::COLLECTION, 
            ['username' => $username],
            ['sort' => ['id' => 1]]
        );
        
        $products = [];
        foreach ($documents as $doc) {
            $product = new Product(
                $doc->id,
                $doc->productName,
                $doc->price,
                $doc->quantity
            );
            $products[] = $product;
        }
        
        return $products;
    }
    
    /**
     * Add quantity to a product
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
     * Subtract quantity from a product
     */
    public static function subtractQuantity($id, $quantityToSubtract) {
        $product = self::getProduct($id);
        if ($product === null) {
            return false;
        }
        
        if ($product->getQuantity() < $quantityToSubtract) {
            return false;
        }
        
        $product->setQuantity($product->getQuantity() - $quantityToSubtract);
        return self::updateProduct($product);
    }
    
    /**
     * Get product count for a specific user
     */
    public static function getProductCount($username) {
        return MongoDBConnection::count(self::COLLECTION, ['username' => $username]);
    }
    
    /**
     * Delete all products for a specific user
     */
    public static function deleteUserProducts($username) {
        $deletedCount = MongoDBConnection::deleteMany(self::COLLECTION, ['username' => $username]);
        return $deletedCount >= 0; // Even 0 deletes is considered success
    }
}
