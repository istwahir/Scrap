<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
session_start();

require_once __DIR__ . '/../../config.php';

// Get database connection
$conn = getDBConnection();

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    // GET request - Fetch collectors
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Single collector details
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("
                SELECT 
                    c.*,
                    c.vehicle_registration as vehicle_reg,
                    u.name, u.email, u.phone, u.created_at,
                    COUNT(DISTINCT cr.id) as total_collections,
                    SUM(CASE WHEN cr.status = 'completed' THEN 1 ELSE 0 END) as completed_collections
                FROM collectors c
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN collection_requests cr ON c.id = cr.collector_id
                WHERE c.id = ?
                GROUP BY c.id
            ");
            $stmt->execute([$_GET['id']]);
            $collector = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($collector) {
                // Set default values for missing fields
                $collector['rating'] = '5.0';
                if (empty($collector['vehicle_type'])) {
                    $collector['vehicle_type'] = 'N/A';
                }
                if (empty($collector['vehicle_reg'])) {
                    $collector['vehicle_reg'] = 'N/A';
                }
                if (empty($collector['id_number'])) {
                    $collector['id_number'] = 'N/A';
                }
                if (empty($collector['address'])) {
                    $collector['address'] = 'N/A';
                }
                echo json_encode([
                    'status' => 'success',
                    'collector' => $collector
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Collector not found']);
            }
            exit;
        }
        
        // All collectors list
        $stmt = $conn->prepare("
            SELECT 
                c.id, 
                c.user_id, 
                CASE 
                    WHEN c.status = 'approved' THEN 'active'
                    ELSE c.status 
                END as status,
                c.vehicle_type,
                c.vehicle_registration as vehicle_reg,
                u.name, 
                u.email, 
                u.phone, 
                u.created_at,
                COUNT(DISTINCT cr.id) as total_collections
            FROM collectors c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN collection_requests cr ON c.id = cr.collector_id AND cr.status = 'completed'
            GROUP BY c.id, c.user_id, c.status, c.vehicle_type, c.vehicle_registration, u.name, u.email, u.phone, u.created_at
            ORDER BY c.created_at DESC
        ");
        $stmt->execute();
        $collectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format collectors data
        foreach ($collectors as &$collector) {
            $collector['rating'] = '5.0';
            // total_collections already set by query
            // Ensure vehicle fields are not null
            if (empty($collector['vehicle_type'])) {
                $collector['vehicle_type'] = 'N/A';
            }
            if (empty($collector['vehicle_reg'])) {
                $collector['vehicle_reg'] = 'N/A';
            }
        }
        
        // Get collector applications (pending, approved, rejected)
        $appsStmt = $conn->prepare("
            SELECT 
                ca.id,
                ca.name as full_name,
                ca.phone as phone_number,
                ca.id_number,
                ca.date_of_birth,
                ca.address as residential_area,
                ca.latitude,
                ca.longitude,
                ca.vehicle_type,
                ca.vehicle_reg,
                ca.id_card_front,
                ca.id_card_back,
                ca.vehicle_doc as vehicle_document,
                ca.good_conduct,
                ca.status,
                ca.created_at,
                GROUP_CONCAT(DISTINCT cam.area_name) as service_areas,
                GROUP_CONCAT(DISTINCT cmat.material_type) as materials_collected
            FROM collector_applications ca
            LEFT JOIN collector_areas cam ON ca.id = cam.application_id
            LEFT JOIN collector_materials cmat ON ca.id = cmat.application_id
            GROUP BY ca.id
            ORDER BY 
                CASE ca.status 
                    WHEN 'pending' THEN 1 
                    WHEN 'approved' THEN 2 
                    WHEN 'rejected' THEN 3 
                END,
                ca.created_at DESC
        ");
        $appsStmt->execute();
        $applications = $appsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended
            FROM collectors
        ");
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        // Get application stats
        $appStatsStmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_applications,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_applications,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_applications,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications
            FROM collector_applications
        ");
        $appStatsStmt->execute();
        $appStats = $appStatsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'collectors' => $collectors,
            'applications' => $applications,
            'stats' => $stats,
            'applicationStats' => $appStats
        ]);
    }
    
    // POST request - Update collector status OR handle application
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Handle application approval/rejection
        if (isset($input['application_id']) && isset($input['action'])) {
            $appId = $input['application_id'];
            $action = $input['action']; // 'approve' or 'reject'
            
            if (!in_array($action, ['approve', 'reject'])) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
                exit;
            }
            
            if ($action === 'approve') {
                // Get application details
                $appStmt = $conn->prepare("
                    SELECT 
                        ca.*,
                        GROUP_CONCAT(DISTINCT cam.area_name) as service_areas,
                        GROUP_CONCAT(DISTINCT cmat.material_type) as materials_collected
                    FROM collector_applications ca
                    LEFT JOIN collector_areas cam ON ca.id = cam.application_id
                    LEFT JOIN collector_materials cmat ON ca.id = cmat.application_id
                    WHERE ca.id = ?
                    GROUP BY ca.id
                ");
                $appStmt->execute([$appId]);
                $application = $appStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$application) {
                    echo json_encode(['status' => 'error', 'message' => 'Application not found']);
                    exit;
                }
                
                // Check if user already has a collector account
                $checkStmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
                $checkStmt->execute([$application['phone']]);
                $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                $userId = null;
                
                if ($existingUser) {
                    $userId = $existingUser['id'];
                } else {
                    // Create new user account for the collector
                    $userStmt = $conn->prepare("
                        INSERT INTO users (name, phone, email, password, role, created_at) 
                        VALUES (?, ?, ?, ?, 'collector', NOW())
                    ");
                    // Generate a temporary password (they should change it on first login)
                    $tempPassword = password_hash('collector' . rand(1000, 9999), PASSWORD_DEFAULT);
                    $email = strtolower(str_replace(' ', '', $application['name'])) . '@collector.local';
                    $userStmt->execute([
                        $application['name'],
                        $application['phone'],
                        $email,
                        $tempPassword
                    ]);
                    $userId = $conn->lastInsertId();
                }
                
                // Convert comma-separated strings to JSON arrays
                $materialsArray = $application['materials_collected'] 
                    ? explode(',', $application['materials_collected']) 
                    : [];
                $areasArray = $application['service_areas'] 
                    ? explode(',', $application['service_areas']) 
                    : [];
                
                $materialsJson = json_encode(array_map('trim', $materialsArray));
                $areasJson = json_encode(array_map('trim', $areasArray));
                
                // Create collector record
                $collectorStmt = $conn->prepare("
                    INSERT INTO collectors (
                        user_id, name, phone, id_number, vehicle_type, vehicle_registration,
                        materials_collected, service_areas, status, license_file, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved', '', NOW())
                ");
                
                $collectorStmt->execute([
                    $userId,
                    $application['name'],
                    $application['phone'],
                    $application['id_number'],
                    $application['vehicle_type'],
                    $application['vehicle_reg'],
                    $materialsJson,
                    $areasJson,
                ]);
                
                $collectorId = $conn->lastInsertId();
                
                // Update user role to collector
                $updateRoleStmt = $conn->prepare("UPDATE users SET role = 'collector' WHERE id = ?");
                $updateRoleStmt->execute([$userId]);
                
                // Create initial location entry from application data
                $latitude = $application['latitude'] ?: -1.286389; // Default to Nairobi if not provided
                $longitude = $application['longitude'] ?: 36.817223;
                
                $locationStmt = $conn->prepare("
                    INSERT INTO collector_locations (collector_id, latitude, longitude, timestamp)
                    VALUES (?, ?, ?, NOW())
                ");
                $locationStmt->execute([$collectorId, $latitude, $longitude]);
                
                // Update application status
                $updateAppStmt = $conn->prepare("UPDATE collector_applications SET status = 'approved' WHERE id = ?");
                $updateAppStmt->execute([$appId]);
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Application approved and collector account created successfully'
                ]);
            } else {
                // Reject application
                $reason = $input['reason'] ?? '';
                $stmt = $conn->prepare("
                    UPDATE collector_applications 
                    SET status = 'rejected', status_notes = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$reason, $appId]);
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Application rejected'
                ]);
            }
            exit;
        }
        
        // Handle collector status update
        if (!isset($input['id']) || !isset($input['status'])) {
            error_log('Missing fields in status update. Input: ' . json_encode($input));
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields (id and status)']);
            exit;
        }
        
        $allowedStatuses = ['active', 'pending', 'suspended', 'rejected'];
        if (!in_array($input['status'], $allowedStatuses)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
            exit;
        }
        
        // Map 'active' to 'approved' for database
        $dbStatus = $input['status'] === 'active' ? 'approved' : $input['status'];
        $reason = isset($input['reason']) ? $input['reason'] : '';
        
        $stmt = $conn->prepare("UPDATE collectors SET status = ? WHERE id = ?");
        $stmt->execute([$dbStatus, $input['id']]);
        
        // Optional: Log the action (if admin_logs table exists)
        try {
            $logDetails = ['new_status' => $input['status']];
            if ($reason) {
                $logDetails['reason'] = $reason;
            }
            
            $logStmt = $conn->prepare("
                INSERT INTO admin_logs (admin_id, action, target_type, target_id, details) 
                VALUES (?, 'status_update', 'collector', ?, ?)
            ");
            $logStmt->execute([
                $_SESSION['user_id'],
                $input['id'],
                json_encode($logDetails)
            ]);
        } catch (PDOException $logError) {
            // Silently fail if admin_logs table doesn't exist
            error_log("Admin log error (non-critical): " . $logError->getMessage());
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Collector status updated successfully'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
