<?php
require_once '../../config.php';
require_once '../../controllers/AuthController.php';

// Set larger time limit for file uploads
set_time_limit(300);

header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $auth = new AuthController();

    // Helper sanitize
    $get = function(string $key, $default = '') { return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default; };

    // Validate required fields
    $required_fields = [
        'fullName', 'phone', 'idNumber', 'dateOfBirth', 'address',
        'latitude', 'longitude', 'vehicleType', 'vehicleReg'
    ];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || trim((string)$_POST[$field]) === '') {
            throw new Exception("Missing required field: $field");
        }
    }

    // Collect inputs
    $fullName   = $get('fullName');
    $phone      = $get('phone');
    $idNumber   = $get('idNumber');
    $dob        = $get('dateOfBirth');
    $address    = $get('address');
    $lat        = (float)$get('latitude');
    $lng        = (float)$get('longitude');
    $vehicle    = strtolower($get('vehicleType'));
    $vehicleReg = strtoupper($get('vehicleReg'));

    // Validate phone number format
    if (!preg_match('/^\+254[17]\d{8}$/', $phone)) {
        throw new Exception('Invalid phone number format');
    }

    // Validate collection areas
    if (!isset($_POST['areas']) || !is_array($_POST['areas']) || empty($_POST['areas'])) {
        throw new Exception('Please select at least one collection area');
    }

    // Validate materials
    if (!isset($_POST['materials']) || !is_array($_POST['materials']) || empty($_POST['materials'])) {
        throw new Exception('Please select at least one material type');
    }

    // Additional validations
    $allowedVehicles = ['truck','pickup','tuktuk','motorcycle'];
    if (!in_array($vehicle, $allowedVehicles, true)) {
        throw new Exception('Invalid vehicle type');
    }

    // Kenyan plate pattern example: KAA 123B (lenient)
    if (!preg_match('/^[A-Z]{3}\s?\d{3}[A-Z]$/', $vehicleReg)) {
        // keep lenient but ensure it's not empty/garbage
        if (strlen($vehicleReg) < 5) {
            throw new Exception('Invalid vehicle registration format');
        }
    }

    // Date validation
    try { $dt = new DateTime($dob); } catch (Throwable $t) { throw new Exception('Invalid date of birth'); }

    // Coordinates validation
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        throw new Exception('Invalid coordinates');
    }

    // Whitelist and normalize areas/materials
    $allowedAreas = ['Kiambu Town','Thika','Ruiru','Juja','Githunguri','Limuru'];
    $areas = array_values(array_unique(array_filter(array_map('trim', (array)$_POST['areas']), function($a) use ($allowedAreas){ return in_array($a, $allowedAreas, true); })));
    if (empty($areas)) throw new Exception('Please select at least one valid collection area');

    $allowedMaterials = ['plastic','paper','metal','glass','electronics'];
    $materials = array_values(array_unique(array_filter(array_map('strtolower', (array)$_POST['materials']), function($m) use ($allowedMaterials){ return in_array($m, $allowedMaterials, true); })));
    if (empty($materials)) throw new Exception('Please select at least one valid material');

    // Prevent duplicate applications (by id_number or phone) in pending/approved
    $dupStmt = $pdo->prepare("SELECT id, status FROM collector_applications WHERE (id_number = :id OR phone = :phone) AND status IN ('pending','approved') ORDER BY id DESC LIMIT 1");
    $dupStmt->execute([':id' => $idNumber, ':phone' => $phone]);
    if ($dup = $dupStmt->fetch(PDO::FETCH_ASSOC)) {
        if ($dup['status'] === 'pending') {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'code' => 'duplicate_pending', 'message' => 'You already have a pending application.']);
            exit;
        }
        if ($dup['status'] === 'approved') {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'code' => 'already_approved', 'message' => 'An approved application already exists.']);
            exit;
        }
    }

    // Validate file uploads
    $required_files = [
        'idCardFront' => 'ID Card (Front)',
        'idCardBack' => 'ID Card (Back)',
        'vehicleDoc' => 'Vehicle Registration',
        'goodConduct' => 'Good Conduct Certificate'
    ];

    $uploaded_files = [];
    // Prepare upload dir
    $todayPath = date('Y/m');
    $baseUploadDir = realpath(__DIR__ . '/../../') . '/uploads/collectors/' . $todayPath;
    if (!is_dir($baseUploadDir)) {
        @mkdir($baseUploadDir, 0777, true);
    }

    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;

    foreach ($required_files as $field => $label) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Missing or invalid file: $label");
        }
        // Validate file type via finfo when possible
        $mime = $_FILES[$field]['type'];
        if ($finfo) {
            $detected = finfo_file($finfo, $_FILES[$field]['tmp_name']);
            if ($detected) $mime = $detected;
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if (!in_array($mime, $allowed_types)) {
            throw new Exception("Invalid file type for $label. Allowed types: JPG, PNG, PDF");
        }

        // Validate file size (5MB max)
        if ($_FILES[$field]['size'] > 5 * 1024 * 1024) {
            throw new Exception("File too large for $label. Maximum size: 5MB");
        }

        // Generate unique filename
        $extension = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
        if (!$extension) {
            $extension = $mime === 'application/pdf' ? 'pdf' : 'jpg';
        }
        $filename = uniqid('', true) . '.' . strtolower($extension);
        $filepath = $baseUploadDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $filepath)) {
            throw new Exception("Failed to save $label");
        }

        // Store relative path from uploads/collectors
        $uploaded_files[$field] = $todayPath . '/' . $filename;
    }

    // Start transaction
    $pdo->beginTransaction();

    // Insert into collector_applications table
    $stmt = $pdo->prepare("
        INSERT INTO collector_applications (
            name, phone, id_number, date_of_birth, address,
            latitude, longitude, vehicle_type, vehicle_reg,
            id_card_front, id_card_back, vehicle_doc, good_conduct,
            status, created_at
        ) VALUES (
            :name, :phone, :id_number, :date_of_birth, :address,
            :latitude, :longitude, :vehicle_type, :vehicle_reg,
            :id_card_front, :id_card_back, :vehicle_doc, :good_conduct,
            'pending', NOW()
        )
    ");

    $stmt->execute([
        'name' => $fullName,
        'phone' => $phone,
        'id_number' => $idNumber,
        'date_of_birth' => $dt->format('Y-m-d'),
        'address' => $address,
        'latitude' => $lat,
        'longitude' => $lng,
        'vehicle_type' => $vehicle,
        'vehicle_reg' => $vehicleReg,
        'id_card_front' => $uploaded_files['idCardFront'],
        'id_card_back' => $uploaded_files['idCardBack'],
        'vehicle_doc' => $uploaded_files['vehicleDoc'],
        'good_conduct' => $uploaded_files['goodConduct']
    ]);

    $application_id = $pdo->lastInsertId();

    // Insert collection areas
    $stmt = $pdo->prepare("
        INSERT INTO collector_areas (
            application_id, area_name
        ) VALUES (
            :application_id, :area_name
        )
    ");

    foreach ($areas as $area) {
        $stmt->execute([
            'application_id' => $application_id,
            'area_name' => $area
        ]);
    }

    // Insert materials
    $stmt = $pdo->prepare("
        INSERT INTO collector_materials (
            application_id, material_type
        ) VALUES (
            :application_id, :material_type
        )
    ");

    foreach ($materials as $material) {
        $stmt->execute([
            'application_id' => $application_id,
            'material_type' => $material
        ]);
    }

    // Commit transaction
    $pdo->commit();

    // Send success response
    http_response_code(201);
    echo json_encode([
        'status' => 'success',
        'message' => 'Application submitted successfully',
        'applicationId' => $application_id,
        'next' => [ 'href' => '/Scrap/profile.php', 'label' => 'View profile' ]
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Clean up uploaded files if they exist
    if (isset($uploaded_files)) {
        foreach ($uploaded_files as $file) {
            $filepath = realpath(__DIR__ . '/../../') . '/uploads/collectors/' . $file;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}