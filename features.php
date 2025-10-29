<?php
// Disable auth requirement for features page
$requireAuth = false;
$pageTitle = 'Platform Features - Kiambu Recycling';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController();
$isAuthenticated = $auth->isAuthenticated();

include 'includes/header.php';
?>

<div class="min-h-screen hero-gradient">
    <!-- Hero Section -->
    <section class="relative py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center mb-16">
                <h1 class="text-5xl md:text-6xl font-bold text-white mb-6">
                    Platform 
                    <span class="bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent">
                        Features
                    </span>
                </h1>
                <p class="text-xl text-emerald-100/80 leading-relaxed">
                    Discover how our comprehensive waste management platform makes recycling convenient, 
                    rewarding, and impactful for everyone in Kiambu County.
                </p>
            </div>
        </div>
    </section>

    <!-- For Citizens Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-500/20 rounded-full mb-4">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <h2 class="text-4xl font-bold text-white mb-4">For Households & Citizens</h2>
                <p class="text-emerald-100/80 text-lg max-w-3xl mx-auto">
                    Make waste disposal effortless while earning rewards for your environmental contribution.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-card p-8 hover:scale-105 transition-all duration-300">
                    <div class="w-14 h-14 bg-emerald-500/20 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Create Collection Requests</h3>
                    <p class="text-emerald-100/70 mb-4">
                        Schedule waste pickups with just a few taps. Select waste type (plastic, paper, metal, e-waste, organic), 
                        estimate quantity, upload photos, and choose your preferred date and time.
                    </p>
                    <ul class="space-y-2 text-sm text-emerald-100/60">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-emerald-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Multiple waste categories
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-emerald-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Photo documentation
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-emerald-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Flexible scheduling
                        </li>
                    </ul>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card p-8 hover:scale-105 transition-all duration-300">
                    <div class="w-14 h-14 bg-sky-500/20 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Real-Time GPS Tracking</h3>
                    <p class="text-emerald-100/70 mb-4">
                        Track your assigned collector's live location on an interactive map. See their exact position, 
                        estimated arrival time, and get notified when they're 10 minutes away.
                    </p>
                    <ul class="space-y-2 text-sm text-emerald-100/60">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-sky-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Live location updates
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-sky-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            ETA notifications
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-sky-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Interactive map view
                        </li>
                    </ul>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card p-8 hover:scale-105 transition-all duration-300">
                    <div class="w-14 h-14 bg-purple-500/20 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Earn Points & Rewards</h3>
                    <p class="text-emerald-100/70 mb-4">
                        Get rewarded for every kilogram of waste recycled. Accumulate points based on material type and weight. 
                        Redeem instantly for M-Pesa cash, airtime, or save up for bigger rewards.
                    </p>
                    <ul class="space-y-2 text-sm text-emerald-100/60">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-purple-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            M-Pesa integration
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-purple-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Instant redemption
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-purple-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Multiple reward options
                        </li>
                    </ul>
                </div>

                <!-- Feature 4 -->
                <div class="glass-card p-8 hover:scale-105 transition-all duration-300">
                    <div class="w-14 h-14 bg-amber-500/20 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Impact Dashboard</h3>
                    <p class="text-emerald-100/70 mb-4">
                        Track your environmental contribution with detailed analytics. View CO₂ emissions prevented, 
                        water conserved, trees saved, and compare your impact with community averages.
                    </p>
                    <ul class="space-y-2 text-sm text-emerald-100/60">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-amber-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Visual charts & graphs
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-amber-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Monthly trends
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-amber-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Community comparison
                        </li>
                    </ul>
                </div>

                <!-- Feature 5 -->
                <div class="glass-card p-8 hover:scale-105 transition-all duration-300">
                    <div class="w-14 h-14 bg-rose-500/20 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Recycling Guide & Education</h3>
                    <p class="text-emerald-100/70 mb-4">
                        Learn proper waste segregation techniques for different materials. Access comprehensive guides 
                        with images showing what's recyclable, how to prepare items, and best practices.
                    </p>
                    <ul class="space-y-2 text-sm text-emerald-100/60">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-rose-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Material categories
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-rose-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Visual tutorials
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-rose-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Best practices tips
                        </li>
                    </ul>
                </div>

                <!-- Feature 6 -->
                <div class="glass-card p-8 hover:scale-105 transition-all duration-300">
                    <div class="w-14 h-14 bg-indigo-500/20 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Request History & Management</h3>
                    <p class="text-emerald-100/70 mb-4">
                        View complete history of all your collection requests. Track status, see collector details, 
                        review past transactions, and manage active requests all in one place.
                    </p>
                    <ul class="space-y-2 text-sm text-emerald-100/60">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-indigo-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Complete transaction log
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-indigo-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Status tracking
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-indigo-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Cancel/modify requests
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- For Collectors Section -->
    <section class="py-16 bg-slate-900/30">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-sky-500/20 rounded-full mb-4">
                    <svg class="w-8 h-8 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <h2 class="text-4xl font-bold text-white mb-4">For Waste Collectors</h2>
                <p class="text-emerald-100/80 text-lg max-w-3xl mx-auto">
                    Professional tools to manage your collection business efficiently and grow your income.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Collector Feature 1 -->
                <div class="glass-card p-6 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Request Management</h3>
                    <p class="text-emerald-100/70 text-sm">
                        View available collection requests in your area. Accept, decline, or negotiate terms before committing.
                    </p>
                </div>

                <!-- Collector Feature 2 -->
                <div class="glass-card p-6 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 bg-sky-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Route Optimization</h3>
                    <p class="text-emerald-100/70 text-sm">
                        Get optimized collection routes based on request locations. Save fuel and time with smart navigation.
                    </p>
                </div>

                <!-- Collector Feature 3 -->
                <div class="glass-card p-6 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Earnings Tracking</h3>
                    <p class="text-emerald-100/70 text-sm">
                        Monitor daily, weekly, and monthly earnings. View payment history and pending payouts in real-time.
                    </p>
                </div>

                <!-- Collector Feature 4 -->
                <div class="glass-card p-6 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Rating System</h3>
                    <p class="text-emerald-100/70 text-sm">
                        Build your reputation through customer ratings. Higher ratings lead to more collection opportunities.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Admin Features Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-500/20 rounded-full mb-4">
                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h2 class="text-4xl font-bold text-white mb-4">For System Administrators</h2>
                <p class="text-emerald-100/80 text-lg max-w-3xl mx-auto">
                    Comprehensive management tools to oversee operations, monitor performance, and ensure smooth platform operation.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Admin Feature 1 -->
                <div class="glass-card p-6 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Analytics Dashboard</h3>
                    <p class="text-emerald-100/70 text-sm mb-3">
                        Real-time statistics on collections, user engagement, revenue, and environmental impact across the county.
                    </p>
                    <div class="text-xs text-emerald-400">View trends & generate reports</div>
                </div>

                <!-- Admin Feature 2 -->
                <div class="glass-card p-6 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 bg-sky-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">User Management</h3>
                    <p class="text-emerald-100/70 text-sm mb-3">
                        Manage citizens, collectors, and admin accounts. Verify collectors, handle disputes, and maintain platform integrity.
                    </p>
                    <div class="text-xs text-sky-400">Full CRUD operations</div>
                </div>

                <!-- Admin Feature 3 -->
                <div class="glass-card p-6 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Rewards Management</h3>
                    <p class="text-emerald-100/70 text-sm mb-3">
                        Configure reward rates, add new reward items, manage redemptions, and monitor reward economics.
                    </p>
                    <div class="text-xs text-purple-400">Dynamic pricing control</div>
                </div>

                <!-- Admin Feature 4 -->
                <div class="glass-card p-6 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 bg-rose-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Request Oversight</h3>
                    <p class="text-emerald-100/70 text-sm mb-3">
                        Monitor all collection requests, resolve issues, handle escalations, and ensure service quality.
                    </p>
                    <div class="text-xs text-rose-400">Real-time monitoring</div>
                </div>

                <!-- Admin Feature 5 -->
                <div class="glass-card p-6 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Drop-off Management</h3>
                    <p class="text-emerald-100/70 text-sm mb-3">
                        Add, edit, and manage collection drop-off points. Update schedules, contact info, and accepted materials.
                    </p>
                    <div class="text-xs text-amber-400">Location mapping</div>
                </div>

                <!-- Admin Feature 6 -->
                <div class="glass-card p-6 hover:scale-105 transition-all duration-300">
                    <div class="w-12 h-12 bg-indigo-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Comprehensive Reports</h3>
                    <p class="text-emerald-100/70 text-sm mb-3">
                        Generate detailed reports on collections, revenue, environmental impact, and user behavior. Export in multiple formats.
                    </p>
                    <div class="text-xs text-indigo-400">PDF/Excel export</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="glass-card p-12 text-center">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    Ready to 
                    <span class="bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent">
                        Get Started?
                    </span>
                </h2>
                <p class="text-xl text-emerald-100/80 mb-8 max-w-2xl mx-auto">
                    Join our growing community and start making a difference today.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <?php if ($isAuthenticated): ?>
                        <a href="/Scrap/views/citizens/dashboard.php" class="inline-flex items-center justify-center gap-3 rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-8 py-4 text-lg font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition-all duration-300 hover:scale-105">
                            Go to Dashboard
                        </a>
                    <?php else: ?>
                        <a href="/Scrap/views/auth/signup.php" class="inline-flex items-center justify-center gap-3 rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-8 py-4 text-lg font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition-all duration-300 hover:scale-105">
                            Sign Up Free
                        </a>
                        <a href="/Scrap/views/auth/login.php" class="inline-flex items-center justify-center gap-3 rounded-full border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:border-emerald-300/60 hover:bg-white/10">
                            Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
