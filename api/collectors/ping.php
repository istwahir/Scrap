<?php
header('Content-Type: application/json');
require_once '../../config.php';
$elapsed = microtime(true);
$response = [
  'status' => 'success',
  'time' => date('c'),
  'has_session' => isset($_SESSION['user_id']),
  'user_id' => $_SESSION['user_id'] ?? null,
  'user_role' => $_SESSION['user_role'] ?? null,
  'session_id' => session_id(),
  'elapsed_ms' => number_format((microtime(true)-$elapsed)*1000,2)
];
echo json_encode($response);
