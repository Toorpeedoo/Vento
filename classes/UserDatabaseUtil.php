<?php
require_once 'User.php';

/**
 * File-based user database utility class
 * Stores users in a text file with format: username|password_hash|created_at|isAdmin
 */
class UserDatabaseUtil {
    private static $DB_FILE = "data/accounts.txt";
    
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
     * Read all users from file
     */
    public static function getAllUsers() {
        $users = [];
        $file = self::getDbFile();
        
        if (!file_exists($file)) {
            return $users;
        }
        
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $user = User::fromFileString($line);
            if ($user !== null) {
                $users[] = $user;
            }
        }
        
        return $users;
    }
    
    /**
     * Write all users to file
     */
    private static function writeAllUsers($users) {
        $file = self::getDbFile();
        $content = "";
        
        foreach ($users as $user) {
            $content .= $user->toFileString() . "\n";
        }
        
        return file_put_contents($file, $content) !== false;
    }
    
    /**
     * Add a new user
     */
    public static function addUser($user) {
        if ($user === null) {
            return false;
        }
        
        $users = self::getAllUsers();
        
        // Check if username already exists (case-insensitive)
        $newUsernameLower = strtolower($user->getUsername());
        foreach ($users as $u) {
            if (strtolower($u->getUsername()) === $newUsernameLower) {
                return false; // Username already exists
            }
        }
        
        $users[] = $user;
        return self::writeAllUsers($users);
    }
    
    /**
     * Get a user by username (case-insensitive)
     */
    public static function getUser($username) {
        $users = self::getAllUsers();
        $usernameLower = strtolower(trim($username));
        foreach ($users as $user) {
            if (strtolower($user->getUsername()) === $usernameLower) {
                return $user;
            }
        }
        return null;
    }
    
    /**
     * Check if a username exists
     */
    public static function usernameExists($username) {
        return self::getUser($username) !== null;
    }
    
    /**
     * Verify user credentials
     */
    public static function verifyUser($username, $password) {
        $user = self::getUser($username);
        if ($user === null) {
            return false;
        }
        
        return $user->verifyPassword($password);
    }
    
    /**
     * Update an existing user
     * @param string $oldUsername The username of the user to update
     * @param User $updatedUser The updated user object
     * @return bool True if successful, false otherwise
     */
    public static function updateUser($oldUsername, $updatedUser) {
        if ($updatedUser === null) {
            return false;
        }
        
        $users = self::getAllUsers();
        $oldUsernameLower = strtolower(trim($oldUsername));
        $found = false;
        
        for ($i = 0; $i < count($users); $i++) {
            if (strtolower($users[$i]->getUsername()) === $oldUsernameLower) {
                // If username is being changed, check if new username already exists
                $newUsernameLower = strtolower($updatedUser->getUsername());
                if ($oldUsernameLower !== $newUsernameLower) {
                    // Check if new username already exists
                    foreach ($users as $u) {
                        if (strtolower($u->getUsername()) === $newUsernameLower) {
                            return false; // New username already exists
                        }
                    }
                }
                $users[$i] = $updatedUser;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            return false;
        }
        
        return self::writeAllUsers($users);
    }
    
    /**
     * Delete a user by username and their associated product data
     * @param string $username The username to delete
     * @return bool True if successful, false otherwise
     */
    public static function deleteUser($username) {
        $users = self::getAllUsers();
        $usernameLower = strtolower(trim($username));
        $filtered = array_filter($users, function($user) use ($usernameLower) {
            return strtolower($user->getUsername()) !== $usernameLower;
        });
        
        if (count($filtered) == count($users)) {
            return false; // User not found
        }
        
        // Delete user's product database file
        require_once __DIR__ . '/FileDatabaseUtil.php';
        FileDatabaseUtil::deleteUserProducts($username);
        
        // Delete the user account
        return self::writeAllUsers(array_values($filtered));
    }
    
    /**
     * Get all non-admin users
     * @return array Array of User objects that are not admins
     */
    public static function getAllRegularUsers() {
        $allUsers = self::getAllUsers();
        return array_filter($allUsers, function($user) {
            return !$user->getIsAdmin();
        });
    }
}

