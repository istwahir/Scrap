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

// Get dashboard data
try {
    $conn = getDBConnection();

    // Get total collection requests
    $stmt = $conn->query("SELECT COUNT(*) FROM collection_requests");
    $totalCollections = $stmt->fetchColumn() ?: 0;

    // Get completed collections count
    $stmt = $conn->query("SELECT COUNT(*) FROM collection_requests WHERE status = 'completed'");
    $completedCollections = $stmt->fetchColumn() ?: 0;

    // Get collectors stats
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN active_status = 'online' OR active_status = 'on_job' THEN 1 ELSE 0 END) as active
        FROM collectors
    ");
    $collectorStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get pending applications
    $stmt = $conn->query("SELECT COUNT(*) FROM collector_applications WHERE status = 'pending'");
    $pendingApplications = $stmt->fetchColumn() ?: 0;

    // Get total rewards points
    $stmt = $conn->query("SELECT COALESCE(SUM(points), 0) FROM rewards WHERE redeemed = FALSE");
    $totalRewards = $stmt->fetchColumn() ?: 0;

    // Get collection trends (last 30 days)
    $stmt = $conn->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM collection_requests 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $trends = [
        'labels' => [],
        'collections' => []
    ];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $trends['labels'][] = date('M j', strtotime($row['date']));
        $trends['collections'][] = (int)$row['count'];
    }
    
    // If no data, add placeholder
    if (empty($trends['labels'])) {
        $trends['labels'] = ['No Data'];
        $trends['collections'] = [0];
    }

    // Get materials distribution
    $stmt = $conn->query("
        SELECT 
            materials,
            COUNT(*) as count
        FROM collection_requests
        WHERE status IN ('completed', 'assigned', 'en_route')
        GROUP BY materials
    ");
    $materialsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $materials = [
        'labels' => [],
        'values' => []
    ];
    
    if (!empty($materialsData)) {
        foreach ($materialsData as $row) {
            // materials is a SET type, may contain multiple comma-separated values
            $materialsList = explode(',', $row['materials']);
            foreach ($materialsList as $mat) {
                $materials['labels'][] = ucfirst(trim($mat));
                $materials['values'][] = (int)$row['count'];
            }
        }
    } else {
        $materials['labels'] = ['No Data'];
        $materials['values'] = [0];
    }

    // Get pending reviews
    $stmt = $conn->query("
        SELECT 
            id,
            name as description,
            'Collector Application' as type,
            created_at
        FROM collector_applications 
        WHERE status = 'pending'
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $pendingReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get admin info
    $stmt = $conn->prepare("
        SELECT name FROM users 
        WHERE id = ? AND role = 'admin'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate growth rate (compare this month to last month)
    $stmt = $conn->query("
        SELECT 
            SUM(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) as current_month,
            SUM(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) THEN 1 ELSE 0 END) as last_month
        FROM collection_requests
        WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 2 MONTH)
    ");
    $growthData = $stmt->fetch(PDO::FETCH_ASSOC);
    $collectionGrowth = 0;
    if ($growthData['last_month'] > 0) {
        $collectionGrowth = (($growthData['current_month'] - $growthData['last_month']) / $growthData['last_month']) * 100;
    }

    // Return dashboard data
    echo json_encode([
        'status' => 'success',
        'stats' => [
            'total_collections' => (int)$totalCollections,
            'collection_growth' => round($collectionGrowth, 1),
            'active_collectors' => (int)($collectorStats['active'] ?? 0),
            'total_collectors' => (int)($collectorStats['total'] ?? 0),
            'pending_approvals' => (int)$pendingApplications,
            'total_rewards' => (int)$totalRewards
        ],
        'trends' => $trends,
        'materials' => $materials,
        'pending_reviews' => $pendingReviews,
        'admin' => $admin
    ]);

} catch (PDOException $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error',
        'debug' => $e->getMessage() // Remove this in production
    ]);
} catch (Exception $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error',
        'debug' => $e->getMessage() // Remove this in production
    ]);
}