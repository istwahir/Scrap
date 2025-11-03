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
    <title>Requests - Kiambu Recycling & Scraps</title>
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

        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <div class="p-4 md:p-6 space-y-6">
                <!-- Header -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-800 dark:to-slate-700 rounded-lg shadow-sm p-5 border border-green-100 dark:border-slate-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Collection Requests</h1>
                            <p class="text-sm text-gray-600 dark:text-slate-300">Manage and track your collection requests</p>
                        </div>
                        <button id="refreshBtn" class="px-4 py-2 bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-100 rounded-lg shadow hover:bg-green-50 dark:hover:bg-green-900/40 border border-gray-200 dark:border-slate-700 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Pending Requests</h2>
                                <span id="pendingCount" class="text-sm font-medium text-amber-600 dark:text-amber-400">0</span>
                            </div>
                            <div id="pendingList" class="space-y-3 min-h-[6rem]">
                                <div class="skeleton h-12 rounded"></div>
                                <div class="skeleton h-12 rounded"></div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Active Requests</h2>
                                <span id="activeCount" class="text-sm font-medium text-green-600 dark:text-green-400">0</span>
                            </div>
                            <div id="activeList" class="space-y-3 min-h-[6rem]">
                                <div class="skeleton h-12 rounded"></div>
                                <div class="skeleton h-12 rounded"></div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Map</h3>
                            <div id="map" class="h-72 rounded"></div>
                        </div>

                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Filters</h3>
                            <select id="materialFilter" class="w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 p-2 text-sm">
                                <option value="">All materials</option>
                                <option value="plastic">Plastic</option>
                                <option value="paper">Paper</option>
                                <option value="metal">Metal</option>
                                <option value="glass">Glass</option>
                                <option value="electronics">Electronics</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="toastContainer" class="fixed top-4 right-4 space-y-2 z-50"></div>
            </div>
        </main>
    </div>

    <script type="module" src="/Scrap/public/js/collector-requests.js"></script>
</body>
</html>
