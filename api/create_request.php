<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Temporarily enabled for debugging
ini_set('log_errors', 1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../includes/upload_config.php';

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

// Check if user is logged in
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $db = getDBConnection();
    
    // Validate required fields
    $required = ['materials', 'address', 'lat', 'lng', 'date', 'time'];
    $missing = array_filter($required, function($field) {
        return !isset($_POST[$field]);
    });
    
    if (!empty($missing)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields: ' . implode(', ', $missing)
        ]);
        exit;
    }
    
    // Validate materials
    $allowedMaterials = ['plastic', 'paper', 'metal', 'glass', 'electronics'];
    $materials = is_array($_POST['materials']) ? $_POST['materials'] : [$_POST['materials']];
    $invalidMaterials = array_diff($materials, $allowedMaterials);
    
    if (!empty($invalidMaterials)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid materials specified'
        ]);
        exit;
    }
    
    // Validate coordinates
    $lat = filter_var($_POST['lat'], FILTER_VALIDATE_FLOAT);
    $lng = filter_var($_POST['lng'], FILTER_VALIDATE_FLOAT);
    
    if ($lat === false || $lng === false) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid coordinates'
        ]);
        exit;
    }
    
    // Validate date and time
    $pickupDate = date('Y-m-d', strtotime($_POST['date']));
    if ($pickupDate < date('Y-m-d')) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Pickup date cannot be in the past'
        ]);
        exit;
    }
    
    // Handle photo upload using UploadHelper
    $photoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        try {
            // Use UploadHelper for organized, validated uploads
            $uploadResult = UploadHelper::uploadFile($_FILES['photo'], 'requests');
            $photoPath = $uploadResult['relative_path'];
            
        } catch (Exception $uploadError) {
            error_log('Photo upload error: ' . $uploadError->getMessage());
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Photo upload failed: ' . $uploadError->getMessage()
            ]);
            exit;
        }
    } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle upload errors
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        
        $errorMessage = $uploadErrors[$_FILES['photo']['error']] ?? 'Unknown upload error';
        
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Upload error: ' . $errorMessage
        ]);
        exit;
    }
    
    // Start database transaction
    $db->beginTransaction();
    
    // Get user ID from session
    $userId = $_SESSION['user_id'];
    
    // Convert time slot to actual time
    $timeSlotMap = [
        'morning' => '09:00:00',
        'afternoon' => '14:00:00',
        'evening' => '17:00:00'
    ];
    
    $timeSlot = $_POST['time'];
    $pickupTime = isset($timeSlotMap[$timeSlot]) ? $timeSlotMap[$timeSlot] : '09:00:00';
    
    // Handle collector_id and dropoff_point_id - set to NULL if empty
    $collectorId = (!empty($_POST['collector_id'])) ? intval($_POST['collector_id']) : null;
    $dropoffPointId = (!empty($_POST['dropoff_point_id'])) ? intval($_POST['dropoff_point_id']) : null;
    
    // Insert collection request
    $stmt = $db->prepare("
        INSERT INTO collection_requests (
            user_id, collector_id, dropoff_point_id, materials, photo_url, estimated_weight, 
            pickup_address, pickup_date, pickup_time, notes, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    // This was failing because sanitizeInput() wasn't defined:
    $materialsString = implode(',', $materials);
    $estimatedWeight = isset($_POST['weight']) ? floatval($_POST['weight']) : null;
    $notes = isset($_POST['notes']) ? sanitizeInput($_POST['notes']) : null;

    $stmt->execute([
        $userId,
        $collectorId,
        $dropoffPointId,
        $materialsString,
        $photoPath,
        $estimatedWeight,
        sanitizeInput($_POST['address']), // This was also failing
        $pickupDate,
        $pickupTime,
        $notes
    ]);
    
    $requestId = $db->lastInsertId();
    
    // Award points for creating request (5 points)
    $rewardStmt = $db->prepare("
        INSERT INTO rewards (user_id, points, activity_type, reference_id) 
        VALUES (?, 5, 'collection', ?)
    ");
    $rewardStmt->execute([$userId, $requestId]);
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Request created successfully',
        'request_id' => $requestId,
        'points_earned' => 5
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollBack();
    }
    
    error_log("Create request error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to create request: ' . $e->getMessage()
    ]);
}
?>