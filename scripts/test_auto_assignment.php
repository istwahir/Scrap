<?php
/**
 * Test script for Smart Collector Auto-Assignment
 * 
 * Run this from command line:
 * php scripts/test_auto_assignment.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CollectorAssignment.php';

echo "=== Smart Collector Auto-Assignment Test ===\n\n";

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create assignment system instance
    $assignmentSystem = new CollectorAssignment($pdo);
    
    // Test Case 1: Plastic collection in Kiambu
    echo "Test 1: Plastic collection in Kiambu\n";
    echo str_repeat("-", 50) . "\n";
    $testRequest1 = [
        'materials' => 'plastic,paper',
        'latitude' => -1.1712,
        'longitude' => 36.8356,
        'pickup_address' => 'Kiambu Town, Kenya'
    ];
    
    $collector1 = $assignmentSystem->findBestCollector($testRequest1);
    if ($collector1) {
        echo "✓ Assigned to Collector ID: $collector1\n";
    } else {
        echo "✗ No collector found\n";
    }
    echo "\n";
    
    // Test Case 2: Electronics collection in Nairobi
    echo "Test 2: Electronics collection in Nairobi\n";
    echo str_repeat("-", 50) . "\n";
    $testRequest2 = [
        'materials' => 'electronics,metal',
        'latitude' => -1.2921,
        'longitude' => 36.8219,
        'pickup_address' => 'Nairobi CBD, Kenya'
    ];
    
    $collector2 = $assignmentSystem->findBestCollector($testRequest2);
    if ($collector2) {
        echo "✓ Assigned to Collector ID: $collector2\n";
    } else {
        echo "✗ No collector found\n";
    }
    echo "\n";
    
    // Test Case 3: Glass collection in Thika
    echo "Test 3: Glass collection in Thika\n";
    echo str_repeat("-", 50) . "\n";
    $testRequest3 = [
        'materials' => 'glass',
        'latitude' => -1.0332,
        'longitude' => 37.0693,
        'pickup_address' => 'Thika Town, Kenya'
    ];
    
    $collector3 = $assignmentSystem->findBestCollector($testRequest3);
    if ($collector3) {
        echo "✓ Assigned to Collector ID: $collector3\n";
    } else {
        echo "✗ No collector found\n";
    }
    echo "\n";
    
    // Display collector statistics
    echo "\n=== Collector Statistics ===\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT 
            c.id,
            c.name,
            c.vehicle_type,
            c.status,
            c.verified,
            c.service_areas,
            c.materials_collected,
            COUNT(DISTINCT cr.id) as active_requests
        FROM collectors c
        LEFT JOIN collection_requests cr ON cr.collector_id = c.id 
            AND cr.status IN ('pending', 'assigned', 'en_route')
        WHERE c.status = 'approved' AND c.verified = 1
        GROUP BY c.id
    ");
    
    $collectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($collectors)) {
        echo "No approved collectors in the system.\n";
    } else {
        foreach ($collectors as $collector) {
            echo "\nCollector ID: {$collector['id']}\n";
            echo "Name: {$collector['name']}\n";
            echo "Vehicle: {$collector['vehicle_type']}\n";
            echo "Status: {$collector['status']}\n";
            echo "Verified: " . ($collector['verified'] ? 'Yes' : 'No') . "\n";
            echo "Active Requests: {$collector['active_requests']}\n";
            echo "Materials: {$collector['materials_collected']}\n";
            echo "Service Areas: {$collector['service_areas']}\n";
            echo str_repeat("-", 50) . "\n";
        }
    }
    
    echo "\n✓ Test completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
