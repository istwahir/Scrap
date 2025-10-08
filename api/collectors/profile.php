<?php
require_once '../../config.php';
header('Content-Type: application/json');
session_start();

// Check if user is logged in and is a collector
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    $collector_id = $_SESSION['user_id'];
    $response = [];

    // Get collector profile information
    $profile_query = "SELECT c.name, c.phone, c.vehicle_type, c.vehicle_registration, c.materials_collected, c.service_areas,
                            c.verified, c.rating, COUNT(DISTINCT r.review_id) as total_reviews
                     FROM collectors c
                     LEFT JOIN reviews r ON c.id = r.collector_id
                     WHERE c.id = ?
                     GROUP BY c.id";
    
    $stmt = $pdo->prepare($profile_query);
    $stmt->execute([$collector_id]);
    $profile_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile_data) {
        throw new Exception('Collector profile not found');
    }

    // Format profile data
    $response['profile'] = [
        'name' => $profile_data['name'],
        'phone' => $profile_data['phone'],
    ];

    // Vehicle information
    $response['vehicle'] = [
        'type' => $profile_data['vehicle_type'],
        'registration' => $profile_data['vehicle_registration'],
        'materials' => json_decode($profile_data['materials_collected'], true)
    ];

    // Service areas
    $response['areas'] = json_decode($profile_data['service_areas'], true);

    // Get statistics
    $stats_query = "SELECT 
                        COUNT(DISTINCT c.collection_id) as total_collections,
                        COALESCE(SUM(c.amount), 0) as total_earnings,
                        COALESCE(AVG(c.rating), 0) as rating,
                        COUNT(DISTINCT CASE WHEN c.status = 'completed' THEN c.collection_id END) * 100.0 / 
                        COUNT(DISTINCT CASE WHEN c.status IN ('completed', 'declined') THEN c.collection_id END) as response_rate
                    FROM collections c
                    WHERE c.collector_id = ?";
    
    $stmt = $pdo->prepare($stats_query);
    $stmt->execute([$collector_id]);
    $stats_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $response['stats'] = [
        'total_collections' => (int)$stats_data['total_collections'],
        'total_earnings' => (float)$stats_data['total_earnings'],
        'rating' => (float)$stats_data['rating'],
        'total_reviews' => (int)$profile_data['total_reviews'],
        'response_rate' => round((float)$stats_data['response_rate'], 1)
    ];

    // Get recent collection history (default: past month)
    $history_query = "SELECT 
                        DATE_FORMAT(c.collection_date, '%Y-%m-%d') as date,
                        c.material_type,
                        c.weight,
                        c.amount,
                        c.rating
                     FROM collections c
                     WHERE c.collector_id = ? 
                     AND c.collection_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                     AND c.status = 'completed'
                     ORDER BY c.collection_date DESC
                     LIMIT 10";
    
    $stmt = $pdo->prepare($history_query);
    $stmt->execute([$collector_id]);
    $response['history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get analytics data
    // 1. Daily earnings for the past week
    $earnings_query = "SELECT 
                        DATE_FORMAT(collection_date, '%Y-%m-%d') as date,
                        SUM(amount) as daily_earnings
                     FROM collections
                     WHERE collector_id = ?
                     AND collection_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                     AND status = 'completed'
                     GROUP BY DATE_FORMAT(collection_date, '%Y-%m-%d')
                     ORDER BY date";
    
    $stmt = $pdo->prepare($earnings_query);
    $stmt->execute([$collector_id]);
    $earnings_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['analytics']['earnings'] = [
        'labels' => array_column($earnings_data, 'date'),
        'values' => array_column($earnings_data, 'daily_earnings')
    ];

    // 2. Materials distribution
    $materials_query = "SELECT 
                        material_type,
                        COUNT(*) as count
                     FROM collections
                     WHERE collector_id = ?
                     AND status = 'completed'
                     GROUP BY material_type
                     ORDER BY count DESC";
    
    $stmt = $pdo->prepare($materials_query);
    $stmt->execute([$collector_id]);
    $materials_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['analytics']['materials'] = [
        'labels' => array_column($materials_data, 'material_type'),
        'values' => array_column($materials_data, 'count')
    ];

    echo json_encode(['status' => 'success'] + $response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>