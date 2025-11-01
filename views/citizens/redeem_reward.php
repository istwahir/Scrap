<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/Reward.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Scrap/views/citizens/rewards.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /Scrap/views/auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

$optionId = isset($_POST['option_id']) ? trim($_POST['option_id']) : null;
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : null; // optional, UI may include this later

if (!$optionId) {
    header('Location: /Scrap/views/citizens/rewards.php?status=error&msg=' . urlencode('Missing reward option'));
    exit;
}

$rewardModel = new Reward();

// Find option details
$options = $rewardModel->getRedemptionOptions();
$selected = null;
foreach ($options as $opt) {
    if ($opt['id'] === $optionId) {
        $selected = $opt;
        break;
    }
}

if (!$selected) {
    header('Location: /Scrap/views/citizens/rewards.php?status=error&msg=' . urlencode('Invalid reward option'));
    exit;
}

// Ensure user can redeem
if (!$rewardModel->canRedeem($userId, $optionId)) {
    header('Location: /Scrap/views/citizens/rewards.php?status=error&msg=' . urlencode('Insufficient points for this reward'));
    exit;
}

try {
    // Deduct points and record redemption in rewards table
    $ok = $rewardModel->processRedemption($userId, $optionId);

    if (!$ok) {
        header('Location: /Scrap/views/citizens/rewards.php?status=error&msg=' . urlencode('Failed to process redemption'));
        exit;
    }

    // For MPESA related options, create a pending mpesa_transactions record so admin can inspect or callbacks can update it
    if (in_array($selected['type'], ['airtime', 'cash'])) {
        // Resolve phone: prefer POST then user's phone from DB
        if (empty($phone)) {
            // try to fetch user phone
            $db = getDBConnection();
            $uStmt = $db->prepare("SELECT phone FROM users WHERE id = ? LIMIT 1");
            $uStmt->execute([$userId]);
            $userRow = $uStmt->fetch(PDO::FETCH_ASSOC);
            $phone = $userRow['phone'] ?? null;
        }

        // Normalize phone rudimentarily
        if ($phone) {
            $phone = preg_replace('/[^0-9+]/', '', $phone);
        }

        $db = getDBConnection();
        $stmt = $db->prepare("INSERT INTO mpesa_transactions (user_id, amount, phone, transaction_type, status, created_at) VALUES (?, ?, ?, 'reward_redemption', 'pending', NOW())");
        $stmt->execute([$userId, $selected['value'], $phone]);

        // Optionally initiate STK push if MPESA constants are configured
    if (defined('MPESA_CONSUMER_KEY') && defined('MPESA_ENV') && constant('MPESA_CONSUMER_KEY')) {
            try {
                require_once __DIR__ . '/../../mpesa/mpesa_init.php';
                $mpesa = new MpesaAPI();
                if ($phone) {
                    $reference = 'reward_' . $optionId . '_' . $userId . '_' . time();
                    $resp = $mpesa->initiateSTKPush($phone, $selected['value'], $reference, $selected['name']);
                    // store the response identifiers if available
                    if (!empty($resp['MerchantRequestID']) || !empty($resp['CheckoutRequestID'])) {
                        $update = $db->prepare("UPDATE mpesa_transactions SET merchant_request_id = ?, checkout_request_id = ?, updated_at = NOW() WHERE id = ?");
                        $txId = $db->lastInsertId();
                        $update->execute([$resp['MerchantRequestID'] ?? null, $resp['CheckoutRequestID'] ?? null, $txId]);
                    }
                }
            } catch (Exception $e) {
                // fail silently; mpesa not required for successful redemption (points already deducted)
                error_log('MPESA init failed: ' . $e->getMessage());
            }
        }
    }

    header('Location: /Scrap/views/citizens/rewards.php?status=success&msg=' . urlencode('Reward redeemed successfully'));
    exit;

} catch (Exception $e) {
    error_log($e->getMessage());
    header('Location: /Scrap/views/citizens/rewards.php?status=error&msg=' . urlencode('Internal error processing redemption'));
    exit;
}

?>
