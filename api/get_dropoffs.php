<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Enable CORS for development
if (ENV === 'development') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Get query parameters
$lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
$lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);
$radius = filter_input(INPUT_GET, 'radius', FILTER_VALIDATE_INT) ?? 5; // Default 5km radius
$materials = filter_input(INPUT_GET, 'materials', FILTER_SANITIZE_STRING);

try {
    $db = getDBConnection();
    
    // Base query
    $query = "SELECT id, name, lat, lng, address, materials, operating_hours, contact_phone 
              FROM dropoff_points 
              WHERE status = 'active'";
    $params = [];
    
    // If coordinates provided, filter by distance
    if ($lat !== false && $lng !== false) {
        // Haversine formula to calculate distance in kilometers
        $query .= " HAVING (
            6371 * acos(
                cos(radians(?)) * 
                cos(radians(lat)) * 
                cos(radians(lng) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(lat))
            )
        ) <= ?";
        array_push($params, $lat, $lng, $lat, $radius);
    }
    
    // If materials filter provided
    if ($materials) {
        $materialArray = explode(',', $materials);
        $materialConditions = [];
        foreach ($materialArray as $material) {
            if (in_array($material, ['plastic', 'paper', 'metal', 'glass', 'electronics'])) {
                $materialConditions[] = "FIND_IN_SET(?, materials)";
                $params[] = $material;
            }
        }
        if (!empty($materialConditions)) {
            $query .= " AND (" . implode(' OR ', $materialConditions) . ")";
        }
    }
    
    // Add ordering by distance if coordinates provided
    if ($lat !== false && $lng !== false) {
        $query .= " ORDER BY (
            6371 * acos(
                cos(radians(?)) * 
                cos(radians(lat)) * 
                cos(radians(lng) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(lat))
            )
        ) ASC";
        array_push($params, $lat, $lng, $lat);
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $dropoffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate distance for each dropoff point if coordinates provided
    if ($lat !== false && $lng !== false) {
        foreach ($dropoffs as &$dropoff) {
            $dropoff['distance'] = round(
                getDistance(
                    $lat, 
                    $lng, 
                    $dropoff['lat'], 
                    $dropoff['lng']
                ),
                2
            );
            // Convert materials string to array
            $dropoff['materials'] = explode(',', $dropoff['materials']);
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $dropoffs
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch dropoff points'
    ]);
}

/**
 * Calculate distance between two points using Haversine formula
 * @param float $lat1 First point latitude
 * @param float $lon1 First point longitude
 * @param float $lat2 Second point latitude
 * @param float $lon2 Second point longitude
 * @return float Distance in kilometers
 */
function getDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth's radius in kilometers
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
         
    $c = 2 * asin(sqrt($a));
    
    return $earthRadius * $c;
}
