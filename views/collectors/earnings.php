<?php
session_start();
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
    <title>Earnings - Kiambu Recycling & Scraps</title>
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
        <?php include __DIR__ . '/../../includes/collector_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <div class="p-4 md:p-6 space-y-6">
                <!-- Header -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-800 dark:to-slate-700 rounded-lg shadow-sm p-5 border border-green-100 dark:border-slate-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Earnings Overview</h1>
                            <p class="text-sm text-gray-600 dark:text-slate-300">Track your earnings and financial performance</p>
                        </div>
                        <div>
                            <select id="periodFilter" class="text-sm rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 px-3 py-2">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Summary Stats -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Earnings</p>
                                <p id="totalEarnings" class="text-2xl font-bold text-gray-900 dark:text-white">KES 0</p>
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
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Average Per Collection</p>
                                <p id="avgEarning" class="text-2xl font-bold text-gray-900 dark:text-white">KES 0</p>
                            </div>
                            <div class="w-11 h-11 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Weight</p>
                                <p id="totalWeight" class="text-2xl font-bold text-gray-900 dark:text-white">0 kg</p>
                            </div>
                            <div class="w-11 h-11 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Earnings Trend -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Earnings Trend</h2>
                        <div class="relative h-72">
                            <canvas id="earningsTrendChart"></canvas>
                        </div>
                    </div>

                    <!-- Materials Distribution -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Earnings by Material Type</h2>
                        <div class="relative h-72">
                            <canvas id="materialsBreakdownChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Earnings History Table -->
                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Earnings History</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Material</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Weight</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="earningsTableBody" class="divide-y divide-gray-200 dark:divide-slate-700">
                                <!-- Will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let earningsChart, materialsChart;

        // Load earnings data
        async function loadEarningsData(period = 'monthly') {
            try {
                const response = await fetch(`/Scrap/api/collectors/earnings.php?period=${period}`, { credentials: 'include' });
                if (response.status === 401) {
                    window.location.href = '/Scrap/views/auth/login.php';
                    return;
                }
                if (response.status === 403) {
                    window.location.href = '/Scrap/views/collectors/register.php';
                    return;
                }
                const data = await response.json();
                if (data.status === 'success') {
                    updateStats(data.summary);
                    updateCharts(data.trend, data.materialBreakdown);
                    updateTable(data.history);
                }
            } catch (error) {
                console.error('Failed to load earnings data:', error);
            }
        }

        function updateStats(summary) {
            document.getElementById('totalEarnings').textContent = `KES ${summary.total_earnings || 0}`;
            document.getElementById('totalCollections').textContent = summary.total_collections || 0;
            document.getElementById('avgEarning').textContent = `KES ${summary.avg_earning || 0}`;
            document.getElementById('totalWeight').textContent = `${summary.total_weight || 0} kg`;
        }

        function updateCharts(trend, materialBreakdown) {
            // Earnings Trend Chart
            const trendCtx = document.getElementById('earningsTrendChart').getContext('2d');
            if (earningsChart) earningsChart.destroy();
            earningsChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trend.labels,
                    datasets: [{
                        label: 'Earnings',
                        data: trend.values,
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
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'KES ' + value;
                                }
                            }
                        }
                    }
                }
            });

            // Materials Breakdown Chart
            const matCtx = document.getElementById('materialsBreakdownChart').getContext('2d');
            if (materialsChart) materialsChart.destroy();
            materialsChart = new Chart(matCtx, {
                type: 'doughnut',
                data: {
                    labels: materialBreakdown.labels,
                    datasets: [{
                        data: materialBreakdown.values,
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

        function updateTable(history) {
            const tbody = document.getElementById('earningsTableBody');
            tbody.innerHTML = '';
            
            if (!history || history.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-slate-400">No earnings history</td></tr>';
                return;
            }

            history.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-100">${item.date}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-100">${item.customer_name}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-100">${item.material_type}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-100">${item.weight} kg</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">KES ${item.amount}</td>
                `;
                tbody.appendChild(row);
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('periodFilter').addEventListener('change', (e) => {
                loadEarningsData(e.target.value);
            });
            loadEarningsData('monthly');
        });
    </script>
</body>
</html>
