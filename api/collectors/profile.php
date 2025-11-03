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

    // Get comprehensive collector record with user details and application info
    $stmt = $pdo->prepare('
        SELECT 
            c.*,
            u.name as user_name,
            u.phone as user_phone,
            u.email,
            ca.date_of_birth,
            ca.address as home_address,
            ca.latitude as home_latitude,
            ca.longitude as home_longitude
        FROM collectors c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN collector_applications ca ON c.phone = ca.phone
        WHERE c.user_id = ?
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $collector = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$collector) {
        // Log for debugging
        error_log("Collector not found for user_id: " . $_SESSION['user_id']);
        
        // Check if user exists and their role
        $userStmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ?');
        $userStmt->execute([$_SESSION['user_id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        http_response_code(403);
        echo json_encode([
            'status' => 'error', 
            'message' => 'No collector record found. Please complete your collector registration.',
            'redirect' => '/Scrap/views/collectors/register.php',
            'debug' => [
                'user_id' => $_SESSION['user_id'],
                'session_role' => $_SESSION['user_role'] ?? 'not set',
                'db_role' => $user ? $user['role'] : 'user not found'
            ]
        ]);
        exit;
    }

    $collectorId = (int)$collector['id'];

    // Parse materials and service areas from JSON
    $materials = [];
    $areas = [];
    
    if (!empty($collector['materials_collected'])) {
        $materialsJson = json_decode($collector['materials_collected'], true);
        if (is_array($materialsJson)) {
            $materials = array_map(function($m) { 
                return ucfirst(str_replace('_', ' ', $m)); 
            }, $materialsJson);
        }
    }
    
    if (!empty($collector['service_areas'])) {
        $areasJson = json_decode($collector['service_areas'], true);
        if (is_array($areasJson)) {
            $areas = $areasJson;
        }
    }

    // Get latest location from collector_locations
    $stmt = $pdo->prepare('
        SELECT latitude, longitude, timestamp 
        FROM collector_locations 
        WHERE collector_id = ? 
        ORDER BY timestamp DESC 
        LIMIT 1
    ');
    $stmt->execute([$collectorId]);
    $location = $stmt->fetch(PDO::FETCH_ASSOC);

    // Enhanced profile info with all collector details
    // Calculate age from date_of_birth
    $age = null;
    $dateOfBirth = null;
    if (!empty($collector['date_of_birth'])) {
        $dateOfBirth = $collector['date_of_birth'];
        $dob = new DateTime($dateOfBirth);
        $now = new DateTime();
        $age = $now->diff($dob)->y; // Calculate years
    }
    
    $profile = [
        'name' => $collector['user_name'] ?? $collector['name'] ?? '—',
        'phone' => $collector['user_phone'] ?? $collector['phone'] ?? '—',
        'email' => $collector['email'] ?? '—',
        'id_number' => $collector['id_number'] ?? '—',
        'date_of_birth' => $dateOfBirth,
        'age' => $age,
        'home_address' => $collector['home_address'] ?? '—',
        'home_latitude' => isset($collector['home_latitude']) ? (float)$collector['home_latitude'] : null,
        'home_longitude' => isset($collector['home_longitude']) ? (float)$collector['home_longitude'] : null,
        'active_status' => $collector['active_status'] ?? 'offline',
        'current_latitude' => $location ? (float)$location['latitude'] : null,
        'current_longitude' => $location ? (float)$location['longitude'] : null,
        'last_active' => $location ? $location['timestamp'] : null,
        'joined_date' => isset($collector['created_at']) ? date('M j, Y', strtotime($collector['created_at'])) : '—',
        'application_status' => $collector['status'] ?? 'pending',
        'verification_status' => ($collector['verified'] ?? 0) == 1 ? 'verified' : 'pending'
    ];

    // Vehicle info with comprehensive details
    $vehicle = [
        'type' => $collector['vehicle_type'] ?? 'N/A',
        'type_display' => ucfirst($collector['vehicle_type'] ?? 'N/A'),
        'registration' => $collector['vehicle_registration'] ?? 'N/A',
        'materials' => $materials
    ];

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

    // Calculate earnings and rating (collectors table doesn't have these stored)
    $totalEarnings = 0.0; // TODO: Calculate from completed collections
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
        DATE_FORMAT(r.updated_at, "%b %d, %Y") AS date,
        COALESCE(r.materials, "Mixed") AS material_type,
        COALESCE(r.estimated_weight, 0) AS weight,
        0 AS amount,
        NULL AS rating,
        u.name as customer_name,
        r.pickup_address
        FROM collection_requests r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.collector_id = ? AND r.status = "completed"
        ORDER BY r.updated_at DESC
        LIMIT 20');
    $stmt->execute([$collectorId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Analytics: earnings trend (weights proxy) last 7 days
    $stmt = $pdo->prepare('SELECT 
        DATE_FORMAT(updated_at, "%b %d") as date, 
        COALESCE(SUM(estimated_weight), 0) as daily_earnings 
        FROM collection_requests 
        WHERE collector_id = ? AND status = "completed" 
        AND updated_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) 
        GROUP BY DATE(updated_at) 
        ORDER BY DATE(updated_at)');
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
        AND updated_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) 
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