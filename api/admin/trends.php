<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
session_start();

require_once __DIR__ . '/../../config.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Get period from query string (default: month)
$period = $_GET['period'] ?? 'month';
$days = 30; // default

switch($period) {
    case 'week':
        $days = 7;
        break;
    case 'month':
        $days = 30;
        break;
    case 'year':
        $days = 365;
        break;
    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid period']);
        exit;
}

// Get collection trends
try {
    $conn = getDBConnection();

    // Query for collection requests trend
    $stmt = $conn->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM collection_requests 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $stmt->execute([$days]);

    $trends = [
        'labels' => [],
        'collections' => []
    ];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format date based on period
        if ($period === 'year') {
            $trends['labels'][] = date('M Y', strtotime($row['date']));
        } else {
            $trends['labels'][] = date('M j', strtotime($row['date']));
        }
        $trends['collections'][] = (int)$row['count'];
    }
    
    // If no data, provide placeholder
    if (empty($trends['labels'])) {
        $trends['labels'] = ['No Data'];
        $trends['collections'] = [0];
    }

    // Return trend data
    echo json_encode([
        'status' => 'success',
        'trends' => $trends,
        'period' => $period,
        'days' => $days
    ]);

} catch (PDOException $e) {
    error_log("Trends API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error',
        'debug' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Trends API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error',
        'debug' => $e->getMessage()
    ]);
}