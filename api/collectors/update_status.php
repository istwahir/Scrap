<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/../../config.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'collector') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Capture user id and release session lock to avoid blocking other requests
$userId = $_SESSION['user_id'];
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['status'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Status is required']);
        exit;
    }
    
    $status = $input['status'];
    $allowedStatuses = ['online', 'offline', 'on_job'];
    
    if (!in_array($status, $allowedStatuses)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid status. Allowed: online, offline, on_job'
        ]);
        exit;
    }
    
    // Get collector ID from user_id
    $stmt = $conn->prepare("SELECT id FROM collectors WHERE user_id = ?");
    $stmt->execute([$userId]);
    $collector = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$collector) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Collector record not found']);
        exit;
    }
    
    // Update active_status and last_active
    $updateStmt = $conn->prepare("
        UPDATE collectors 
        SET active_status = ?, last_active = NOW() 
        WHERE id = ?
    ");
    $updateStmt->execute([$status, $collector['id']]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Status updated successfully',
        'active_status' => $status
    ]);
    
} catch (PDOException $e) {
    error_log('Status update error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
}
