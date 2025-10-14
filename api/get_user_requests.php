<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Request.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $requestModel = new Request();
    
    // Get user's requests
    $requests = $requestModel->getByUserId($userId);
    
    // Calculate statistics
    $stats = [
        'total' => count($requests),
        'completed' => 0,
        'active' => 0,
        'cancelled' => 0,
        'total_weight' => 0
    ];
    
    foreach ($requests as $request) {
        switch ($request['status']) {
            case 'completed':
                $stats['completed']++;
                if ($request['estimated_weight']) {
                    $stats['total_weight'] += (float)$request['estimated_weight'];
                }
                break;
            case 'pending':
            case 'assigned':
            case 'en_route':
                $stats['active']++;
                break;
            case 'cancelled':
                $stats['cancelled']++;
                break;
        }
    }
    
    // Format requests for frontend
    $formattedRequests = array_map(function($request) {
        return [
            'id' => (int)$request['id'],
            'materials' => $request['materials'],
            'estimated_weight' => $request['estimated_weight'] ? (float)$request['estimated_weight'] : null,
            'pickup_address' => $request['pickup_address'],
            'pickup_date' => $request['pickup_date'],
            'pickup_time' => $request['pickup_time'],
            'status' => $request['status'],
            'created_at' => $request['created_at'],
            'updated_at' => $request['updated_at'] ?? null,
            'collector_name' => $request['collector_name'] ?? null,
            'dropoff_name' => $request['dropoff_name'] ?? null,
            'dropoff_address' => $request['dropoff_address'] ?? null,
            'notes' => $request['notes'] ?? null,
            'photo_url' => $request['photo_url'] ?? null
        ];
    }, $requests);
    
    // Sort by created_at descending
    usort($formattedRequests, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    echo json_encode([
        'status' => 'success',
        'requests' => $formattedRequests,
        'stats' => $stats,
        'message' => 'Requests retrieved successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_user_requests.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
}
?>