<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config.php';
require_once '../../controllers/AuthController.php';

header('Content-Type: application/json');

// Verify collector authentication
$auth = new AuthController();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Verify the user is a collector
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT c.id
        FROM collectors c 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $collector = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$collector) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'User is not a collector']);
        exit;
    }

    // Handle location update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Check if we have location data or just status update
        $hasLocation = isset($data['latitude']) && isset($data['longitude']);
        $hasStatus = isset($data['status']);
        
        if (!$hasLocation && !$hasStatus) {
            throw new Exception('Missing location or status data');
        }

        // Start transaction
        $pdo->beginTransaction();

        if ($hasLocation) {
            // Validate coordinates
            $latitude = floatval($data['latitude']);
            $longitude = floatval($data['longitude']);

            if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                throw new Exception('Invalid coordinates');
            }

            // Insert into location history
            $stmt = $pdo->prepare("
                INSERT INTO collector_locations (
                    collector_id, latitude, longitude
                ) VALUES (
                    :collector_id, :latitude, :longitude
                )
            ");
            $stmt->execute([
                'collector_id' => $collector['id'],
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);

            // Update collector's current location
            $stmt = $pdo->prepare("
                UPDATE collectors 
                SET current_latitude = :latitude,
                    current_longitude = :longitude,
                    last_active = NOW()
                WHERE id = :collector_id
            ");
            $stmt->execute([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'collector_id' => $collector['id']
            ]);
        }

        // Update status if provided
        if ($hasStatus) {
            $status = $data['status'];
            
            // Validate status value
            $validStatuses = ['online', 'offline', 'on_job'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid status value. Must be: online, offline, or on_job');
            }

            // Update collector's active status
            $stmt = $pdo->prepare("
                UPDATE collectors 
                SET active_status = :status,
                    last_active = NOW()
                WHERE id = :collector_id
            ");
            $stmt->execute([
                'status' => $status,
                'collector_id' => $collector['id']
            ]);
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);

    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log the actual error for debugging
    error_log("Update location error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}