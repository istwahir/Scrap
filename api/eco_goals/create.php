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
    $title = sanitizeInput($input['title'] ?? '');

    if (!$title) {
        jsonError('Title is required');
        exit;
    }
    if (mb_strlen($title) > 255) {
        jsonError('Title too long (max 255)');
        exit;
    }

    $userId = (int) $_SESSION['user_id'];
    // Place new items at the top by assigning a sort_order just above the current minimum
    $minStmt = $conn->prepare('SELECT COALESCE(MIN(sort_order), 0) - 1 AS next_order FROM eco_goals WHERE user_id = :uid');
    $minStmt->execute([':uid' => $userId]);
    $nextOrder = (int) ($minStmt->fetch(PDO::FETCH_ASSOC)['next_order'] ?? 0);

    $stmt = $conn->prepare('INSERT INTO eco_goals (user_id, title, sort_order) VALUES (:uid, :title, :sorder)');
    $stmt->execute([':uid' => $userId, ':title' => $title, ':sorder' => $nextOrder]);
    $id = (int)$conn->lastInsertId();

    jsonSuccess([
        'goal' => [
            'id' => $id,
            'title' => $title,
            'is_completed' => false,
        ]
    ], 201);
} catch (Throwable $e) {
    error_log('eco_goals/create error: ' . $e->getMessage());
    jsonError('Internal server error', 500);
}
