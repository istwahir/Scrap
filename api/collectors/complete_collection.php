<?php
require_once '../../config.php';
require_once '../../controllers/AuthController.php';

header('Content-Type: application/json');

// Verify collector authentication
$auth = new AuthController();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Get and validate request data
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['request_id']) || !isset($data['weight'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verify collector
    $stmt = $pdo->prepare("
        SELECT c.id 
        FROM collectors c 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $collector = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$collector) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'User is not a collector']);
        exit;
    }

    // Start transaction
    $pdo->beginTransaction();

    // Get request details
    $stmt = $pdo->prepare("
        SELECT r.*, u.phone as customer_phone
        FROM collection_requests r
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ? 
        AND r.collector_id = ?
        AND r.status = 'accepted'
    ");
    $stmt->execute([$data['request_id'], $collector['id']]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        throw new Exception('Request not found or not accepted');
    }

    // Calculate payment amount based on material type and weight
    $rates = [
        'plastic' => 30, // KES per kg
        'paper' => 20,
        'metal' => 50,
        'glass' => 15,
        'electronics' => 100
    ];

    $rate = $rates[strtolower($request['material_type'])] ?? 20;
    $amount = $data['weight'] * $rate;

    // Create collection record
    $stmt = $pdo->prepare("
        INSERT INTO collections (
            request_id, collector_id, user_id,
            material_type, weight, amount,
            address, latitude, longitude,
            completed_at
        ) VALUES (
            :request_id, :collector_id, :user_id,
            :material_type, :weight, :amount,
            :address, :latitude, :longitude,
            NOW()
        )
    ");

    $stmt->execute([
        'request_id' => $request['id'],
        'collector_id' => $collector['id'],
        'user_id' => $request['user_id'],
        'material_type' => $request['material_type'],
        'weight' => $data['weight'],
        'amount' => $amount,
        'address' => $request['address'],
        'latitude' => $request['latitude'],
        'longitude' => $request['longitude']
    ]);

    // Update request status
    $stmt = $pdo->prepare("
        UPDATE collection_requests 
        SET 
            status = 'completed',
            completed_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$data['request_id']]);

    // Update collector stats
    $stmt = $pdo->prepare("
        UPDATE collectors 
        SET 
            active_status = 'online',
            total_collections = total_collections + 1,
            total_earnings = total_earnings + :amount
        WHERE id = ?
    ");
    $stmt->execute([
        'amount' => $amount,
        'id' => $collector['id']
    ]);

    // Send SMS notification to customer (mock in development)
    if (MOCK_SMS) {
        $message = sprintf(
            "Collection completed! Weight: %.2fkg, Amount: KES %.2f. Thank you for recycling!",
            $data['weight'],
            $amount
        );
        error_log("SMS to {$request['customer_phone']}: $message");
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Collection completed successfully',
        'collection' => [
            'weight' => $data['weight'],
            'amount' => $amount
        ]
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}