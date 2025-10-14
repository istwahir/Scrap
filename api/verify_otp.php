<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['email']) || !isset($input['otp'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Email address and OTP are required'
        ]);
        exit;
    }

    $email = trim($input['email']);
    $otp = trim($input['otp']);

    // Validate OTP format
    if (!preg_match('/^\d{6}$/', $otp)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'OTP must be 6 digits'
        ]);
        exit;
    }

    // Initialize auth controller
    $auth = new AuthController();

    // Verify OTP
    $result = $auth->verifyOTP($email, $otp);

    if ($result['status'] === 'success') {
        // Set session
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['user_role'] = $result['user']['role'];
        $_SESSION['email'] = $email;
        $_SESSION['user_name'] = $result['user']['name'];

        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error'
    ]);
}