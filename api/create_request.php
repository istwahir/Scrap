<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Temporarily enabled for debugging
ini_set('log_errors', 1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

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
    
    // Handle photo upload
    $photoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        try {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('Failed to create upload directory');
                }
            }
            
            // Check if upload directory is writable
            if (!is_writable($uploadDir)) {
                throw new Exception('Upload directory is not writable');
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedType = finfo_file($fileInfo, $_FILES['photo']['tmp_name']);
            finfo_close($fileInfo);
            
            if (!in_array($detectedType, $allowedTypes)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.'
                ]);
                exit;
            }
            
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($_FILES['photo']['size'] > $maxSize) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'File size too large. Maximum size is 5MB.'
                ]);
                exit;
            }
            
            // Get proper extension based on detected type
            $extensionMap = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif'
            ];
            $extension = $extensionMap[$detectedType] ?? 'jpg';
            
            $fileName = uniqid() . '_' . time() . '.' . $extension;
            $fullPath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $fullPath)) {
                throw new Exception('Failed to move uploaded file');
            }
            
            // Store relative path for database
            $photoPath = 'uploads/' . $fileName;
            
        } catch (Exception $uploadError) {
            error_log('Photo upload error: ' . $uploadError->getMessage());
            http_response_code(500);
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