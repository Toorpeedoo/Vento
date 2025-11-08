<?php
/**
 * Product class with proper OOP encapsulation
 * Uses private properties with getters and setters
 */
class Product {
    private $id;
    private $productName;
    private $price;
    private $quantity;
    
    /**
     * Default constructor
     */
    public function __construct($id = 0, $productName = "", $price = 0.0, $quantity = 0) {
        $this->setId($id);
        $this->setProductName($productName);
        $this->setPrice($price);
        $this->setQuantity($quantity);
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getProductName() {
        return $this->productName;
    }
    
    public function getPrice() {
        return $this->price;
    }
    
    public function getQuantity() {
        return $this->quantity;
    }
    
    // Setters
    public function setId($id) {
        if (!is_numeric($id) || $id < 0) {
            throw new InvalidArgumentException("ID must be a non-negative number");
        }
        $this->id = (int)$id;
    }
    
    public function setProductName($productName) {
        if (empty(trim($productName))) {
            throw new InvalidArgumentException("Product name cannot be empty");
        }
        $this->productName = trim($productName);
    }
    
    public function setPrice($price) {
        if (!is_numeric($price) || $price < 0) {
            throw new InvalidArgumentException("Price must be a non-negative number");
        }
        $this->price = (float)$price;
    }
    
    public function setQuantity($quantity) {
        if (!is_numeric($quantity) || $quantity < 0) {
            throw new InvalidArgumentException("Quantity must be a non-negative number");
        }
        $this->quantity = (int)$quantity;
    }
    
    /**
     * Converts product to file string format
     */
    public function toFileString() {
        return $this->id . "|" . $this->productName . "|" . $this->price . "|" . $this->quantity;
    }
    
    /**
     * Creates a Product from a file string
     */
    public static function fromFileString($line) {
        if (empty(trim($line))) {
            return null;
        }
        
        $parts = explode("|", trim($line));
        if (count($parts) != 4) {
            return null;
        }
        
        try {
            $id = (int)trim($parts[0]);
            $name = trim($parts[1]);
            $price = (float)trim($parts[2]);
            $quantity = (int)trim($parts[3]);
            
            return new Product($id, $name, $price, $quantity);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Convert to array for JSON/display
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'productName' => $this->productName,
            'price' => $this->price,
            'quantity' => $this->quantity
        ];
    }
}

