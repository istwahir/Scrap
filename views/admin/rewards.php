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
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Rewards Management</h1>
                <p class="text-gray-600 dark:text-slate-400">Create and manage rewards for recycling activities</p>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Total Rewards</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalRewards">0</p>
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
                            <p class="text-sm text-gray-600 dark:text-slate-400">Active Rewards</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="activeRewards">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Redemptions</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalRedemptions">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Total Points Used</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalPointsUsed">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Bar -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex gap-3">
                        <select id="statusFilter" class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <input type="search" id="searchReward" placeholder="Search rewards..." 
                               class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white w-64">
                    </div>
                    <div class="flex gap-3">
                        <button onclick="openAddModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Reward
                        </button>
                        <button onclick="refreshRewards()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rewards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="rewardsGrid">
                <div class="col-span-full flex items-center justify-center py-12">
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 border-3 border-green-600 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-gray-500 dark:text-slate-400">Loading rewards...</span>
                    </div>
                </div>
            </div>

            <!-- Recent Redemptions -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
                <div class="p-6 border-b border-gray-100 dark:border-slate-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Recent Redemptions</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">User</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Reward</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Points</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody id="redemptionsTableBody" class="divide-y divide-gray-200 dark:divide-slate-700">
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">
                                    Loading redemptions...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Add/Edit Reward Modal -->
    <div id="rewardModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 p-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white" id="modalTitle">Add Reward</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="rewardForm" class="p-6 space-y-4">
                <input type="hidden" id="rewardId" name="id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Reward Title *</label>
                    <input type="text" id="rewardTitle" name="title" required
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Description *</label>
                    <textarea id="rewardDescription" name="description" rows="3" required
                              class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Points Required *</label>
                        <input type="number" id="rewardPoints" name="points" required min="1"
                               class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Stock Quantity</label>
                        <input type="number" id="rewardStock" name="stock" min="0"
                               class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Image URL</label>
                    <input type="url" id="rewardImage" name="image"
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Status</label>
                    <select id="rewardStatus" name="status"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                        Save Reward
                    </button>
                    <button type="button" onclick="closeModal()" class="px-6 py-3 bg-gray-300 dark:bg-slate-700 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-400 dark:hover:bg-slate-600 transition-colors font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let rewards = [];
        let redemptions = [];
        
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
                    rewards = data.rewards;
                    redemptions = data.redemptions || [];
                    updateStats(data.stats);
                    renderRewards();
                    renderRedemptions();
                }
            } catch (error) {
                console.error('Failed to load rewards:', error);
                document.getElementById('rewardsGrid').innerHTML = `
                    <div class="col-span-full text-center text-red-600 dark:text-red-400 py-8">
                        Failed to load rewards. Please try again.
                    </div>
                `;
            }
        }

        function updateStats(stats) {
            document.getElementById('totalRewards').textContent = stats.total;
            document.getElementById('activeRewards').textContent = stats.active;
            document.getElementById('totalRedemptions').textContent = stats.total_redemptions;
            document.getElementById('totalPointsUsed').textContent = stats.total_points_used;
        }

        function renderRewards() {
            const grid = document.getElementById('rewardsGrid');
            const statusFilter = document.getElementById('statusFilter').value;
            const searchTerm = document.getElementById('searchReward').value.toLowerCase();

            let filtered = rewards.filter(r => {
                const matchesStatus = !statusFilter || r.status === statusFilter;
                const matchesSearch = !searchTerm || 
                    r.title.toLowerCase().includes(searchTerm) ||
                    r.description.toLowerCase().includes(searchTerm);
                return matchesStatus && matchesSearch;
            });

            if (filtered.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center text-gray-500 dark:text-slate-400 py-8">
                        No rewards found matching your filters.
                    </div>
                `;
                return;
            }

            grid.innerHTML = filtered.map(reward => `
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden hover:shadow-md transition-shadow">
                    ${reward.image ? `
                    <div class="h-48 bg-gray-100 dark:bg-slate-700 overflow-hidden">
                        <img src="${reward.image}" alt="${reward.title}" class="w-full h-full object-cover">
                    </div>
                    ` : `
                    <div class="h-48 bg-gradient-to-br from-yellow-100 to-yellow-200 dark:from-yellow-900/20 dark:to-yellow-800/20 flex items-center justify-center">
                        <svg class="w-16 h-16 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    `}
                    <div class="p-6 space-y-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">${reward.title}</h3>
                                <p class="text-sm text-gray-500 dark:text-slate-400 line-clamp-2">${reward.description}</p>
                            </div>
                            ${getStatusBadge(reward.status)}
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-slate-700">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400">Points Required</p>
                                <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400">${reward.points}</p>
                            </div>
                            ${reward.stock !== null ? `
                            <div class="text-right">
                                <p class="text-xs text-gray-500 dark:text-slate-400">Stock</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">${reward.stock}</p>
                            </div>
                            ` : ''}
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button onclick="editReward(${reward.id})" 
                                    class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                Edit
                            </button>
                            <button onclick="deleteReward(${reward.id})" 
                                    class="flex-1 px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderRedemptions() {
            const tbody = document.getElementById('redemptionsTableBody');
            
            if (redemptions.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">
                            No redemptions yet.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = redemptions.map(redemption => `
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${redemption.user_name}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 dark:text-white">${redemption.reward_title}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-yellow-600 dark:text-yellow-400">${redemption.points}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 dark:text-white">${new Date(redemption.redeemed_at).toLocaleDateString()}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400">Redeemed</span>
                    </td>
                </tr>
            `).join('');
        }

        function getStatusBadge(status) {
            return status === 'active' 
                ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400">Active</span>'
                : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-900/20 text-gray-600 dark:text-gray-400">Inactive</span>';
        }

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Reward';
            document.getElementById('rewardForm').reset();
            document.getElementById('rewardId').value = '';
            document.getElementById('rewardModal').classList.remove('hidden');
        }

        function editReward(id) {
            const reward = rewards.find(r => r.id === id);
            if (!reward) return;

            document.getElementById('modalTitle').textContent = 'Edit Reward';
            document.getElementById('rewardId').value = reward.id;
            document.getElementById('rewardTitle').value = reward.title;
            document.getElementById('rewardDescription').value = reward.description;
            document.getElementById('rewardPoints').value = reward.points;
            document.getElementById('rewardStock').value = reward.stock || '';
            document.getElementById('rewardImage').value = reward.image || '';
            document.getElementById('rewardStatus').value = reward.status;
            
            document.getElementById('rewardModal').classList.remove('hidden');
        }

        async function deleteReward(id) {
            if (!confirm('Are you sure you want to delete this reward?')) return;

            try {
                const response = await fetch('/Scrap/api/admin/rewards.php', {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const data = await response.json();
                if (data.status === 'success') {
                    loadRewards();
                } else {
                    alert('Failed to delete reward: ' + data.message);
                }
            } catch (error) {
                alert('Error deleting reward');
            }
        }

        function closeModal() {
            document.getElementById('rewardModal').classList.add('hidden');
        }

        document.getElementById('rewardForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                id: document.getElementById('rewardId').value,
                title: document.getElementById('rewardTitle').value,
                description: document.getElementById('rewardDescription').value,
                points: document.getElementById('rewardPoints').value,
                stock: document.getElementById('rewardStock').value,
                image: document.getElementById('rewardImage').value,
                status: document.getElementById('rewardStatus').value
            };

            try {
                const response = await fetch('/Scrap/api/admin/rewards.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                if (data.status === 'success') {
                    closeModal();
                    loadRewards();
                } else {
                    alert('Failed to save reward: ' + data.message);
                }
            } catch (error) {
                alert('Error saving reward');
            }
        });

        function refreshRewards() {
            loadRewards();
        }

        function logout() {
            fetch('/Scrap/api/logout.php', { method: 'POST', credentials: 'include' })
                .then(() => {
                    sessionStorage.clear();
                    window.location.href = '/Scrap/views/auth/login.php';
                });
        }

        document.getElementById('statusFilter').addEventListener('change', renderRewards);
        document.getElementById('searchReward').addEventListener('input', renderRewards);

        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('ready');
            loadRewards();
        });
    </script>
</body>
</html>
