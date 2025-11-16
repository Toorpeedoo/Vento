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
     * Get password hash (for database storage)
     */
    public function getPasswordHash() {
        return $this->password;
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
        // Store plain text password (for admin viewing and database storage)
        $this->plainPassword = $password;
        $this->password = $password; // Store as plain text, not hashed
    }
    
    /**
     * Set plain text password directly
     */
    public function setPlainPassword($password) {
        $this->plainPassword = $password;
        $this->password = $password; // Store as plain text
    }
    
    /**
     * Set password hash directly (for backward compatibility - but we store plain text now)
     */
    public function setPasswordHash($passwordHash) {
        // Check if it's a hash (starts with $2y$) or plain text
        if (strpos($passwordHash, '$2y$') === 0 || strpos($passwordHash, '$2a$') === 0 || strpos($passwordHash, '$2b$') === 0) {
            // It's a hash, but we can't convert it back to plain text
            // Store it as-is (for backward compatibility with old data)
            $this->password = trim($passwordHash);
            $this->plainPassword = null;
        } else {
            // It's plain text
            $this->password = trim($passwordHash);
            $this->plainPassword = trim($passwordHash);
        }
    }
    
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }
    
    public function setIsAdmin($isAdmin) {
        $this->isAdmin = (bool)$isAdmin;
    }
    
    /**
     * Verify password against plain text or hash (for backward compatibility)
     */
    public function verifyPassword($password) {
        if (empty($this->password)) {
            return false;
        }
        $password = trim($password);
        
        // If we have plain text password stored, compare directly
        if ($this->plainPassword !== null) {
            return $this->plainPassword === $password;
        }
        
        // If password looks like a hash, verify against hash (for backward compatibility)
        if (strpos($this->password, '$2y$') === 0 || strpos($this->password, '$2a$') === 0 || strpos($this->password, '$2b$') === 0) {
            return password_verify($password, $this->password);
        }
        
        // Otherwise, compare as plain text
        return $this->password === $password;
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

