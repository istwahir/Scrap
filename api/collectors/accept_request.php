<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
if (!isset($data['request_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing request ID']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verify collector status
    $stmt = $pdo->prepare("
        SELECT c.id, c.status 
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

    if ($collector['status'] !== 'approved') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Collector account not approved']);
        exit;
    }

    // Start transaction
    $pdo->beginTransaction();

    // Update request status to 'assigned' (accepted by collector)
    $stmt = $pdo->prepare("
        UPDATE collection_requests 
        SET 
            status = 'assigned',
            updated_at = NOW()
        WHERE id = ? 
        AND collector_id = ?
        AND status = 'pending'
    ");
    $stmt->execute([$data['request_id'], $collector['id']]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Request not found, not assigned to you, or already processed');
    }

    // Get request details for response
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as customer_name, u.phone as customer_phone
        FROM collection_requests r
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$data['request_id']]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    // Send SMS notification to customer (mock in development)
    if (defined('MOCK_SMS') && MOCK_SMS) {
        error_log("SMS to {$request['customer_phone']}: Your collection request has been accepted. The collector will arrive shortly.");
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Request accepted successfully',
        'request' => [
            'id' => $request['id'],
            'customer_name' => $request['customer_name'],
            'address' => isset($request['pickup_address']) ? $request['pickup_address'] : $request['address'],
            'material_type' => isset($request['materials']) ? $request['materials'] : 'Mixed'
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