<?php
require_once '../../config.php';
require_once '../../controllers/AuthController.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Authentication: allow unauthenticated GET (SSE) consumers (map) to access locations feed
// but keep AuthController available for future use.
$auth = new AuthController();
// If you want to restrict access to authenticated users, change this behavior.

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Determine if collectors table has inline position columns
    $hasInlinePosition = false;
    try {
        $chk = $pdo->prepare("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'collectors' AND COLUMN_NAME IN ('current_latitude','current_longitude')");
        $chk->execute(['db' => DB_NAME]);
        $row = $chk->fetch(PDO::FETCH_ASSOC);
        $hasInlinePosition = isset($row['cnt']) && (int)$row['cnt'] >= 2;
    } catch (Throwable $t) {
        $hasInlinePosition = false;
    }

    // Keep connection alive
    while (true) {
        // Clear output buffer
        if (ob_get_level()) ob_end_clean();

        // Query active collectors (schema-aware for location columns)
        if ($hasInlinePosition) {
            $sql = "
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
            ";
        } else {
            // fallback: use last known position from collector_locations
            $sql = "
                SELECT
                    c.id,
                    loc.latitude AS current_latitude,
                    loc.longitude AS current_longitude,
                    c.active_status,
                    c.last_active,
                    ca.name,
                    ca.vehicle_type,
                    GROUP_CONCAT(DISTINCT cm.material_type) as materials,
                    GROUP_CONCAT(DISTINCT car.area_name) as areas
                FROM collectors c
                JOIN collector_applications ca ON c.application_id = ca.id
                LEFT JOIN (
                    SELECT cl1.collector_id, cl1.latitude, cl1.longitude
                    FROM collector_locations cl1
                    INNER JOIN (
                        SELECT collector_id, MAX(recorded_at) AS max_time
                        FROM collector_locations
                        GROUP BY collector_id
                    ) cl2 ON cl1.collector_id = cl2.collector_id AND cl1.recorded_at = cl2.max_time
                ) loc ON loc.collector_id = c.id
                LEFT JOIN collector_materials cm ON ca.id = cm.application_id
                LEFT JOIN collector_areas car ON ca.id = car.application_id
                WHERE c.active_status != 'offline'
                AND c.last_active >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                GROUP BY c.id
            ";
        }

        $stmt = $pdo->query($sql);
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
                    'materials' => $collector['materials'] !== null && $collector['materials'] !== '' ? explode(',', $collector['materials']) : [],
                    'areas' => $collector['areas'] !== null && $collector['areas'] !== '' ? explode(',', $collector['areas']) : [],
                    'position' => [
                        'lat' => isset($collector['current_latitude']) ? floatval($collector['current_latitude']) : null,
                        'lng' => isset($collector['current_longitude']) ? floatval($collector['current_longitude']) : null
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