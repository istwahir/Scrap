<?php
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
        SELECT c.id, c.active_status 
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
        
        if (!isset($data['latitude']) || !isset($data['longitude'])) {
            throw new Exception('Missing location data');
        }

        // Validate coordinates
        $latitude = floatval($data['latitude']);
        $longitude = floatval($data['longitude']);

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            throw new Exception('Invalid coordinates');
        }

        // Start transaction
        $pdo->beginTransaction();

        // Check if collectors table has current_latitude/current_longitude
        $hasInlinePosition = false;
        try {
            $chk = $pdo->prepare("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'collectors' AND COLUMN_NAME IN ('current_latitude','current_longitude')");
            $chk->execute(['db' => DB_NAME]);
            $row = $chk->fetch(PDO::FETCH_ASSOC);
            $hasInlinePosition = isset($row['cnt']) && (int)$row['cnt'] >= 2;
        } catch (Throwable $t) {
            $hasInlinePosition = false;
        }

        // Update collectors table (conditionally include lat/lng)
        if ($hasInlinePosition) {
            $stmt = $pdo->prepare("
                UPDATE collectors 
                SET current_latitude = :latitude,
                    current_longitude = :longitude,
                    last_active = NOW(),
                    active_status = :status
                WHERE id = :id
            ");
            $stmt->execute([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => $data['status'] ?? $collector['active_status'],
                'id' => $collector['id']
            ]);
        } else {
            // Fallback: update only status/last_active
            $stmt = $pdo->prepare("
                UPDATE collectors 
                SET last_active = NOW(),
                    active_status = :status
                WHERE id = :id
            ");
            $stmt->execute([
                'status' => $data['status'] ?? $collector['active_status'],
                'id' => $collector['id']
            ]);
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

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Location updated successfully'
        ]);

    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}