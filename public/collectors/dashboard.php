<?php
// Start session and check authentication
require_once '../../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'collector') {
    header('Location: /Scrap/views/auth/login.php');
    exit();
}

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Collector Dashboard - Kiambu Recycling & Scraps</title>
    <meta name="color-scheme" content="light dark" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Prevent FOUC (Flash of Unstyled Content) */
        body { opacity: 0; transition: opacity 0.2s ease-in; }
        body.ready { opacity: 1; }
        
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        .skeleton { position: relative; overflow: hidden; background: linear-gradient(110deg,#f4f4f5 8%,#e4e4e7 18%,#f4f4f5 33%); background-size:200% 100%; animation: shine 1.1s linear infinite; }
        .dark .skeleton { background: linear-gradient(110deg,#334155 8%,#475569 18%,#334155 33%); }
        @keyframes shine { to { background-position-x: -200%; } }
    </style>
    <script>
        // Ensure body is shown after styles are loaded
        window.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('ready');
        });
    </script>
        </head>
<body class="h-full bg-gray-50 dark:bg-slate-900 dark:text-slate-100 antialiased">
    <div class="min-h-screen flex ml-64">
        <?php include '../../includes/collector_sidebar.php'; ?>
        <!-- Main content -->
        <main class="flex-1 overflow-auto">
            <div class="p-4 md:p-6 space-y-6">
                <!-- Welcome Header -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-800 dark:to-slate-700 rounded-lg shadow-sm p-5 border border-green-100 dark:border-slate-600">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Welcome back, <span id="collectorNameHeader">Collector</span>!</h1>
                    <p class="text-sm text-gray-600 dark:text-slate-300">Here's your dashboard overview for today</p>
                </div>

                <!-- Statistics Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Today's Collections</p>
                                <p id="todayCollections" class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                            </div>
                            <div class="w-11 h-11 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Today's Earnings</p>
                                <p id="todayEarnings" class="text-2xl font-bold text-gray-900 dark:text-white">KES 0</p>
                            </div>
                            <div class="w-11 h-11 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Weight Today</p>
                                <p id="totalWeight" class="text-2xl font-bold text-gray-900 dark:text-white">0 kg</p>
                            </div>
                            <div class="w-11 h-11 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Rating</p>
                                <p id="rating" class="text-2xl font-bold text-gray-900 dark:text-white">0.0</p>
                            </div>
                            <div class="w-11 h-11 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Map and Active Requests -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2 space-y-4">
                            <!-- Map -->
                            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Collection Route Map</h2>
                                    <div class="flex items-center gap-2">
                                        <button id="fitActiveBtn" class="px-3 py-1.5 text-xs bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-gray-700 dark:text-slate-200 rounded transition-colors">Fit Active</button>
                                        <button id="locateMeBtn" class="px-3 py-1.5 text-xs bg-green-600 text-white hover:bg-green-700 rounded transition-colors">My Location</button>
                                    </div>
                                </div>
                                <div id="map" class="h-80 md:h-96 rounded-lg overflow-hidden border border-gray-200 dark:border-slate-600"></div>
                            </div>
                            <!-- Active Route Panel -->
                            <div id="activeRoutePanel" class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700 hidden">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-semibold text-sm text-gray-900 dark:text-white">Active Route Details</h3>
                                    <button id="clearRouteBtn" class="text-xs text-red-600 dark:text-red-400 hover:underline">Clear Route</button>
                                </div>
                                <div id="routeDetails" class="text-xs space-y-1.5 text-slate-600 dark:text-slate-300"></div>
                            </div>
                        </div>
                        <!-- Active Requests Panel -->
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700 h-fit">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Active Requests</h2>
                                <button id="refreshBtn" class="text-xs px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-gray-700 dark:text-slate-200 transition-colors">Refresh</button>
                            </div>
                            <div id="activeRequests" class="space-y-3 min-h-[4rem]">
                                <div class="skeleton h-10 rounded"></div>
                                <div class="skeleton h-10 rounded"></div>
                            </div>
                        </div>
                    </div>

                <!-- Vehicle & Service Areas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Vehicle Info -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Vehicle Information</h2>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-slate-700">
                                <span class="text-gray-600 dark:text-slate-400">Vehicle Type</span>
                                <span id="vehicleType" class="font-medium text-gray-900 dark:text-white">Loading...</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-slate-700">
                                <span class="text-gray-600 dark:text-slate-400">Registration</span>
                                <span id="vehicleReg" class="font-medium text-gray-900 dark:text-white">Loading...</span>
                            </div>
                            <div class="pt-2">
                                <span class="text-gray-600 dark:text-slate-400 block mb-2">Materials Collected</span>
                                <div id="materialsList" class="flex flex-wrap gap-2">
                                    <div class="skeleton h-6 w-16 rounded-full"></div>
                                    <div class="skeleton h-6 w-20 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Areas -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Service Areas</h2>
                        </div>
                        <div id="areasList" class="grid grid-cols-2 gap-2 text-sm">
                            <div class="skeleton h-8 rounded"></div>
                            <div class="skeleton h-8 rounded"></div>
                        </div>
                    </div>
                </div>

                <!-- Performance Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Earnings Trend -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Earnings Trend</h2>
                        <div class="relative h-72">
                            <canvas id="earningsChart"></canvas>
                        </div>
                    </div>

                    <!-- Materials Distribution -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Materials Distribution</h2>
                        <div class="relative h-72">
                            <canvas id="materialsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
    </main>
    </div>
<script src="/Scrap/public/js/collector-tracker.js"></script>
<script type="module" src="/Scrap/public/js/collector-dashboard.js"></script>
<div id="toastContainer" class="fixed top-4 right-4 space-y-2 z-50"></div>
<?php 
// Pass extra scripts placeholder if needed later
$extraScripts = '';

?>
</body>
</html>