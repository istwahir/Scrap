<?php
header('Content-Type: application/json');
require_once '../../config.php';

// Get database connection
$conn = getDBConnection();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// Check if user is a collector
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'collector') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    // Validate required fields
    if (!isset($_POST['id']) || !isset($_POST['name']) || !isset($_POST['address']) || !isset($_POST['lat']) || !isset($_POST['lng'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }
    
    $dropoffId = intval($_POST['id']);
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);
    $contactPhone = isset($_POST['contact_phone']) ? trim($_POST['contact_phone']) : null;
    $operatingHours = isset($_POST['operating_hours']) ? trim($_POST['operating_hours']) : null;
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'active';
    
    // Validate coordinates
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid coordinates']);
        exit;
    }
    
    // Validate materials
    if (!isset($_POST['materials']) || !is_array($_POST['materials']) || count($_POST['materials']) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please select at least one material']);
        exit;
    }
    
    $materials = implode(',', $_POST['materials']);
    
    // Verify ownership - ensure this drop-off point belongs to this collector
    $checkStmt = $conn->prepare("
        SELECT id, photo_url FROM dropoff_points 
        WHERE id = ? AND added_by = ? AND added_by_role = 'collector'
    ");
    $checkStmt->execute([$dropoffId, $userId]);
    $existingDropoff = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingDropoff) {
        echo json_encode(['status' => 'error', 'message' => 'Drop-off point not found or you do not have permission to edit it']);
        exit;
    }
    
    // Handle photo upload if provided
    $photoPath = $existingDropoff['photo_url']; // Keep existing photo by default
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../public/uploads/dropoffs/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['photo']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed']);
            exit;
        }
        
        if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'File size exceeds 5MB limit']);
            exit;
        }
        
        // Generate unique filename
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = 'dropoff_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            // Delete old photo if exists
            if ($existingDropoff['photo_url'] && file_exists('../../public/' . $existingDropoff['photo_url'])) {
                unlink('../../public/' . $existingDropoff['photo_url']);
            }
            $photoPath = 'uploads/dropoffs/' . $filename;
        } else {
            error_log("Failed to move uploaded file to: " . $targetPath);
        }
    }
    
    // Update the drop-off point
    $stmt = $conn->prepare("
        UPDATE dropoff_points 
        SET name = ?,
            address = ?,
            lat = ?,
            lng = ?,
            contact_phone = ?,
            operating_hours = ?,
            materials = ?,
            photo_url = ?,
            status = ?
        WHERE id = ? AND added_by = ? AND added_by_role = 'collector'
    ");
    
    $stmt->execute([
        $name,
        $address,
        $lat,
        $lng,
        $contactPhone,
        $operatingHours,
        $materials,
        $photoPath,
        $status,
        $dropoffId,
        $userId
    ]);
    
    if ($stmt->rowCount() > 0) {
        error_log("Collector ID $userId updated drop-off point: $name (ID: $dropoffId)");
        echo json_encode([
            'status' => 'success',
            'message' => 'Drop-off point updated successfully!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No changes were made'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Update Dropoff Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Update Dropoff Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error occurred']);
}
