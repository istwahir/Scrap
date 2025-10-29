<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
session_start();

require_once __DIR__ . '/../../config.php';

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
                    u.name, u.email, u.phone, u.created_at,
                    ca.id_number, ca.address, ca.age,
                    ca.vehicle_type, ca.vehicle_registration as vehicle_reg,
                    COUNT(DISTINCT r.id) as total_collections,
                    COUNT(DISTINCT CASE WHEN r.status = 'completed' THEN r.id END) as completed_collections,
                    AVG(CASE WHEN r.rating > 0 THEN r.rating END) as rating
                FROM collectors c
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN collector_applications ca ON c.id = ca.collector_id
                LEFT JOIN requests r ON r.collector_id = c.id
                WHERE c.id = ?
                GROUP BY c.id
            ");
            $stmt->execute([$_GET['id']]);
            $collector = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($collector) {
                $collector['rating'] = $collector['rating'] ? number_format($collector['rating'], 1) : '5.0';
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
                c.id, c.user_id, c.status,
                u.name, u.email, u.phone, u.created_at,
                ca.vehicle_type, ca.vehicle_registration as vehicle_reg,
                COUNT(DISTINCT r.id) as total_collections,
                AVG(CASE WHEN r.rating > 0 THEN r.rating END) as rating
            FROM collectors c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN collector_applications ca ON c.id = ca.collector_id
            LEFT JOIN requests r ON r.collector_id = c.id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute();
        $collectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format collectors data
        foreach ($collectors as &$collector) {
            $collector['rating'] = $collector['rating'] ? number_format($collector['rating'], 1) : '5.0';
            $collector['total_collections'] = (int)$collector['total_collections'];
        }
        
        // Get statistics
        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended
            FROM collectors
        ");
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'collectors' => $collectors,
            'stats' => $stats
        ]);
    }
    
    // POST request - Update collector status
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id']) || !isset($input['status'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            exit;
        }
        
        $allowedStatuses = ['active', 'pending', 'suspended', 'rejected'];
        if (!in_array($input['status'], $allowedStatuses)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE collectors SET status = ? WHERE id = ?");
        $stmt->execute([$input['status'], $input['id']]);
        
        // Log the action
        $logStmt = $conn->prepare("
            INSERT INTO admin_logs (admin_id, action, target_type, target_id, details) 
            VALUES (?, 'status_update', 'collector', ?, ?)
        ");
        $logStmt->execute([
            $_SESSION['user_id'],
            $input['id'],
            json_encode(['new_status' => $input['status']])
        ]);
        
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
