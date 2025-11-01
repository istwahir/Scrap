<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: /Scrap/views/auth/login.php');
    exit;
}

try {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    // Get user info
    $userStmt = $conn->prepare('SELECT name, email FROM users WHERE id = :id');
    $userStmt->execute([':id' => $user_id]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get request statistics
    $statsStmt = $conn->prepare('
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status IN ("pending", "assigned", "en_route") THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status = "completed" AND estimated_weight IS NOT NULL THEN estimated_weight ELSE 0 END) as total_weight
        FROM collection_requests 
        WHERE user_id = :id
    ');
    $statsStmt->execute([':id' => $user_id]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent requests
    $recentStmt = $conn->prepare('
        SELECT r.*, c.name as collector_name 
        FROM collection_requests r
        LEFT JOIN collectors col ON r.collector_id = col.id
        LEFT JOIN users c ON col.user_id = c.id
        WHERE r.user_id = :id 
        ORDER BY r.created_at DESC 
        LIMIT 5
    ');
    $recentStmt->execute([':id' => $user_id]);
    $recentRequests = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Throwable $e) {
    $recentRequests = [];
    $stats = ['total' => 0, 'completed' => 0, 'active' => 0, 'cancelled' => 0, 'total_weight' => 0];
}

// Helper functions for display
function statusBadgeStyles(string $status): string {
    return match ($status) {
        'completed' => 'border-emerald-400/60 bg-emerald-500/10 text-emerald-200',
        'pending' => 'border-amber-400/60 bg-amber-500/10 text-amber-200',
        'assigned', 'en_route' => 'border-sky-400/60 bg-sky-500/10 text-sky-200',
        'cancelled' => 'border-rose-400/60 bg-rose-500/10 text-rose-200',
        default => 'border-white/20 bg-white/5 text-slate-200',
    };
}

function statusLabel(string $status): string {
    return match ($status) {
        'pending' => 'Pending',
        'assigned' => 'Collector assigned',
        'en_route' => 'Collector en route',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        default => ucfirst(str_replace('_', ' ', $status)),
    };
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen hero-gradient">
    <div class="container mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">
                Welcome back, <?= htmlspecialchars($user['name'] ?? 'User') ?>
            </h1>
            <p class="text-emerald-100/80">Track your recycling impact and schedule new collections</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="glass-card p-6 text-center">
                <div class="text-3xl font-bold text-emerald-300"><?= $stats['total'] ?? 0 ?></div>
                <div class="text-sm text-slate-300">Total Requests</div>
            </div>
            <div class="glass-card p-6 text-center">
                <div class="text-3xl font-bold text-sky-300"><?= $stats['active'] ?? 0 ?></div>
                <div class="text-sm text-slate-300">Active Pickups</div>
            </div>
            <div class="glass-card p-6 text-center">
                <div class="text-3xl font-bold text-emerald-300"><?= $stats['completed'] ?? 0 ?></div>
                <div class="text-sm text-slate-300">Completed</div>
            </div>
            <div class="glass-card p-6 text-center">
                <div class="text-3xl font-bold text-amber-300"><?= number_format($stats['total_weight'] ?? 0, 1) ?> kg</div>
                <div class="text-sm text-slate-300">Total Recycled</div>
            </div>
        </div>

        <!-- Recent Requests -->
        <div class="glass-card p-6">
            <h2 class="text-xl font-semibold text-white mb-4">Recent Requests</h2>
            <div class="space-y-3">
                <?php if (!empty($recentRequests)): foreach ($recentRequests as $request): ?>
                <div class="flex items-center justify-between p-4 rounded-lg bg-white/5 border border-white/10">
                    <?php if (!empty($request['photo_url'])): ?>
                        <?php $imgSrc = BASE_URL . '/' . ltrim($request['photo_url'], '/'); ?>
                        <div class="flex-shrink-0 mr-4">
                            <a href="/Scrap/views/citizens/request_details.php?id=<?= $request['id'] ?>">
                                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Request photo" class="w-20 h-20 object-cover rounded-lg border border-white/10">
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="flex-1">
                        <p class="font-medium text-white"><?= htmlspecialchars($request['materials']) ?></p>
                        <p class="text-sm text-emerald-100/80"><?= $request['pickup_date'] ?? '' ?> <?= $request['pickup_time'] ?? '' ?></p>
                        <p class="text-sm text-slate-300"><?= $request['estimated_weight'] ?? '-' ?> kg</p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-xs font-semibold border <?= statusBadgeStyles($request['status']) ?>">
                        <?= statusLabel($request['status']) ?>
                    </span>
                </div>
                <?php endforeach; else: ?>
                <p class="text-slate-400 text-center py-8">No requests yet. Start by scheduling your first pickup!</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 flex flex-wrap gap-4 justify-center">
            <a href="/Scrap/views/citizens/request.php" class="inline-flex items-center gap-3 rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-6 py-3 text-sm font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition hover:scale-[1.02] no-underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Schedule Pickup
            </a>
            <a href="/Scrap/views/citizens/rewards.php" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:border-emerald-300/60 no-underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
                View Rewards
            </a>
            <a href="/Scrap/views/citizens/profile.php" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:border-emerald-300/60 no-underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Edit Profile
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
async function logout() {
    try {
        const response = await fetch('/Scrap/api/logout.php', { method: 'POST' });
        const data = await response.json();
        if (data.status === 'success') {
            window.location.href = '/Scrap/views/auth/login.php';
        } else {
            alert('Logout failed: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Logout failed.');
    }
}
</script>