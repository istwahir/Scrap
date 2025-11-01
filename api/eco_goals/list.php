<?php
require_once __DIR__ . '/_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
    exit;
}

try {
    $conn = getDBConnection();
    ensureGoalsTable($conn);

    $userId = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT id, title, is_completed, sort_order, created_at, updated_at FROM eco_goals WHERE user_id = :uid ORDER BY is_completed ASC, sort_order ASC, created_at DESC');
    $stmt->execute([':uid' => $userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $goals = array_map(function ($r) {
        return [
            'id' => (int)$r['id'],
            'title' => (string)$r['title'],
            'is_completed' => (bool)$r['is_completed'],
            'sort_order' => isset($r['sort_order']) ? (int)$r['sort_order'] : 0,
            'created_at' => $r['created_at'],
            'updated_at' => $r['updated_at'],
        ];
    }, $rows ?: []);

    jsonSuccess(['goals' => $goals]);
} catch (Throwable $e) {
    error_log('eco_goals/list error: ' . $e->getMessage());
    jsonError('Internal server error', 500);
}
