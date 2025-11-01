<?php
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
    <title>Collector Profile - Kiambu Recycling & Scraps</title>
    <meta name="color-scheme" content="light dark" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <script src="https://cdn.tailwindcss.com"></script>
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
                <!-- Profile Header Card -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-800 dark:to-slate-700 rounded-lg shadow-sm p-6 border border-green-100 dark:border-slate-600">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 rounded-full border-2 border-white dark:border-slate-800 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h1 id="collectorName" class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Loading...</h1>
                                <p id="collectorPhone" class="text-sm text-gray-600 dark:text-slate-300 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span></span>
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Verified
                        </span>
                    </div>
                </div>

                <!-- Personal Details Card -->
                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Personal Details</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">Email Address</p>
                            <p id="collectorEmail" class="font-medium text-gray-900 dark:text-white">—</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">ID Number</p>
                            <p id="collectorIdNumber" class="font-medium text-gray-900 dark:text-white">—</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">Age</p>
                            <p id="collectorAge" class="font-medium text-gray-900 dark:text-white">—</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">Home Address</p>
                            <p id="collectorAddress" class="font-medium text-gray-900 dark:text-white">—</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">Status</p>
                            <p id="collectorStatus" class="font-medium">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-slate-200">
                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                    Offline
                                </span>
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">Member Since</p>
                            <p id="collectorJoinedDate" class="font-medium text-gray-900 dark:text-white">—</p>
                        </div>
                    </div>
                </div>

                <!-- Statistics Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Collections</p>
                                <p id="totalCollections" class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
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
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Active Requests</p>
                                <p id="activeRequests" class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                            </div>
                            <div class="w-11 h-11 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Earnings</p>
                                <p id="totalEarnings" class="text-2xl font-bold text-gray-900 dark:text-white">KES 0</p>
                            </div>
                            <div class="w-11 h-11 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Rating</p>
                                <p id="rating" class="text-2xl font-bold text-gray-900 dark:text-white">0.0</p>
                                <p id="totalReviews" class="text-[10px] text-gray-500 dark:text-slate-400 mt-0.5">0 reviews</p>
                            </div>
                            <div class="w-11 h-11 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            </div>
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
                                <span id="vehicleType" class="font-medium text-gray-900 dark:text-white"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-slate-700">
                                <span class="text-gray-600 dark:text-slate-400">Registration</span>
                                <span id="vehicleReg" class="font-medium text-gray-900 dark:text-white"></span>
                            </div>
                            <div class="pt-2">
                                <span class="text-gray-600 dark:text-slate-400 block mb-2">Materials Collected</span>
                                <div id="materialsList" class="flex flex-wrap gap-2">
                                    <!-- Will be populated dynamically -->
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
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>
                </div>

                <!-- Collection History -->
                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Collection History</h2>
                        <select id="historyPeriod" class="text-sm rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            <option value="week">Past Week</option>
                            <option value="month" selected>Past Month</option>
                            <option value="year">Past Year</option>
                        </select>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Location</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Material</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Weight</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Rating</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody" class="divide-y divide-gray-200 dark:divide-slate-700">
                                <!-- Will be populated dynamically -->
                            </tbody>
                        </table>
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

    <script>
        // Load profile data
        async function loadProfileData() {
            try {
                const response = await fetch('/Scrap/api/collectors/profile.php', { credentials: 'include' });
                if (response.status === 401) {
                    window.location.href = '/Scrap/login.php';
                    return;
                }
                if (response.status === 403) {
                    // Not a collector yet – send to collector register
                    window.location.href = '/Scrap/public/collectors/register.php';
                    return;
                }
                const data = await response.json();
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Failed to load profile');
                }
                updateProfileHeader(data.profile);
                updateStats(data.stats);
                updateVehicleInfo(data.vehicle);
                updateServiceAreas(data.areas);
                updateHistory(data.history);
                updateCharts(data.analytics);
            } catch (error) {
                console.error('Failed to load profile data:', error);
            }
        }

        // Update profile header
        function updateProfileHeader(profile) {
            document.getElementById('collectorName').textContent = profile.name;
            document.getElementById('collectorPhone').querySelector('span').textContent = profile.phone;
            
            // Update personal details
            document.getElementById('collectorEmail').textContent = profile.email || '—';
            document.getElementById('collectorIdNumber').textContent = profile.id_number || '—';
            document.getElementById('collectorAge').textContent = profile.age ? `${profile.age} years` : '—';
            document.getElementById('collectorAddress').textContent = profile.home_address || '—';
            document.getElementById('collectorJoinedDate').textContent = profile.joined_date || '—';
            
            // Update status with color indicator
            const statusEl = document.getElementById('collectorStatus');
            const statusMap = {
                'online': { text: 'Available', color: 'bg-green-500', bgColor: 'bg-green-100 dark:bg-green-900/30', textColor: 'text-green-700 dark:text-green-300' },
                'on_job': { text: 'On Job', color: 'bg-blue-500', bgColor: 'bg-blue-100 dark:bg-blue-900/30', textColor: 'text-blue-700 dark:text-blue-300' },
                'offline': { text: 'Offline', color: 'bg-gray-400', bgColor: 'bg-gray-100 dark:bg-slate-700', textColor: 'text-gray-700 dark:text-slate-200' }
            };
            const status = statusMap[profile.active_status] || statusMap['offline'];
            statusEl.innerHTML = `
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs ${status.bgColor} ${status.textColor}">
                    <span class="w-2 h-2 rounded-full ${status.color}"></span>
                    ${status.text}
                </span>
            `;
        }

        // Update statistics
        function updateStats(stats) {
            document.getElementById('totalCollections').textContent = stats.total_collections;
            document.getElementById('activeRequests').textContent = stats.active_requests || 0;
            document.getElementById('totalEarnings').textContent = `KES ${Number(stats.total_earnings).toLocaleString()}`;
            document.getElementById('rating').textContent = stats.rating.toFixed(1);
            document.getElementById('totalReviews').textContent = `${stats.total_reviews} reviews`;
        }

        // Update vehicle information
        function updateVehicleInfo(vehicle) {
            document.getElementById('vehicleType').textContent = vehicle.type;
            document.getElementById('vehicleReg').textContent = vehicle.registration;
            
            const materialsList = document.getElementById('materialsList');
            materialsList.innerHTML = '';
            
            vehicle.materials.forEach(material => {
                const badge = document.createElement('span');
                badge.className = 'px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-slate-200';
                badge.textContent = material;
                materialsList.appendChild(badge);
            });
        }

        // Update service areas
        function updateServiceAreas(areas) {
            const areasList = document.getElementById('areasList');
            areasList.innerHTML = '';
            
            areas.forEach(area => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-slate-700 rounded text-gray-700 dark:text-slate-200';
                div.innerHTML = `
                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-sm">${area}</span>
                `;
                areasList.appendChild(div);
            });
        }

        // Update history table
        function updateHistory(history) {
            const tbody = document.getElementById('historyTableBody');
            tbody.innerHTML = '';
            
            history.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-100">${item.date}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-100">${item.customer_name || '—'}</td>
                    <td class="px-4 py-4 text-sm text-gray-900 dark:text-slate-100">
                        <div class="max-w-xs truncate" title="${item.pickup_address || '—'}">
                            ${item.pickup_address || '—'}
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-100">${item.material_type}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-100">${item.weight} kg</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-100">KES ${Number(item.amount).toLocaleString()}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-100">
                        <div class="flex items-center">
                            <span class="text-yellow-500 dark:text-yellow-400 mr-1">★</span>
                            ${item.rating ?? 'N/A'}
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Update charts
        function updateCharts(analytics) {
            // Earnings trend chart
            const earningsCtx = document.getElementById('earningsChart').getContext('2d');
            new Chart(earningsCtx, {
                type: 'line',
                data: {
                    labels: analytics.earnings.labels,
                    datasets: [{
                        label: 'Daily Earnings',
                        data: analytics.earnings.values,
                        borderColor: 'rgb(34, 197, 94)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Materials distribution chart
            const materialsCtx = document.getElementById('materialsChart').getContext('2d');
            new Chart(materialsCtx, {
                type: 'doughnut',
                data: {
                    labels: analytics.materials.labels,
                    datasets: [{
                        data: analytics.materials.values,
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(234, 179, 8)',
                            'rgb(75, 85, 99)',
                            'rgb(34, 197, 94)',
                            'rgb(168, 85, 247)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Handle history period changes
        document.getElementById('historyPeriod').addEventListener('change', async function(e) {
            try {
                const response = await fetch(`/Scrap/api/collectors/history.php?period=${encodeURIComponent(e.target.value)}` , { credentials: 'include' });
                if (response.status === 401) {
                    window.location.href = '/Scrap/login.php';
                    return;
                }
                const data = await response.json();
                if (data.status === 'success') {
                    updateHistory(data.history);
                }
            } catch (error) {
                console.error('Failed to update history:', error);
            }
        });

        // Logout function
        async function logout() {
            try {
                await fetch('/Scrap/api/logout.php', { credentials: 'include' });
                sessionStorage.clear();
                window.location.href = '/Scrap/login.php';
            } catch (error) {
                console.error('Logout failed:', error);
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadProfileData();
        });
    </script>
</body>
</html>