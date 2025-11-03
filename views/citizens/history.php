<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $conn = getDBConnection();
    
    // Get user info
    $userStmt = $conn->prepare('SELECT name, email FROM users WHERE id = ?');
    $userStmt->execute([$user_id]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get all user requests with statistics
    $requestsStmt = $conn->prepare('
        SELECT r.*, c.name as collector_name, dp.name as dropoff_name, dp.address as dropoff_address
        FROM collection_requests r
        LEFT JOIN collectors col ON r.collector_id = col.id
        LEFT JOIN users c ON col.user_id = c.id
        LEFT JOIN dropoff_points dp ON r.dropoff_point_id = dp.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ');
    $requestsStmt->execute([$user_id]);
    $requests = $requestsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate statistics
    $stats = [
        'total' => count($requests),
        'completed' => 0,
        'pending' => 0,
        'active' => 0,
        'cancelled' => 0,
        'total_weight' => 0
    ];
    
    foreach ($requests as $request) {
        switch ($request['status']) {
            case 'completed':
                $stats['completed']++;
                if ($request['estimated_weight']) {
                    $stats['total_weight'] += (float)$request['estimated_weight'];
                }
                break;
            case 'pending':
                $stats['pending']++;
                break;
            case 'assigned':
            case 'en_route':
                $stats['active']++;
                break;
            case 'cancelled':
                $stats['cancelled']++;
                break;
        }
    }
    
} catch (Exception $e) {
    $requests = [];
    $stats = ['total' => 0, 'completed' => 0, 'pending' => 0, 'active' => 0, 'cancelled' => 0, 'total_weight' => 0];
    error_log("History page error: " . $e->getMessage());
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

include __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen hero-gradient">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-white mb-4">Collection History</h1>
            <p class="text-emerald-100/80 text-lg">Track all your recycling collection requests and their progress</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="glass-card p-4 text-center">
                <div class="text-2xl font-bold text-white"><?= $stats['total'] ?></div>
                <div class="text-sm text-slate-300">Total</div>
            </div>
            <div class="glass-card p-4 text-center">
                <div class="text-2xl font-bold text-emerald-300"><?= $stats['completed'] ?></div>
                <div class="text-sm text-slate-300">Completed</div>
            </div>
            <div class="glass-card p-4 text-center">
                <div class="text-2xl font-bold text-amber-300"><?= $stats['pending'] ?></div>
                <div class="text-sm text-slate-300">Pending</div>
            </div>
            <div class="glass-card p-4 text-center">
                <div class="text-2xl font-bold text-sky-300"><?= $stats['active'] ?></div>
                <div class="text-sm text-slate-300">Active</div>
            </div>
            <div class="glass-card p-4 text-center">
                <div class="text-2xl font-bold text-emerald-300"><?= number_format($stats['total_weight'], 1) ?>kg</div>
                <div class="text-sm text-slate-300">Recycled</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="glass-card p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <select id="statusFilter" class="bg-black/30 border border-white/20 rounded-lg px-4 py-2 text-white focus:border-emerald-400 focus:outline-none">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="assigned">Assigned</option>
                    <option value="en_route">En Route</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                
                <select id="materialFilter" class="bg-black/30 border border-white/20 rounded-lg px-4 py-2 text-white focus:border-emerald-400 focus:outline-none">
                    <option value="">All Materials</option>
                    <option value="plastic">Plastic</option>
                    <option value="paper">Paper</option>
                    <option value="metal">Metal</option>
                    <option value="glass">Glass</option>
                    <option value="electronics">Electronics</option>
                </select>
                
                <input type="date" id="dateFilter" class="bg-black/30 border border-white/20 rounded-lg px-4 py-2 text-white focus:border-emerald-400 focus:outline-none">
                
                <button onclick="clearFilters()" class="px-4 py-2 border border-white/20 rounded-lg text-white hover:border-emerald-300 transition">
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Requests List -->
        <div class="space-y-4" id="requestsList">
            <?php if (!empty($requests)): ?>
                <?php foreach ($requests as $request): ?>
                <div class="glass-card p-6 request-item" 
                     data-status="<?= $request['status'] ?>" 
                     data-materials="<?= htmlspecialchars($request['materials']) ?>"
                     data-date="<?= $request['pickup_date'] ?>">
                    
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <!-- Request Info -->
                        <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-white">Request #<?= $request['id'] ?></h3>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium border <?= getStatusBadgeClass($request['status']) ?>">
                                        <?= getStatusLabel($request['status']) ?>
                                    </span>
                                </div>

                                <?php if (!empty($request['photo_url'])): ?>
                                <?php $imgSrc = BASE_URL . '/' . ltrim($request['photo_url'], '/'); ?>
                                <div class="mt-3 mb-3">
                                    <a href="/Scrap/views/citizens/request_details.php?id=<?= $request['id'] ?>">
                                        <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Request photo" class="w-40 h-28 object-cover rounded-lg border border-white/10">
                                    </a>
                                </div>
                                <?php endif; ?>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-emerald-100/80">
                                        <span class="font-medium">Materials:</span> 
                                        <?= htmlspecialchars(ucwords(str_replace(',', ', ', $request['materials']))) ?>
                                    </p>
                                    <p class="text-emerald-100/80">
                                        <span class="font-medium">Weight:</span> 
                                        <?= $request['estimated_weight'] ? $request['estimated_weight'] . ' kg' : 'Not specified' ?>
                                    </p>
                                    <p class="text-emerald-100/80">
                                        <span class="font-medium">Pickup:</span> 
                                        <?= formatDateTime($request['pickup_date'], $request['pickup_time']) ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-emerald-100/80">
                                        <span class="font-medium">Address:</span> 
                                        <?= htmlspecialchars($request['pickup_address']) ?>
                                    </p>
                                    <?php if ($request['collector_name']): ?>
                                    <p class="text-emerald-100/80">
                                        <span class="font-medium">Collector:</span> 
                                        <?= htmlspecialchars($request['collector_name']) ?>
                                    </p>
                                    <?php endif; ?>
                                    <p class="text-emerald-100/80">
                                        <span class="font-medium">Created:</span> 
                                        <?= formatDateTime($request['created_at']) ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($request['notes']): ?>
                            <div class="mt-3 p-3 bg-black/20 rounded-lg">
                                <p class="text-sm text-emerald-100/80">
                                    <span class="font-medium">Notes:</span> 
                                    <?= htmlspecialchars($request['notes']) ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2 md:w-32">
                            <?php if ($request['status'] === 'pending'): ?>
                            <button onclick="deleteRequest(<?= $request['id'] ?>)" 
                                    class="px-4 py-2 bg-red-500/20 border border-red-400/30 text-red-300 rounded-lg hover:bg-red-500/30 transition text-sm">
                                Delete
                            </button>
                            <?php endif; ?>
                            
                            <button onclick="viewDetails(<?= $request['id'] ?>)" 
                                    class="px-4 py-2 border border-white/20 text-white rounded-lg hover:border-emerald-300 transition text-sm">
                                Details
                            </button>
                            
                            <?php if ($request['status'] === 'completed'): ?>
                            <button onclick="downloadReceipt(<?= $request['id'] ?>)" 
                                    class="px-4 py-2 bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 rounded-lg hover:bg-emerald-500/30 transition text-sm">
                                Receipt
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="glass-card p-12 text-center">
                <div class="w-16 h-16 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Requests Yet</h3>
                <p class="text-emerald-100/80 mb-6">You haven't made any collection requests yet. Start recycling today!</p>
                <a href="/Scrap/views/citizens/request.php" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl hover:shadow-lg transition duration-200 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Schedule Pickup
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 flex flex-wrap gap-4 justify-center">
            <a href="/Scrap/views/citizens/request.php" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl hover:shadow-lg transition duration-200 font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Request
            </a>
            <a href="/Scrap/views/citizens/dashboard.php" class="inline-flex items-center gap-2 px-6 py-3 border border-white/20 text-white rounded-xl hover:border-emerald-300 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                </svg>
                Dashboard
            </a>
            <a href="/Scrap/views/citizens/rewards.php" class="inline-flex items-center gap-2 px-6 py-3 border border-white/20 text-white rounded-xl hover:border-emerald-300 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
                Rewards
            </a>
        </div>
    </div>
</div>

<script>
// Filter functionality
function filterRequests() {
    const statusFilter = document.getElementById('statusFilter').value;
    const materialFilter = document.getElementById('materialFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    const requests = document.querySelectorAll('.request-item');
    
    requests.forEach(request => {
        let show = true;
        
        // Status filter
        if (statusFilter && request.dataset.status !== statusFilter) {
            show = false;
        }
        
        // Material filter
        if (materialFilter && !request.dataset.materials.includes(materialFilter)) {
            show = false;
        }
        
        // Date filter
        if (dateFilter && request.dataset.date !== dateFilter) {
            show = false;
        }
        
        request.style.display = show ? 'block' : 'none';
    });
}

function clearFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('materialFilter').value = '';
    document.getElementById('dateFilter').value = '';
    filterRequests();
}

// Event listeners for filters
document.getElementById('statusFilter').addEventListener('change', filterRequests);
document.getElementById('materialFilter').addEventListener('change', filterRequests);
document.getElementById('dateFilter').addEventListener('change', filterRequests);

// Action functions
async function cancelRequest(requestId) {
    if (!confirm('Are you sure you want to cancel this request?')) {
        return;
    }
    
    try {
        const response = await fetch('/Scrap/api/cancel_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ request_id: requestId })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            location.reload();
        } else {
            alert('Failed to cancel request: ' + data.message);
        }
    } catch (error) {
        alert('Failed to cancel request. Please try again.');
    }
}

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
            location.reload();
        } else {
            alert('Failed to delete request: ' + data.message);
        }
    } catch (error) {
        alert('Failed to delete request. Please try again.');
    }
}

function viewDetails(requestId) {
    window.location.href = `/Scrap/request_details.php?id=${requestId}`;
}

function downloadReceipt(requestId) {
    window.open(`/Scrap/api/generate_receipt.php?id=${requestId}`, '_blank');
}

// Profile menu toggle
function toggleProfileMenu() {
    const menu = document.getElementById('profileMenu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

// Logout function
async function logout() {
    try {
        const response = await fetch('/Scrap/api/logout.php', { method: 'POST' });
        const data = await response.json();
        if (data.status === 'success') {
            // Clear sessionStorage before redirecting
            sessionStorage.clear();
            window.location.href = '/Scrap/views/auth/login.php?logout=1';
        } else {
            alert('Logout failed: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Logout failed.');
    }
}

// Close profile menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('profileMenu');
    const button = e.target.closest('[onclick*="toggleProfileMenu"]');
    
    if (!button && menu && !menu.contains(e.target)) {
        menu.classList.add('hidden');
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>