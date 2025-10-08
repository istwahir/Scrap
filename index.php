<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController();

// If user is already authenticated, redirect to dashboard
// if ($auth->isAuthenticated()) {
//     header('Location: /scrap/dashboard.html');
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiambu Recycling & Scraps - Sustainable Waste Management</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom styles -->
    <style>
        .hero-bg {
            background: linear-gradient(135deg, #16a34a 0%, #059669 100%);
        }
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .stat-number {
            background: linear-gradient(135deg, #16a34a, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#16a34a">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icon-192x192.png">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-green-600">Kiambu Recycling</h1>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="#features" class="text-gray-700 hover:text-green-600 transition duration-200">Features</a>
                    <a href="#how-it-works" class="text-gray-700 hover:text-green-600 transition duration-200">How It Works</a>
                    <a href="#impact" class="text-gray-700 hover:text-green-600 transition duration-200">Impact</a>
                    <a href="/guide.html" class="text-gray-700 hover:text-green-600 transition duration-200">Guide</a>
                    <a href="/scrap/login.html" class="text-gray-700 hover:text-green-600 transition duration-200">Login</a>
                    <a href="/scrap/signup.html" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg text-white">
        <div class="container mx-auto px-4 py-20">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl md:text-6xl font-bold mb-6">
                    Sustainable Waste Management for
                    <span class="block text-green-200">Kiambu County</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-green-100">
                    Turn your recyclables into rewards while helping create a cleaner, greener environment.
                    Join thousands of residents making a difference.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/scrap/signup.html" class="bg-white text-green-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition duration-200">
                        Start Recycling Today
                    </a>
                    <a href="/scrap/map.html" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-green-600 transition duration-200">
                        Find Drop-off Points
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="stat-number text-4xl font-bold mb-2">10,000+</div>
                    <p class="text-gray-600">Tons Recycled</p>
                </div>
                <div class="text-center">
                    <div class="stat-number text-4xl font-bold mb-2">5,000+</div>
                    <p class="text-gray-600">Active Users</p>
                </div>
                <div class="text-center">
                    <div class="stat-number text-4xl font-bold mb-2">500+</div>
                    <p class="text-gray-600">Collection Points</p>
                </div>
                <div class="text-center">
                    <div class="stat-number text-4xl font-bold mb-2">50,000+</div>
                    <p class="text-gray-600">Trees Saved</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Choose Kiambu Recycling?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Our platform makes recycling easy, rewarding, and impactful for everyone in Kiambu County.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Convenient Collection</h3>
                    <p class="text-gray-600">
                        Schedule pickups at your doorstep or find the nearest drop-off point using our interactive map.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card bg-white rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Earn Rewards</h3>
                    <p class="text-gray-600">
                        Get points for every kilogram recycled. Redeem them for M-Pesa cash, airtime, or eco-friendly products.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card bg-white rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Track Your Impact</h3>
                    <p class="text-gray-600">
                        Monitor your environmental contribution with real-time stats on CO₂ reduced, trees saved, and water conserved.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card bg-white rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Mobile App Experience</h3>
                    <p class="text-gray-600">
                        Install as a PWA for offline access, push notifications, and native app-like experience on any device.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="feature-card bg-white rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Community Driven</h3>
                    <p class="text-gray-600">
                        Join a network of collectors and residents working together to build a sustainable future for Kiambu County.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="feature-card bg-white rounded-xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Secure & Private</h3>
                    <p class="text-gray-600">
                        Your data is protected with industry-standard security. We only collect information necessary for service delivery.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Getting started with recycling has never been easier. Follow these simple steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-white text-2xl font-bold">1</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Sign Up</h3>
                    <p class="text-gray-600">
                        Create your account using your phone number. We'll send you a verification code to get started.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-white text-2xl font-bold">2</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Request Pickup</h3>
                    <p class="text-gray-600">
                        Tell us what materials you have and when you'd like them collected. Our collectors will handle the rest.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-white text-2xl font-bold">3</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Earn & Track</h3>
                    <p class="text-gray-600">
                        Get rewarded for your contribution while tracking your environmental impact in real-time.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Section -->
    <section id="impact" class="py-20 bg-green-600 text-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Your Impact Matters</h2>
                <p class="text-xl text-green-100 max-w-2xl mx-auto">
                    Every piece of recycled material contributes to a healthier planet. See the difference you're making.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="text-6xl font-bold mb-4">🌳</div>
                    <h3 class="text-2xl font-semibold mb-2">Trees Saved</h3>
                    <p class="text-green-100">
                        Recycling paper and cardboard prevents deforestation and reduces greenhouse gas emissions.
                    </p>
                </div>
                <div class="text-center">
                    <div class="text-6xl font-bold mb-4">💧</div>
                    <h3 class="text-2xl font-semibold mb-2">Water Conserved</h3>
                    <p class="text-green-100">
                        Manufacturing with recycled materials uses significantly less water than virgin materials.
                    </p>
                </div>
                <div class="text-center">
                    <div class="text-6xl font-bold mb-4">🌍</div>
                    <h3 class="text-2xl font-semibold mb-2">CO₂ Reduced</h3>
                    <p class="text-green-100">
                        Recycling reduces energy consumption and lowers carbon dioxide emissions from manufacturing.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gray-900 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-4">Ready to Make a Difference?</h2>
            <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                Join thousands of Kiambu residents who are already recycling smarter and earning rewards.
                Start your journey towards a sustainable future today.
            </p>
            <a href="/scrap/signup.html" class="bg-green-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-green-700 transition duration-200">
                Get Started Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-white text-lg font-semibold mb-4">Kiambu Recycling</h3>
                    <p class="text-sm">
                        Sustainable waste management platform for Kiambu County,
                        connecting residents with recycling opportunities.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/scrap/map.html" class="hover:text-white">Find Drop-off Points</a></li>
                        <li><a href="/scrap/guide.html" class="hover:text-white">Recycling Guide</a></li>
                        <li><a href="/scrap/request.html" class="hover:text-white">Request Pickup</a></li>
                        <li><a href="/scrap/tracking.html" class="hover:text-white">Live Tracking</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/scrap/guide.html" class="hover:text-white">How to Recycle</a></li>
                        <li><a href="/scrap/guide.html#faq" class="hover:text-white">FAQ</a></li>
                        <li><a href="tel:+254700000000" class="hover:text-white">Contact Us</a></li>
                        <li><a href="/scrap/guide.html#materials" class="hover:text-white">Accepted Materials</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Connect</h4>
                    <p class="text-sm mb-4">
                        Follow us for tips, updates, and environmental news.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">📘</a>
                        <a href="#" class="text-gray-400 hover:text-white">🐦</a>
                        <a href="#" class="text-gray-400 hover:text-white">📷</a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm">
                <p>&copy; 2024 Kiambu Recycling & Scraps. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // PWA installation
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        // Service worker registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/scrap/public/service-worker.js')
                    .then(registration => {
                        console.log('ServiceWorker registered:', registration);
                    })
                    .catch(error => {
                        console.log('ServiceWorker registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>