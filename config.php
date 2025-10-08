<?php
// Environment
define('ENV', 'development'); // 'development' or 'production'
define('BASE_URL', 'http://localhost/Scrap');
define('TIMEZONE', 'Africa/Nairobi');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kiambu_recycling');

// M-Pesa API Configuration (Sandbox)
define('MPESA_ENV', 'sandbox');
define('MPESA_CONSUMER_KEY', 'your_consumer_key');
define('MPESA_CONSUMER_SECRET', 'your_consumer_secret');
define('MPESA_PASSKEY', 'your_passkey');
define('MPESA_SHORTCODE', '174379'); // Sandbox shortcode
define('MPESA_CALLBACK_URL', BASE_URL . '/mpesa/callback.php');

// Security
define('CSRF_TOKEN_SECRET', 'change_this_to_a_random_string');
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Email/OTP Configuration (Mock for development)
define('OTP_LENGTH', 6);
define('OTP_EXPIRY', 300); // 5 minutes
define('MOCK_EMAIL', true); // Set to false in production

// Error Reporting
if (ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

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

// Security Functions
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Initialize session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => ENV === 'production',
    'cookie_samesite' => 'Strict',
    'gc_maxlifetime' => SESSION_LIFETIME
]);
