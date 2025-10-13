<?php
require_once 'includes/auth.php';
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $conn = getDBConnection();
} catch (Throwable $e) {
    die('Database connection error.');
}

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT name, email, phone, created_at FROM users WHERE id = :id');
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('User not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email) {
        $error_message = 'Name and email are required.';
    } else {
        $params = ['name' => $name, 'email' => $email, 'phone' => $phone, 'id' => $user_id];
        $sql = 'UPDATE users SET name = :name, email = :email, phone = :phone';

        if (!empty($password)) {
            $sql .= ', password = :password';
            $params['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $sql .= ' WHERE id = :id';

        try {
            $updateStmt = $conn->prepare($sql);
            $updateStmt->execute($params);
            $success_message = 'Profile updated successfully.';
            $user['name'] = $name;
            $user['email'] = $email;
            $user['phone'] = $phone;
        } catch (Throwable $e) {
            $error_message = 'Failed to update profile. Please try again.';
        }
    }
}

$statusCounts = [];
$recentRequests = [];
$nextPickup = null;
$pointsSnapshot = [
    'balance' => 0,
    'total_earned' => 0,
    'total_redeemed' => 0,
];

try {
    $statusStmt = $conn->prepare('
        SELECT status, COUNT(*) AS total
        FROM collection_requests
        WHERE user_id = :id
        GROUP BY status
    ');
    $statusStmt->execute([':id' => $user_id]);
    while ($row = $statusStmt->fetch(PDO::FETCH_ASSOC)) {
        $statusCounts[$row['status']] = (int) $row['total'];
    }
} catch (Throwable $e) {
    $statusCounts = [];
}

$aggregateStats = [
    'total_requests' => array_sum($statusCounts),
    'completed_requests' => $statusCounts['completed'] ?? 0,
    'active_requests' => ($statusCounts['pending'] ?? 0)
        + ($statusCounts['assigned'] ?? 0)
        + ($statusCounts['en_route'] ?? 0),
    'cancelled_requests' => $statusCounts['cancelled'] ?? 0,
];

try {
    $nextStmt = $conn->prepare('
        SELECT id, materials, status, pickup_date, pickup_time, pickup_address
        FROM collection_requests
        WHERE user_id = :id
          AND status IN ("pending", "assigned", "en_route")
        ORDER BY pickup_date IS NULL, pickup_date ASC, pickup_time ASC
        LIMIT 1
    ');
    $nextStmt->execute([':id' => $user_id]);
    $nextPickup = $nextStmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    $nextPickup = null;
}

try {
    $recentStmt = $conn->prepare('
        SELECT id, materials, status, pickup_date, pickup_time, created_at, estimated_weight
        FROM collection_requests
        WHERE user_id = :id
        ORDER BY created_at DESC
        LIMIT 6
    ');
    $recentStmt->execute([':id' => $user_id]);
    $recentRequests = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $recentRequests = [];
}

try {
    $pointsStmt = $conn->prepare('
        SELECT
            COALESCE(SUM(points), 0) AS balance,
            COALESCE(SUM(CASE WHEN points > 0 THEN points ELSE 0 END), 0) AS total_earned,
            COALESCE(ABS(SUM(CASE WHEN points < 0 THEN points ELSE 0 END)), 0) AS total_redeemed
        FROM rewards
        WHERE user_id = :id
    ');
    $pointsStmt->execute([':id' => $user_id]);
    $pointsSnapshot = $pointsStmt->fetch(PDO::FETCH_ASSOC) ?: $pointsSnapshot;
} catch (Throwable $e) {
    // leave defaults
}

if (!function_exists('formatDateTimeDisplay')) {
    function formatDateTimeDisplay(?string $date, ?string $time = null, string $fallback = '—'): string
    {
        if (!$date) {
            return $fallback;
        }

        try {
            $formatted = $date;
            if ($time) {
                $formatted = sprintf('%s %s', $date, $time);
            }

            $dt = new DateTime($formatted);
            return $dt->format('M j, Y • g:i A');
        } catch (Throwable $e) {
            return $fallback;
        }
    }
}

if (!function_exists('statusBadgeStyles')) {
    function statusBadgeStyles(string $status): string
    {
        return match ($status) {
            'completed' => 'border-emerald-400/60 bg-emerald-500/10 text-emerald-200',
            'pending' => 'border-amber-400/60 bg-amber-500/10 text-amber-200',
            'assigned', 'en_route' => 'border-sky-400/60 bg-sky-500/10 text-sky-200',
            'cancelled' => 'border-rose-400/60 bg-rose-500/10 text-rose-200',
            default => 'border-white/20 bg-white/5 text-slate-200',
        };
    }
}

if (!function_exists('statusLabel')) {
    function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pending',
            'assigned' => 'Collector assigned',
            'en_route' => 'Collector en route',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}

$joinedText = '—';
if (!empty($user['created_at'])) {
    try {
        $joinedDate = new DateTime($user['created_at']);
        $joinedText = $joinedDate->format('F j, Y');
    } catch (Throwable $e) {
        $joinedText = '—';
    }
}

$nameParts = preg_split('/\s+/', trim($user['name'] ?? '')) ?: [];
$initials = strtoupper(mb_substr($nameParts[0] ?? '', 0, 1) . mb_substr($nameParts[1] ?? '', 0, 1));
$initials = $initials ?: 'U';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Kiambu Recycling &amp; Scraps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
            backdrop-filter: blur(18px);
            box-shadow: 0 20px 45px -25px rgba(15, 118, 110, 0.6);
        }
        .hero-gradient {
            background: radial-gradient(120% 120% at 50% 0%, rgba(16, 185, 129, 0.25) 0%, transparent 60%),
                        linear-gradient(135deg, #064e3b 0%, #0f172a 60%, #020617 100%);
        }
        .grid-fade {
            background-image: linear-gradient(rgba(99, 102, 241, 0.12) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(56, 189, 248, 0.12) 1px, transparent 1px);
            background-size: 32px 32px;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased">
    <?php include 'includes/header.php'; ?>

    <header class="hero-gradient relative overflow-hidden pb-28">
        <div class="absolute inset-0 grid-fade opacity-25"></div>
        <div class="relative z-10 mx-auto max-w-6xl px-6">
            <div class="pt-24">
                <div class="grid gap-12 lg:grid-cols-[1.15fr,0.85fr] lg:items-center">
                    <div class="space-y-8">
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200/30 bg-emerald-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-emerald-100">
                            Personal dashboard · live snapshot
                        </div>
                        <div class="space-y-4">
                            <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                                Hi <?php echo htmlspecialchars($user['name']); ?>, keep the momentum going.
                            </h1>
                            <p class="max-w-2xl text-base text-emerald-100/80 sm:text-lg">
                                Review your recycling progress, update your details, and plan the next pickup — all in one place.
                                Your impact updates in real time as collectors complete requests.
                            </p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="glass-card rounded-2xl border border-white/10 p-5">
                                <div class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-200/80">Total pickups</div>
                                <div class="mt-3 text-3xl font-semibold text-white" data-counter data-target="<?php echo (int) $aggregateStats['total_requests']; ?>">0</div>
                            </div>
                            <div class="glass-card rounded-2xl border border-white/10 p-5">
                                <div class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-200/80">Completed</div>
                                <div class="mt-3 text-3xl font-semibold text-white" data-counter data-target="<?php echo (int) $aggregateStats['completed_requests']; ?>">0</div>
                            </div>
                            <div class="glass-card rounded-2xl border border-white/10 p-5">
                                <div class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-200/80">Active</div>
                                <div class="mt-3 text-3xl font-semibold text-white" data-counter data-target="<?php echo (int) $aggregateStats['active_requests']; ?>">0</div>
                            </div>
                            <div class="glass-card rounded-2xl border border-white/10 p-5">
                                <div class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-200/80">Reward points</div>
                                <div class="mt-3 text-3xl font-semibold text-white" data-counter data-target="<?php echo (int) $pointsSnapshot['balance']; ?>">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card relative overflow-hidden rounded-3xl border border-white/10 p-8">
                        <div class="absolute -left-14 -top-14 h-40 w-40 rounded-full bg-emerald-400/20 blur-3xl"></div>
                        <div class="absolute -bottom-14 right-0 h-44 w-44 rounded-full bg-sky-400/20 blur-3xl"></div>
                        <div class="relative space-y-5">
                            <div class="flex items-center justify-between gap-4">
                                <h2 class="text-lg font-semibold text-white">Next pickup</h2>
                                <span class="rounded-full border border-white/10 px-3 py-1 text-xs uppercase tracking-[0.25em] text-white/70">Planner</span>
                            </div>
                            <?php if ($nextPickup) : ?>
                                <div class="space-y-3 text-sm text-emerald-100/80">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-lg text-emerald-300">🚚</span>
                                        <div>
                                            <div class="text-white/90 font-medium">
                                                <?php echo htmlspecialchars($nextPickup['materials'] ?? 'Mixed materials'); ?>
                                            </div>
                                            <div class="text-xs uppercase tracking-[0.3em] text-emerald-200/80">
                                                <?php echo statusLabel($nextPickup['status'] ?? 'pending'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm">
                                        <div class="text-emerald-200/90">Scheduled for</div>
                                        <div class="text-white font-medium">
                                            <?php echo formatDateTimeDisplay($nextPickup['pickup_date'] ?? null, $nextPickup['pickup_time'] ?? null, 'Not scheduled'); ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($nextPickup['pickup_address'])) : ?>
                                        <div class="flex items-start gap-3 text-sm text-slate-200/90">
                                            <span class="mt-1 text-lg text-emerald-300">📍</span>
                                            <span><?php echo htmlspecialchars($nextPickup['pickup_address']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <a href="request.php" class="inline-flex items-center gap-2 rounded-full bg-emerald-500/90 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-emerald-400">
                                        Manage requests
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            <?php else : ?>
                                <div class="space-y-4 text-sm text-emerald-100/80">
                                    <p>No pickups queued right now. Schedule one to keep earning rewards.</p>
                                    <a href="request.php" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-emerald-400">
                                        Book a new pickup
                                        <span aria-hidden="true">→</span>
                                    </a>
                                    <p class="text-xs uppercase tracking-[0.3em] text-emerald-200/70">Tip: Mornings fill first — book early.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="relative z-10 -mt-16 space-y-16 pb-24">
        <section class="mx-auto max-w-6xl px-6">
            <div class="grid gap-8 lg:grid-cols-[1.05fr,0.95fr]">
                <div class="glass-card rounded-3xl border border-white/10 p-8">
                    <div class="flex flex-col gap-6 md:flex-row md:items-center">
                        <div class="grid h-20 w-20 place-items-center rounded-2xl border border-white/10 bg-white/10 text-2xl font-semibold text-white">
                            <?php echo htmlspecialchars($initials); ?>
                        </div>
                        <div class="space-y-4 text-sm text-slate-200/90">
                            <div>
                                <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Account owner</div>
                                <div class="text-2xl font-semibold text-white">
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <span class="text-emerald-300">✉️</span>
                                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                                <?php if (!empty($user['phone'])) : ?>
                                    <div class="flex items-center gap-3">
                                        <span class="text-emerald-300">📞</span>
                                        <span><?php echo htmlspecialchars($user['phone']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="flex items-center gap-3 text-sm text-slate-300/80">
                                    <span class="text-emerald-300">🗓</span>
                                    <span>Joined on <?php echo htmlspecialchars($joinedText); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 p-4 text-sm text-emerald-100/85">
                            <h3 class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-200/80">Completed pickups</h3>
                            <p class="mt-2 text-lg font-semibold text-white">
                                <?php echo (int) $aggregateStats['completed_requests']; ?>
                                <span class="text-xs font-normal uppercase tracking-[0.3em] text-emerald-200/70">all time</span>
                            </p>
                        </div>
                        <div class="rounded-2xl border border-sky-400/30 bg-sky-500/10 p-4 text-sm text-sky-100/85">
                            <h3 class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-200/80">Active queue</h3>
                            <p class="mt-2 text-lg font-semibold text-white">
                                <?php echo (int) $aggregateStats['active_requests']; ?>
                                <span class="text-xs font-normal uppercase tracking-[0.3em] text-sky-200/70">in progress</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-3xl border border-white/10 p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-white">Update your profile</h2>
                            <p class="mt-1 text-sm text-slate-300/80">Adjust your contact details and secure your account. Changes apply immediately.</p>
                        </div>
                        <span class="rounded-full border border-white/10 px-3 py-1 text-xs uppercase tracking-[0.25em] text-white/70">Profile</span>
                    </div>

                    <?php if (isset($success_message)) : ?>
                        <div class="mt-6 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100/90">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($error_message)) : ?>
                        <div class="mt-6 rounded-2xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100/90">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="mt-8 space-y-5">
                        <div>
                            <label for="name" class="text-xs font-semibold uppercase tracking-[0.3em] text-white/60">Full name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="<?php echo htmlspecialchars($user['name']); ?>"
                                required
                                class="mt-2 w-full rounded-2xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                            >
                        </div>
                        <div>
                            <label for="email" class="text-xs font-semibold uppercase tracking-[0.3em] text-white/60">Email address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                required
                                class="mt-2 w-full rounded-2xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                            >
                        </div>
                        <div>
                            <label for="phone" class="text-xs font-semibold uppercase tracking-[0.3em] text-white/60">Phone number</label>
                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                placeholder="e.g. 07xx xxx xxx"
                                class="mt-2 w-full rounded-2xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                            >
                        </div>
                        <div>
                            <label for="password" class="text-xs font-semibold uppercase tracking-[0.3em] text-white/60">Password</label>
                            <p class="mt-2 text-xs text-slate-300/70">Leave blank to keep your current password. Use 12+ characters for strong security.</p>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="mt-2 w-full rounded-2xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                            >
                        </div>
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                        >
                            Save changes
                            <span aria-hidden="true">→</span>
                        </button>
                    </form>

                    <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-4 text-xs text-slate-300/70">
                        <strong class="text-white/80">Security reminder:</strong> enable two-factor authentication in the mobile app to protect your rewards balance.
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-6">
            <div class="grid gap-8 lg:grid-cols-[1.15fr,0.85fr]">
                <div class="glass-card rounded-3xl border border-white/10 p-8">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-white">Recent recycling activity</h2>
                            <p class="mt-1 text-sm text-slate-300/80">Track the latest pickups and their status. Rebook in one tap.</p>
                        </div>
                        <a href="request.php" class="inline-flex items-center gap-2 rounded-full border border-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-white/70 transition hover:border-emerald-300/50 hover:text-emerald-200">
                            View all
                        </a>
                    </div>

                    <div class="mt-8 space-y-4">
                        <?php if ($recentRequests) : ?>
                            <?php foreach ($recentRequests as $request) : ?>
                                <article class="rounded-2xl border border-white/10 bg-white/5 p-5 transition hover;border-emerald-400/50">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="space-y-1 text-sm text-slate-200/90">
                                            <div class="text-white font-semibold">
                                                <?php echo htmlspecialchars($request['materials'] ?? 'Mixed materials'); ?>
                                            </div>
                                            <div class="text-xs uppercase tracking-[0.3em] text-slate-400/80">
                                                Requested <?php echo formatDateTimeDisplay($request['created_at'] ?? null, null, 'recently'); ?>
                                            </div>
                                            <div class="text-xs text-slate-300/70">
                                                Pickup window: <?php echo formatDateTimeDisplay($request['pickup_date'] ?? null, $request['pickup_time'] ?? null, 'Not scheduled'); ?>
                                            </div>
                                            <?php if (!empty($request['estimated_weight'])) : ?>
                                                <div class="text-xs text-slate-300/70">
                                                    Estimated weight: <?php echo htmlspecialchars($request['estimated_weight']); ?> kg
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex items-center gap-3 self-start">
                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] <?php echo statusBadgeStyles($request['status'] ?? 'pending'); ?>">
                                                <?php echo statusLabel($request['status'] ?? 'pending'); ?>
                                            </span>
                                            <a href="request.php" class="inline-flex items-center gap-1 rounded-full border border-white/15 px-3 py-1 text-xs uppercase tracking-[0.3em] text-white/70 transition hover:border-emerald-300/50 hover:text-emerald-200">
                                                Rebook
                                                <span aria-hidden="true">↻</span>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-sm text-slate-300/80">
                                No recycling activity yet. Schedule your first collection to see it here.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="glass-card rounded-3xl border border-white/10 p-8">
                        <div class="flex items-center محسوس justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold text-white">Rewards snapshot</h2>
                                <p class="mt-1 text-sm text-slate-300/80">Redeem points for cash, airtime, or eco goodies.</p>
                            </div>
                            <span class="rounded-full border border-white/10 px-3 py-1 text-xs uppercase tracking-[0.25em] text-white/70">Rewards</span>
                        </div>

                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 p-4 text-sm text-emerald-100/80">
                                <div class="text-xs uppercase tracking-[0.3em] text-emerald-200/80">Available points</div>
                                <div class="mt-2 text-2xl font-semibold text-white"><?php echo (int) $pointsSnapshot['balance']; ?></div>
                            </div>
                            <div class="rounded-2xl border border-sky-400/30 bg-sky-500/10 p-4 text-sm text-sky-100/80">
                                <div class="text-xs uppercase tracking-[0.3em] text-sky-200/80">Total earned</div>
                                <div class="mt-2 text-2xl font-semibold text-white"><?php echo (int) $pointsSnapshot['total_earned']; ?></div>
                            </div>
                            <div class="rounded-2xl border border-amber-400/30 bg-amber-500/10 p-4 text-sm text-amber-100/80">
                                <div class="text-xs uppercase tracking-[0.3em] text-amber-200/80">Redeemed</div>
                                <div class="mt-2 text-2xl font-semibold text-white"><?php echo (int) $pointsSnapshot['total_redeemed']; ?></div>
                            </div>
                            <div class="rounded-2xl border border-white/15 bg-white/5 p-4 text-sm text-slate-200/85">
                                <div class="text-xs uppercase tracking-[0.3em] text-white/60">Next milestone</div>
                                <div class="mt-2 text-lg font-semibold text-white">Redeem 1,000 pts for KSh 100</div>
                            </div>
                        </div>

                        <a href="reward.html" class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-emerald-100">
                            Browse reward catalog
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>

                    <div class="glass-card rounded-3xl border border-white/10 p-8">
                        <h2 class="text-xl font-semibold text-white">Eco goals for this week</h2>
                        <p class="mt-1 text-sm text-slate-300/80">Stay consistent with quick wins recommended for you.</p>
                        <ul class="mt-6 space-y-4 text-sm text-slate-200/90">
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-emerald-400"></span>
                                Set out clean plastics and metals to earn a completion streak bonus.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-sky-400"></span>
                                Invite a neighbour via your referral link for 200 bonus points.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-amber-400"></span>
                                Drop small e-waste at a hub to unlock the electronics badge.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function animateCounters() {
            const counters = document.querySelectorAll('[data-counter]');
            if (!counters.length) return;

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;

                    const el = entry.target;
                    const target = parseInt(el.dataset.target || '0', 10);
                    let current = 0;
                    const increment = Math.max(1, Math.ceil(target / 120));

                    const update = () => {
                        current += increment;
                        if (current >= target) {
                            el.textContent = target.toLocaleString();
                        } else {
                            el.textContent = current.toLocaleString();
                            requestAnimationFrame(update);
                        }
                    };

                    requestAnimationFrame(update);
                    obs.unobserve(el);
                });
            }, { threshold: 0.5 });

            counters.forEach(counter => observer.observe(counter));
        }

        document.addEventListener('DOMContentLoaded', animateCounters);
    </script>
</body>
</html>