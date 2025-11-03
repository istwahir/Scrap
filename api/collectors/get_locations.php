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

    // Determine best timestamp/ordering column for collector_locations
    $locationTimeColumn = null;
    try {
        $colChk = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'collector_locations' AND COLUMN_NAME IN ('recorded_at','created_at','timestamp','updated_at') ORDER BY FIELD(COLUMN_NAME,'recorded_at','created_at','timestamp','updated_at') LIMIT 1");
        $colChk->execute(['db' => DB_NAME]);
        $colRow = $colChk->fetch(PDO::FETCH_ASSOC);
        if ($colRow && !empty($colRow['COLUMN_NAME'])) {
            $locationTimeColumn = $colRow['COLUMN_NAME'];
        }
    } catch (Throwable $t) {
        $locationTimeColumn = null;
    }

    // Keep connection alive
    while (true) {
        // Clear output buffer
        if (ob_get_level()) ob_end_clean();

        // Get latest location for each collector from collector_locations
        if ($locationTimeColumn) {
            $innerJoin = "
                SELECT cl1.collector_id, cl1.latitude, cl1.longitude, cl1.`$locationTimeColumn` as last_updated
                FROM collector_locations cl1
                INNER JOIN (
                    SELECT collector_id, MAX(`$locationTimeColumn`) AS max_time
                    FROM collector_locations
                    GROUP BY collector_id
                ) cl2 ON cl1.collector_id = cl2.collector_id AND cl1.`$locationTimeColumn` = cl2.max_time
            ";
        } else {
            // Fallback to MAX(id) if no timestamp-like column exists
            $innerJoin = "
                SELECT cl1.collector_id, cl1.latitude, cl1.longitude, NOW() as last_updated
                FROM collector_locations cl1
                INNER JOIN (
                    SELECT collector_id, MAX(id) AS max_id
                    FROM collector_locations
                    GROUP BY collector_id
                ) cl2 ON cl1.collector_id = cl2.collector_id AND cl1.id = cl2.max_id
            ";
        }

        $sql = "
            SELECT
                c.id,
                c.name,
                c.vehicle_type,
                c.status,
                c.materials_collected,
                c.service_areas,
                loc.latitude AS current_latitude,
                loc.longitude AS current_longitude,
                loc.last_updated
            FROM collectors c
            LEFT JOIN (
                $innerJoin
            ) loc ON loc.collector_id = c.id
            WHERE c.status = 'approved'
            AND loc.last_updated >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ";

        $stmt = $pdo->query($sql);
        $collectors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Send collectors data
        echo "event: update\n";
        echo "data: " . json_encode([
            'status' => 'success',
            'collectors' => array_map(function($collector) {
                // Parse JSON fields
                $materials = [];
                if (!empty($collector['materials_collected'])) {
                    $parsed = json_decode($collector['materials_collected'], true);
                    if (is_array($parsed)) {
                        $materials = $parsed;
                    }
                }
                
                $areas = [];
                if (!empty($collector['service_areas'])) {
                    $parsed = json_decode($collector['service_areas'], true);
                    if (is_array($parsed)) {
                        $areas = $parsed;
                    }
                }
                
                return [
                    'id' => $collector['id'],
                    'name' => $collector['name'],
                    'vehicle' => $collector['vehicle_type'],
                    'status' => $collector['status'],
                    'materials' => $materials,
                    'areas' => $areas,
                    'position' => [
                        'lat' => isset($collector['current_latitude']) ? floatval($collector['current_latitude']) : null,
                        'lng' => isset($collector['current_longitude']) ? floatval($collector['current_longitude']) : null
                    ],
                    'lastActive' => $collector['last_updated'] ?? null
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