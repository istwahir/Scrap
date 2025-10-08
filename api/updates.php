<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json');

// Enable CORS for development
if (ENV === 'development') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type');
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
$lastCheck = filter_input(INPUT_GET, 'last_check', FILTER_VALIDATE_INT) ?? 0;

try {
    $db = getDBConnection();
    
    // Get request updates
    $stmt = $db->prepare(
        "SELECT 
            r.id as request_id,
            r.status,
            r.updated_at,
            c.lat as collector_lat,
            c.lng as collector_lng
         FROM collection_requests r
         LEFT JOIN collectors c ON r.collector_id = c.id
         WHERE r.user_id = ? 
         AND r.updated_at > FROM_UNIXTIME(?)
         AND r.status IN ('assigned', 'en_route', 'completed')"
    );
    
    $stmt->execute([$userId, $lastCheck]);
    $updates = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $update = [
            'request_id' => $row['request_id'],
            'status' => $row['status'],
            'updated_at' => $row['updated_at']
        ];
        
        if ($row['collector_lat'] && $row['collector_lng']) {
            $update['collector_location'] = [
                'lat' => $row['collector_lat'],
                'lng' => $row['collector_lng']
            ];
        }
        
        $updates[] = $update;
    }
    
    // Get new notifications
    $stmt = $db->prepare(
        "SELECT id, title, message, created_at
         FROM notifications
         WHERE user_id = ?
         AND created_at > FROM_UNIXTIME(?)"
    );
    
    $stmt->execute([$userId, $lastCheck]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'timestamp' => time(),
        'updates' => $updates,
        'notifications' => $notifications
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch updates'
    ]);
}