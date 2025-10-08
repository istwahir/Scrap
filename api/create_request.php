<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json');

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
    
    // Handle file upload
    $photoUrl = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $_FILES['photo']['tmp_name']);
        
        if (!in_array($mimeType, ALLOWED_FILE_TYPES)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid file type. Allowed types: ' . implode(', ', ALLOWED_FILE_TYPES)
            ]);
            exit;
        }
        
        if ($_FILES['photo']['size'] > MAX_FILE_SIZE) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'File too large. Maximum size: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'
            ]);
            exit;
        }
        
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadDir = __DIR__ . '/../public/uploads/requests/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
            $photoUrl = 'uploads/requests/' . $filename;
        }
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Insert request
    $stmt = $db->prepare(
        "INSERT INTO collection_requests (
            user_id, 
            materials, 
            estimated_weight, 
            pickup_address, 
            lat, 
            lng, 
            pickup_date, 
            pickup_time,
            photo_url,
            notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    
    $stmt->execute([
        $_SESSION['user_id'],
        implode(',', $materials),
        $_POST['weight'] ?? null,
        $_POST['address'],
        $lat,
        $lng,
        $pickupDate,
        $_POST['time'],
        $photoUrl,
        $_POST['notes'] ?? null
    ]);
    
    $requestId = $db->lastInsertId();
    
    // Find nearest drop-off point
    $stmt = $db->prepare(
        "SELECT id, (
            6371 * acos(
                cos(radians(?)) * 
                cos(radians(lat)) * 
                cos(radians(lng) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(lat))
            )
        ) as distance 
        FROM dropoff_points 
        WHERE status = 'active' 
        HAVING distance <= 10
        ORDER BY distance 
        LIMIT 1"
    );
    
    $stmt->execute([$lat, $lng, $lat]);
    $dropoff = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dropoff) {
        $stmt = $db->prepare(
            "UPDATE collection_requests 
             SET dropoff_point_id = ? 
             WHERE id = ?"
        );
        $stmt->execute([$dropoff['id'], $requestId]);
    }
    
    // Find available collector
    $stmt = $db->prepare(
        "SELECT c.id 
         FROM collectors c 
         LEFT JOIN collection_requests r ON c.id = r.collector_id 
         WHERE c.status = 'approved' 
         GROUP BY c.id 
         HAVING COUNT(r.id) < 5
         ORDER BY RAND() 
         LIMIT 1"
    );
    
    $stmt->execute();
    $collector = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($collector) {
        $stmt = $db->prepare(
            "UPDATE collection_requests 
             SET collector_id = ?, status = 'assigned' 
             WHERE id = ?"
        );
        $stmt->execute([$collector['id'], $requestId]);
    }
    
    // Commit transaction
    $db->commit();
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Request created successfully',
        'request_id' => $requestId
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollBack();
    }
    
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to create request'
    ]);
}