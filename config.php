<?php
// Environment
define('ENV', 'development');
define('BASE_URL', 'http://localhost/Scrap');
define('TIMEZONE', 'Africa/Nairobi');

// Email configuration
define('MOCK_EMAIL', true); // Set to false in production to send real emails

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kiambu_recycling');

// Database Connection
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Utility function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Initialize session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => ENV === 'production',
    'cookie_samesite' => 'Strict'
]);
?>
