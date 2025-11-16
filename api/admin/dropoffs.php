<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
session_start();

require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Get database connection
    $conn = getDBConnection();
    
    // GET - Fetch drop-offs
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $conn->prepare("
            SELECT 
                d.*,
                CASE 
                    WHEN d.added_by_role = 'collector' THEN c.user_id
                    WHEN d.added_by_role = 'admin' THEN d.added_by
                    ELSE NULL
                END as user_ref,
                CASE 
                    WHEN d.added_by_role = 'collector' THEN u_collector.name
                    WHEN d.added_by_role = 'admin' THEN u_admin.name
                    ELSE 'System'
                END as added_by_name,
                d.added_by_role,
                COUNT(DISTINCT r.id) as collection_count
            FROM dropoff_points d
            LEFT JOIN collectors c ON d.added_by = c.id AND d.added_by_role = 'collector'
            LEFT JOIN users u_collector ON c.user_id = u_collector.id
            LEFT JOIN users u_admin ON d.added_by = u_admin.id AND d.added_by_role = 'admin'
            LEFT JOIN collection_requests r ON r.dropoff_point_id = d.id
            GROUP BY d.id, d.photo_url, d.added_by, d.added_by_role, u_collector.name, u_admin.name
            ORDER BY d.created_at DESC
        ");
        $stmt->execute();
        $dropoffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                (SELECT COUNT(*) FROM collection_requests WHERE dropoff_point_id IS NOT NULL) as total_collections,
                (SELECT COUNT(*) FROM collection_requests WHERE dropoff_point_id IS NOT NULL 
                 AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                 AND YEAR(created_at) = YEAR(CURRENT_DATE())) as month_collections
            FROM dropoff_points
        ");
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'dropoffs' => $dropoffs,
            'stats' => $stats
        ]);
    }
    
    // POST - Create or Update drop-off
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['name']) || !isset($input['address'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            exit;
        }
        
        if (!empty($input['id'])) {
            // Update existing
            $stmt = $conn->prepare("
                UPDATE dropoff_points 
                SET name = ?, address = ?, lat = ?, lng = ?, 
                    contact_phone = ?, operating_hours = ?, materials = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $input['name'],
                $input['address'],
                $input['lat'] ?: null,
                $input['lng'] ?: null,
                $input['contact_phone'] ?: null,
                $input['operating_hours'] ?: null,
                $input['materials'] ?: null,
                $input['status'] ?: 'active',
                $input['id']
            ]);
            $message = 'Drop-off point updated successfully';
        } else {
            // Create new
            $stmt = $conn->prepare("
                INSERT INTO dropoff_points (name, address, lat, lng, contact_phone, operating_hours, materials, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $input['name'],
                $input['address'],
                $input['lat'] ?: null,
                $input['lng'] ?: null,
                $input['contact_phone'] ?: null,
                $input['operating_hours'] ?: null,
                $input['materials'] ?: null,
                $input['status'] ?: 'active'
            ]);
            $message = 'Drop-off point created successfully';
        }
        
        echo json_encode(['status' => 'success', 'message' => $message]);
    }
    
        // DELETE - Remove drop-off
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing drop-off ID']);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM dropoff_points WHERE id = ?");
        $stmt->execute([$input['id']]);
        
        echo json_encode(['status' => 'success', 'message' => 'Drop-off point deleted successfully']);
    }
    
} catch (PDOException $e) {
    error_log("Admin Dropoffs API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Exception $e) {
    error_log("Admin Dropoffs API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
