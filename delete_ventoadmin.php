<?php
/**
 * Delete User Script
 * Deletes the VentoAdmin user so it can be recreated
 */

require_once 'classes/UserDatabaseUtil.php';

echo "Deleting VentoAdmin user...\n";

try {
    if (UserDatabaseUtil::deleteUser("VentoAdmin")) {
        echo "✓ User deleted successfully!\n";
    } else {
        echo "User not found or could not be deleted.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
