<?php
session_start();
session_start();
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kiambu Recycling & Scraps</title>
    <meta name="color-scheme" content="light dark" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        
        /* Prevent FOUC */
        body { opacity: 0; transition: opacity 0.2s ease-in; }
        body.ready { opacity: 1; }
        
        /* Gradient animations */
        @keyframes gradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .gradient-animated {
            background-size: 200% 200%;
            animation: gradient 15s ease infinite;
        }
    </style>
    
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('ready');
        });
    </script>
</head>
<body class="bg-gray-50 dark:bg-slate-900 dark:text-slate-100 antialiased min-h-screen">
    <?php include __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="ml-64 p-6 md:p-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 dark:from-slate-800 dark:via-slate-700 dark:to-slate-800 rounded-2xl p-6 border border-green-100 dark:border-slate-600 shadow-sm">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Admin Dashboard</h1>
                <p class="text-gray-600 dark:text-slate-300">Monitor and manage the recycling platform</p>
            </div>
        </div>
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Collections -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1 uppercase tracking-wide">Total Collections</p>
                <h3 id="totalCollections" class="text-3xl font-bold text-gray-900 dark:text-white mb-2">0</h3>
                <div class="flex items-center text-sm">
                    <span id="collectionGrowth" class="font-medium text-green-600 dark:text-green-400">+0%</span>
                    <span class="text-gray-500 dark:text-slate-400 ml-2">vs last month</span>
                </div>
            </div>

            <!-- Active Collectors -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1 uppercase tracking-wide">Active Collectors</p>
                <h3 id="activeCollectors" class="text-3xl font-bold text-gray-900 dark:text-white mb-2">0</h3>
                <div class="flex items-center text-sm text-gray-500 dark:text-slate-400">
                    Out of <span id="totalCollectors" class="mx-1 font-medium text-gray-900 dark:text-white">0</span> total
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1 uppercase tracking-wide">Pending Approvals</p>
                <h3 id="pendingApprovals" class="text-3xl font-bold text-gray-900 dark:text-white mb-2">0</h3>
                <div class="flex items-center text-sm text-gray-500 dark:text-slate-400">
                    Requires review
                </div>
            </div>

            <!-- Total Rewards -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1 uppercase tracking-wide">Total Rewards</p>
                <h3 id="totalRewards" class="text-3xl font-bold text-gray-900 dark:text-white mb-2">KES 0</h3>
                <div class="flex items-center text-sm text-gray-500 dark:text-slate-400">
                    Via M-Pesa
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Charts Section -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Collection Trends -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Collection Trends</h2>
                        <select id="chartPeriod" class="text-sm rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 focus:border-green-500 focus:ring-green-500">
                            <option value="week">Past Week</option>
                            <option value="month" selected>Past Month</option>
                            <option value="year">Past Year</option>
                        </select>
                    </div>
                    <div class="relative h-72">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Materials Distribution -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Materials Distribution</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative h-64">
                            <canvas id="materialsChart"></canvas>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Plastic</span>
                                </div>
                                <span id="plasticPercentage" class="text-sm font-bold text-gray-900 dark:text-white">0%</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Paper</span>
                                </div>
                                <span id="paperPercentage" class="text-sm font-bold text-gray-900 dark:text-white">0%</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900/20">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-gray-500"></div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Metal</span>
                                </div>
                                <span id="metalPercentage" class="text-sm font-bold text-gray-900 dark:text-white">0%</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-green-50 dark:bg-green-900/20">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Glass</span>
                                </div>
                                <span id="glassPercentage" class="text-sm font-bold text-gray-900 dark:text-white">0%</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Electronics</span>
                                </div>
                                <span id="electronicsPercentage" class="text-sm font-bold text-gray-900 dark:text-white">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Panel -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <button onclick="location.href='/Scrap/views/admin/collectors.php'"
                                class="w-full flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all shadow-sm hover:shadow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="font-medium">Manage Collectors</span>
                        </button>
                        <button onclick="location.href='/Scrap/views/admin/dropoffs.php'"
                                class="w-full flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all shadow-sm hover:shadow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="font-medium">Drop-off Points</span>
                        </button>
                        <button onclick="location.href='/Scrap/views/admin/reports.php'"
                                class="w-full flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all shadow-sm hover:shadow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="font-medium">Generate Reports</span>
                        </button>
                        <button onclick="location.href='/Scrap/views/admin/rewards.php'"
                                class="w-full flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-lg hover:from-yellow-600 hover:to-yellow-700 transition-all shadow-sm hover:shadow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-medium">Manage Rewards</span>
                        </button>
                    </div>
                </div>

                <!-- Pending Reviews -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pending Reviews</h2>
                    <div class="space-y-3" id="pendingReviewsList">
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50">
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-slate-600 animate-pulse"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-3 bg-gray-200 dark:bg-slate-600 rounded animate-pulse"></div>
                                <div class="h-2 bg-gray-200 dark:bg-slate-600 rounded w-2/3 animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Status</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-600 dark:text-slate-400">Server Load</span>
                                <span id="serverLoad" class="text-sm font-semibold text-gray-900 dark:text-white">0%</span>
                            </div>
                            <div class="h-2 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div id="serverLoadBar" class="h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full transition-all duration-500" style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-green-50 dark:bg-green-900/20">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-slate-300">Database</span>
                            </div>
                            <span id="dbStatus" class="text-xs font-semibold text-green-600 dark:text-green-400">Connected</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-slate-300">M-Pesa</span>
                            </div>
                            <span id="mpesaStatus" class="text-xs font-semibold text-blue-600 dark:text-blue-400">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Check admin authentication
        const userRole = sessionStorage.getItem('user_role');
        if (!sessionStorage.getItem('user_id') || userRole !== 'admin') {
            window.location.href = '/Scrap/views/auth/login.php';
        }

        // Store chart instances globally
        let trendChartInstance = null;
        let materialsChartInstance = null;

        // Load admin data
        async function loadAdminData() {
            try {
                const response = await fetch('/Scrap/api/admin/dashboard.php', {
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Update stats
                    document.getElementById('totalCollections').textContent = data.stats.total_collections;
                    document.getElementById('collectionGrowth').textContent = 
                        (data.stats.collection_growth > 0 ? '+' : '') + 
                        data.stats.collection_growth + '%';
                    
                    document.getElementById('activeCollectors').textContent = data.stats.active_collectors;
                    document.getElementById('totalCollectors').textContent = data.stats.total_collectors;
                    
                    document.getElementById('pendingApprovals').textContent = data.stats.pending_approvals;
                    document.getElementById('totalRewards').textContent = 'KES ' + data.stats.total_rewards;
                    
                    // Update admin name if element exists
                    const adminNameEl = document.getElementById('adminName');
                    if (adminNameEl && data.admin && data.admin.name) {
                        adminNameEl.textContent = data.admin.name;
                    }
                    
                    // Update system status if data exists
                    if (data.system) {
                        updateSystemStatus(data.system);
                    }
                    
                    // Update charts
                    updateTrendChart(data.trends);
                    updateMaterialsChart(data.materials);
                    
                    // Update pending reviews
                    updatePendingReviews(data.pending_reviews);
                }
            } catch (error) {
                console.error('Failed to load admin data:', error);
            }
        }

        // Update system status indicators
        function updateSystemStatus(system) {
            const serverLoad = document.getElementById('serverLoad');
            const serverLoadBar = document.getElementById('serverLoadBar');
            const dbStatus = document.getElementById('dbStatus');
            const mpesaStatus = document.getElementById('mpesaStatus');
            
            serverLoad.textContent = system.server_load + '%';
            serverLoadBar.style.width = system.server_load + '%';
            
            // Update server load color based on percentage
            if (system.server_load > 80) {
                serverLoadBar.classList.remove('from-green-500', 'to-emerald-500');
                serverLoadBar.classList.add('from-red-500', 'to-red-600');
            } else if (system.server_load > 60) {
                serverLoadBar.classList.remove('from-green-500', 'to-emerald-500');
                serverLoadBar.classList.add('from-yellow-500', 'to-yellow-600');
            }
            
            // Update database status
            const dbParent = dbStatus.closest('.flex.items-center.justify-between');
            dbStatus.textContent = system.database_connected ? 'Connected' : 'Disconnected';
            if (system.database_connected) {
                dbStatus.className = 'text-xs font-semibold text-green-600 dark:text-green-400';
                dbParent.className = 'flex items-center justify-between p-3 rounded-lg bg-green-50 dark:bg-green-900/20';
            } else {
                dbStatus.className = 'text-xs font-semibold text-red-600 dark:text-red-400';
                dbParent.className = 'flex items-center justify-between p-3 rounded-lg bg-red-50 dark:bg-red-900/20';
            }
            
            // Update M-Pesa status
            const mpesaParent = mpesaStatus.closest('.flex.items-center.justify-between');
            mpesaStatus.textContent = system.mpesa_connected ? 'Active' : 'Inactive';
            if (system.mpesa_connected) {
                mpesaStatus.className = 'text-xs font-semibold text-blue-600 dark:text-blue-400';
                mpesaParent.className = 'flex items-center justify-between p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20';
            } else {
                mpesaStatus.className = 'text-xs font-semibold text-red-600 dark:text-red-400';
                mpesaParent.className = 'flex items-center justify-between p-3 rounded-lg bg-red-50 dark:bg-red-900/20';
            }
        }

        // Update collection trend chart
        function updateTrendChart(trends) {
            // Destroy existing chart if it exists
            if (trendChartInstance) {
                trendChartInstance.destroy();
            }
            
            const ctx = document.getElementById('trendChart').getContext('2d');
            trendChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trends.labels,
                    datasets: [{
                        label: 'Collections',
                        data: trends.collections,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: 'rgb(34, 197, 94)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Update materials distribution chart
        function updateMaterialsChart(materials) {
            // Destroy existing chart if it exists
            if (materialsChartInstance) {
                materialsChartInstance.destroy();
            }
            
            const ctx = document.getElementById('materialsChart').getContext('2d');
            
            // Use dynamic labels and values from API
            const labels = materials.labels || ['No Data'];
            const values = materials.values || [0];
            
            // Generate colors dynamically
            const colors = [
                'rgb(59, 130, 246)',   // Blue
                'rgb(234, 179, 8)',    // Yellow
                'rgb(75, 85, 99)',     // Gray
                'rgb(34, 197, 94)',    // Green
                'rgb(168, 85, 247)',   // Purple
                'rgb(239, 68, 68)',    // Red
                'rgb(236, 72, 153)'    // Pink
            ];
            
            materialsChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors.slice(0, labels.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    },
                    cutout: '65%'
                }
            });
            
            // Update percentage labels if they exist
            const total = values.reduce((a, b) => a + b, 0);
            if (total > 0) {
                const percentages = values.map(v => ((v / total) * 100).toFixed(1));
                
                const plasticEl = document.getElementById('plasticPercentage');
                const paperEl = document.getElementById('paperPercentage');
                const metalEl = document.getElementById('metalPercentage');
                const glassEl = document.getElementById('glassPercentage');
                const electronicsEl = document.getElementById('electronicsPercentage');
                
                if (plasticEl) plasticEl.textContent = (percentages[0] || 0) + '%';
                if (paperEl) paperEl.textContent = (percentages[1] || 0) + '%';
                if (metalEl) metalEl.textContent = (percentages[2] || 0) + '%';
                if (glassEl) glassEl.textContent = (percentages[3] || 0) + '%';
                if (electronicsEl) electronicsEl.textContent = (percentages[4] || 0) + '%';
            }
        }

        // Update pending reviews list
        function updatePendingReviews(reviews) {
            const list = document.getElementById('pendingReviewsList');
            
            if (reviews.length === 0) {
                list.innerHTML = '<p class="text-sm text-gray-500 dark:text-slate-400 text-center py-4">No pending reviews</p>';
                return;
            }
            
            list.innerHTML = reviews.map(review => `
                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
                        ${review.type.charAt(0)}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white text-sm truncate">${review.description}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">${review.type}</p>
                    </div>
                    <button onclick="reviewItem('${review.type}', ${review.id})"
                            class="px-3 py-1.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-xs font-medium rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all shadow-sm hover:shadow flex-shrink-0">
                        Review
                    </button>
                </div>
            `).join('');
        }

        // Handle review item click
        function reviewItem(type, id) {
            switch (type) {
                case 'Collector Application':
                    window.location.href = `/admin/collectors.html?review=${id}`;
                    break;
                case 'Collection Report':
                    window.location.href = `/admin/reports.html?review=${id}`;
                    break;
                case 'Reward Claim':
                    window.location.href = `/admin/rewards.html?review=${id}`;
                    break;
            }
        }

        // Handle chart period change
        document.getElementById('chartPeriod').addEventListener('change', function(e) {
            fetch(`/Scrap/api/admin/trends.php?period=${e.target.value}`, {
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        updateTrendChart(data.trends);
                    }
                })
                .catch(error => console.error('Failed to update trends:', error));
        });

        // Logout function
        function logout() {
            fetch('/Scrap/api/logout.php', {
                method: 'POST',
                credentials: 'include'
            })
                .then(() => {
                    sessionStorage.clear();
                    window.location.href = '/Scrap/views/auth/login.php?logout=1';
                })
                .catch(error => console.error('Logout failed:', error));
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Show content after load (prevent FOUC)
            document.body.classList.add('ready');
            
            // Load admin data
            loadAdminData();
        });
        
        // Refresh data every 30 seconds
        setInterval(loadAdminData, 30000);
    </script>
</body>
</html>