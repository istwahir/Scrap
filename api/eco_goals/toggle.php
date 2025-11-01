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
    $completed = isset($input['is_completed']) ? (bool)$input['is_completed'] : null;

    if ($id <= 0) { jsonError('Invalid id'); exit; }
    if ($completed === null) { jsonError('is_completed required'); exit; }

    $userId = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare('UPDATE eco_goals SET is_completed = :c WHERE id = :id AND user_id = :uid');
    $stmt->execute([':c' => $completed ? 1 : 0, ':id' => $id, ':uid' => $userId]);

    if ($stmt->rowCount() === 0) { jsonError('Goal not found', 404); exit; }

    jsonSuccess(['goal' => ['id' => $id, 'is_completed' => $completed]]);
} catch (Throwable $e) {
    error_log('eco_goals/toggle error: ' . $e->getMessage());
    jsonError('Internal server error', 500);
}
