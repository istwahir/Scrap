<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
session_start();

require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    // GET - Fetch drop-offs
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $conn->prepare("
            SELECT 
                d.*,
                COUNT(DISTINCT r.id) as collection_count
            FROM dropoffs d
            LEFT JOIN requests r ON r.dropoff_id = d.id
            GROUP BY d.id
            ORDER BY d.created_at DESC
        ");
        $stmt->execute();
        $dropoffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                (SELECT COUNT(*) FROM requests WHERE dropoff_id IS NOT NULL) as total_collections,
                (SELECT COUNT(*) FROM requests WHERE dropoff_id IS NOT NULL 
                 AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                 AND YEAR(created_at) = YEAR(CURRENT_DATE())) as month_collections
            FROM dropoffs
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
        
        if (!isset($input['name']) || !isset($input['location'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            exit;
        }
        
        if (!empty($input['id'])) {
            // Update existing
            $stmt = $conn->prepare("
                UPDATE dropoffs 
                SET name = ?, location = ?, latitude = ?, longitude = ?, 
                    contact = ?, hours = ?, description = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $input['name'],
                $input['location'],
                $input['latitude'] ?: null,
                $input['longitude'] ?: null,
                $input['contact'] ?: null,
                $input['hours'] ?: null,
                $input['description'] ?: null,
                $input['status'] ?: 'active',
                $input['id']
            ]);
            $message = 'Drop-off point updated successfully';
        } else {
            // Create new
            $stmt = $conn->prepare("
                INSERT INTO dropoffs (name, location, latitude, longitude, contact, hours, description, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $input['name'],
                $input['location'],
                $input['latitude'] ?: null,
                $input['longitude'] ?: null,
                $input['contact'] ?: null,
                $input['hours'] ?: null,
                $input['description'] ?: null,
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
        
        $stmt = $conn->prepare("DELETE FROM dropoffs WHERE id = ?");
        $stmt->execute([$input['id']]);
        
        echo json_encode(['status' => 'success', 'message' => 'Drop-off point deleted successfully']);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
