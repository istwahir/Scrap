<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Enable CORS for development
if (ENV === 'development') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Check authentication
$auth = new AuthController();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    exit;
}

$userId = $_SESSION['user_id'];
$lastEventId = isset($_SERVER['HTTP_LAST_EVENT_ID']) ? 
    intval($_SERVER['HTTP_LAST_EVENT_ID']) : 0;

try {
    $db = getDBConnection();
    
    while (true) {
        // Check for request updates
        $stmt = $db->prepare(
            "SELECT 
                r.id,
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
        
        $stmt->execute([$userId, $lastEventId]);
        $updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($updates as $update) {
            $data = [
                'type' => 'request_update',
                'request_id' => $update['id'],
                'status' => $update['status']
            ];
            
            // Include collector location if available
            if ($update['collector_lat'] && $update['collector_lng']) {
                $data['collector_location'] = [
                    'lat' => $update['collector_lat'],
                    'lng' => $update['collector_lng']
                ];
            }
            
            echo "id: " . strtotime($update['updated_at']) . "\n";
            echo "event: update\n";
            echo "data: " . json_encode($data) . "\n\n";
            
            ob_flush();
            flush();
        }
        
        // Check for new notifications
        $stmt = $db->prepare(
            "SELECT id, title, message, created_at
             FROM notifications
             WHERE user_id = ?
             AND created_at > FROM_UNIXTIME(?)"
        );
        
        $stmt->execute([$userId, $lastEventId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($notifications as $notification) {
            echo "id: " . strtotime($notification['created_at']) . "\n";
            echo "event: notification\n";
            echo "data: " . json_encode($notification) . "\n\n";
            
            ob_flush();
            flush();
        }
        
        // Update last event ID
        if (!empty($updates) || !empty($notifications)) {
            $lastEventId = time();
        }
        
        // Sleep to prevent excessive database queries
        sleep(5);
    }
    
} catch (Exception $e) {
    error_log("SSE Error: " . $e->getMessage());
    exit;
}