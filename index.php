<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController();

if ($auth->isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
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
                    Sustainable Waste Management for
                    <span class="block bg-gradient-to-r from-emerald-300 via-emerald-200 to-sky-300 bg-clip-text text-transparent">
                        Kiambu County
                    </span> §   
                </h1>
                
                <!-- Subtitle -->
                <p class="text-xl md:text-2xl mb-8 text-emerald-100/90 max-w-3xl mx-auto leading-relaxed">
                    Turn your recyclables into rewards while helping create a cleaner, greener environment.
                    Join thousands of residents making a difference.
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                    <a href="signup.php" class="group inline-flex items-center justify-center gap-3 rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-8 py-4 text-lg font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition-all duration-300 hover:scale-105 hover:shadow-emerald-400/40">
                        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Start Recycling Today
                    </a>
                    <a href="map.php" class="group inline-flex items-center justify-center gap-3 rounded-full border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:border-emerald-300/60 hover:bg-white/10">
                        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Find Drop-off Points
                    </a>
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
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="glass-card p-6 text-center group hover:scale-105 transition-transform duration-300">
                    <div class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-emerald-300 to-emerald-400 bg-clip-text text-transparent mb-2">
                        <?= $stats['tons_recycled'] ?>
                    </div>
                    <p class="text-emerald-100/80 text-sm md:text-base">Tons Recycled</p>
                </div>
                <div class="glass-card p-6 text-center group hover:scale-105 transition-transform duration-300">
                    <div class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-sky-300 to-sky-400 bg-clip-text text-transparent mb-2">
                        <?= $stats['active_users'] ?>
                    </div>
                    <p class="text-emerald-100/80 text-sm md:text-base">Active Users</p>
                </div>
                <div class="glass-card p-6 text-center group hover:scale-105 transition-transform duration-300">
                    <div class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-300 to-purple-400 bg-clip-text text-transparent mb-2">
                        <?= $stats['collection_points'] ?>
                    </div>
                    <p class="text-emerald-100/80 text-sm md:text-base">Collection Points</p>
                </div>
                <div class="glass-card p-6 text-center group hover:scale-105 transition-transform duration-300">
                    <div class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent mb-2">
                        <?= $stats['trees_saved'] ?>
                    </div>
                    <p class="text-emerald-100/80 text-sm md:text-base">Trees Saved</p>
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
                        Kiambu Recycling?
                    </span>
                </h2>
                <p class="text-xl text-emerald-100/80 max-w-3xl mx-auto">
                    Our platform makes recycling easy, rewarding, and impactful for everyone in Kiambu County.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-emerald-400/20 to-emerald-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Convenient Collection</h3>
                    <p class="text-emerald-100/70">
                        Schedule pickups at your doorstep or find the nearest drop-off point using our interactive map.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-sky-400/20 to-sky-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Earn Rewards</h3>
                    <p class="text-emerald-100/70">
                        Get points for every kilogram recycled. Redeem them for M-Pesa cash, airtime, or eco-friendly products.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-400/20 to-purple-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Track Your Impact</h3>
                    <p class="text-emerald-100/70">
                        Monitor your environmental contribution with real-time stats on CO₂ reduced, trees saved, and water conserved.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-amber-400/20 to-amber-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Mobile Experience</h3>
                    <p class="text-emerald-100/70">
                        Install as a PWA for offline access, push notifications, and native app-like experience on any device.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-rose-400/20 to-rose-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Community Driven</h3>
                    <p class="text-emerald-100/70">
                        Join a network of collectors and residents working together to build a sustainable future for Kiambu County.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="glass-card p-8 group hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-indigo-400/20 to-indigo-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Secure & Private</h3>
                    <p class="text-emerald-100/70">
                        Your data is protected with industry-standard security. We only collect information necessary for service delivery.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20" id="">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    How 
                    <span class="bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent">
                        It Works
                    </span>
                </h2>
                <p class="text-xl text-emerald-100/80 max-w-3xl mx-auto">
                    Getting started with recycling has never been easier. Follow these simple steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white text-2xl font-bold">1</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Sign Up</h3>
                    <p class="text-emerald-100/70 max-w-xs mx-auto">
                        Create your account using your phone number. We'll send you a verification code to get started.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-sky-500 to-sky-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-sky-500/30 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white text-2xl font-bold">2</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Request Pickup</h3>
                    <p class="text-emerald-100/70 max-w-xs mx-auto">
                        Tell us what materials you have and when you'd like them collected. Our collectors will handle the rest.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white text-2xl font-bold">3</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Earn & Track</h3>
                    <p class="text-emerald-100/70 max-w-xs mx-auto">
                        Get rewarded for your contribution while tracking your environmental impact in real-time.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="glass-card p-12 text-center">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    Your Impact 
                    <span class="bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent">
                        Matters
                    </span>
                </h2>
                <p class="text-xl text-emerald-100/80 max-w-3xl mx-auto mb-12">
                    Every piece of recycled material contributes to a healthier planet. See the difference you're making.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="group">
                        <div class="text-6xl mb-4 group-hover:scale-110 transition-transform duration-300">🌳</div>
                        <h3 class="text-2xl font-semibold text-white mb-2">Trees Saved</h3>
                        <p class="text-emerald-100/70">
                            Recycling paper and cardboard prevents deforestation and reduces greenhouse gas emissions.
                        </p>
                    </div>
                    <div class="group">
                        <div class="text-6xl mb-4 group-hover:scale-110 transition-transform duration-300">💧</div>
                        <h3 class="text-2xl font-semibold text-white mb-2">Water Conserved</h3>
                        <p class="text-emerald-100/70">
                            Manufacturing with recycled materials uses significantly less water than virgin materials.
                        </p>
                    </div>
                    <div class="group">
                        <div class="text-6xl mb-4 group-hover:scale-110 transition-transform duration-300">🌍</div>
                        <h3 class="text-2xl font-semibold text-white mb-2">CO₂ Reduced</h3>
                        <p class="text-emerald-100/70">
                            Recycling reduces energy consumption and lowers carbon dioxide emissions from manufacturing.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="glass-card p-12 text-center">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    Ready to Make a 
                    <span class="bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent">
                        Difference?
                    </span>
                </h2>
                <p class="text-xl text-emerald-100/80 mb-8 max-w-3xl mx-auto">
                    Join thousands of Kiambu residents who are already recycling smarter and earning rewards.
                    Start your journey towards a sustainable future today.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="signup.php" class="group inline-flex items-center justify-center gap-3 rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-8 py-4 text-lg font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition-all duration-300 hover:scale-105">
                        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Get Started Now
                    </a>
                    <a href="login.php" class="group inline-flex items-center justify-center gap-3 rounded-full border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:border-emerald-300/60 hover:bg-white/10">
                        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Already a Member?
                    </a>
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