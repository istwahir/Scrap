<?php
require_once '../../config.php';
require_once '../../controllers/AuthController.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Verify authentication
$auth = new AuthController();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo "event: error\n";
    echo "data: " . json_encode(['message' => 'Unauthorized']) . "\n\n";
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Keep connection alive
    while (true) {
        // Clear output buffer
        if (ob_get_level()) ob_end_clean();

        // Query active collectors
        $stmt = $pdo->query("
            SELECT 
                c.id,
                c.current_latitude,
                c.current_longitude,
                c.active_status,
                c.last_active,
                ca.name,
                ca.vehicle_type,
                GROUP_CONCAT(DISTINCT cm.material_type) as materials,
                GROUP_CONCAT(DISTINCT car.area_name) as areas
            FROM collectors c
            JOIN collector_applications ca ON c.application_id = ca.id
            LEFT JOIN collector_materials cm ON ca.id = cm.application_id
            LEFT JOIN collector_areas car ON ca.id = car.application_id
            WHERE c.active_status != 'offline'
            AND c.last_active >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            GROUP BY c.id
        ");

        $collectors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Send collectors data
        echo "event: update\n";
        echo "data: " . json_encode([
            'status' => 'success',
            'collectors' => array_map(function($collector) {
                return [
                    'id' => $collector['id'],
                    'name' => $collector['name'],
                    'vehicle' => $collector['vehicle_type'],
                    'status' => $collector['active_status'],
                    'materials' => explode(',', $collector['materials']),
                    'areas' => explode(',', $collector['areas']),
                    'position' => [
                        'lat' => floatval($collector['current_latitude']),
                        'lng' => floatval($collector['current_longitude'])
                    ],
                    'lastActive' => $collector['last_active']
                ];
            }, $collectors)
        ]) . "\n\n";

        // Flush output
        flush();

        // Wait before next update
        sleep(3);
    }

} catch (Exception $e) {
    echo "event: error\n";
    echo "data: " . json_encode(['message' => $e->getMessage()]) . "\n\n";
    exit;
}