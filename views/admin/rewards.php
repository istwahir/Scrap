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
    <title>Rewards Management - Admin Dashboard</title>
    <meta name="color-scheme" content="light dark" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    
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
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Points & Rewards Management</h1>
                <p class="text-gray-600 dark:text-slate-400">Manage user points, transactions, and award bonus points</p>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Total Points Issued</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalPointsIssued">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Available Points</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="availablePoints">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Redeemed Points</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="redeemedPoints">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Active Users</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalUsers">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Bar -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex gap-3">
                        <select id="activityTypeFilter" class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white">
                            <option value="">All Types</option>
                            <option value="collection">Collection</option>
                            <option value="referral">Referral</option>
                            <option value="bonus">Bonus</option>
                        </select>
                        <input type="search" id="searchUser" placeholder="Search by user..." 
                               class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white w-64">
                    </div>
                    <div class="flex gap-3">
                        <button onclick="openAwardModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Award Bonus Points
                        </button>
                        <button onclick="refreshRewards()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- User Points Summary -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
                <div class="p-6 border-b border-gray-100 dark:border-slate-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">User Points Summary</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">User</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Email</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Available</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Redeemed</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Total</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userPointsTableBody" class="divide-y divide-gray-200 dark:divide-slate-700">
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">
                                    Loading user points...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
                <div class="p-6 border-b border-gray-100 dark:border-slate-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Recent Transactions</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">User</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Type</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Points</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Date</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Status</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsTableBody" class="divide-y divide-gray-200 dark:divide-slate-700">
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">
                                    Loading transactions...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Award Bonus Points Modal -->
    <div id="rewardModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full">
            <div class="bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 p-6 flex justify-between items-center rounded-t-xl">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Award Bonus Points</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="rewardForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Select User *</label>
                    <select id="awardUserId" name="user_id" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                        <option value="">-- Select a user --</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Points to Award *</label>
                    <input type="number" id="awardPoints" name="points" required min="1" max="10000" value="100"
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500 dark:text-slate-400">Enter the number of bonus points to award (1-10,000)</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                        Award Points
                    </button>
                    <button type="button" onclick="closeModal()" class="px-6 py-3 bg-gray-300 dark:bg-slate-700 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-400 dark:hover:bg-slate-600 transition-colors font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let transactions = [];
        let userPoints = [];
        let allUsers = [];
        
        if (!sessionStorage.getItem('user_id') || sessionStorage.getItem('user_role') !== 'admin') {
            window.location.href = '/Scrap/views/auth/login.php';
        }

        async function loadRewards() {
            try {
                const response = await fetch('/Scrap/api/admin/rewards.php', {
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    transactions = data.rewards;
                    userPoints = data.userPoints || [];
                    updateStats(data.stats);
                    renderUserPoints();
                    renderTransactions();
                }
            } catch (error) {
                console.error('Failed to load rewards:', error);
                showError('Failed to load points data. Please try again.');
            }
        }

        function updateStats(stats) {
            document.getElementById('totalPointsIssued').textContent = Number(stats.total_points_issued || 0).toLocaleString();
            document.getElementById('availablePoints').textContent = Number(stats.total_available_points || 0).toLocaleString();
            document.getElementById('redeemedPoints').textContent = Number(stats.total_redeemed_points || 0).toLocaleString();
            document.getElementById('totalUsers').textContent = stats.total_users || 0;
        }

        function renderUserPoints() {
            const tbody = document.getElementById('userPointsTableBody');
            
            if (userPoints.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">
                            No users with points yet.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = userPoints.map(user => `
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${user.user_name || 'Unknown'}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600 dark:text-slate-400">${user.user_email || 'N/A'}</p>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <p class="text-sm font-semibold text-green-600 dark:text-green-400">${Number(user.available_points || 0).toLocaleString()}</p>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <p class="text-sm font-semibold text-gray-600 dark:text-slate-400">${Number(user.redeemed_points || 0).toLocaleString()}</p>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <p class="text-sm font-bold text-yellow-600 dark:text-yellow-400">${Number(user.total_points || 0).toLocaleString()}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="awardPointsToUser(${user.user_id}, '${user.user_name}')" 
                                class="px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition-colors">
                            Award Points
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function renderTransactions() {
            const tbody = document.getElementById('transactionsTableBody');
            const activityFilter = document.getElementById('activityTypeFilter').value;
            const searchTerm = document.getElementById('searchUser').value.toLowerCase();

            let filtered = transactions.filter(t => {
                const matchesType = !activityFilter || t.activity_type === activityFilter;
                const matchesSearch = !searchTerm || 
                    (t.user_name && t.user_name.toLowerCase().includes(searchTerm)) ||
                    (t.user_email && t.user_email.toLowerCase().includes(searchTerm));
                return matchesType && matchesSearch;
            });
            
            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">
                            No transactions found matching your filters.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = filtered.map(transaction => `
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${transaction.user_name || 'Unknown'}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">${transaction.user_email || ''}</p>
                    </td>
                    <td class="px-6 py-4">
                        ${getActivityTypeBadge(transaction.activity_type)}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <p class="text-sm font-semibold text-yellow-600 dark:text-yellow-400">${Number(transaction.points || 0).toLocaleString()}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 dark:text-white">${new Date(transaction.created_at).toLocaleDateString()}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">${new Date(transaction.created_at).toLocaleTimeString()}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        ${transaction.redeemed == 1 
                            ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-900/20 text-gray-600 dark:text-gray-400">Redeemed</span>'
                            : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400">Available</span>'
                        }
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="deleteTransaction(${transaction.id})" 
                                class="px-3 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors">
                            Delete
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function getActivityTypeBadge(type) {
            const badges = {
                collection: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">Collection</span>',
                referral: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">Referral</span>',
                bonus: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400">Bonus</span>'
            };
            return badges[type] || '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Unknown</span>';
        }

        function openAwardModal() {
            // Populate user dropdown
            const select = document.getElementById('awardUserId');
            select.innerHTML = '<option value="">-- Select a user --</option>' + 
                userPoints.map(user => 
                    `<option value="${user.user_id}">${user.user_name} (${user.available_points} pts available)</option>`
                ).join('');
            
            document.getElementById('rewardModal').classList.remove('hidden');
        }

        function awardPointsToUser(userId, userName) {
            const select = document.getElementById('awardUserId');
            select.innerHTML = '<option value="">-- Select a user --</option>' + 
                userPoints.map(user => 
                    `<option value="${user.user_id}" ${user.user_id == userId ? 'selected' : ''}>${user.user_name} (${user.available_points} pts available)</option>`
                ).join('');
            
            document.getElementById('rewardModal').classList.remove('hidden');
        }

        async function deleteTransaction(id) {
            if (!confirm('Are you sure you want to delete this transaction? This cannot be undone.')) return;

            try {
                const response = await fetch('/Scrap/api/admin/rewards.php', {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const data = await response.json();
                if (data.status === 'success') {
                    showSuccess('Transaction deleted successfully');
                    loadRewards();
                } else {
                    alert('Failed to delete transaction: ' + data.message);
                }
            } catch (error) {
                alert('Error deleting transaction');
            }
        }

        function closeModal() {
            document.getElementById('rewardModal').classList.add('hidden');
        }

        document.getElementById('rewardForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                user_id: document.getElementById('awardUserId').value,
                points: document.getElementById('awardPoints').value
            };

            if (!formData.user_id) {
                alert('Please select a user');
                return;
            }

            try {
                const response = await fetch('/Scrap/api/admin/rewards.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                if (data.status === 'success') {
                    showSuccess('Bonus points awarded successfully!');
                    closeModal();
                    loadRewards();
                } else {
                    alert('Failed to award points: ' + data.message);
                }
            } catch (error) {
                alert('Error awarding points');
            }
        });

        function refreshRewards() {
            loadRewards();
        }

        function showSuccess(message) {
            // Simple alert for now, can be replaced with toast notification
            alert(message);
        }

        function showError(message) {
            alert(message);
        }

        function logout() {
            fetch('/Scrap/api/logout.php', { method: 'POST', credentials: 'include' })
                .then(() => {
                    sessionStorage.clear();
                    window.location.href = '/Scrap/views/auth/login.php?logout=1';
                });
        }

        document.getElementById('activityTypeFilter').addEventListener('change', renderTransactions);
        document.getElementById('searchUser').addEventListener('input', renderTransactions);

        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('ready');
            loadRewards();
        });
    </script>
</body>
</html>
