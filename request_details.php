<?php
require_once 'config.php';
require_once 'includes/auth.php';

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$request_id) {
    header('Location: history.php');
    exit();
}

try {
    $conn = getDBConnection();
    // Get request details with all related information
    $requestStmt = $conn->prepare('
        SELECT r.*, 
               c.name as collector_name, 
               c.phone as collector_phone,
               dp.name as dropoff_name, 
               dp.address as dropoff_address
        FROM collection_requests r
        LEFT JOIN collectors col ON r.collector_id = col.id
        LEFT JOIN users c ON col.user_id = c.id
        LEFT JOIN dropoff_points dp ON r.dropoff_point_id = dp.id
        WHERE r.id = ? AND r.user_id = ?
    ');
    $requestStmt->execute([$request_id, $user_id]);
    $request = $requestStmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        header('Location: history.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Request details error: " . $e->getMessage());
    header('Location: history.php');
    exit();
}

// Helper functions
function getStatusBadgeClass($status) {
    return match($status) {
        'completed' => 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30',
        'pending' => 'bg-amber-500/20 text-amber-300 border-amber-400/30',
        'assigned' => 'bg-blue-500/20 text-blue-300 border-blue-400/30',
        'en_route' => 'bg-sky-500/20 text-sky-300 border-sky-400/30',
        'cancelled' => 'bg-red-500/20 text-red-300 border-red-400/30',
        default => 'bg-gray-500/20 text-gray-300 border-gray-400/30'
    };
}

function getStatusLabel($status) {
    return match($status) {
        'pending' => 'Pending',
        'assigned' => 'Assigned',
        'en_route' => 'En Route',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        default => ucfirst($status)
    };
}

function formatDateTime($date, $time = null) {
    if (!$date) return '-';
    try {
        $dt = new DateTime($date);
        $formatted = $dt->format('M j, Y');
        if ($time) {
            $timeObj = new DateTime($time);
            $formatted .= ' at ' . $timeObj->format('g:i A');
        }
        return $formatted;
    } catch (Exception $e) {
        return $date;
    }
}

include 'includes/header.php';
?>

<div class="min-h-screen hero-gradient">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <a href="history.php" class="inline-flex items-center gap-2 px-4 py-2 border border-white/20 text-white rounded-lg hover:border-emerald-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to History
                </a>
            </div>
            <h1 class="text-4xl font-bold text-white mb-4">Request Details</h1>
            <p class="text-emerald-100/80 text-lg">Detailed information for Request #<?= $request['id'] ?></p>
        </div>

        <div class="glass-card p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-white">Request #<?= $request['id'] ?></h2>
                <span class="px-4 py-2 rounded-full text-sm font-medium border <?= getStatusBadgeClass($request['status']) ?>">
                    <?= getStatusLabel($request['status']) ?>
                </span>
            </div>
            <?php if ($request['status'] === 'pending'): ?>
            <div class="bg-amber-500/10 border border-amber-400/30 rounded-lg p-4 mb-4">
                <p class="text-amber-300 text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    Your request is pending assignment to a collector.
                </p>
            </div>
            <?php elseif ($request['status'] === 'assigned'): ?>
            <div class="bg-blue-500/10 border border-blue-400/30 rounded-lg p-4 mb-4">
                <p class="text-blue-300 text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Your request has been assigned to a collector.
                </p>
            </div>
            <?php elseif ($request['status'] === 'en_route'): ?>
            <div class="bg-sky-500/10 border border-sky-400/30 rounded-lg p-4 mb-4">
                <p class="text-sky-300 text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    The collector is on their way!
                </p>
            </div>
            <?php elseif ($request['status'] === 'completed'): ?>
            <div class="bg-emerald-500/10 border border-emerald-400/30 rounded-lg p-4 mb-4">
                <p class="text-emerald-300 text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Collection completed successfully!
                </p>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="glass-card p-6">
                <h3 class="text-xl font-bold text-white mb-4">Request Information</h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-emerald-300">Materials</label>
                        <p class="text-white"><?= htmlspecialchars(ucwords(str_replace(',', ', ', $request['materials']))) ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-emerald-300">Estimated Weight</label>
                        <p class="text-white"><?= $request['estimated_weight'] ? $request['estimated_weight'] . ' kg' : 'Not specified' ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-emerald-300">Pickup Date & Time</label>
                        <p class="text-white"><?= formatDateTime($request['pickup_date'], $request['pickup_time']) ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-emerald-300">Pickup Address</label>
                        <p class="text-white"><?= htmlspecialchars($request['pickup_address']) ?></p>
                    </div>
                    <?php if ($request['notes']): ?>
                    <div>
                        <label class="text-sm font-medium text-emerald-300">Notes</label>
                        <p class="text-white"><?= htmlspecialchars($request['notes']) ?></p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <label class="text-sm font-medium text-emerald-300">Request Created</label>
                        <p class="text-white"><?= formatDateTime($request['created_at']) ?></p>
                    </div>
                </div>
                <?php if ($request['photo_url']): ?>
                <div class="mt-6">
                    <label class="text-sm font-medium text-emerald-300">Photo</label>
                    <div class="mt-2">
                        <img src="<?= htmlspecialchars($request['photo_url']) ?>" alt="Request photo" class="max-w-full h-auto rounded-lg border border-white/20">
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="space-y-6">
                <?php if ($request['collector_name']): ?>
                <div class="glass-card p-6">
                    <h3 class="text-xl font-bold text-white mb-4">Collector Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-emerald-300">Collector Name</label>
                            <p class="text-white"><?= htmlspecialchars($request['collector_name']) ?></p>
                        </div>
                        <?php if ($request['collector_phone']): ?>
                        <div>
                            <label class="text-sm font-medium text-emerald-300">Phone</label>
                            <p class="text-white">
                                <a href="tel:<?= htmlspecialchars($request['collector_phone']) ?>" class="text-emerald-300 hover:text-emerald-200">
                                    <?= htmlspecialchars($request['collector_phone']) ?>
                                </a>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($request['dropoff_name']): ?>
                <div class="glass-card p-6">
                    <h3 class="text-xl font-bold text-white mb-4">Drop-off Point</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-emerald-300">Location</label>
                            <p class="text-white"><?= htmlspecialchars($request['dropoff_name']) ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-emerald-300">Address</label>
                            <p class="text-white"><?= htmlspecialchars($request['dropoff_address']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="glass-card p-6">
                    <h3 class="text-xl font-bold text-white mb-4">Actions</h3>
                    <div class="flex flex-col gap-3">
                        <?php if ($request['status'] === 'pending'): ?>
                        <button onclick="openEditModal()" class="px-4 py-2 bg-blue-500/20 border border-blue-400/30 text-blue-300 rounded-lg hover:bg-blue-500/30 transition">
                            Edit Request
                        </button>
                        <button onclick="deleteRequest(<?= $request['id'] ?>)" class="px-4 py-2 bg-red-500/20 border border-red-400/30 text-red-300 rounded-lg hover:bg-red-500/30 transition">
                            Delete Request
                        </button>
                        <?php endif; ?>
                        <?php if ($request['status'] === 'completed'): ?>
                        <button onclick="downloadReceipt(<?= $request['id'] ?>)" class="px-4 py-2 bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 rounded-lg hover:bg-emerald-500/30 transition">
                            Download Receipt
                        </button>
                        <?php endif; ?>
                        <a href="request.php" class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-lg hover:shadow-lg transition text-center">
                            Create New Request
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden">
    <div class="bg-slate-900 rounded-2xl p-8 w-full max-w-lg shadow-2xl relative">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white text-xl">&times;</button>
        <h2 class="text-2xl font-bold text-white mb-6">Edit Request</h2>
        <form onsubmit="submitEditForm(event)">
            <div class="mb-4">
                <label class="block text-sm font-medium text-emerald-300 mb-1">Materials</label>
                <input type="text" name="materials" value="<?= htmlspecialchars($request['materials']) ?>" class="w-full rounded-lg border border-white/20 bg-black/20 px-4 py-2 text-white" required>
                <small class="text-slate-400">Comma separated (e.g. plastic, paper)</small>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-emerald-300 mb-1">Estimated Weight (kg)</label>
                <input type="number" name="estimated_weight" value="<?= $request['estimated_weight'] ?>" class="w-full rounded-lg border border-white/20 bg-black/20 px-4 py-2 text-white">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-emerald-300 mb-1">Pickup Address</label>
                <input type="text" name="pickup_address" value="<?= htmlspecialchars($request['pickup_address']) ?>" class="w-full rounded-lg border border-white/20 bg-black/20 px-4 py-2 text-white" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-emerald-300 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border border-white/20 bg-black/20 px-4 py-2 text-white"><?= htmlspecialchars($request['notes']) ?></textarea>
            </div>
            <div class="flex justify-end gap-4 mt-6">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg bg-slate-700 text-white">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-emerald-500 text-slate-900 font-semibold hover:bg-emerald-600">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
async function deleteRequest(requestId) {
    if (!confirm('Are you sure you want to permanently delete this request? This action cannot be undone.')) {
        return;
    }
    try {
        const response = await fetch('/Scrap/api/delete_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ request_id: requestId })
        });
        const data = await response.json();
        if (data.status === 'success') {
            window.location.href = '/Scrap/history.php';
        } else {
            alert('Failed to delete request: ' + data.message);
        }
    } catch (error) {
        alert('Failed to delete request. Please try again.');
    }
}

function downloadReceipt(requestId) {
    window.open(`/Scrap/api/generate_receipt.php?id=${requestId}`, '_blank');
}

// Edit Modal functions
function openEditModal() {
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
async function submitEditForm(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    formData.append('request_id', <?= $request['id'] ?>);
    try {
        const response = await fetch('/Scrap/api/update_request.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.status === 'success') {
            closeEditModal();
            window.location.reload();
        } else {
            alert('Failed to update request: ' + data.message);
        }
    } catch (error) {
        alert('Failed to update request. Please try again.');
    }
}
</script>

<?php include 'includes/footer.php'; ?>