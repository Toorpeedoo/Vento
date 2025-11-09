<?php
/**
 * User class for account management
 * Uses private properties with getters and setters
 */
class User {
    private $username;
    private $password; // Stored as hash (for authentication)
    private $plainPassword; // Stored as plain text (for admin viewing)
    private $createdAt;
    private $isAdmin;
    
    /**
     * Default constructor
     */
    public function __construct($username = "", $password = "", $createdAt = null, $isHashed = false, $isAdmin = false) {
        $this->setUsername($username);
        if (!empty($password)) {
            $this->setPassword($password, $isHashed);
        }
        $this->setCreatedAt($createdAt ? $createdAt : date('Y-m-d H:i:s'));
        $this->setIsAdmin($isAdmin);
    }
    
    // Getters
    public function getUsername() {
        return $this->username;
    }
    
    public function getPassword() {
        return $this->password;
    }
    
    public function getCreatedAt() {
        return $this->createdAt;
    }
    
    public function getIsAdmin() {
        return $this->isAdmin;
    }
    
    /**
     * Get plain text password (for admin viewing)
     */
    public function getPlainPassword() {
        return $this->plainPassword;
    }
    
    // Setters
    public function setUsername($username) {
        if (empty(trim($username))) {
            throw new InvalidArgumentException("Username cannot be empty");
        }
        $this->username = trim($username);
    }
    
    public function setPassword($password, $isHashed = false) {
        if (empty(trim($password))) {
            throw new InvalidArgumentException("Password cannot be empty");
        }
        $password = trim($password);
        // Store plain text password for admin viewing
        $this->plainPassword = $password;
        // Also create hash for authentication
        if (!$isHashed) {
            $this->password = password_hash($password, PASSWORD_DEFAULT);
        } else {
            $this->password = $password;
            // If it's a hash, we can't retrieve plain text, so set to null
            $this->plainPassword = null;
        }
    }
    
    /**
     * Set plain text password directly (updates both plain text and hash)
     */
    public function setPlainPassword($password) {
        $this->plainPassword = $password;
        // Also update the hash for authentication
        if (!empty($password)) {
            $this->password = password_hash($password, PASSWORD_DEFAULT);
        }
    }
    
    /**
     * Set password hash directly (for loading from file)
     */
    public function setPasswordHash($passwordHash) {
        $this->password = trim($passwordHash);
        // If setting a hash, we don't have the plain text
        $this->plainPassword = null;
    }
    
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }
    
    public function setIsAdmin($isAdmin) {
        $this->isAdmin = (bool)$isAdmin;
    }
    
    /**
     * Verify password against hash or plain text
     */
    public function verifyPassword($password) {
        if (empty($this->password)) {
            return false;
        }
        // If we have plain text password stored, compare directly
        if ($this->plainPassword !== null) {
            return $this->plainPassword === $password;
        }
        // Otherwise, verify against hash
        return password_verify($password, $this->password);
    }
    
    /**
     * Converts user to file string format
     * Format: username|password_plaintext|created_at|isAdmin
     * Note: Passwords are stored in plain text for admin visibility
     */
    public function toFileString() {
        $isAdminStr = $this->isAdmin ? '1' : '0';
        // Store plain text password if available, otherwise use stored password (for backward compatibility)
        $passwordToStore = $this->plainPassword !== null ? $this->plainPassword : $this->password;
        return $this->username . "|" . $passwordToStore . "|" . $this->createdAt . "|" . $isAdminStr;
    }
    
    /**
     * Creates a User from a file string
     * Supports both old format (3 parts) and new format (4 parts) for backward compatibility
     * Format: username|password|created_at|isAdmin
     */
    public static function fromFileString($line) {
        if (empty(trim($line))) {
            return null;
        }
        
        $parts = explode("|", trim($line));
        // Support both old format (3 parts) and new format (4 parts)
        if (count($parts) < 3 || count($parts) > 4) {
            return null;
        }
        
        try {
            $username = trim($parts[0]);
            $password = trim($parts[1]);
            $createdAt = trim($parts[2]);
            $isAdmin = false; // Default to false
            
            // If 4 parts, the last one is the admin flag
            if (count($parts) == 4) {
                $isAdmin = trim($parts[3]) === '1' || strtolower(trim($parts[3])) === 'true';
            }
            
            $user = new User($username, "", $createdAt, false, $isAdmin);
            // Check if password looks like a hash (starts with $2y$) or is plain text
            if (strpos($password, '$2y$') === 0) {
                // It's a hash (old format), store as hash and set plain password to null
                $user->setPasswordHash($password);
                $user->plainPassword = null;
            } else {
                // It's plain text (new format), store as plain text
                $user->setPlainPassword($password);
            }
            return $user;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Convert to array for JSON/display (without password)
     */
    public function toArray() {
        return [
            'username' => $this->username,
            'createdAt' => $this->createdAt,
            'isAdmin' => $this->isAdmin
        ];
    }
}

