<?php
require_once 'config.php';

header('Content-Type: text/plain');

echo "=== SESSION DEBUG ===\n\n";

echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "\n";
echo "Session ID: " . session_id() . "\n\n";

echo "Session Data:\n";
if (empty($_SESSION)) {
    echo "  (Session is empty - NOT LOGGED IN)\n";
} else {
    foreach ($_SESSION as $key => $value) {
        echo "  $key => " . (is_array($value) ? json_encode($value) : $value) . "\n";
    }
}

echo "\n=== COOKIES ===\n";
if (empty($_COOKIE)) {
    echo "  (No cookies)\n";
} else {
    foreach ($_COOKIE as $key => $value) {
        echo "  $key => $value\n";
    }
}

echo "\n=== AUTHENTICATION CHECK ===\n";
if (isset($_SESSION['user_id'])) {
    echo "✓ user_id is set: " . $_SESSION['user_id'] . "\n";
} else {
    echo "✗ user_id is NOT set\n";
}

if (isset($_SESSION['user_role'])) {
    echo "✓ user_role is set: " . $_SESSION['user_role'] . "\n";
} else {
    echo "✗ user_role is NOT set\n";
}

echo "\n";
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'collector') {
    echo "RESULT: ✓ User is authenticated as collector\n";
} else {
    echo "RESULT: ✗ User is NOT authenticated as collector\n";
}
