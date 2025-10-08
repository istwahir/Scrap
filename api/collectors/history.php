<?php
require_once '../../config.php';
header('Content-Type: application/json');
session_start();

// Check if user is logged in and is a collector
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    $collector_id = $_SESSION['user_id'];
    $period = $_GET['period'] ?? 'month';

    // Define date range based on period
    switch ($period) {
        case 'week':
            $interval = '7 DAY';
            break;
        case 'month':
            $interval = '1 MONTH';
            break;
        case 'year':
            $interval = '1 YEAR';
            break;
        default:
            throw new Exception('Invalid period specified');
    }

    // Get collection history for the specified period
    $history_query = "SELECT 
                        DATE_FORMAT(c.collection_date, '%Y-%m-%d') as date,
                        c.material_type,
                        c.weight,
                        c.amount,
                        c.rating
                     FROM collections c
                     WHERE c.collector_id = ? 
                     AND c.collection_date >= DATE_SUB(CURDATE(), INTERVAL " . $interval . ")
                     AND c.status = 'completed'
                     ORDER BY c.collection_date DESC";
    
    $stmt = $pdo->prepare($history_query);
    $stmt->execute([$collector_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'history' => $history
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>