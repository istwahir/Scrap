<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');
// Allow same-origin; CORS open for localhost dev if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

function ensureGoalsTable(PDO $conn): void {
    $sql = "CREATE TABLE IF NOT EXISTS eco_goals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        is_completed TINYINT(1) NOT NULL DEFAULT 0,
        sort_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_completed_order (user_id, is_completed, sort_order),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->exec($sql);

    // Attempt to add missing sort_order column/index if table pre-existed without them
    try { $conn->exec('ALTER TABLE eco_goals ADD COLUMN sort_order INT NOT NULL DEFAULT 0'); } catch (Throwable $e) { /* ignore duplicate column */ }
    try { $conn->exec('CREATE INDEX idx_user_completed_order ON eco_goals (user_id, is_completed, sort_order)'); } catch (Throwable $e) { /* ignore duplicate index */ }
}

function getJsonInput(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function jsonSuccess($data = [], int $code = 200): void {
    http_response_code($code);
    echo json_encode(['status' => 'success', 'data' => $data]);
}

function jsonError(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
}
