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
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    
    // Handle export requests
    if (isset($_GET['export'])) {
        $format = $_GET['export'];
        exportReport($conn, $format, $startDate, $endDate);
        exit;
    }
    
    // Overview Statistics
    $overviewStmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT r.id) as total_collections,
            COALESCE(SUM(r.payment_amount), 0) as total_revenue,
            COUNT(DISTINCT u.id) as active_users,
            (SELECT COUNT(*) FROM requests WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?) as period_collections,
            (SELECT COUNT(*) FROM requests WHERE DATE(created_at) >= DATE_SUB(?, INTERVAL 30 DAY) AND DATE(created_at) < ?) as prev_period_collections
        FROM requests r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?
    ");
    $overviewStmt->execute([$startDate, $endDate, $startDate, $startDate, $startDate, $endDate]);
    $overview = $overviewStmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate growth rate
    $growth_rate = 0;
    if ($overview['prev_period_collections'] > 0) {
        $growth_rate = (($overview['period_collections'] - $overview['prev_period_collections']) / $overview['prev_period_collections']) * 100;
    }
    $overview['growth_rate'] = number_format($growth_rate, 1);
    
    // Timeline data (daily aggregation)
    $timelineStmt = $conn->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as collections,
            COALESCE(SUM(payment_amount), 0) as revenue
        FROM requests
        WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $timelineStmt->execute([$startDate, $endDate]);
    $timelineData = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $timeline = [
        'labels' => array_map(function($item) { return date('M d', strtotime($item['date'])); }, $timelineData),
        'collections' => array_map(function($item) { return (int)$item['collections']; }, $timelineData),
        'revenue' => array_map(function($item) { return (float)$item['revenue']; }, $timelineData)
    ];
    
    // Materials distribution
    $materialsStmt = $conn->prepare("
        SELECT 
            material as type,
            COUNT(*) as count,
            COALESCE(SUM(weight), 0) as weight
        FROM requests
        WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?
        GROUP BY material
        ORDER BY count DESC
    ");
    $materialsStmt->execute([$startDate, $endDate]);
    $materialsData = $materialsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $materials = [
        'labels' => array_map(function($item) { return ucfirst($item['type']); }, $materialsData),
        'values' => array_map(function($item) { return (int)$item['count']; }, $materialsData)
    ];
    
    // Top collectors
    $collectorsStmt = $conn->prepare("
        SELECT 
            u.name,
            COUNT(r.id) as collections,
            COALESCE(SUM(r.payment_amount), 0) as earnings
        FROM collectors c
        LEFT JOIN users u ON c.user_id = u.id
        LEFT JOIN requests r ON r.collector_id = c.id 
            AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?
        WHERE c.status = 'active'
        GROUP BY c.id
        HAVING collections > 0
        ORDER BY collections DESC
        LIMIT 10
    ");
    $collectorsStmt->execute([$startDate, $endDate]);
    $top_collectors = $collectorsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // User activity
    $userActivityStmt = $conn->prepare("
        SELECT 
            u.name,
            COUNT(r.id) as requests,
            COALESCE(u.points, 0) as points
        FROM users u
        LEFT JOIN requests r ON r.user_id = u.id 
            AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?
        WHERE u.role = 'user'
        GROUP BY u.id
        HAVING requests > 0
        ORDER BY requests DESC
        LIMIT 10
    ");
    $userActivityStmt->execute([$startDate, $endDate]);
    $user_activity = $userActivityStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'overview' => $overview,
        'timeline' => $timeline,
        'materials' => $materials,
        'material_stats' => $materialsData,
        'top_collectors' => $top_collectors,
        'user_activity' => $user_activity
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

function exportReport($conn, $format, $startDate, $endDate) {
    // Get report data
    $stmt = $conn->prepare("
        SELECT 
            r.id,
            r.created_at,
            u.name as user_name,
            cu.name as collector_name,
            r.material,
            r.weight,
            r.status,
            r.payment_amount
        FROM requests r
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN collectors c ON r.collector_id = c.id
        LEFT JOIN users cu ON c.user_id = cu.id
        WHERE DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report_' . $startDate . '_to_' . $endDate . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Date', 'User', 'Collector', 'Material', 'Weight', 'Status', 'Payment']);
        
        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['created_at'],
                $row['user_name'],
                $row['collector_name'] ?? 'N/A',
                $row['material'],
                $row['weight'] ?? 'N/A',
                $row['status'],
                $row['payment_amount'] ?? 'N/A'
            ]);
        }
        
        fclose($output);
    } elseif ($format === 'pdf') {
        // For PDF export, you would use a library like TCPDF or FPDF
        // This is a simplified version that returns JSON data
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'info',
            'message' => 'PDF export requires additional PDF library. Use CSV export instead.',
            'data' => $data
        ]);
    }
}
