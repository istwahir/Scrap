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
    $title = sanitizeInput($input['title'] ?? '');

    if ($id <= 0) { jsonError('Invalid id'); exit; }
    if (!$title) { jsonError('Title is required'); exit; }
    if (mb_strlen($title) > 255) { jsonError('Title too long (max 255)'); exit; }

    $userId = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare('UPDATE eco_goals SET title = :title WHERE id = :id AND user_id = :uid');
    $stmt->execute([':title' => $title, ':id' => $id, ':uid' => $userId]);

    if ($stmt->rowCount() === 0) { jsonError('Goal not found', 404); exit; }

    jsonSuccess(['goal' => ['id' => $id, 'title' => $title]]);
} catch (Throwable $e) {
    error_log('eco_goals/update error: ' . $e->getMessage());
    jsonError('Internal server error', 500);
}
