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

    if (!$input || !isset($input['name']) || !isset($input['email']) || !isset($input['phone']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Name, email address, phone number, and password are required'
        ]);
        exit;
    }

    $name = trim($input['name']);
    $email = trim($input['email']);
    $phone = trim($input['phone']);
    $password = $input['password'];

    // Initialize auth controller
    $auth = new AuthController();

    // Register user
    // Validate Kenyan phone number format: +2547XXXXXXXX or +2541XXXXXXXX
    if (!preg_match('/^\+254[17]\d{8}$/', $phone)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid phone number format. Use +2547XXXXXXXX or +2541XXXXXXXX'
        ]);
        exit;
    }

    $result = $auth->register($name, $email, $phone, $password);

    if ($result['status'] === 'success') {
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