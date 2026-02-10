<?php
/**
 * Authentication Functions
 * Handles admin login/logout and session management
 */

session_start();

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Admin login function
 */
function adminLogin($username, $password) {
    global $pdo;
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        return true;
    }
    
    return false;
}

/**
 * Admin logout function
 */
function adminLogout() {
    session_unset();
    session_destroy();
}

/**
 * Redirect to login if not authenticated
 */
function requireAuth() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
?>