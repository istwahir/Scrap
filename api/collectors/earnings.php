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

    // Get collector details
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

    $period = $_GET['period'] ?? 'monthly';
    
    // Determine date range and group format based on period
    $dateCondition = '';
    $groupBy = '';
    $dateFormat = '';
    
    switch ($period) {
        case 'daily':
            $dateCondition = 'AND DATE(COALESCE(completed_at, updated_at, created_at)) >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)';
            $groupBy = 'DATE(COALESCE(completed_at, updated_at, created_at))';
            $dateFormat = '%b %d';
            break;
        case 'weekly':
            $dateCondition = 'AND YEARWEEK(COALESCE(completed_at, updated_at, created_at), 1) >= YEARWEEK(DATE_SUB(CURRENT_DATE, INTERVAL 12 WEEK), 1)';
            $groupBy = 'YEARWEEK(COALESCE(completed_at, updated_at, created_at), 1)';
            $dateFormat = 'Week %U';
            break;
        case 'yearly':
            $dateCondition = 'AND YEAR(COALESCE(completed_at, updated_at, created_at)) >= YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 5 YEAR))';
            $groupBy = 'YEAR(COALESCE(completed_at, updated_at, created_at))';
            $dateFormat = '%Y';
            break;
        case 'monthly':
        default:
            $dateCondition = 'AND COALESCE(completed_at, updated_at, created_at) >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)';
            $groupBy = 'DATE_FORMAT(COALESCE(completed_at, updated_at, created_at), "%Y-%m")';
            $dateFormat = '%b %Y';
            break;
    }

    // Get summary stats
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_collections,
            0 as total_earnings,
            COALESCE(SUM(estimated_weight), 0) as total_weight
        FROM collection_requests
        WHERE collector_id = ?
          AND status = 'completed'
          $dateCondition
    ");
    $stmt->execute([$collector['id']]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    $summary['avg_earning'] = $summary['total_collections'] > 0 
        ? round($summary['total_earnings'] / $summary['total_collections'], 2) 
        : 0;

    // Get earnings trend
    $stmt = $pdo->prepare("
        SELECT
            $groupBy as period,
            DATE_FORMAT(MIN(COALESCE(completed_at, updated_at, created_at)), '$dateFormat') as label,
            0 as earnings,
            COALESCE(SUM(estimated_weight), 0) as weight
        FROM collection_requests
        WHERE collector_id = ?
          AND status = 'completed'
          $dateCondition
        GROUP BY $groupBy
        ORDER BY period ASC
    ");
    $stmt->execute([$collector['id']]);
    $trend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get material breakdown
    $stmt = $pdo->prepare("
        SELECT
            COALESCE(materials, 'Unknown') as material_type,
            0 as earnings,
            COUNT(*) as count
        FROM collection_requests
        WHERE collector_id = ?
          AND status = 'completed'
          $dateCondition
        GROUP BY COALESCE(materials, 'Unknown')
    ");
    $stmt->execute([$collector['id']]);
    $materialBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get earnings history
    $stmt = $pdo->prepare("
        SELECT
            DATE_FORMAT(COALESCE(r.completed_at, r.updated_at, r.created_at), '%b %d, %Y') as date,
            u.name as customer_name,
            COALESCE(r.materials, 'Mixed') as material_type,
            COALESCE(r.estimated_weight, 0) as weight,
            0 as amount
        FROM collection_requests r
        JOIN users u ON r.user_id = u.id
        WHERE r.collector_id = ?
          AND r.status = 'completed'
          $dateCondition
        ORDER BY COALESCE(r.completed_at, r.updated_at, r.created_at) DESC
        LIMIT 50
    ");
    $stmt->execute([$collector['id']]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'summary' => [
            'total_earnings' => (float)$summary['total_earnings'],
            'total_collections' => (int)$summary['total_collections'],
            'avg_earning' => (float)$summary['avg_earning'],
            'total_weight' => (float)$summary['total_weight']
        ],
        'trend' => [
            'labels' => array_column($trend, 'label'),
            'values' => array_map('floatval', array_column($trend, 'earnings'))
        ],
        'materialBreakdown' => [
            'labels' => array_column($materialBreakdown, 'material_type'),
            'values' => array_map('floatval', array_column($materialBreakdown, 'earnings'))
        ],
        'history' => $history
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
