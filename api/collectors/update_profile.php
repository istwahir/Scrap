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

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get collector details
    $stmt = $pdo->prepare('
        SELECT c.*, u.email
        FROM collectors c
        JOIN users u ON c.user_id = u.id
        WHERE c.user_id = ?
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $collector = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$collector) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'User is not a collector']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Start transaction
        $pdo->beginTransaction();

        // Update users table
        if (isset($data['name']) || isset($data['phone']) || isset($data['email'])) {
            $updateFields = [];
            $params = [];
            
            if (isset($data['name']) && !empty($data['name'])) {
                $updateFields[] = "name = ?";
                $params[] = trim($data['name']);
            }
            
            if (isset($data['phone']) && !empty($data['phone'])) {
                $updateFields[] = "phone = ?";
                $params[] = trim($data['phone']);
            }
            
            if (isset($data['email']) && !empty($data['email'])) {
                // Validate email
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email format');
                }
                $updateFields[] = "email = ?";
                $params[] = trim($data['email']);
            }
            
            if (!empty($updateFields)) {
                $params[] = $_SESSION['user_id'];
                $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
        }

        // Update collectors table
        $updateFields = [];
        $params = [];
        
        if (isset($data['name']) && !empty($data['name'])) {
            $updateFields[] = "name = ?";
            $params[] = trim($data['name']);
        }
        
        if (isset($data['phone']) && !empty($data['phone'])) {
            $updateFields[] = "phone = ?";
            $params[] = trim($data['phone']);
        }
        
        if (isset($data['vehicle_registration']) && !empty($data['vehicle_registration'])) {
            $updateFields[] = "vehicle_registration = ?";
            $params[] = strtoupper(trim($data['vehicle_registration']));
        }
        
        if (isset($data['vehicle_type']) && !empty($data['vehicle_type'])) {
            $updateFields[] = "vehicle_type = ?";
            $params[] = strtolower(trim($data['vehicle_type']));
        }
        
        if (isset($data['materials']) && is_array($data['materials'])) {
            $updateFields[] = "materials_collected = ?";
            $params[] = json_encode($data['materials']);
        }
        
        if (isset($data['service_areas']) && is_array($data['service_areas'])) {
            $updateFields[] = "service_areas = ?";
            $params[] = json_encode($data['service_areas']);
        }
        
        if (!empty($updateFields)) {
            $params[] = $collector['id'];
            $sql = "UPDATE collectors SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        // Update collector_applications table if exists
        $stmt = $pdo->prepare('SELECT id FROM collector_applications WHERE phone = ?');
        $stmt->execute([$collector['phone']]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($application) {
            $updateFields = [];
            $params = [];
            
            if (isset($data['name']) && !empty($data['name'])) {
                $updateFields[] = "name = ?";
                $params[] = trim($data['name']);
            }
            
            if (isset($data['phone']) && !empty($data['phone'])) {
                $updateFields[] = "phone = ?";
                $params[] = trim($data['phone']);
            }
            
            if (isset($data['address']) && !empty($data['address'])) {
                $updateFields[] = "address = ?";
                $params[] = trim($data['address']);
            }
            
            if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
                $updateFields[] = "date_of_birth = ?";
                $params[] = $data['date_of_birth'];
            }
            
            if (isset($data['id_number']) && !empty($data['id_number'])) {
                $updateFields[] = "id_number = ?";
                $params[] = trim($data['id_number']);
            }
            
            if (isset($data['vehicle_registration']) && !empty($data['vehicle_registration'])) {
                $updateFields[] = "vehicle_reg = ?";
                $params[] = strtoupper(trim($data['vehicle_registration']));
            }
            
            if (!empty($updateFields)) {
                $params[] = $application['id'];
                $sql = "UPDATE collector_applications SET " . implode(", ", $updateFields) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Profile updated successfully'
        ]);

    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Update profile error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
