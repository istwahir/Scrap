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
            COUNT(DISTINCT r.user_id) as active_users,
            (SELECT COUNT(*) FROM collection_requests WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?) as period_collections,
            (SELECT COUNT(*) FROM collection_requests WHERE DATE(created_at) >= DATE_SUB(?, INTERVAL 30 DAY) AND DATE(created_at) < ?) as prev_period_collections
        FROM collection_requests r
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
    
    // Get total points awarded (as proxy for revenue)
    $pointsStmt = $conn->prepare("
        SELECT COALESCE(SUM(points), 0) as total_points
        FROM rewards
        WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?
    ");
    $pointsStmt->execute([$startDate, $endDate]);
    $pointsData = $pointsStmt->fetch(PDO::FETCH_ASSOC);
    $overview['total_points'] = $pointsData['total_points'];
    
    // Timeline data (daily aggregation)
    $timelineStmt = $conn->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as collections
        FROM collection_requests
        WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $timelineStmt->execute([$startDate, $endDate]);
    $timelineData = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get points timeline
    $pointsTimelineStmt = $conn->prepare("
        SELECT 
            DATE(created_at) as date,
            COALESCE(SUM(points), 0) as points
        FROM rewards
        WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $pointsTimelineStmt->execute([$startDate, $endDate]);
    $pointsTimelineData = $pointsTimelineStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Merge timeline data
    $dateMap = [];
    foreach ($timelineData as $item) {
        $dateMap[$item['date']] = ['collections' => (int)$item['collections'], 'points' => 0];
    }
    foreach ($pointsTimelineData as $item) {
        if (isset($dateMap[$item['date']])) {
            $dateMap[$item['date']]['points'] = (float)$item['points'];
        } else {
            $dateMap[$item['date']] = ['collections' => 0, 'points' => (float)$item['points']];
        }
    }
    ksort($dateMap);
    
    $timeline = [
        'labels' => array_map(function($date) { return date('M d', strtotime($date)); }, array_keys($dateMap)),
        'collections' => array_map(function($item) { return $item['collections']; }, array_values($dateMap)),
        'points' => array_map(function($item) { return $item['points']; }, array_values($dateMap))
    ];
    
    // Materials distribution
    $materialsStmt = $conn->prepare("
        SELECT 
            materials as type,
            COUNT(*) as count,
            COALESCE(SUM(estimated_weight), 0) as weight
        FROM collection_requests
        WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?
        GROUP BY materials
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
            COALESCE(SUM(rw.points), 0) as points_awarded
        FROM collectors c
        LEFT JOIN users u ON c.user_id = u.id
        LEFT JOIN collection_requests r ON r.collector_id = c.id 
            AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?
        LEFT JOIN rewards rw ON rw.reference_id = r.id 
            AND rw.activity_type = 'collection'
        WHERE c.status = 'active'
        GROUP BY c.id, u.name
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
            u.email,
            COUNT(r.id) as requests,
            COALESCE(SUM(rw.points), 0) as points_earned
        FROM users u
        LEFT JOIN collection_requests r ON r.user_id = u.id 
            AND DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?
        LEFT JOIN rewards rw ON rw.user_id = u.id 
            AND DATE(rw.created_at) >= ? AND DATE(rw.created_at) <= ?
        WHERE u.role = 'citizen'
        GROUP BY u.id, u.name, u.email
        HAVING requests > 0
        ORDER BY requests DESC
        LIMIT 10
    ");
    $userActivityStmt->execute([$startDate, $endDate, $startDate, $endDate]);
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
    error_log("Admin Reports API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Admin Reports API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function exportReport($conn, $format, $startDate, $endDate) {
    // Get report data
    $stmt = $conn->prepare("
        SELECT 
            r.id,
            r.created_at,
            u.name as user_name,
            u.email as user_email,
            cu.name as collector_name,
            r.materials,
            r.estimated_weight,
            r.status,
            r.pickup_address,
            r.pickup_date
        FROM collection_requests r
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
        fputcsv($output, ['ID', 'Date', 'User', 'Email', 'Collector', 'Materials', 'Weight (kg)', 'Status', 'Pickup Address', 'Pickup Date']);
        
        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['created_at'],
                $row['user_name'],
                $row['user_email'],
                $row['collector_name'] ?? 'N/A',
                $row['materials'],
                $row['estimated_weight'] ?? 'N/A',
                $row['status'],
                $row['pickup_address'],
                $row['pickup_date'] ?? 'N/A'
            ]);
        }
        
        fclose($output);
    } elseif ($format === 'pdf') {
        // Load composer autoloader
        require_once(__DIR__ . '/../../vendor/autoload.php');
        
        // Get summary statistics
        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(DISTINCT r.id) as total_collections,
                COUNT(DISTINCT r.user_id) as total_users,
                COUNT(DISTINCT r.collector_id) as total_collectors,
                COALESCE(SUM(r.estimated_weight), 0) as total_weight
            FROM collection_requests r
            WHERE DATE(r.created_at) >= ? AND DATE(r.created_at) <= ?
        ");
        $statsStmt->execute([$startDate, $endDate]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        // Create PDF instance
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Kiambu Recycling Platform');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Collections Report - ' . $startDate . ' to ' . $endDate);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Title
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, 'Collection Requests Report', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 8, 'Period: ' . date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate)), 0, 1, 'C');
        $pdf->Cell(0, 8, 'Generated: ' . date('M d, Y h:i A'), 0, 1, 'C');
        
        $pdf->Ln(5);
        
        // Summary statistics box
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Summary Statistics', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetFillColor(240, 240, 240);
        
        // Create summary table
        $summaryHtml = '<table border="1" cellpadding="5" cellspacing="0">
            <tr style="background-color:#f0f0f0;font-weight:bold;">
                <td width="25%">Total Collections</td>
                <td width="25%">Active Users</td>
                <td width="25%">Active Collectors</td>
                <td width="25%">Total Weight (kg)</td>
            </tr>
            <tr>
                <td width="25%" align="center">' . number_format($stats['total_collections']) . '</td>
                <td width="25%" align="center">' . number_format($stats['total_users']) . '</td>
                <td width="25%" align="center">' . number_format($stats['total_collectors']) . '</td>
                <td width="25%" align="center">' . number_format($stats['total_weight'], 2) . '</td>
            </tr>
        </table>';
        
        $pdf->writeHTML($summaryHtml, true, false, true, false, '');
        
        $pdf->Ln(8);
        
        // Collections table
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Collection Requests Details', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        
        // Table header
        $html = '<table border="1" cellpadding="4" cellspacing="0">
            <thead>
                <tr style="background-color:#4a5568;color:#ffffff;font-weight:bold;">
                    <th width="6%">ID</th>
                    <th width="12%">Date</th>
                    <th width="15%">User</th>
                    <th width="15%">Collector</th>
                    <th width="12%">Materials</th>
                    <th width="10%">Weight (kg)</th>
                    <th width="10%">Status</th>
                    <th width="20%">Pickup Address</th>
                </tr>
            </thead>
            <tbody>';
        
        // Table rows
        $rowColor = true;
        foreach ($data as $row) {
            $bgColor = $rowColor ? '#ffffff' : '#f7fafc';
            $rowColor = !$rowColor;
            
            $html .= '<tr style="background-color:' . $bgColor . ';">
                <td width="6%">' . htmlspecialchars($row['id']) . '</td>
                <td width="12%">' . date('M d, Y', strtotime($row['created_at'])) . '</td>
                <td width="15%">' . htmlspecialchars($row['user_name']) . '</td>
                <td width="15%">' . htmlspecialchars($row['collector_name'] ?? 'N/A') . '</td>
                <td width="12%">' . htmlspecialchars(ucfirst($row['materials'])) . '</td>
                <td width="10%">' . ($row['estimated_weight'] ?? 'N/A') . '</td>
                <td width="10%">' . htmlspecialchars(ucfirst($row['status'])) . '</td>
                <td width="20%">' . htmlspecialchars(substr($row['pickup_address'], 0, 40)) . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Kiambu Recycling Platform - Environmental Impact Report', 0, 0, 'C');
        
        // Output PDF
        $filename = 'collections_report_' . $startDate . '_to_' . $endDate . '.pdf';
        $pdf->Output($filename, 'D');
    }
}
