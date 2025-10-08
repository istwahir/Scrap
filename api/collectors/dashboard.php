<?php
require_once '../../config.php';
require_once '../../controllers/AuthController.php';

header('Content-Type: application/json');

// Verify collector authentication
$auth = new AuthController();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get collector ID
    $stmt = $pdo->prepare("
        SELECT c.*, ca.name 
        FROM collectors c
        JOIN collector_applications ca ON c.application_id = ca.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $collector = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$collector) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'User is not a collector']);
        exit;
    }

    // Get today's stats
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as collection_count,
            COALESCE(SUM(amount), 0) as total_earnings,
            COALESCE(SUM(weight), 0) as total_weight
        FROM collections
        WHERE collector_id = ?
        AND DATE(completed_at) = CURRENT_DATE
    ");
    $stmt->execute([$collector['id']]);
    $todayStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get active requests
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as customer_name
        FROM collection_requests r
        JOIN users u ON r.user_id = u.id
        WHERE r.collector_id = ?
        AND r.status = 'accepted'
        ORDER BY r.created_at ASC
    ");
    $stmt->execute([$collector['id']]);
    $activeRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get pending requests
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as customer_name
        FROM collection_requests r
        JOIN users u ON r.user_id = u.id
        WHERE r.collector_id = ?
        AND r.status = 'pending'
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$collector['id']]);
    $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get collection history
    $stmt = $pdo->prepare("
        SELECT c.*, u.name as customer_name
        FROM collections c
        JOIN users u ON c.user_id = u.id
        WHERE c.collector_id = ?
        ORDER BY c.completed_at DESC
        LIMIT 20
    ");
    $stmt->execute([$collector['id']]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get earnings trend (last 7 days)
    $stmt = $pdo->prepare("
        SELECT 
            DATE(completed_at) as date,
            SUM(amount) as daily_earnings
        FROM collections
        WHERE collector_id = ?
        AND completed_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
        GROUP BY DATE(completed_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$collector['id']]);
    $earningsTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get materials breakdown
    $stmt = $pdo->prepare("
        SELECT 
            material_type,
            COUNT(*) as count
        FROM collections
        WHERE collector_id = ?
        AND completed_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
        GROUP BY material_type
    ");
    $stmt->execute([$collector['id']]);
    $materialsBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format response data
    $response = [
        'status' => 'success',
        'stats' => [
            'name' => $collector['name'],
            'today_collections' => $todayStats['collection_count'],
            'today_earnings' => $todayStats['total_earnings'],
            'rating' => $collector['rating'],
            'total_weight' => $todayStats['total_weight']
        ],
        'activeRequests' => array_map(function($request) {
            return [
                'id' => $request['id'],
                'customer_name' => $request['customer_name'],
                'material_type' => $request['material_type'],
                'address' => $request['address'],
                'latitude' => $request['latitude'],
                'longitude' => $request['longitude']
            ];
        }, $activeRequests),
        'pendingRequests' => array_map(function($request) {
            return [
                'id' => $request['id'],
                'customer_name' => $request['customer_name'],
                'material_type' => $request['material_type'],
                'address' => $request['address'],
                'created_at' => date('M j, Y g:i A', strtotime($request['created_at']))
            ];
        }, $pendingRequests),
        'history' => array_map(function($item) {
            return [
                'id' => $item['id'],
                'customer_name' => $item['customer_name'],
                'material_type' => $item['material_type'],
                'weight' => $item['weight'],
                'amount' => $item['amount'],
                'address' => $item['address'],
                'completed_at' => date('M j, Y g:i A', strtotime($item['completed_at']))
            ];
        }, $history),
        'earnings' => [
            'trend' => [
                'labels' => array_map(function($item) {
                    return date('M j', strtotime($item['date']));
                }, $earningsTrend),
                'values' => array_map(function($item) {
                    return $item['daily_earnings'];
                }, $earningsTrend)
            ],
            'materials' => [
                'labels' => array_map(function($item) {
                    return $item['material_type'];
                }, $materialsBreakdown),
                'values' => array_map(function($item) {
                    return $item['count'];
                }, $materialsBreakdown)
            ]
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}