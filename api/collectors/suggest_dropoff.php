<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Ensure user is a collector
requireCollector();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Validate required fields
    if (empty($_POST['name']) || empty($_POST['address']) || empty($_POST['lat']) || empty($_POST['lng'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);
    $contact_phone = !empty($_POST['contact_phone']) ? trim($_POST['contact_phone']) : null;
    $operating_hours = !empty($_POST['operating_hours']) ? trim($_POST['operating_hours']) : null;
    $materials = !empty($_POST['materials']) ? $_POST['materials'] : null;

    // Validate materials
    if (empty($materials)) {
        echo json_encode(['status' => 'error', 'message' => 'At least one material type is required']);
        exit;
    }

    // Validate coordinates
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid coordinates']);
        exit;
    }

    // Check for similar existing drop-offs (exact duplicates)
    $checkStmt = $conn->prepare("
        SELECT id, name, address 
        FROM dropoff_points 
        WHERE LOWER(name) = LOWER(?) 
        OR (
            lat BETWEEN ? - 0.009 AND ? + 0.009 
            AND lng BETWEEN ? - 0.009 AND ? + 0.009
        )
        LIMIT 1
    ");
    $checkStmt->execute([$name, $lat, $lat, $lng, $lng]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'A similar drop-off point already exists: ' . $existing['name']
        ]);
        exit;
    }

    // Handle photo upload if provided
    $photoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../public/uploads/dropoffs/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileInfo = pathinfo($_FILES['photo']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Validate file type
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp']);
            exit;
        }

        // Validate file size (max 5MB)
        if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'File size exceeds 5MB limit']);
            exit;
        }

        // Generate unique filename
        $filename = 'dropoff_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $photoPath = 'uploads/dropoffs/' . $filename;
        } else {
            error_log("Failed to move uploaded file to: " . $targetPath);
        }
    }

    // Get collector ID
    $collectorStmt = $conn->prepare("SELECT id FROM collectors WHERE user_id = ?");
    $collectorStmt->execute([$_SESSION['user_id']]);
    $collector = $collectorStmt->fetch(PDO::FETCH_ASSOC);

    if (!$collector) {
        echo json_encode(['status' => 'error', 'message' => 'Collector profile not found']);
        exit;
    }

    // Insert the drop-off point added by the collector
    $stmt = $conn->prepare("
        INSERT INTO dropoff_points 
        (name, address, lat, lng, contact_phone, operating_hours, materials, photo_url, added_by, added_by_role, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'collector', 'active', NOW())
    ");
    
    $stmt->execute([
        $name,
        $address,
        $lat,
        $lng,
        $contact_phone,
        $operating_hours,
        $materials,
        $photoPath,
        $_SESSION['user_id']  // Use user_id instead of collector id to match FK constraint
    ]);

    $dropoffId = $conn->lastInsertId();

    // Log the activity
    error_log("Collector ID {$collector['id']} added drop-off point: {$name} (ID: {$dropoffId})");

    echo json_encode([
        'status' => 'success',
        'message' => 'Drop-off point added successfully!',
        'dropoff_id' => $dropoffId
    ]);

} catch (PDOException $e) {
    error_log("Suggest Dropoff Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Suggest Dropoff Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error occurred'
    ]);
}
?>
