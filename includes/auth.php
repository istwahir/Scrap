<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Initialize authentication
$auth = new AuthController();

// Check if user is authenticated
if (!$auth->isAuthenticated()) {
    // Redirect to login page for protected routes
    $currentPath = $_SERVER['REQUEST_URI'];
    $currentFile = basename(parse_url($currentPath, PHP_URL_PATH));
    
    // Only redirect if NOT already on login, signup, or index pages
    if ($currentFile !== 'login.php' && $currentFile !== 'signup.php' && $currentFile !== 'index.php' && $currentFile !== '') {
        header('Location: /Scrap/login.php');
        exit;
    }
} else {
    // Load user data into session if not already loaded
    if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_role'])) {
        $user = $auth->getCurrentUser();
        if ($user) {
            $_SESSION['user_name'] = $user['name'] ?? 'User';
            $_SESSION['user_role'] = $user['role'];
        }
    }
}

/**
 * Check if current user has admin role
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if current user is a collector
 */
function isCollector() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'collector';
}

/**
 * Check if current user is a regular user
 */
function isUser() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user';
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $auth = new AuthController();
    return $auth->getCurrentUser();
}

/**
 * Require authentication for a page
 * Call this at the top of protected pages
 */
function requireAuth() {
    global $auth;
    if (!$auth->isAuthenticated()) {
        header('Location: /Scrap/login.php');
        exit;
    }
}

/**
 * Require admin role for a page
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('Location: /scrap/dashboard.html');
        exit;
    }
}

/**
 * Require collector role for a page
 */
function requireCollector() {
    requireAuth();
    if (!isCollector()) {
        header('Location: /scrap/dashboard.html');
        exit;
    }
}