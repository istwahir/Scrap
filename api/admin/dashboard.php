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

// Get dashboard data
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get total collections
    $stmt = $pdo->query("SELECT COUNT(*) FROM collections");
    $totalCollections = $stmt->fetchColumn();

    // Get collections growth
    $stmt = $pdo->query("
        SELECT 
            (COUNT(*) - LAG(COUNT(*)) OVER ()) / LAG(COUNT(*)) OVER () * 100 as growth
        FROM collections 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 2 MONTH)
        GROUP BY MONTH(created_at)
        ORDER BY MONTH(created_at) DESC
        LIMIT 1
    ");
    $collectionGrowth = $stmt->fetchColumn() ?: 0;

    // Get collectors stats
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN last_active >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as active
        FROM collectors
    ");
    $collectorStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get pending approvals
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM (
            SELECT id FROM collector_applications WHERE status = 'pending'
            UNION ALL
            SELECT id FROM collection_reports WHERE status = 'pending'
            UNION ALL
            SELECT id FROM reward_claims WHERE status = 'pending'
        ) as pending
    ");
    $pendingApprovals = $stmt->fetchColumn();

    // Get total rewards
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM rewards WHERE status = 'paid'");
    $totalRewards = $stmt->fetchColumn();

    // Get collection trends (default: month)
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM collections 
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

    // Get materials distribution
    $stmt = $pdo->query("
        SELECT 
            material_type,
            COUNT(*) * 100.0 / (SELECT COUNT(*) FROM collections) as percentage
        FROM collections
        GROUP BY material_type
    ");
    $materials = [
        'percentages' => array_fill(0, 5, 0) // Initialize with zeros [plastic, paper, metal, glass, electronics]
    ];
    $materialIndex = [
        'plastic' => 0,
        'paper' => 1,
        'metal' => 2,
        'glass' => 3,
        'electronics' => 4
    ];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $index = $materialIndex[strtolower($row['material_type'])] ?? null;
        if ($index !== null) {
            $materials['percentages'][$index] = round($row['percentage'], 1);
        }
    }

    // Get pending reviews
    $stmt = $pdo->query("
        (SELECT 'Collector Application' as type, id, 
                CONCAT('New collector application from ', name) as description
         FROM collector_applications 
         WHERE status = 'pending'
         LIMIT 5)
        UNION ALL
        (SELECT 'Collection Report' as type, id,
                CONCAT('Collection report from ', collector_id) as description
         FROM collection_reports 
         WHERE status = 'pending'
         LIMIT 5)
        UNION ALL
        (SELECT 'Reward Claim' as type, id,
                CONCAT('Reward claim of KES ', amount) as description
         FROM reward_claims 
         WHERE status = 'pending'
         LIMIT 5)
        ORDER BY id DESC
        LIMIT 10
    ");
    $pendingReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get admin info
    $stmt = $pdo->prepare("
        SELECT name FROM users 
        WHERE id = ? AND role = 'admin'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // System status check
    $systemStatus = [
        'server_load' => sys_getloadavg()[0] * 100, // Convert load average to percentage
        'database_connected' => true,
        'mpesa_connected' => testMpesaConnection()
    ];

    // Return dashboard data
    echo json_encode([
        'status' => 'success',
        'stats' => [
            'total_collections' => $totalCollections,
            'collection_growth' => round($collectionGrowth, 1),
            'active_collectors' => $collectorStats['active'],
            'total_collectors' => $collectorStats['total'],
            'pending_approvals' => $pendingApprovals,
            'total_rewards' => $totalRewards
        ],
        'trends' => $trends,
        'materials' => $materials,
        'pending_reviews' => $pendingReviews,
        'admin' => $admin,
        'system' => $systemStatus
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}

// Helper function to test M-Pesa API connection
function testMpesaConnection() {
    // Implement actual M-Pesa connection test here
    // For now, return true as a placeholder
    return true;
}