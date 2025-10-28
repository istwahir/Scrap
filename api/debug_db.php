<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$out = ['env' => ENV, 'base' => BASE_URL];
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query('SELECT NOW() as now');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $out['db'] = 'ok';
    $out['time'] = $row['now'] ?? null;
} catch (Exception $e) {
    $out['db'] = 'error';
    $out['error'] = $e->getMessage();
}

echo json_encode($out);
