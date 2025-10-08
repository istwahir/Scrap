<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Reward.php';

header('Content-Type: application/json');

// Enable CORS for development
if (ENV === 'development') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
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

// Check authentication
$auth = new AuthController();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['option_id'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Option ID is required'
        ]);
        exit;
    }

    $optionId = trim($input['option_id']);
    $rewardModel = new Reward();

    // Process redemption
    $success = $rewardModel->processRedemption($userId, $optionId);

    if ($success) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Reward redeemed successfully. You will receive your reward shortly.'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Redemption failed. Please check your points balance and try again.'
        ]);
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error'
    ]);
}