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
        header('Location: /Scrap/views/auth/login.php');
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
    
    // Role-based access control: redirect to appropriate dashboard if user is on wrong page
    $currentPath = $_SERVER['REQUEST_URI'];
    $userRole = $_SESSION['user_role'] ?? 'user';
    
    // Check if admin trying to access non-admin pages
    if ($userRole === 'admin' && strpos($currentPath, '/views/admin/') === false && strpos($currentPath, '/login.php') === false && strpos($currentPath, '/logout.php') === false) {
        // Admin should only access admin pages
        if (strpos($currentPath, '/views/collectors/') !== false || strpos($currentPath, '/views/citizens/') !== false) {
            header('Location: /Scrap/views/admin/dashboard.php');
            exit;
        }
    }
    
    // Check if collector trying to access non-collector pages
    if ($userRole === 'collector' && strpos($currentPath, '/views/collectors/') === false && strpos($currentPath, '/login.php') === false && strpos($currentPath, '/logout.php') === false) {
        // Collector should only access collector pages
        if (strpos($currentPath, '/views/admin/') !== false || strpos($currentPath, '/views/citizens/') !== false) {
            header('Location: /Scrap/views/collectors/dashboard.php');
            exit;
        }
    }
    
    // Check if regular user trying to access admin or collector pages
    if ($userRole === 'user' && (strpos($currentPath, '/views/admin/') !== false || strpos($currentPath, '/views/collectors/') !== false)) {
        header('Location: /Scrap/views/citizens/dashboard.php');
        exit;
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
 * Get the appropriate dashboard URL for the current user's role
 */
function getDashboardUrl() {
    if (isAdmin()) {
        return '/Scrap/views/admin/dashboard.php';
    } elseif (isCollector()) {
        return '/Scrap/views/collectors/dashboard.php';
    } else {
        return '/Scrap/views/citizens/dashboard.php';
    }
}

/**
 * Require authentication for a page
 * Call this at the top of protected pages
 */
function requireAuth() {
    global $auth;
    if (!$auth->isAuthenticated()) {
        header('Location: /Scrap/views/auth/login.php');
        exit;
    }
}

/**
 * Require admin role for a page
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        // Redirect non-admin users to their appropriate dashboard
        if (isCollector()) {
            header('Location: /Scrap/views/collectors/dashboard.php');
        } else {
            header('Location: /Scrap/views/citizens/dashboard.php');
        }
        exit;
    }
}

/**
 * Require collector role for a page
 */
function requireCollector() {
    requireAuth();
    if (!isCollector()) {
        // Redirect non-collector users to their appropriate dashboard
        if (isAdmin()) {
            header('Location: /Scrap/views/admin/dashboard.php');
        } else {
            header('Location: /Scrap/views/citizens/dashboard.php');
        }
        exit;
    }
}

/**
 * Get base path for views (NEW - for restructured paths)
 * @param string $type - 'auth', 'citizen', 'admin', 'collector'
 * @param string $file - filename with .php extension
 * @return string - Full path to view file
 */
function getViewPath($type, $file) {
    $paths = [
        'auth' => '/views/auth/',
        'citizen' => '/views/citizens/',
        'admin' => '/views/admin/',
        'collector' => '/views/collectors/'
    ];
    
    if (!isset($paths[$type])) {
        return '/Scrap/index.php';
    }
    
    return '/Scrap' . $paths[$type] . $file;
}

/**
 * Redirect to appropriate dashboard based on user role (NEW)
 */
function redirectToDashboard() {
    if (isAdmin()) {
        header('Location: ' . getViewPath('admin', 'dashboard.php'));
    } elseif (isCollector()) {
        header('Location: ' . getViewPath('collector', 'dashboard.php'));
    } else {
        header('Location: ' . getViewPath('citizen', 'dashboard.php'));
    }
    exit;
}

/**
 * Get old path redirect mapping (for backward compatibility)
 */
function getPathRedirect($oldPath) {
    $redirects = [
        '/Scrap/login.php' => '/Scrap/views/auth/login.php',
        '/Scrap/signup.php' => '/Scrap/views/auth/signup.php',
        '/Scrap/dashboard.php' => '/Scrap/views/citizens/dashboard.php',
        '/Scrap/profile.php' => '/Scrap/views/citizens/profile.php',
        '/Scrap/history.php' => '/Scrap/views/citizens/history.php',
        '/Scrap/request.php' => '/Scrap/views/citizens/request.php',
        '/Scrap/request_details.php' => '/Scrap/views/citizens/request_details.php',
        '/Scrap/rewards.php' => '/Scrap/views/citizens/rewards.php',
        '/Scrap/map.php' => '/Scrap/views/citizens/map.php',
        '/Scrap/guide.php' => '/Scrap/views/citizens/guide.php',
    ];
    
    return $redirects[$oldPath] ?? $oldPath;
}