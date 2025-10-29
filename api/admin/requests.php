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
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Single request details
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("
                SELECT 
                    r.*,
                    u.name as user_name, u.phone as user_phone, u.email as user_email,
                    c.id as collector_id,
                    cu.name as collector_name
                FROM requests r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN collectors c ON r.collector_id = c.id
                LEFT JOIN users cu ON c.user_id = cu.id
                WHERE r.id = ?
            ");
            $stmt->execute([$_GET['id']]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($request) {
                echo json_encode([
                    'status' => 'success',
                    'request' => $request
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Request not found']);
            }
            exit;
        }
        
        // All requests list
        $stmt = $conn->prepare("
            SELECT 
                r.id, r.user_id, r.collector_id, r.material, r.weight, 
                r.location, r.status, r.created_at, r.completed_at,
                r.description,
                u.name as user_name, u.phone as user_phone,
                cu.name as collector_name
            FROM requests r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN collectors c ON r.collector_id = c.id
            LEFT JOIN users cu ON c.user_id = cu.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status IN ('accepted', 'in_progress') THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM requests
        ");
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'requests' => $requests,
            'stats' => $stats
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
