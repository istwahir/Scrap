<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config.php';

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

// Check if user is already a collector
$isCollector = false;
$collectorStatus = null;
try {
    $cstmt = $conn->prepare('SELECT id, active_status FROM collectors WHERE user_id = :id LIMIT 1');
    $cstmt->execute([':id' => $user_id]);
    if ($row = $cstmt->fetch(PDO::FETCH_ASSOC)) {
        $isCollector = true;
        $collectorStatus = $row['active_status'] ?? null;
    }
} catch (Throwable $e) {
    $isCollector = false;
}
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
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
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
    <?php include __DIR__ . '/../../includes/header.php'; ?>

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
                                    <a href="/Scrap/views/citizens/request.php" class="inline-flex items-center gap-2 rounded-full bg-emerald-500/90 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-emerald-400">
                                        Manage requests
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            <?php else : ?>
                                <div class="space-y-4 text-sm text-emerald-100/80">
                                    <p>No pickups queued right now. Schedule one to keep earning rewards.</p>
                                    <a href="/Scrap/views/citizens/request.php" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-emerald-400">
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
                <div class="glass-card rounded-3xl border border-white/10 p-8 mx-auto max-w-3xl lg:col-span-2">
                    <div class="flex flex-col gap-6 md:flex-row md:items-center">
                        <div class="grid h-24 w-24 place-items-center rounded-2xl border border-white/10 bg-white/10 text-3xl font-semibold text-white">
                            <?php echo htmlspecialchars($initials); ?>
                        </div>
                        <div class="space-y-4 text-sm text-slate-200/90">
                            <div>
                                <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Account owner</div>
                                <div class="text-3xl sm:text-4xl font-semibold text-white">
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

                    <div class="mt-8 grid gap-4 sm:grid-cols-2 mb-3">
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
                                class="mt-2 w-full rounded-2xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        </div>
                        <div>
                            <label for="email" class="text-xs font-semibold uppercase tracking-[0.3em] text-white/60">Email address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                required
                                class="mt-2 w-full rounded-2xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        </div>
                        <div>
                            <label for="phone" class="text-xs font-semibold uppercase tracking-[0.3em] text-white/60">Phone number</label>
                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                placeholder="e.g. 07xx xxx xxx"
                                class="mt-2 w-full rounded-2xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        </div>
                        <div>
                            <label for="password" class="text-xs font-semibold uppercase tracking-[0.3em] text-white/60">Password</label>
                            <p class="mt-2 text-xs text-slate-300/70">Leave blank to keep your current password. Use 12+ characters for strong security.</p>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="mt-2 w-full rounded-2xl border border-white/15 bg-white/5 px-4 py-3 text-sm text-white shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                        </div>
                        <button
                            type="submit"
                            class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
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
            <div>
                <div class="grid gap-8 md:grid-cols-2">
                    <div class="glass-card rounded-3xl border border-white/10 p-8 self-start max-h-96 overflow-auto">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold text-white">Eco goals</h2>
                                <p class="mt-1 text-sm text-slate-300/80">Create your weekly to-do list and check items off as you go.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button id="btnCompleteAll" class="rounded-lg border border-white/15 px-3 py-1 text-xs text-white/80 hover:border-emerald-300/50 hover:text-emerald-200">Complete all</button>
                                <button id="btnClearCompleted" class="rounded-lg border border-rose-400/30 px-3 py-1 text-xs text-rose-100/90 hover:bg-rose-500/10">Clear completed</button>
                                <span class="rounded-full border border-white/10 px-3 py-1 text-xs uppercase tracking-[0.25em] text-white/70">To‑Do</span>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <input id="newGoalInput" type="text" maxlength="255" placeholder="Add a new goal..." class="flex-1 rounded-2xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-slate-400 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
                            <button id="addGoalBtn" class="rounded-2xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-emerald-400">Add</button>
                        </div>

                        <ul id="goalsList" class="mt-6 space-y-3"></ul>

                        <template id="goalItemTemplate">
                            <li class="flex items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/5 p-3" draggable="true">
                                <div class="flex items-center gap-3 flex-1">
                                    <span class="cursor-move select-none text-slate-400">⠿</span>
                                    <input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-slate-900/50" />
                                    <span class="text-sm text-slate-200/90 flex-1"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button data-action="edit" class="rounded-lg border border-white/15 px-2 py-1 text-xs text-white/80 hover:border-emerald-300/50 hover:text-emerald-200">Edit</button>
                                    <button data-action="save" class="hidden rounded-lg bg-emerald-500 px-2 py-1 text-xs font-semibold text-slate-900 hover:bg-emerald-400">Save</button>
                                    <button data-action="cancel" class="hidden rounded-lg border border-white/15 px-2 py-1 text-xs text-white/80 hover:border-rose-400/40 hover:text-rose-200">Cancel</button>
                                    <button data-action="delete" class="rounded-lg border border-rose-400/30 px-2 py-1 text-xs text-rose-100/90 hover:bg-rose-500/10">Delete</button>
                                </div>
                            </li>
                        </template>
                    </div>
                    <div class="glass-card rounded-3xl border border-white/10 p-8 max-h-70 self-start">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold text-white">
                                    <?php echo $isCollector ? 'Collector tools' : 'Become a collector'; ?>
                                </h2>
                                <p class="mt-1 text-sm text-slate-300/80">
                                    <?php if ($isCollector): ?>
                                        Access your collector dashboard and manage pickups.
                                        <?php if ($collectorStatus): ?>
                                            <span class="ml-1 rounded-full border border-white/10 px-2 py-0.5 text-[10px] uppercase tracking-[0.25em] text-white/70">Status: <?php echo htmlspecialchars($collectorStatus); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Register to collect recyclables in your area and start earning.
                                    <?php endif; ?>
                                </p>
                            </div>
                            <span class="rounded-full border border-white/10 px-3 py-1 text-xs uppercase tracking-[0.25em] text-white/70">Collector</span>
                        </div>

                        <?php if ($isCollector): ?>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="/Scrap/views/collectors/dashboard.php" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-emerald-400">Open dashboard <span aria-hidden="true">→</span></a>
                                <a href="/Scrap/views/collectors/requests.php" class="inline-flex items-center gap-2 rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white/80 transition hover:border-emerald-300/50 hover:text-emerald-200">View requests</a>
                            </div>
                        <?php else: ?>
                            <div class="mt-6">
                                <a href="/Scrap/views/collectors/register.php" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-emerald-100">
                                    Register as a collector
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modals for Eco Goals (Edit & Delete) -->
    <div id="editGoalModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative mx-auto mt-28 w-[92%] max-w-md rounded-2xl border border-white/10 bg-slate-900 p-6 text-slate-100 shadow-xl">
            <h3 class="text-lg font-semibold text-white">Edit goal</h3>
            <p class="mt-1 text-sm text-slate-300/80">Update the title of your goal.</p>
            <input id="editGoalInput" type="text" maxlength="255" class="mt-4 w-full rounded-2xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm text-white placeholder:text-slate-400 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40" />
            <div class="mt-5 flex justify-end gap-2">
                <button id="editGoalCancelBtn" class="rounded-xl border border-white/15 px-4 py-2 text-sm text-white/80 hover:border-emerald-300/40 hover:text-emerald-200">Cancel</button>
                <button id="editGoalSaveBtn" class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-emerald-400">Save</button>
            </div>
        </div>
    </div>

    <div id="deleteGoalModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative mx-auto mt-28 w-[92%] max-w-md rounded-2xl border border-white/10 bg-slate-900 p-6 text-slate-100 shadow-xl">
            <h3 class="text-lg font-semibold text-white">Delete goal</h3>
            <p class="mt-1 text-sm text-slate-300/80">Are you sure you want to delete this goal?</p>
            <div class="mt-3 rounded-xl border border-white/10 bg-white/5 p-3 text-sm text-slate-200/90" id="deleteGoalTitle">—</div>
            <div class="mt-5 flex justify-end gap-2">
                <button id="deleteGoalCancelBtn" class="rounded-xl border border-white/15 px-4 py-2 text-sm text-white/80 hover:border-emerald-300/40 hover:text-emerald-200">Cancel</button>
                <button id="deleteGoalConfirmBtn" class="rounded-xl border border-rose-400/30 bg-rose-500/10 px-4 py-2 text-sm font-semibold text-rose-100/90 hover:bg-rose-500/20">Delete</button>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script>
        const ECO_BASE = '<?php echo rtrim(BASE_URL, '/'); ?>/api/eco_goals';

        async function goalsFetch(path, opts = {}) {
            const res = await fetch(`${ECO_BASE}/${path}`, {
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                ...opts,
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || data.status !== 'success') {
                throw new Error(data.message || 'Request failed');
            }
            return data.data || {};
        }

        async function loadGoals() {
            try {
                const {
                    goals = []
                } = await goalsFetch('list.php');
                renderGoals(goals);
            } catch (e) {
                console.error('Failed to load goals', e);
            }
        }

        function renderGoals(goals) {
            const ul = document.getElementById('goalsList');
            const tpl = document.getElementById('goalItemTemplate');
            ul.innerHTML = '';
            goals.forEach(g => {
                const node = tpl.content.firstElementChild.cloneNode(true);
                node.dataset.id = g.id;
                const cb = node.querySelector('input[type="checkbox"]');
                const text = node.querySelector('span');
                cb.checked = !!g.is_completed;
                text.textContent = g.title;
                if (cb.checked) {
                    text.classList.add('line-through', 'text-slate-400');
                }
                // Drag & drop handlers
                node.addEventListener('dragstart', (e) => {
                    node.classList.add('opacity-60');
                    e.dataTransfer.setData('text/plain', g.id);
                });
                node.addEventListener('dragend', () => node.classList.remove('opacity-60'));
                node.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    node.classList.add('ring-1', 'ring-emerald-400/40');
                });
                node.addEventListener('dragleave', () => node.classList.remove('ring-1', 'ring-emerald-400/40'));
                node.addEventListener('drop', async (e) => {
                    e.preventDefault();
                    node.classList.remove('ring-1', 'ring-emerald-400/40');
                    const draggedId = e.dataTransfer.getData('text/plain');
                    const draggedEl = [...ul.children].find(li => li.dataset.id === draggedId);
                    if (!draggedEl || draggedEl === node) return;
                    // Insert before the drop target
                    ul.insertBefore(draggedEl, node);
                    await persistOrder(ul);
                });
                cb.addEventListener('change', async () => {
                    try {
                        await goalsFetch('toggle.php', {
                            method: 'POST',
                            body: JSON.stringify({
                                id: g.id,
                                is_completed: cb.checked
                            })
                        });
                        text.classList.toggle('line-through', cb.checked);
                        text.classList.toggle('text-slate-400', cb.checked);
                    } catch (e) {
                        cb.checked = !cb.checked;
                    }
                });
                node.querySelector('[data-action="delete"]').addEventListener('click', () => {
                    openDeleteGoalModal(g.id, text.textContent || '', () => {
                        node.remove();
                    });
                });
                // Inline edit
                const btnEdit = node.querySelector('[data-action="edit"]');
                const btnSave = node.querySelector('[data-action="save"]');
                const btnCancel = node.querySelector('[data-action="cancel"]');
                let editInput;

                function enterEdit() {
                    if (editInput) return;
                    btnEdit.classList.add('hidden');
                    btnSave.classList.remove('hidden');
                    btnCancel.classList.remove('hidden');
                    editInput = document.createElement('input');
                    editInput.type = 'text';
                    editInput.maxLength = 255;
                    editInput.value = text.textContent || '';
                    editInput.className = 'flex-1 rounded-xl border border-white/15 bg-white/5 px-2 py-1 text-sm text-white focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/30';
                    text.replaceWith(editInput);
                    editInput.focus();
                    editInput.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') btnSave.click();
                        if (e.key === 'Escape') btnCancel.click();
                    });
                }

                function exitEdit(saved = false) {
                    if (!editInput) return;
                    const newSpan = document.createElement('span');
                    newSpan.className = 'text-sm text-slate-200/90 flex-1';
                    newSpan.textContent = saved ? (editInput.value || '').trim() : text.textContent || '';
                    editInput.replaceWith(newSpan);
                    editInput = null;
                    btnEdit.classList.remove('hidden');
                    btnSave.classList.add('hidden');
                    btnCancel.classList.add('hidden');
                    // update local variable reference
                    // eslint-disable-next-line no-param-reassign
                    node.querySelector('.text-sm.text-slate-200\/90');
                }
                btnEdit.addEventListener('click', enterEdit);
                btnCancel.addEventListener('click', () => exitEdit(false));
                btnSave.addEventListener('click', async () => {
                    if (!editInput) return;
                    const val = (editInput.value || '').trim();
                    if (!val) {
                        editInput.focus();
                        return;
                    }
                    try {
                        await goalsFetch('update.php', {
                            method: 'POST',
                            body: JSON.stringify({
                                id: g.id,
                                title: val
                            })
                        });
                        text.textContent = val;
                        exitEdit(true);
                    } catch (e) {
                        /* noop */ }
                });
                ul.appendChild(node);
            });
        }

        async function persistOrder(ul) {
            const order = Array.from(ul.children).map(li => parseInt(li.dataset.id, 10)).filter(Boolean);
            try {
                await goalsFetch('reorder.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        order
                    })
                });
            } catch (e) {
                console.error('reorder failed', e);
                loadGoals();
            }
        }

        // Modal handling for Edit/Delete
        const modalState = {
            edit: {
                id: null,
                onSaved: null
            },
            delete: {
                id: null,
                onDeleted: null
            },
        };

        function show(el) {
            el.classList.remove('hidden');
        }

        function hide(el) {
            el.classList.add('hidden');
        }

        function initGoalModals() {
            // Edit modal
            const editEl = document.getElementById('editGoalModal');
            const editInput = document.getElementById('editGoalInput');
            const editSave = document.getElementById('editGoalSaveBtn');
            const editCancel = document.getElementById('editGoalCancelBtn');

            editCancel.addEventListener('click', () => hide(editEl));
            editSave.addEventListener('click', async () => {
                const id = modalState.edit.id;
                const val = (editInput.value || '').trim();
                if (!id || !val) return;
                try {
                    await goalsFetch('update.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            id,
                            title: val
                        })
                    });
                    if (typeof modalState.edit.onSaved === 'function') modalState.edit.onSaved(val);
                    hide(editEl);
                } catch (e) {
                    /* noop */ }
            });
            editInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') editSave.click();
            });

            // Delete modal
            const delEl = document.getElementById('deleteGoalModal');
            const delTitle = document.getElementById('deleteGoalTitle');
            const delConfirm = document.getElementById('deleteGoalConfirmBtn');
            const delCancel = document.getElementById('deleteGoalCancelBtn');

            delCancel.addEventListener('click', () => hide(delEl));
            delConfirm.addEventListener('click', async () => {
                const id = modalState.delete.id;
                if (!id) return;
                try {
                    await goalsFetch('delete.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            id
                        })
                    });
                    if (typeof modalState.delete.onDeleted === 'function') modalState.delete.onDeleted();
                    hide(delEl);
                } catch (e) {
                    /* noop */ }
            });

            // Expose open functions
            window.openEditGoalModal = (id, currentTitle, onSaved) => {
                modalState.edit.id = id;
                modalState.edit.onSaved = onSaved;
                editInput.value = currentTitle || '';
                show(editEl);
                setTimeout(() => editInput.focus(), 0);
            };
            window.openDeleteGoalModal = (id, title, onDeleted) => {
                modalState.delete.id = id;
                modalState.delete.onDeleted = onDeleted;
                delTitle.textContent = title || 'This goal';
                show(delEl);
            };
        }

        function initGoalsUI() {
            const input = document.getElementById('newGoalInput');
            const btn = document.getElementById('addGoalBtn');
            const btnCompleteAll = document.getElementById('btnCompleteAll');
            const btnClearCompleted = document.getElementById('btnClearCompleted');
            const add = async () => {
                const title = (input.value || '').trim();
                if (!title) return;
                try {
                    const {
                        goal
                    } = await goalsFetch('create.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            title
                        })
                    });
                    input.value = '';
                    // Prepend new goal to list
                    const current = document.querySelectorAll('#goalsList li');
                    const goals = Array.from(current).map(li => ({
                        id: +li.dataset.id,
                        title: li.querySelector('span').textContent,
                        is_completed: li.querySelector('input').checked
                    }));
                    goals.unshift(goal);
                    renderGoals(goals);
                } catch (e) {
                    console.error('Failed to add goal', e);
                }
            };
            btn.addEventListener('click', add);
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') add();
            });
            // Bulk actions
            btnCompleteAll.addEventListener('click', async () => {
                const items = Array.from(document.querySelectorAll('#goalsList li'));
                const ops = items.map(li => {
                    const id = parseInt(li.dataset.id, 10);
                    const checked = li.querySelector('input[type="checkbox"]').checked;
                    if (checked) return null;
                    return goalsFetch('toggle.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            id,
                            is_completed: true
                        })
                    });
                }).filter(Boolean);
                try {
                    await Promise.all(ops);
                    loadGoals();
                } catch (e) {
                    /* noop */ }
            });
            btnClearCompleted.addEventListener('click', async () => {
                const items = Array.from(document.querySelectorAll('#goalsList li'));
                const ops = items.map(li => {
                    const id = parseInt(li.dataset.id, 10);
                    const checked = li.querySelector('input[type="checkbox"]').checked;
                    if (!checked) return null;
                    return goalsFetch('delete.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            id
                        })
                    });
                }).filter(Boolean);
                try {
                    await Promise.all(ops);
                    loadGoals();
                } catch (e) {
                    /* noop */ }
            });
            loadGoals();
        }
        document.addEventListener('DOMContentLoaded', () => {
            initGoalModals();
            initGoalsUI();
        });

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
            }, {
                threshold: 0.5
            });

            counters.forEach(counter => observer.observe(counter));
        }

        document.addEventListener('DOMContentLoaded', animateCounters);
    </script>
</body>

</html>