<?php
/**
 * Authentication System
 * PHP 7.2 compatible
 */

require_once __DIR__ . '/db.php';

/**
 * Initialize session
 */
function initSession() {
    $config = require __DIR__ . '/config.php';
    
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', $config['session']['secure'] ? 1 : 0);
        ini_set('session.cookie_samesite', 'Lax');
        
        session_name($config['session']['name']);
        session_start();
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    initSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['account_id']);
}

/**
 * Require login (redirect to login page if not authenticated)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Attempt to log in a user
 */
function login($email, $password) {
    $db = getDB();
    
    // Get user with account info
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.account_id,
            u.email,
            u.name,
            u.password_hash,
            a.name as account_name
        FROM users u
        JOIN accounts a ON u.account_id = a.id
        WHERE u.email = ? AND u.password_hash IS NOT NULL
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return false;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }
    
    // Set session
    initSession();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['account_id'] = $user['account_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['account_name'] = $user['account_name'];
    
    // Update last login
    $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")
       ->execute([$user['id']]);
    
    return true;
}

/**
 * Log out current user
 */
function logout() {
    initSession();
    $_SESSION = [];
    session_destroy();
}

/**
 * Get current user info
 */
function currentUser() {
    initSession();
    
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'account_id' => $_SESSION['account_id'],
        'email' => $_SESSION['email'],
        'name' => $_SESSION['name'],
        'account_name' => $_SESSION['account_name'],
    ];
}

/**
 * Get current account ID
 */
function currentAccountId() {
    initSession();
    return $_SESSION['account_id'] ?? null;
}

