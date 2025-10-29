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
        // Get all rewards
        $stmt = $conn->prepare("SELECT * FROM rewards ORDER BY created_at DESC");
        $stmt->execute();
        $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get recent redemptions
        $redemptionsStmt = $conn->prepare("
            SELECT 
                rr.*, r.title as reward_title, r.points,
                u.name as user_name
            FROM reward_redemptions rr
            LEFT JOIN rewards r ON rr.reward_id = r.id
            LEFT JOIN users u ON rr.user_id = u.id
            ORDER BY rr.redeemed_at DESC
            LIMIT 20
        ");
        $redemptionsStmt->execute();
        $redemptions = $redemptionsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                (SELECT COUNT(*) FROM reward_redemptions) as total_redemptions,
                (SELECT SUM(points) FROM reward_redemptions rr 
                 JOIN rewards r ON rr.reward_id = r.id) as total_points_used
            FROM rewards
        ");
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'rewards' => $rewards,
            'redemptions' => $redemptions,
            'stats' => $stats
        ]);
    }
    
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['title']) || !isset($input['points'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            exit;
        }
        
        if (!empty($input['id'])) {
            // Update
            $stmt = $conn->prepare("
                UPDATE rewards 
                SET title = ?, description = ?, points = ?, stock = ?, image = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $input['title'],
                $input['description'],
                $input['points'],
                $input['stock'] ?: null,
                $input['image'] ?: null,
                $input['status'] ?: 'active',
                $input['id']
            ]);
            $message = 'Reward updated successfully';
        } else {
            // Create
            $stmt = $conn->prepare("
                INSERT INTO rewards (title, description, points, stock, image, status)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $input['title'],
                $input['description'],
                $input['points'],
                $input['stock'] ?: null,
                $input['image'] ?: null,
                $input['status'] ?: 'active'
            ]);
            $message = 'Reward created successfully';
        }
        
        echo json_encode(['status' => 'success', 'message' => $message]);
    }
    
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing reward ID']);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM rewards WHERE id = ?");
        $stmt->execute([$input['id']]);
        
        echo json_encode(['status' => 'success', 'message' => 'Reward deleted successfully']);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
