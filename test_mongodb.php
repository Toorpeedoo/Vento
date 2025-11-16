<?php
/**
 * MongoDB Connection Test Script
 * Run this file to verify MongoDB connection is working
 */

echo "<h1>MongoDB Connection Test</h1>";
echo "<pre>";

// Test 1: Check if MongoDB extension is loaded
echo "\n=== Test 1: MongoDB Extension Check ===\n";
if (extension_loaded('mongodb')) {
    echo "✓ MongoDB extension is loaded\n";
} else {
    echo "✗ MongoDB extension is NOT loaded\n";
    echo "  Please install the MongoDB extension for PHP\n";
    echo "  Visit: https://www.php.net/manual/en/mongodb.installation.php\n";
}

// Test 2: Try to connect to MongoDB
echo "\n=== Test 2: MongoDB Connection ===\n";
try {
    require_once 'classes/MongoDBConnection.php';
    
    $db = MongoDBConnection::getDatabase();
    echo "✓ Successfully connected to MongoDB!\n";
    echo "  Database: " . MongoDBConnection::getDatabaseName() . "\n";
    
} catch (Exception $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
}

// Test 3: Try a simple operation
echo "\n=== Test 3: Database Operations ===\n";
try {
    // Try to count documents in users collection
    $count = MongoDBConnection::count('users');
    echo "✓ Successfully queried database\n";
    echo "  Users collection has {$count} document(s)\n";
    
    $productCount = MongoDBConnection::count('products');
    echo "  Products collection has {$productCount} document(s)\n";
    
} catch (Exception $e) {
    echo "✗ Query failed: " . $e->getMessage() . "\n";
}

// Test 4: PHP Version
echo "\n=== Test 4: PHP Information ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "MongoDB Driver Version: ";
if (extension_loaded('mongodb')) {
    echo phpversion('mongodb') . "\n";
} else {
    echo "Not installed\n";
}

echo "\n=== Summary ===\n";
if (extension_loaded('mongodb')) {
    echo "✓ Your system is ready to use MongoDB!\n";
} else {
    echo "✗ MongoDB extension needs to be installed\n";
    echo "\nInstallation Instructions:\n";
    echo "1. Download the MongoDB extension DLL from: https://pecl.php.net/package/mongodb\n";
    echo "2. Copy php_mongodb.dll to your PHP ext/ folder (C:\\PHP\\ext\\)\n";
    echo "3. Add 'extension=mongodb' to your php.ini file (C:\\PHP\\php.ini)\n";
    echo "4. Restart your PHP server\n";
}

echo "</pre>";
?>
