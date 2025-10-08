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

    // Validate required fields
    $required_fields = [
        'fullName', 'phone', 'idNumber', 'dateOfBirth', 'address',
        'latitude', 'longitude', 'vehicleType', 'vehicleReg'
    ];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate phone number format
    if (!preg_match('/^\+254[17]\d{8}$/', $_POST['phone'])) {
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

    // Validate file uploads
    $required_files = [
        'idCardFront' => 'ID Card (Front)',
        'idCardBack' => 'ID Card (Back)',
        'vehicleDoc' => 'Vehicle Registration',
        'goodConduct' => 'Good Conduct Certificate'
    ];

    $uploaded_files = [];
    foreach ($required_files as $field => $label) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Missing or invalid file: $label");
        }

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if (!in_array($_FILES[$field]['type'], $allowed_types)) {
            throw new Exception("Invalid file type for $label. Allowed types: JPG, PNG, PDF");
        }

        // Validate file size (5MB max)
        if ($_FILES[$field]['size'] > 5 * 1024 * 1024) {
            throw new Exception("File too large for $label. Maximum size: 5MB");
        }

        // Generate unique filename
        $extension = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = '../../uploads/collectors/' . $filename;

        // Ensure upload directory exists
        if (!is_dir('../../uploads/collectors')) {
            mkdir('../../uploads/collectors', 0777, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $filepath)) {
            throw new Exception("Failed to save $label");
        }

        $uploaded_files[$field] = $filename;
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
        'name' => $_POST['fullName'],
        'phone' => $_POST['phone'],
        'id_number' => $_POST['idNumber'],
        'date_of_birth' => $_POST['dateOfBirth'],
        'address' => $_POST['address'],
        'latitude' => $_POST['latitude'],
        'longitude' => $_POST['longitude'],
        'vehicle_type' => $_POST['vehicleType'],
        'vehicle_reg' => $_POST['vehicleReg'],
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

    foreach ($_POST['areas'] as $area) {
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

    foreach ($_POST['materials'] as $material) {
        $stmt->execute([
            'application_id' => $application_id,
            'material_type' => $material
        ]);
    }

    // Commit transaction
    $pdo->commit();

    // Send success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Application submitted successfully',
        'applicationId' => $application_id
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Clean up uploaded files if they exist
    if (isset($uploaded_files)) {
        foreach ($uploaded_files as $file) {
            $filepath = '../../uploads/collectors/' . $file;
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