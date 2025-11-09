<?php
/**
 * Authentication helper functions
 */
require_once 'classes/UserDatabaseUtil.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['username']) && !empty($_SESSION['username']);
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Get current logged in username
 */
function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $username = getCurrentUsername();
    if ($username === null) {
        return false;
    }
    
    $user = UserDatabaseUtil::getUser($username);
    return $user !== null && $user->getIsAdmin();
}

/**
 * Require admin access - redirect to main menu if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: main_menu.php');
        exit();
    }
}

/**
 * Get current user object
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $username = getCurrentUsername();
    if ($username === null) {
        return null;
    }
    
    return UserDatabaseUtil::getUser($username);
}


