<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();

// Simple reverse geocoding proxy to avoid CORS issues
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

if ($lat === null || $lng === null) {
    echo json_encode(['status' => 'error', 'message' => 'Missing lat/lng parameters']);
    exit;
}

// Validate coordinates
if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid coordinates']);
    exit;
}

try {
    // Use Nominatim for reverse geocoding
    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1";
    
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: KiambuRecycling/1.0',
                'Referer: http://localhost'
            ],
            'timeout' => 5
        ]
    ];
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        // Fallback: return coordinate-based location
        echo json_encode([
            'status' => 'success',
            'address' => "Lat: " . round($lat, 6) . ", Lng: " . round($lng, 6),
            'display_name' => "Location: " . round($lat, 6) . ", " . round($lng, 6),
            'fallback' => true
        ]);
        exit;
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['display_name'])) {
        echo json_encode([
            'status' => 'success',
            'address' => $data['display_name'],
            'display_name' => $data['display_name'],
            'lat' => $data['lat'] ?? $lat,
            'lon' => $data['lon'] ?? $lng,
            'place_id' => $data['place_id'] ?? null,
            'fallback' => false
        ]);
    } else {
        // No address found, return coordinates
        echo json_encode([
            'status' => 'success',
            'address' => "Lat: " . round($lat, 6) . ", Lng: " . round($lng, 6),
            'display_name' => "Location: " . round($lat, 6) . ", " . round($lng, 6),
            'fallback' => true
        ]);
    }
    
} catch (Exception $e) {
    error_log("Geocoding Error: " . $e->getMessage());
    // Return coordinate-based fallback
    echo json_encode([
        'status' => 'success',
        'address' => "Lat: " . round($lat, 6) . ", Lng: " . round($lng, 6),
        'display_name' => "Location: " . round($lat, 6) . ", " . round($lng, 6),
        'fallback' => true
    ]);
}
?>
