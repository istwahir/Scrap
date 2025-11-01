<?php
require_once __DIR__ . '/_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
    exit;
}

try {
    $conn = getDBConnection();
    ensureGoalsTable($conn);

    $input = getJsonInput();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) { jsonError('Invalid id'); exit; }

    $userId = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare('DELETE FROM eco_goals WHERE id = :id AND user_id = :uid');
    $stmt->execute([':id' => $id, ':uid' => $userId]);

    if ($stmt->rowCount() === 0) { jsonError('Goal not found', 404); exit; }

    jsonSuccess(['deleted' => true]);
} catch (Throwable $e) {
    error_log('eco_goals/delete error: ' . $e->getMessage());
    jsonError('Internal server error', 500);
}
