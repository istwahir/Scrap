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
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get collector details for this logged-in user
    $stmt = $pdo->prepare('
        SELECT c.*
        FROM collectors c
        WHERE c.user_id = ?
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $collector = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$collector) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'User is not a collector']);
        exit;
    }

    // Today's stats (from completed requests today)
    $stmt = $pdo->prepare('
        SELECT
            COUNT(*) AS collection_count,
            0 AS total_earnings,
            COALESCE(SUM(estimated_weight), 0) AS total_weight
        FROM collection_requests
        WHERE collector_id = ?
          AND status = "completed"
          AND DATE(updated_at) = CURRENT_DATE
    ');
    $stmt->execute([$collector['id']]);
    $todayStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['collection_count' => 0, 'total_earnings' => 0, 'total_weight' => 0];

    // Active requests (assigned or en_route)
    $stmt = $pdo->prepare('
        SELECT r.*, u.name as customer_name
        FROM collection_requests r
        JOIN users u ON r.user_id = u.id
        WHERE r.collector_id = ?
          AND r.status IN ("assigned","en_route")
        ORDER BY r.created_at ASC
    ');
    $stmt->execute([$collector['id']]);
    $activeRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pending requests (awaiting action by this collector)
    $stmt = $pdo->prepare('
        SELECT r.*, u.name as customer_name
        FROM collection_requests r
        JOIN users u ON r.user_id = u.id
        WHERE r.collector_id = ?
          AND r.status = "pending"
        ORDER BY r.created_at DESC
    ');
    $stmt->execute([$collector['id']]);
    $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent history (completed)
    $stmt = $pdo->prepare('
        SELECT r.*, u.name as customer_name
        FROM collection_requests r
        JOIN users u ON r.user_id = u.id
        WHERE r.collector_id = ?
          AND r.status = "completed"
        ORDER BY r.updated_at DESC
        LIMIT 20
    ');
    $stmt->execute([$collector['id']]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Earnings trend placeholder (7 days - using weight as proxy, earnings=0)
    $stmt = $pdo->prepare('
        SELECT
            DATE(updated_at) as date,
            0 as daily_earnings,
            COALESCE(SUM(estimated_weight),0) as daily_weight
        FROM collection_requests
        WHERE collector_id = ?
          AND status = "completed"
          AND updated_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
        GROUP BY DATE(updated_at)
        ORDER BY date ASC
    ');
    $stmt->execute([$collector['id']]);
    $earningsTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Materials breakdown (completed in last 30 days)
    $stmt = $pdo->prepare('
        SELECT COALESCE(materials,"unknown") as material_type, COUNT(*) as count
        FROM collection_requests
        WHERE collector_id = ?
          AND status = "completed"
          AND updated_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
        GROUP BY COALESCE(materials,"unknown")
    ');
    $stmt->execute([$collector['id']]);
    $materialsBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Analytics
    $stmt = $pdo->prepare('SELECT DATE(created_at) as day, COUNT(*) as cnt FROM collection_requests WHERE collector_id = ? AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 14 DAY) GROUP BY DATE(created_at) ORDER BY day ASC');
    $stmt->execute([$collector['id']]);
    $requestsTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT status, COUNT(*) as cnt FROM collection_requests WHERE collector_id = ? AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) GROUP BY status');
    $stmt->execute([$collector['id']]);
    $statusDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT COALESCE(materials,"unknown") as material_type, COUNT(*) as cnt FROM collection_requests WHERE collector_id = ? AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) GROUP BY COALESCE(materials,"unknown")');
    $stmt->execute([$collector['id']]);
    $requestMaterials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fill missing days for requests trend (14 days)
    $trendMap = [];
    foreach ($requestsTrend as $row) { $trendMap[$row['day']] = (int)$row['cnt']; }
    $trendLabels = [];
    $trendValues = [];
    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime('-' . $i . ' day'));
        $trendLabels[] = date('M j', strtotime($d));
        $trendValues[] = $trendMap[$d] ?? 0;
    }

    // Arrays for distributions
    $statusLabels = array_map(function($r) { return $r['status']; }, $statusDistribution);
    $statusValues = array_map(function($r) { return (int)$r['cnt']; }, $statusDistribution);
    $reqMatLabels = array_map(function($r) { return $r['material_type']; }, $requestMaterials);
    $reqMatValues = array_map(function($r) { return (int)$r['cnt']; }, $requestMaterials);

    // Get vehicle info
    $vehicle = ['type' => $collector['vehicle_type'] ?? 'N/A', 'registration' => $collector['vehicle_registration'] ?? 'N/A', 'materials' => []];
    
    // Get materials this collector handles from JSON field
    if (!empty($collector['materials_collected'])) {
        $materialsJson = json_decode($collector['materials_collected'], true);
        if (is_array($materialsJson)) {
            $vehicle['materials'] = $materialsJson;
        }
    }
    
    // Get service areas from JSON field
    $areas = [];
    if (!empty($collector['service_areas'])) {
        $areasJson = json_decode($collector['service_areas'], true);
        if (is_array($areasJson)) {
            $areas = $areasJson;
        }
    }

    // Format response
    echo json_encode([
        'status' => 'success',
        'stats' => [
            'name' => isset($collector['name']) ? $collector['name'] : '—',
            'today_collections' => (int)$todayStats['collection_count'],
            'today_earnings' => (float)$todayStats['total_earnings'],
            'rating' => isset($collector['rating']) ? (float)$collector['rating'] : 0,
            'total_weight' => (float)$todayStats['total_weight'],
            'active_status' => isset($collector['active_status']) ? $collector['active_status'] : 'offline'
        ],
        'activeRequests' => array_map(function($r) {
            return [
                'id' => (int)$r['id'],
                'customer_name' => $r['customer_name'],
                'material_type' => isset($r['materials']) ? $r['materials'] : 'Mixed',
                'address' => isset($r['pickup_address']) ? $r['pickup_address'] : '',
                'latitude' => isset($r['latitude']) ? (float)$r['latitude'] : null,
                'longitude' => isset($r['longitude']) ? (float)$r['longitude'] : null,
            ];
        }, $activeRequests),
        'pendingRequests' => array_map(function($r) {
            return [
                'id' => (int)$r['id'],
                'customer_name' => $r['customer_name'],
                'material_type' => isset($r['materials']) ? $r['materials'] : 'Mixed',
                'address' => isset($r['pickup_address']) ? $r['pickup_address'] : '',
                'created_at' => !empty($r['created_at']) ? date('M j, Y g:i A', strtotime($r['created_at'])) : ''
            ];
        }, $pendingRequests),
        'history' => array_map(function($r) {
            return [
                'id' => (int)$r['id'],
                'customer_name' => $r['customer_name'],
                'material_type' => isset($r['materials']) ? $r['materials'] : 'Mixed',
                'weight' => isset($r['estimated_weight']) ? (float)$r['estimated_weight'] : 0,
                'amount' => 0,
                'address' => isset($r['pickup_address']) ? $r['pickup_address'] : '',
                'completed_at' => isset($r['updated_at']) && !empty($r['updated_at']) ? date('M j, Y g:i A', strtotime($r['updated_at'])) : ''
            ];
        }, $history),
        'earnings' => [
            'trend' => [
                'labels' => array_map(function($row) { return date('M j', strtotime($row['date'])); }, $earningsTrend),
                'values' => array_map(function($row) { return (float)$row['daily_earnings']; }, $earningsTrend)
            ],
            'materials' => [
                'labels' => array_map(function($row) { return $row['material_type']; }, $materialsBreakdown),
                'values' => array_map(function($row) { return (int)$row['count']; }, $materialsBreakdown)
            ]
        ],
        'analytics' => [
            'requests_trend' => [ 'labels' => $trendLabels, 'values' => $trendValues ],
            'status_distribution' => [ 'labels' => $statusLabels, 'values' => $statusValues ],
            'request_materials' => [ 'labels' => $reqMatLabels, 'values' => $reqMatValues ]
        ],
        'vehicle' => $vehicle,
        'areas' => $areas
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}