<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Reward.php';

header('Content-Type: application/json');

// Enable CORS for development
if (ENV === 'development') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
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
    $rewardModel = new Reward();

    // Get reward statistics
    $stats = $rewardModel->getStats($userId);

    echo json_encode([
        'status' => 'success',
        'stats' => $stats
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch rewards data'
    ]);
}