<?php
require_once 'User.php';
require_once 'MongoDBConnection.php';

/**
 * MongoDB-based user database utility class
 */
class UserDatabaseUtil {
    private const COLLECTION = "users";
    
    /**
     * Read all users from MongoDB
     */
    public static function getAllUsers() {
        $documents = MongoDBConnection::find(self::COLLECTION);
        $users = [];
        
        foreach ($documents as $doc) {
            // Try to get plain text password, fall back to passwordHash for backward compatibility
            $password = $doc->password ?? $doc->passwordHash ?? '';
            $user = new User(
                $doc->username,
                $password,
                $doc->createdAt ?? date('Y-m-d H:i:s'),
                false, // Store as plain text, not hashed
                $doc->isAdmin ?? false
            );
            $users[] = $user;
        }
        
        return $users;
    }
    
    /**
     * Add a new user
     */
    public static function addUser($user) {
        if ($user === null) {
            return false;
        }
        
        // Check if username already exists (case-insensitive)
        if (self::usernameExists($user->getUsername())) {
            return false;
        }
        
        $document = [
            'username' => $user->getUsername(),
            'password' => $user->getPlainPassword() ?: $user->getPasswordHash(), // Store plain text password
            'passwordHash' => $user->getPlainPassword() ?: $user->getPasswordHash(), // Keep for backward compatibility
            'createdAt' => $user->getCreatedAt(),
            'isAdmin' => $user->getIsAdmin(),
            'createdAtTimestamp' => new MongoDB\BSON\UTCDateTime()
        ];
        
        return MongoDBConnection::insertOne(self::COLLECTION, $document);
    }
    
    /**
     * Get a user by username (case-insensitive)
     */
    public static function getUser($username) {
        $filter = [
            'username' => new MongoDB\BSON\Regex('^' . preg_quote(trim($username), '/') . '$', 'i')
        ];
        
        $doc = MongoDBConnection::findOne(self::COLLECTION, $filter);
        
        if ($doc === null) {
            return null;
        }
        
        // Try to get plain text password, fall back to passwordHash for backward compatibility
        $password = $doc->password ?? $doc->passwordHash ?? '';
        return new User(
            $doc->username,
            $password,
            $doc->createdAt ?? date('Y-m-d H:i:s'),
            false, // Store as plain text, not hashed
            $doc->isAdmin ?? false
        );
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
     */
    public static function updateUser($oldUsername, $updatedUser) {
        if ($updatedUser === null) {
            return false;
        }
        
        // If username is being changed, check if new username already exists
        if (strtolower(trim($oldUsername)) !== strtolower(trim($updatedUser->getUsername()))) {
            if (self::usernameExists($updatedUser->getUsername())) {
                return false;
            }
        }
        
        $filter = [
            'username' => new MongoDB\BSON\Regex('^' . preg_quote(trim($oldUsername), '/') . '$', 'i')
        ];
        
        $plainPassword = $updatedUser->getPlainPassword() ?: $updatedUser->getPasswordHash();
        $update = [
            '$set' => [
                'username' => $updatedUser->getUsername(),
                'password' => $plainPassword, // Store plain text password
                'passwordHash' => $plainPassword, // Keep for backward compatibility
                'createdAt' => $updatedUser->getCreatedAt(),
                'isAdmin' => $updatedUser->getIsAdmin(),
                'updatedAt' => new MongoDB\BSON\UTCDateTime()
            ]
        ];
        
        return MongoDBConnection::updateOne(self::COLLECTION, $filter, $update);
    }
    
    /**
     * Delete a user by username and their associated product data
     */
    public static function deleteUser($username) {
        // Delete user's products
        require_once __DIR__ . '/ProductDatabaseUtil.php';
        ProductDatabaseUtil::deleteUserProducts($username);
        
        // Delete the user account
        $filter = [
            'username' => new MongoDB\BSON\Regex('^' . preg_quote(trim($username), '/') . '$', 'i')
        ];
        
        return MongoDBConnection::deleteOne(self::COLLECTION, $filter);
    }
    
    /**
     * Get all non-admin users
     */
    public static function getAllRegularUsers() {
        $allUsers = self::getAllUsers();
        return array_filter($allUsers, function($user) {
            return !$user->getIsAdmin();
        });
    }
}

