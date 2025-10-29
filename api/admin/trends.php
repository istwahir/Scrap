<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Verify admin authentication
$auth = new AuthController();
if (!$auth->isAuthenticated() || !$auth->isAdmin()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Get period from query string (default: month)
$period = $_GET['period'] ?? 'month';
$interval = '30 DAY'; // default

switch($period) {
    case 'week':
        $interval = '7 DAY';
        break;
    case 'month':
        $interval = '30 DAY';
        break;
    case 'year':
        $interval = '365 DAY';
        break;
    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid period']);
        exit;
}

// Get collection trends
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query for collections trend
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM collections 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL ?)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $stmt->execute([$interval]);

    $trends = [
        'labels' => [],
        'collections' => []
    ];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $trends['labels'][] = date('M j', strtotime($row['date']));
        $trends['collections'][] = (int)$row['count'];
    }

    // Return trend data
    echo json_encode([
        'status' => 'success',
        'trends' => $trends
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}