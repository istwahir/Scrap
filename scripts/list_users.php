<?php
require_once __DIR__ . '/../config.php';
$db = getDBConnection();
$stmt = $db->query('SELECT id, email, phone, name FROM users ORDER BY id ASC LIMIT 20');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['id'] . ' | ' . ($r['email'] ?? '(no-email)') . ' | ' . ($r['name'] ?? '') . ' | ' . ($r['phone'] ?? '') . "\n";
}

?>
