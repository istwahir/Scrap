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
    $order = $input['order'] ?? null; // array of goal IDs in desired order (top first)
    if (!is_array($order) || empty($order)) { jsonError('Invalid order payload'); exit; }

    $userId = (int) $_SESSION['user_id'];
    $conn->beginTransaction();
    $idx = 0;
    $update = $conn->prepare('UPDATE eco_goals SET sort_order = :ord WHERE id = :id AND user_id = :uid');
    foreach ($order as $id) {
        $gid = (int) $id;
        if ($gid <= 0) continue;
        $update->execute([':ord' => $idx++, ':id' => $gid, ':uid' => $userId]);
    }
    $conn->commit();

    jsonSuccess(['reordered' => true]);
} catch (Throwable $e) {
    if ($conn && $conn->inTransaction()) { $conn->rollBack(); }
    error_log('eco_goals/reorder error: ' . $e->getMessage());
    jsonError('Internal server error', 500);
}
