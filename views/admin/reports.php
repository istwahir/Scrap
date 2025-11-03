<?php
session_start();
session_start();
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <m                <!-- Points Trends -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Points Awarded Trends</h3>
                    <div class="h-64">
                        <canvas id="pointsChart"></canvas>
                    </div>
                </div>rset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Admin Dashboard</title>
    <meta name="color-scheme" content="light dark" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        
        body { opacity: 0; transition: opacity 0.2s ease-in; }
        body.ready { opacity: 1; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-slate-900">

    <?php include __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="ml-64 min-h-screen">
        <header class="bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 dark:from-slate-800 dark:via-slate-800 dark:to-slate-800 border-b border-gray-200 dark:border-slate-700">
            <div class="p-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Reports & Analytics</h1>
                <p class="text-gray-600 dark:text-slate-400">Comprehensive insights and exportable reports</p>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <!-- Date Range Filter -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex gap-3 items-center">
                        <label class="text-sm font-medium text-gray-700 dark:text-slate-300">Date Range:</label>
                        <input type="date" id="startDate" class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white">
                        <span class="text-gray-500 dark:text-slate-400">to</span>
                        <input type="date" id="endDate" class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white">
                        <select id="quickRange" class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white">
                            <option value="">Quick Select</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="year">This Year</option>
                        </select>
                        <button onclick="applyDateFilter()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="exportReport('pdf')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export PDF
                        </button>
                        <button onclick="exportReport('csv')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Overview Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Total Collections</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalCollections">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Total Points Awarded</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalPoints">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Active Users</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="activeUsers">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Growth Rate</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white"><span id="growthRate">0</span>%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Collections Over Time -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Collections Over Time</h3>
                    <div class="h-72">
                        <canvas id="collectionsChart"></canvas>
                    </div>
                </div>

                <!-- Material Distribution -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Material Distribution</h3>
                    <div class="h-72">
                        <canvas id="materialsChart"></canvas>
                    </div>
                </div>

                <!-- Revenue Trends -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue Trends</h3>
                    <div class="h-72">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Collector Performance -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Collectors</h3>
                    <div class="space-y-3" id="topCollectors">
                        <div class="text-center py-8 text-gray-500 dark:text-slate-400">Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Detailed Reports -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- User Activity Report -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <div class="p-6 border-b border-gray-100 dark:border-slate-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">User Activity</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Email</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Requests</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Points Earned</th>
                                </tr>
                            </thead>
                            <tbody id="userActivityTable" class="divide-y divide-gray-200 dark:divide-slate-700">
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Material Statistics -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <div class="p-6 border-b border-gray-100 dark:border-slate-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Material Statistics</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Material</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Weight (kg)</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Collections</th>
                                </tr>
                            </thead>
                            <tbody id="materialStatsTable" class="divide-y divide-gray-200 dark:divide-slate-700">
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        let reportData = {};
        let chartInstances = {
            collections: null,
            materials: null,
            points: null
        };
        
        if (!sessionStorage.getItem('user_id') || sessionStorage.getItem('user_role') !== 'admin') {
            window.location.href = '/Scrap/views/auth/login.php';
        }

        // Set default dates (last 30 days)
        const today = new Date();
        const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
        document.getElementById('endDate').valueAsDate = today;
        document.getElementById('startDate').valueAsDate = thirtyDaysAgo;

        async function loadReports() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            console.log('Loading reports:', { startDate, endDate });

            // Show loading indicator
            showLoadingState(true);

            try {
                const response = await fetch(`/Scrap/api/admin/reports.php?start_date=${startDate}&end_date=${endDate}`, {
                    credentials: 'include'
                });
                
                const data = await response.json();
                console.log('Reports data received:', data);
                
                if (data.status === 'success') {
                    reportData = data;
                    updateStats(data.overview);
                    renderCharts(data);
                    renderTopCollectors(data.top_collectors);
                    renderUserActivity(data.user_activity);
                    renderMaterialStats(data.material_stats);
                } else {
                    console.error('API returned error:', data.message);
                    alert('Failed to load reports: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Failed to load reports:', error);
                alert('Error loading reports. Please check console for details.');
            } finally {
                // Hide loading indicator
                showLoadingState(false);
            }
        }

        function showLoadingState(isLoading) {
            const statsCards = document.querySelectorAll('#totalCollections, #totalPoints, #activeUsers, #growthRate');
            statsCards.forEach(card => {
                if (isLoading) {
                    card.style.opacity = '0.5';
                } else {
                    card.style.opacity = '1';
                }
            });
        }

        function updateStats(overview) {
            document.getElementById('totalCollections').textContent = overview.total_collections;
            document.getElementById('totalPoints').textContent = Number(overview.total_points || 0).toLocaleString();
            document.getElementById('activeUsers').textContent = overview.active_users;
            document.getElementById('growthRate').textContent = overview.growth_rate;
        }

        function renderCharts(data) {
            // Destroy existing chart instances before creating new ones
            if (chartInstances.collections) {
                chartInstances.collections.destroy();
            }
            if (chartInstances.materials) {
                chartInstances.materials.destroy();
            }
            if (chartInstances.points) {
                chartInstances.points.destroy();
            }

            // Ensure data structure exists with defaults
            if (!data.timeline) {
                data.timeline = { labels: [], collections: [], points: [] };
            }
            if (!data.materials) {
                data.materials = { labels: [], values: [] };
            }

            // Collections Over Time
            const collectionsCtx = document.getElementById('collectionsChart').getContext('2d');
            chartInstances.collections = new Chart(collectionsCtx, {
                type: 'line',
                data: {
                    labels: data.timeline.labels || [],
                    datasets: [{
                        label: 'Collections',
                        data: data.timeline.collections || [],
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // Materials Distribution
            const materialsCtx = document.getElementById('materialsChart').getContext('2d');
            chartInstances.materials = new Chart(materialsCtx, {
                type: 'doughnut',
                data: {
                    labels: data.materials.labels || [],
                    datasets: [{
                        data: data.materials.values || [],
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
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Points Awarded Trends
            const pointsCtx = document.getElementById('pointsChart').getContext('2d');
            chartInstances.points = new Chart(pointsCtx, {
                type: 'bar',
                data: {
                    labels: data.timeline.labels || [],
                    datasets: [{
                        label: 'Points Awarded',
                        data: data.timeline.points || [],
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function renderTopCollectors(collectors) {
            const container = document.getElementById('topCollectors');
            
            if (!collectors || collectors.length === 0) {
                container.innerHTML = '<div class="text-center py-8 text-gray-500 dark:text-slate-400">No data available</div>';
                return;
            }

            container.innerHTML = collectors.map((collector, index) => `
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white font-semibold text-sm">
                            ${index + 1}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white text-sm">${collector.name}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">${collector.collections} collections</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-green-600 dark:text-green-400">${Number(collector.points_awarded || 0).toLocaleString()} pts</p>
                    </div>
                </div>
            `).join('');
        }

        function renderUserActivity(users) {
            const tbody = document.getElementById('userActivityTable');
            
            if (!users || users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">No data available</td></tr>';
                return;
            }

            tbody.innerHTML = users.map(user => `
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${user.name}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600 dark:text-slate-400">${user.email || 'N/A'}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300">
                            ${user.requests}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <p class="text-sm font-semibold text-yellow-600 dark:text-yellow-400">${Number(user.points_earned || 0).toLocaleString()} pts</p>
                    </td>
                </tr>
            `).join('');
        }

        function renderMaterialStats(materials) {
            const tbody = document.getElementById('materialStatsTable');
            
            if (!materials || materials.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">No data available</td></tr>';
                return;
            }

            tbody.innerHTML = materials.map(material => `
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white capitalize">${material.type}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${Number(material.weight).toLocaleString()}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${material.count}</td>
                </tr>
            `).join('');
        }

        function applyDateFilter() {
            loadReports();
        }

        document.getElementById('quickRange').addEventListener('change', function(e) {
            const value = e.target.value;
            
            if (!value) return; // Empty selection, do nothing
            
            const today = new Date();
            let startDate = new Date();

            switch(value) {
                case 'today':
                    startDate = new Date();
                    break;
                case 'week':
                    startDate = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                    break;
                case 'month':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    break;
                case 'year':
                    startDate = new Date(today.getFullYear(), 0, 1);
                    break;
                default:
                    return;
            }

            console.log('Quick range selected:', value, { startDate, endDate: today });

            document.getElementById('startDate').valueAsDate = startDate;
            document.getElementById('endDate').valueAsDate = today;
            
            // Reset dropdown to default
            e.target.value = '';
            
            // Load reports with new dates
            loadReports();
        });

        // Auto-update when start date changes
        document.getElementById('startDate').addEventListener('change', function() {
            console.log('Start date changed:', this.value);
            loadReports();
        });

        // Auto-update when end date changes
        document.getElementById('endDate').addEventListener('change', function() {
            console.log('End date changed:', this.value);
            loadReports();
        });

        function exportReport(format) {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            window.location.href = `/Scrap/api/admin/reports.php?export=${format}&start_date=${startDate}&end_date=${endDate}`;
        }

        function logout() {
            fetch('/Scrap/api/logout.php', { method: 'POST', credentials: 'include' })
                .then(() => {
                    sessionStorage.clear();
                    window.location.href = '/Scrap/views/auth/login.php?logout=1';
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('ready');
            loadReports();
        });
    </script>
</body>
</html>
