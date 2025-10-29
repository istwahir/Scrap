<?php
require_once '../../config.php';
require_once '../../controllers/AuthController.php';

header('Content-Type: application/json');

// Verify authentication
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

    // Get comprehensive collector record with application and user details
    $stmt = $pdo->prepare('
        SELECT 
            c.*,
            ca.name as application_name,
            ca.phone as application_phone,
            ca.id_number,
            ca.date_of_birth,
            ca.address as home_address,
            ca.latitude as home_latitude,
            ca.longitude as home_longitude,
            ca.vehicle_type,
            ca.vehicle_reg,
            ca.status as application_status,
            ca.created_at as joined_date,
            u.name as user_name,
            u.phone as user_phone,
            u.email
        FROM collectors c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN collector_applications ca ON c.application_id = ca.id
        WHERE c.user_id = ?
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $collector = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$collector) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'User is not a collector']);
        exit;
    }

    $collectorId = (int)$collector['id'];
    $applicationId = isset($collector['application_id']) ? (int)$collector['application_id'] : null;

    // Enhanced profile info with all collector details
    $profile = [
        'name' => $collector['user_name'] ?? ($collector['application_name'] ?? '—'),
        'phone' => $collector['user_phone'] ?? ($collector['application_phone'] ?? '—'),
        'email' => $collector['email'] ?? '—',
        'id_number' => $collector['id_number'] ?? '—',
        'date_of_birth' => $collector['date_of_birth'] ?? null,
        'age' => null,
        'home_address' => $collector['home_address'] ?? '—',
        'home_latitude' => isset($collector['home_latitude']) ? (float)$collector['home_latitude'] : null,
        'home_longitude' => isset($collector['home_longitude']) ? (float)$collector['home_longitude'] : null,
        'active_status' => $collector['active_status'] ?? 'offline',
        'current_latitude' => isset($collector['current_latitude']) ? (float)$collector['current_latitude'] : null,
        'current_longitude' => isset($collector['current_longitude']) ? (float)$collector['current_longitude'] : null,
        'last_active' => $collector['last_active'] ?? null,
        'joined_date' => isset($collector['joined_date']) ? date('M j, Y', strtotime($collector['joined_date'])) : '—',
        'application_status' => $collector['application_status'] ?? 'approved',
        'verification_status' => ($collector['application_status'] ?? '') === 'approved' ? 'verified' : 'pending'
    ];

    // Calculate age if DOB is available
    if ($collector['date_of_birth']) {
        $dob = new DateTime($collector['date_of_birth']);
        $now = new DateTime();
        $profile['age'] = $now->diff($dob)->y;
    }

    // Vehicle info with comprehensive details
    $vehicle = [
        'type' => $collector['vehicle_type'] ?? 'N/A',
        'type_display' => ucfirst($collector['vehicle_type'] ?? 'N/A'),
        'registration' => $collector['vehicle_reg'] ?? 'N/A',
        'materials' => []
    ];

    // Materials from collector_materials
    if ($applicationId) {
        $stmt = $pdo->prepare('SELECT material_type FROM collector_materials WHERE application_id = ?');
        $stmt->execute([$applicationId]);
        $vehicle['materials'] = array_map(function($r){ 
            return ucfirst(str_replace('_', ' ', $r['material_type'])); 
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Service areas from collector_areas
    $areas = [];
    if ($applicationId) {
        $stmt = $pdo->prepare('SELECT area_name FROM collector_areas WHERE application_id = ?');
        $stmt->execute([$applicationId]);
        $areas = array_map(function($r){ return $r['area_name']; }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Enhanced stats from multiple sources
    // Total collections (completed)
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM collection_requests WHERE collector_id = ? AND status = "completed"');
    $stmt->execute([$collectorId]);
    $totalCollections = (int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

    // Active requests
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM collection_requests WHERE collector_id = ? AND status IN ("assigned", "en_route")');
    $stmt->execute([$collectorId]);
    $activeRequests = (int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

    // Pending requests
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM collection_requests WHERE collector_id = ? AND status = "pending"');
    $stmt->execute([$collectorId]);
    $pendingRequests = (int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

    // Total weight collected
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(estimated_weight), 0) AS total_weight FROM collection_requests WHERE collector_id = ? AND status = "completed"');
    $stmt->execute([$collectorId]);
    $totalWeight = (float)($stmt->fetch(PDO::FETCH_ASSOC)['total_weight'] ?? 0);

    // Response rate: completed / (completed + declined)
    $stmt = $pdo->prepare('SELECT 
        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) AS completed_cnt,
        SUM(CASE WHEN status = "declined" THEN 1 ELSE 0 END) AS declined_cnt
        FROM collection_requests WHERE collector_id = ?');
    $stmt->execute([$collectorId]);
    $resp = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['completed_cnt' => 0, 'declined_cnt' => 0];
    $den = (int)$resp['completed_cnt'] + (int)$resp['declined_cnt'];
    $responseRate = $den > 0 ? round(((int)$resp['completed_cnt'] * 100.0) / $den, 1) : 0.0;

    // Use stored totals from collectors table
    $totalEarnings = isset($collector['total_earnings']) ? (float)$collector['total_earnings'] : 0.0;
    $rating = isset($collector['rating']) ? (float)$collector['rating'] : 0.0;

    // Count reviews (assuming completed requests can be reviewed)
    $totalReviews = 0; // Placeholder - would need reviews table

    $stats = [
        'total_collections' => $totalCollections,
        'active_requests' => $activeRequests,
        'pending_requests' => $pendingRequests,
        'total_earnings' => $totalEarnings,
        'total_weight' => $totalWeight,
        'rating' => $rating,
        'total_reviews' => $totalReviews,
        'response_rate' => $responseRate
    ];

    // History (last 20 completed with more details)
    $stmt = $pdo->prepare('SELECT 
        DATE_FORMAT(COALESCE(r.completed_at, r.updated_at, r.created_at), "%b %d, %Y") AS date,
        COALESCE(r.materials, "Mixed") AS material_type,
        COALESCE(r.estimated_weight, 0) AS weight,
        0 AS amount,
        NULL AS rating,
        u.name as customer_name,
        r.pickup_address
        FROM collection_requests r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.collector_id = ? AND r.status = "completed"
        ORDER BY COALESCE(r.completed_at, r.updated_at, r.created_at) DESC
        LIMIT 20');
    $stmt->execute([$collectorId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Analytics: earnings trend (weights proxy) last 7 days
    $stmt = $pdo->prepare('SELECT 
        DATE_FORMAT(COALESCE(completed_at, updated_at, created_at), "%b %d") as date, 
        COALESCE(SUM(estimated_weight), 0) as daily_earnings 
        FROM collection_requests 
        WHERE collector_id = ? AND status = "completed" 
        AND COALESCE(completed_at, updated_at, created_at) >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) 
        GROUP BY DATE(COALESCE(completed_at, updated_at, created_at)) 
        ORDER BY DATE(COALESCE(completed_at, updated_at, created_at))');
    $stmt->execute([$collectorId]);
    $earningsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $earnings = [
        'labels' => array_map(function($r){ return $r['date']; }, $earningsData),
        'values' => array_map(function($r){ return (float)$r['daily_earnings']; }, $earningsData)
    ];

    // Materials distribution (last 30 days)
    $stmt = $pdo->prepare('SELECT 
        COALESCE(materials, "Unknown") as material_type, 
        COUNT(*) as cnt 
        FROM collection_requests 
        WHERE collector_id = ? AND status = "completed" 
        AND COALESCE(completed_at, updated_at, created_at) >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) 
        GROUP BY COALESCE(materials, "Unknown") 
        ORDER BY cnt DESC');
    $stmt->execute([$collectorId]);
    $materialsRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $materials = [
        'labels' => array_map(function($r){ return ucfirst(str_replace('_', ' ', $r['material_type'])); }, $materialsRows),
        'values' => array_map(function($r){ return (int)$r['cnt']; }, $materialsRows)
    ];

    echo json_encode([
        'status' => 'success',
        'profile' => $profile,
        'vehicle' => $vehicle,
        'areas' => $areas,
        'stats' => $stats,
        'history' => $history,
        'analytics' => [ 'earnings' => $earnings, 'materials' => $materials ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}