<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/Reward.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Scrap/views/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

$rewardModel = new Reward();
$stats = $rewardModel->getStats($user_id);
$options = $rewardModel->getRedemptionOptions();
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<style>
    .hero-gradient {
        background: radial-gradient(120% 120% at 50% 0%, rgba(16,185,129,0.18) 0%, transparent 60%),
                    linear-gradient(135deg, #064e3b 0%, #0f172a 60%, #020617 100%);
    }
    .glass-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.12), rgba(255,255,255,0.04));
        backdrop-filter: blur(18px);
        box-shadow: 0 20px 45px -25px rgba(15,118,110,0.3);
    }
    .reward-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .reward-card:hover {
        transform: translateY(-4px) scale(1.03);
        box-shadow: 0 16px 32px -8px rgba(16,185,129,0.18);
    }
    .points-badge {
        background: linear-gradient(135deg, #16a34a, #059669);
    }
</style>
<div class="hero-gradient min-h-screen text-slate-100 antialiased">
    <div class="relative z-10 max-w-6xl mx-auto px-4 py-8">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-white mb-2">My Rewards</h1>
            <p class="text-emerald-100/80 text-lg">Track your points and redeem rewards for your recycling efforts</p>
        </div>
        <!-- Points Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="glass-card rounded-2xl p-6 border border-white/10 text-center">
                <p class="text-sm font-medium text-emerald-100/60 uppercase tracking-wider mb-2">Available Points</p>
                <div class="flex justify-center items-center mb-2">
                    <span class="text-4xl font-bold text-emerald-300"><?= $stats['available_points'] ?></span>
                    <span class="ml-2 px-2 py-1 rounded-full points-badge text-xs font-semibold">pts</span>
                </div>
                <p class="text-slate-300 text-xs">Earn more by recycling!</p>
            </div>
            <div class="glass-card rounded-2xl p-6 border border-white/10 text-center">
                <p class="text-sm font-medium text-emerald-100/60 uppercase tracking-wider mb-2">Total Earned</p>
                <span class="text-3xl font-bold text-blue-300"><?= $stats['total_earned'] ?></span>
                <p class="text-slate-300 text-xs">All-time points</p>
            </div>
            <div class="glass-card rounded-2xl p-6 border border-white/10 text-center">
                <p class="text-sm font-medium text-emerald-100/60 uppercase tracking-wider mb-2">Total Redeemed</p>
                <span class="text-3xl font-bold text-purple-300"><?= $stats['total_redeemed'] ?></span>
                <p class="text-slate-300 text-xs">Rewards claimed</p>
            </div>
        </div>
        <!-- Redemption Options -->
        <div class="glass-card rounded-2xl p-6 border border-white/10 mb-8">
            <h2 class="text-xl font-semibold mb-6 text-white">Redeem Your Points</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($options as $option): 
                    $canRedeem = $stats['available_points'] >= $option['points_required'];
                ?>
                <div class="reward-card glass-card border rounded-lg p-6 <?= $canRedeem ? 'border-green-200 bg-emerald-900/10' : 'border-gray-200 bg-slate-900/10' ?>">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-2"><?= $option['type'] === 'airtime' ? '📱' : '💰' ?></div>
                        <h3 class="font-semibold text-lg text-white"><?= htmlspecialchars($option['name']) ?></h3>
                        <p class="text-sm text-emerald-100/80 mt-1"><?= $option['points_required'] ?> points required</p>
                    </div>
                    <form method="post" action="redeem_reward.php">
                        <input type="hidden" name="option_id" value="<?= $option['id'] ?>">
                        <button type="submit"
                            class="w-full py-2 px-4 rounded-lg font-medium transition duration-200 <?= $canRedeem ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white hover:shadow-xl hover:shadow-emerald-500/40' : 'bg-gray-300 text-gray-500 cursor-not-allowed' ?>"
                            <?= $canRedeem ? '' : 'disabled' ?>>
                            <?= $canRedeem ? 'Redeem Now' : 'Insufficient Points' ?>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Recent Transactions -->
        <div class="glass-card rounded-2xl p-6 border border-white/10">
            <h2 class="text-xl font-semibold mb-6 text-white">Recent Transactions</h2>
            <div class="space-y-4">
                <?php if (empty($stats['recent_transactions'])): ?>
                    <p class="text-slate-400 text-center py-8">No transactions yet. Start recycling to earn points!</p>
                <?php else: foreach ($stats['recent_transactions'] as $transaction): 
                    $isPositive = $transaction['points'] > 0;
                    $date = date('M j, Y', strtotime($transaction['created_at']));
                ?>
                <div class="flex items-center justify-between p-4 border rounded-lg glass-card bg-slate-900/10">
                    <div>
                        <p class="font-medium text-white"><?= htmlspecialchars($transaction['description'] ?? ($isPositive ? 'Points earned' : 'Points redeemed')) ?></p>
                        <p class="text-sm text-emerald-100/80"><?= $date ?></p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold <?= $isPositive ? 'text-green-300' : 'text-red-300' ?>">
                            <?= $isPositive ? '+' : '' ?><?= $transaction['points'] ?> pts
                        </p>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>