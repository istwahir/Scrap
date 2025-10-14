<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json');

// Enable CORS for development
if (ENV === 'development') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Clear all session variables
    session_unset();
    
    // Destroy the session
    session_destroy();
    
    // Clear the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Logged out successfully'
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Logout failed'
    ]);
}