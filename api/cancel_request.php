<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

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

// Initialize auth controller
$auth = new AuthController();

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
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['request_id'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Request ID is required'
        ]);
        exit;
    }
    
    $requestId = intval($input['request_id']);
    $userId = $_SESSION['user_id'];
    
    $db = getDBConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    // Check if request exists and belongs to user
    $stmt = $db->prepare("
        SELECT id, status, user_id 
        FROM collection_requests 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$requestId, $userId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        $db->rollback();
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Request not found or access denied'
        ]);
        exit;
    }
    
    // Check if request can be cancelled
    if (!in_array($request['status'], ['pending', 'assigned'])) {
        $db->rollback();
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Cannot cancel request with status: ' . $request['status']
        ]);
        exit;
    }
    
    // Update request status to cancelled
    $updateStmt = $db->prepare("
        UPDATE collection_requests 
        SET status = 'cancelled', updated_at = NOW() 
        WHERE id = ?
    ");
    $updateStmt->execute([$requestId]);
    
    // If request was assigned to a collector, notify them (optional)
    // You could add notification logic here
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Request cancelled successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollback();
    }
    
    error_log("Cancel request error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to cancel request: ' . $e->getMessage()
    ]);
}
?>