<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Ensure user is a collector
requireCollector();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    $conn = getDBConnection();
    
    $name = isset($_GET['name']) ? trim($_GET['name']) : null;
    $lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
    $lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

    $similarName = false;
    $similarLocation = false;

    // Check for similar name (case-insensitive partial match)
    if ($name && strlen($name) >= 3) {
        $nameStmt = $conn->prepare("
            SELECT id, name 
            FROM dropoff_points 
            WHERE LOWER(name) LIKE LOWER(?)
            LIMIT 1
        ");
        $nameStmt->execute(['%' . $name . '%']);
        $similarName = $nameStmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // Check for similar location (within ~1km radius)
    // 0.009 degrees ≈ 1km at the equator
    if ($lat !== null && $lng !== null) {
        $radius = 0.009; // approximately 1km
        $locationStmt = $conn->prepare("
            SELECT id, name, lat, lng,
                   (6371 * acos(
                       cos(radians(?)) * cos(radians(lat)) *
                       cos(radians(lng) - radians(?)) +
                       sin(radians(?)) * sin(radians(lat))
                   )) AS distance
            FROM dropoff_points
            HAVING distance < 1
            ORDER BY distance
            LIMIT 1
        ");
        $locationStmt->execute([$lat, $lng, $lat]);
        $nearbyLocation = $locationStmt->fetch(PDO::FETCH_ASSOC);
        $similarLocation = $nearbyLocation !== false;
    }

    echo json_encode([
        'status' => 'success',
        'similar_name' => $similarName,
        'similar_location' => $similarLocation
    ]);

} catch (PDOException $e) {
    error_log("Check Dropoff Similarity Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Check Dropoff Similarity Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error occurred'
    ]);
}
?>
