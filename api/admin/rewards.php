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
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get all reward transactions (points earned/used by users)
        $stmt = $conn->prepare("
            SELECT 
                r.*,
                u.name as user_name,
                u.email as user_email
            FROM rewards r
            LEFT JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get user points summary
        $userPointsStmt = $conn->prepare("
            SELECT 
                u.id as user_id,
                u.name as user_name,
                u.email as user_email,
                COALESCE(SUM(CASE WHEN r.redeemed = 0 THEN r.points ELSE 0 END), 0) as available_points,
                COALESCE(SUM(CASE WHEN r.redeemed = 1 THEN r.points ELSE 0 END), 0) as redeemed_points,
                COALESCE(SUM(r.points), 0) as total_points
            FROM users u
            LEFT JOIN rewards r ON u.id = r.user_id
            WHERE u.role = 'citizen'
            GROUP BY u.id, u.name, u.email
            HAVING total_points > 0
            ORDER BY available_points DESC
            LIMIT 50
        ");
        $userPointsStmt->execute();
        $userPoints = $userPointsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(DISTINCT user_id) as total_users,
                COUNT(*) as total_transactions,
                COALESCE(SUM(CASE WHEN redeemed = 0 THEN points ELSE 0 END), 0) as total_available_points,
                COALESCE(SUM(CASE WHEN redeemed = 1 THEN points ELSE 0 END), 0) as total_redeemed_points,
                COALESCE(SUM(points), 0) as total_points_issued,
                COUNT(CASE WHEN activity_type = 'collection' THEN 1 END) as collection_rewards,
                COUNT(CASE WHEN activity_type = 'referral' THEN 1 END) as referral_rewards,
                COUNT(CASE WHEN activity_type = 'bonus' THEN 1 END) as bonus_rewards
            FROM rewards
        ");
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'rewards' => $rewards,
            'userPoints' => $userPoints,
            'stats' => $stats
        ]);
    }
    
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Award bonus points to a user
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['user_id']) || !isset($input['points'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields (user_id, points)']);
            exit;
        }
        
        $stmt = $conn->prepare("
            INSERT INTO rewards (user_id, points, activity_type, reference_id, redeemed)
            VALUES (?, ?, 'bonus', NULL, 0)
        ");
        $stmt->execute([
            $input['user_id'],
            $input['points']
        ]);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Bonus points awarded successfully'
        ]);
    }
    
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Delete a reward transaction
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing reward ID']);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM rewards WHERE id = ?");
        $stmt->execute([$input['id']]);
        
        echo json_encode(['status' => 'success', 'message' => 'Reward transaction deleted successfully']);
    }
    
} catch (PDOException $e) {
    error_log("Admin Rewards API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Exception $e) {
    error_log("Admin Rewards API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
