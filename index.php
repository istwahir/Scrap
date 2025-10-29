<?php
// Disable auth requirement for landing page
$requireAuth = false;

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController();
$isAuthenticated = $auth->isAuthenticated();

// Get user role if authenticated
$userRole = null;
$dashboardUrl = '/Scrap/views/auth/login.php';

if ($isAuthenticated) {
    $userRole = $_SESSION['user_role'] ?? 'user';
    
    // Set appropriate dashboard URL based on role
    if ($userRole === 'admin') {
        $dashboardUrl = '/Scrap/views/admin/dashboard.php';
    } elseif ($userRole === 'collector') {
        $dashboardUrl = '/Scrap/views/collectors/dashboard.php';
    } else {
        $dashboardUrl = '/Scrap/views/citizens/dashboard.php';
    }
}

// Get some dynamic stats from database (optional)
$stats = [
    'tons_recycled' => '12,500+',
    'active_users' => '8,200+',
    'collection_points' => '650+',
    'trees_saved' => '75,000+'
];

try {
    $conn = getDBConnection();
    
    // Get actual user count
    $userStmt = $conn->prepare('SELECT COUNT(*) FROM users WHERE role = "citizen"');
    $userStmt->execute();
    $userCount = $userStmt->fetchColumn();
    if ($userCount > 0) {
        $stats['active_users'] = number_format($userCount) . '+';
    }
    
    // Get request count as recycling activity
    $requestStmt = $conn->prepare('SELECT COUNT(*) FROM collection_requests WHERE status = "completed"');
    $requestStmt->execute();
    $completedRequests = $requestStmt->fetchColumn();
    if ($completedRequests > 0) {
        $stats['tons_recycled'] = number_format($completedRequests * 2.5, 0) . '+'; // Estimate 2.5kg per request
    }
} catch (Exception $e) {
    // Use default stats if database query fails
    error_log("Index stats query failed: " . $e->getMessage());
}

include 'includes/header.php';
?>

