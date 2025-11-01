<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/Reward.php';

try {
    // Use user id from argv[1] or default to 7
    $userId = isset($argv[1]) ? (int)$argv[1] : 7;
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, email, phone FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Test user id {$userId} not found\n";
        exit(1);
    }
    $reward = new Reward();

    $optionId = isset($argv[2]) ? $argv[2] : 'mpesa_50';
    echo "Attempting redemption for user {$userId} ({$user['email']}) option {$optionId}\n";
    $ok = $reward->processRedemption($userId, $optionId);

    if ($ok) {
        echo "processRedemption returned true\n";
        $stats = $reward->getStats($userId);
        echo "Available points after redemption: " . $stats['available_points'] . "\n";
    } else {
        echo "processRedemption returned false\n";
    }

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    exit(1);
}

?>
