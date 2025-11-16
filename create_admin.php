<?php
/**
 * Create Admin User Script
 * This script creates an admin user with username: VentoAdmin and password: Vento2025
 */

require_once 'classes/User.php';
require_once 'classes/UserDatabaseUtil.php';

echo "Creating admin user...\n\n";

$username = "VentoAdmin";
$password = "Vento2025";

try {
    // Check if user already exists
    if (UserDatabaseUtil::usernameExists($username)) {
        echo "Error: User '$username' already exists!\n";
        echo "If you want to recreate this user, please delete it first.\n";
        exit(1);
    }
    
    // Create new admin user
    // User($username, $password, $createdAt, $isHashed, $isAdmin)
    $admin = new User($username, $password, date('Y-m-d H:i:s'), false, true);
    
    if (UserDatabaseUtil::addUser($admin)) {
        echo "✓ Admin user created successfully!\n\n";
        echo "Login Credentials:\n";
        echo "  Username: $username\n";
        echo "  Password: $password\n";
        echo "  Role: Admin\n\n";
        echo "You can now login at: http://localhost:8000/login.php\n";
    } else {
        echo "✗ Failed to create admin user.\n";
        echo "Please check your MongoDB connection.\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
