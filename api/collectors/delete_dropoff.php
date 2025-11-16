<?php
header('Content-Type: application/json');
require_once '../../config.php';

// Get database connection
$conn = getDBConnection();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// Check if user is a collector
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'collector') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Drop-off point ID is required']);
        exit;
    }
    
    $dropoffId = intval($input['id']);
    
    // Verify ownership - ensure this drop-off point belongs to this collector
    $checkStmt = $conn->prepare("
        SELECT id, name FROM dropoff_points 
        WHERE id = ? AND added_by = ? AND added_by_role = 'collector'
    ");
    $checkStmt->execute([$dropoffId, $userId]);
    $dropoff = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dropoff) {
        echo json_encode(['status' => 'error', 'message' => 'Drop-off point not found or you do not have permission to delete it']);
        exit;
    }
    
    // Check if there are any active collection requests for this drop-off point
    $requestsStmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM collection_requests 
        WHERE dropoff_point_id = ? AND status NOT IN ('completed', 'cancelled')
    ");
    $requestsStmt->execute([$dropoffId]);
    $requestsCount = $requestsStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($requestsCount > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Cannot delete drop-off point with active collection requests. Please complete or cancel them first.'
        ]);
        exit;
    }
    
    // Delete the drop-off point
    $stmt = $conn->prepare("
        DELETE FROM dropoff_points 
        WHERE id = ? AND added_by = ? AND added_by_role = 'collector'
    ");
    
    $stmt->execute([$dropoffId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        error_log("Collector ID $userId deleted drop-off point: {$dropoff['name']} (ID: $dropoffId)");
        echo json_encode([
            'status' => 'success',
            'message' => 'Drop-off point deleted successfully!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete drop-off point'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Delete Dropoff Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Delete Dropoff Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error occurred']);
}