<div class="min-h-screen hero-gradient overflow-hidden">
    <!-- Hero Section -->
    <section class="relative">
        <div class="container mx-auto px-4 py-16 lg:py-24">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Main Heading -->
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
                    Transforming Waste into
                    <span class="block bg-gradient-to-r from-emerald-300 via-emerald-200 to-sky-300 bg-clip-text text-transparent">
                        Wealth & Cleaner Communities
                    </span>
                </h1>
                
                <!-- Subtitle -->
                <p class="text-xl md:text-2xl mb-8 text-emerald-100/90 max-w-3xl mx-auto leading-relaxed">
                    Kiambu County's innovative digital platform connecting households with waste collectors. 
                    Earn rewards for recycling, reduce environmental pollution, and build a sustainable future—one collection at a time.
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                    <?php if ($isAuthenticated): ?>
                        <a href="<?= $dashboardUrl ?>" class="group inline-flex items-center justify-center gap-3 rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-8 py-4 text-lg font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition-all duration-300 hover:scale-105 hover:shadow-emerald-400/40">
                            <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Go to Dashboard
                        </a>
                        <a href="<?= $userRole === 'user' ? '/Scrap/views/citizens/request.php' : $dashboardUrl ?>" class="group inline-flex items-center justify-center gap-3 rounded-full border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:border-emerald-300/60 hover:bg-white/10">
                            <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <?= $userRole === 'user' ? 'Request Pickup' : 'View Activity' ?>
                        </a>
                    <?php else: ?>
                        <a href="views/auth/signup.php" class="group inline-flex items-center justify-center gap-3 rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-8 py-4 text-lg font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition-all duration-300 hover:scale-105 hover:shadow-emerald-400/40">
                            <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Start Recycling Today
                        </a>
                        <a href="views/auth/login.php" class="group inline-flex items-center justify-center gap-3 rounded-full border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:border-emerald-300/60 hover:bg-white/10">
                            <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Already a Member? Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 w-20 h-20 bg-emerald-400/20 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute top-40 right-20 w-32 h-32 bg-sky-400/20 rounded-full blur-xl animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-20 left-1/4 w-24 h-24 bg-emerald-300/20 rounded-full blur-xl animate-pulse" style="animation-delay: 4s;"></div>
    </section>

    <!-- Stats Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <!-- Problem Statement -->
            <div class="glass-card p-8 mb-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-500/20 rounded-full mb-4">
                    <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-3">The Waste Crisis in Kiambu County</h3>
                <p class="text-emerald-100/80 text-lg max-w-4xl mx-auto">
                    Kiambu County generates over <strong class="text-emerald-300">800 tons of waste daily</strong>, with only 35% properly collected and managed. 
                    Illegal dumping sites, blocked drainage systems, and burning of waste create health hazards and environmental degradation. 
                    Our platform bridges the gap between households and waste collectors, making proper waste management accessible, rewarding, and trackable.
                </p>
            </div>

            <!-- Impact Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="glass-card p-6 text-center group hover:scale-105 transition-transform duration-300">
                    <div class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-emerald-300 to-emerald-400 bg-clip-text text-transparent mb-2">
                        <?= $stats['tons_recycled'] ?>
                    </div>
                    <p class="text-emerald-100/80 text-sm md:text-base">Kilograms Collected</p>
                    <p class="text-emerald-300/60 text-xs mt-1">Via Platform</p>
                </div>
                <div class="glass-card p-6 text-center group hover:scale-105 transition-transform duration-300">
                    <div class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-sky-300 to-sky-400 bg-clip-text text-transparent mb-2">
                        <?= $stats['active_users'] ?>
                    </div>
                    <p class="text-emerald-100/80 text-sm md:text-base">Active Households</p>
                    <p class="text-emerald-300/60 text-xs mt-1">Registered Users</p>
                </div>
                <div class="glass-card p-6 text-center group hover:scale-105 transition-transform duration-300">
                    <div class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-300 to-purple-400 bg-clip-text text-transparent mb-2">
                        <?= $stats['collection_points'] ?>
                    </div>
                    <p class="text-emerald-100/80 text-sm md:text-base">Verified Collectors</p>
                    <p class="text-emerald-300/60 text-xs mt-1">Active Network</p>
                </div>
                <div class="glass-card p-6 text-center group hover:scale-105 transition-transform duration-300">
                    <div class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent mb-2">
                        Ksh <?= number_format(intval(str_replace([',', '+'], '', $stats['tons_recycled'])) * 5) ?>+
                    </div>
                    <p class="text-emerald-100/80 text-sm md:text-base">Rewards Earned</p>
                    <p class="text-emerald-300/60 text-xs mt-1">By Residents</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    Why Choose 
                    <span class="bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent">
                        Kiambu Recycling Platform?
                    </span>
                </h2>
                <p class="text-xl text-emerald-100/80 max-w-3xl mx-auto">
                    A comprehensive digital solution addressing waste management challenges through technology, 
                    community engagement, and economic incentives.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-emerald-400/20 to-emerald-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">On-Demand Collection Requests</h3>
                    <p class="text-emerald-100/70 mb-4">
                        Schedule waste pickups directly from your phone. Specify waste type, quantity, and preferred collection time. 
                        Get matched with verified collectors in your area within minutes.
                    </p>
                    <div class="flex items-center text-emerald-400 text-sm">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Real-time tracking available
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-sky-400/20 to-sky-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Points-Based Reward System</h3>
                    <p class="text-emerald-100/70 mb-4">
                        Earn points for every kilogram of waste recycled. Redeem points for M-Pesa cash, airtime, shopping vouchers, 
                        or eco-friendly products. Track your earnings and environmental impact in real-time.
                    </p>
                    <div class="flex items-center text-sky-400 text-sm">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        M-Pesa integration
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-400/20 to-purple-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Live Collector Tracking</h3>
                    <p class="text-emerald-100/70 mb-4">
                        See your assigned collector's real-time location on an interactive map. Get notified when they're approaching. 
                        Know exactly when your waste will be collected—no more waiting around all day.
                    </p>
                    <div class="flex items-center text-purple-400 text-sm">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        GPS-enabled tracking
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-amber-400/20 to-amber-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Waste Categorization & Education</h3>
                    <p class="text-emerald-100/70 mb-4">
                        Learn proper waste segregation for plastics, paper, metals, e-waste, and organic materials. 
                        Access comprehensive guides with images showing what's recyclable and how to prepare items for collection.
                    </p>
                    <div class="flex items-center text-amber-400 text-sm">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Interactive guides
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-rose-400/20 to-rose-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Verified Collector Network</h3>
                    <p class="text-emerald-100/70 mb-4">
                        All collectors undergo background checks and receive proper training. Rate your collection experience 
                        and provide feedback. Build trust through transparent ratings and verified credentials.
                    </p>
                    <div class="flex items-center text-rose-400 text-sm">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Background verified
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-indigo-400/20 to-indigo-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Detailed Analytics Dashboard</h3>
                    <p class="text-emerald-100/70 mb-4">
                        Track your recycling history, see CO₂ emissions prevented, water conserved, and trees saved. 
                        View monthly trends, compare with neighbors, and celebrate environmental milestones.
                    </p>
                    <div class="flex items-center text-indigo-400 text-sm">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Visual insights
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20" id="how-it-works">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    How 
                    <span class="bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent">
                        It Works
                    </span>
                </h2>
                <p class="text-xl text-emerald-100/80 max-w-3xl mx-auto">
                    From registration to reward redemption—our streamlined process makes waste management effortless.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white text-2xl font-bold">1</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Sign Up & Verify</h3>
                    <p class="text-emerald-100/70 max-w-xs mx-auto">
                        Register with your phone number and location. Complete OTP verification for account security. 
                        Set up your profile and preferences in under 2 minutes.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-sky-500 to-sky-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-sky-500/30 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white text-2xl font-bold">2</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Create Collection Request</h3>
                    <p class="text-emerald-100/70 max-w-xs mx-auto">
                        Select waste materials (plastic, paper, metal, e-waste, etc.), specify estimated weight, 
                        add photos, and choose your preferred pickup date and time.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white text-2xl font-bold">3</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Get Matched & Track</h3>
                    <p class="text-emerald-100/70 max-w-xs mx-auto">
                        A verified collector accepts your request. Track their real-time location via GPS. 
                        Receive notifications when they're 10 minutes away from your location.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-amber-500 to-amber-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white text-2xl font-bold">4</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Earn & Redeem Rewards</h3>
                    <p class="text-emerald-100/70 max-w-xs mx-auto">
                        Get points based on weight and material type collected. Redeem instantly for M-Pesa, 
                        airtime, or save up for bigger rewards. View impact statistics on your dashboard.
                    </p>
                </div>
            </div>

            <!-- Additional Process Info -->
            <div class="mt-16 glass-card p-8">
                <h3 class="text-2xl font-bold text-white mb-6 text-center">What Happens After Collection?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-white mb-2">Sorting & Processing</h4>
                            <p class="text-emerald-100/70 text-sm">
                                Materials are sorted by type and processed at certified recycling facilities following environmental standards.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-sky-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-white mb-2">Recycling & Reuse</h4>
                            <p class="text-emerald-100/70 text-sm">
                                Recycled materials become raw materials for new products, reducing reliance on virgin resources and energy.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-white mb-2">Impact Tracking</h4>
                            <p class="text-emerald-100/70 text-sm">
                                Your dashboard shows CO₂ saved, water conserved, and landfill space prevented—making your impact visible and measurable.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="glass-card p-12">
                <div class="text-center mb-12">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                        Your Impact 
                        <span class="bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent">
                            Matters
                        </span>
                    </h2>
                    <p class="text-xl text-emerald-100/80 max-w-3xl mx-auto mb-8">
                        Every kilogram of waste properly recycled prevents environmental damage and creates economic value. 
                        Here's the measurable difference your participation makes.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <div class="group text-center">
                        <div class="text-6xl mb-4 group-hover:scale-110 transition-transform duration-300">🌳</div>
                        <h3 class="text-2xl font-semibold text-white mb-3">Trees Saved</h3>
                        <p class="text-emerald-100/70 mb-4">
                            Recycling 1 ton of paper saves approximately 17 trees, 7,000 gallons of water, 
                            and prevents 3 cubic yards of landfill space.
                        </p>
                        <div class="text-emerald-400 font-semibold">~17 trees per ton recycled</div>
                    </div>
                    <div class="group text-center">
                        <div class="text-6xl mb-4 group-hover:scale-110 transition-transform duration-300">💧</div>
                        <h3 class="text-2xl font-semibold text-white mb-3">Water Conserved</h3>
                        <p class="text-emerald-100/70 mb-4">
                            Manufacturing with recycled plastic uses 88% less water than virgin materials. 
                            Recycled aluminum saves 95% of energy and significant water resources.
                        </p>
                        <div class="text-sky-400 font-semibold">Up to 90% water savings</div>
                    </div>
                    <div class="group text-center">
                        <div class="text-6xl mb-4 group-hover:scale-110 transition-transform duration-300">🌍</div>
                        <h3 class="text-2xl font-semibold text-white mb-3">CO₂ Emissions Reduced</h3>
                        <p class="text-emerald-100/70 mb-4">
                            Recycling reduces greenhouse gas emissions by decreasing energy-intensive extraction and processing 
                            of raw materials. 1 ton of recycled plastic prevents 2 tons of CO₂.
                        </p>
                        <div class="text-purple-400 font-semibold">2:1 CO₂ reduction ratio</div>
                    </div>
                </div>

                <!-- Real Problem Context -->
                <div class="border-t border-emerald-500/20 pt-12">
                    <h3 class="text-3xl font-bold text-white mb-8 text-center">Addressing Real Challenges</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-slate-800/50 rounded-lg p-6 border border-red-500/20">
                            <div class="flex items-center mb-4">
                                <svg class="w-8 h-8 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <h4 class="text-xl font-semibold text-white">Without Proper Systems</h4>
                            </div>
                            <ul class="space-y-3 text-emerald-100/70">
                                <li class="flex items-start">
                                    <span class="text-red-400 mr-2">✗</span>
                                    <span>Illegal dumping sites proliferate in residential areas</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-400 mr-2">✗</span>
                                    <span>Drainage systems blocked with plastic waste</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-400 mr-2">✗</span>
                                    <span>Open burning releases toxic chemicals into the air</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-400 mr-2">✗</span>
                                    <span>Valuable recyclables end up in landfills</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-400 mr-2">✗</span>
                                    <span>Community health risks increase (malaria, cholera)</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-400 mr-2">✗</span>
                                    <span>No incentive for proper waste disposal</span>
                                </li>
                            </ul>
                        </div>

                        <div class="bg-slate-800/50 rounded-lg p-6 border border-emerald-500/20">
                            <div class="flex items-center mb-4">
                                <svg class="w-8 h-8 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h4 class="text-xl font-semibold text-white">With Our Platform</h4>
                            </div>
                            <ul class="space-y-3 text-emerald-100/70">
                                <li class="flex items-start">
                                    <span class="text-emerald-400 mr-2">✓</span>
                                    <span>Scheduled collections prevent waste accumulation</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-emerald-400 mr-2">✓</span>
                                    <span>Materials properly sorted and recycled</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-emerald-400 mr-2">✓</span>
                                    <span>Zero open burning—safe processing methods</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-emerald-400 mr-2">✓</span>
                                    <span>Economic value recovered from waste materials</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-emerald-400 mr-2">✓</span>
                                    <span>Cleaner neighborhoods, healthier communities</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-emerald-400 mr-2">✓</span>
                                    <span>Residents earn rewards for responsible behavior</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="glass-card p-12">
                <div class="text-center mb-8">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                        Ready to Make a 
                        <span class="bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent">
                            Difference?
                        </span>
                    </h2>
                    <p class="text-xl text-emerald-100/80 mb-8 max-w-3xl mx-auto">
                        Join the movement transforming waste management in Kiambu County. Whether you're a household looking 
                        to earn from your recyclables or a collector seeking consistent income—there's a place for you here.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                        <?php if ($isAuthenticated): ?>
                            <a href="<?= $dashboardUrl ?>" class="group inline-flex items-center justify-center gap-3 rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-8 py-4 text-lg font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition-all duration-300 hover:scale-105">
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Go to Dashboard
                            </a>
                        <?php else: ?>
                            <a href="views/auth/signup.php" class="group inline-flex items-center justify-center gap-3 rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-8 py-4 text-lg font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition-all duration-300 hover:scale-105">
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Get Started Now
                            </a>
                            <a href="views/auth/login.php" class="group inline-flex items-center justify-center gap-3 rounded-full border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:border-emerald-300/60 hover:bg-white/10">
                                <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Already a Member?
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Role Options -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-8 border-t border-emerald-500/20">
                    <div class="text-center p-6 bg-slate-800/30 rounded-lg hover:bg-slate-800/50 transition-all duration-300">
                        <div class="w-16 h-16 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">For Households</h3>
                        <p class="text-emerald-100/70 text-sm mb-3">
                            Schedule pickups, earn rewards, track your environmental impact
                        </p>
                        <div class="text-emerald-400 text-xs font-semibold">Free Registration</div>
                    </div>

                    <div class="text-center p-6 bg-slate-800/30 rounded-lg hover:bg-slate-800/50 transition-all duration-300">
                        <div class="w-16 h-16 bg-sky-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">For Collectors</h3>
                        <p class="text-emerald-100/70 text-sm mb-3">
                            Access consistent work, flexible schedules, fair compensation
                        </p>
                        <div class="text-sky-400 text-xs font-semibold">Verification Required</div>
                    </div>

                    <div class="text-center p-6 bg-slate-800/30 rounded-lg hover:bg-slate-800/50 transition-all duration-300">
                        <div class="w-16 h-16 bg-purple-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">For Organizations</h3>
                        <p class="text-emerald-100/70 text-sm mb-3">
                            Bulk collection services, CSR partnerships, waste audits
                        </p>
                        <div class="text-purple-400 text-xs font-semibold">Contact Admin</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// PWA installation
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    
    // Show install button or notification
    showInstallPrompt();
});

function showInstallPrompt() {
    // You can add a custom install prompt here
    console.log('PWA installation available');
}

// Service worker registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/Scrap/public/service-worker.js')
            .then(registration => {
                console.log('ServiceWorker registered:', registration);
            })
            .catch(error => {
                console.log('ServiceWorker registration failed:', error);
            });
    });
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add intersection observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-fadeInUp');
        }
    });
}, observerOptions);

// Observe all glass cards for animation
document.querySelectorAll('.glass-card').forEach(card => {
    observer.observe(card);
});

// Add CSS animation class
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fadeInUp {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    
    .glass-card {
        opacity: 0;
    }
`;
document.head.appendChild(style);
</script>

<?php include 'includes/footer.php'; ?>