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
    <title>Requests Management - Admin Dashboard</title>
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
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Requests Management</h1>
                <p class="text-gray-600 dark:text-slate-400">Monitor and manage all collection requests</p>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Total</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalRequests">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Pending</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="pendingRequests">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">In Progress</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="inProgressRequests">0</p>
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
                            <p class="text-sm text-gray-600 dark:text-slate-400">Completed</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="completedRequests">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Cancelled</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="cancelledRequests">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex gap-3">
                        <select id="statusFilter" class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Accepted</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <input type="date" id="dateFilter" class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white">
                    </div>
                    <div class="flex gap-3">
                        <input type="search" id="searchRequest" placeholder="Search requests..." 
                               class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white w-64">
                        <button onclick="refreshRequests()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Request ID</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">User</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Material</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Location</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Collector</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requestsTableBody" class="divide-y divide-gray-200 dark:divide-slate-700">
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <div class="w-5 h-5 border-3 border-green-600 border-t-transparent rounded-full animate-spin"></div>
                                        <span class="text-gray-500 dark:text-slate-400">Loading requests...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Request Details Modal -->
    <div id="requestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 p-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Request Details</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="requestModalContent" class="p-6"></div>
        </div>
    </div>

    <script>
        let requests = [];
        
        if (!sessionStorage.getItem('user_id') || sessionStorage.getItem('user_role') !== 'admin') {
            window.location.href = '/Scrap/views/auth/login.php';
        }

        async function loadRequests() {
            try {
                const response = await fetch('/Scrap/api/admin/requests.php', {
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    requests = data.requests;
                    updateStats(data.stats);
                    renderRequests();
                }
            } catch (error) {
                console.error('Failed to load requests:', error);
                document.getElementById('requestsTableBody').innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-red-600 dark:text-red-400">
                            Failed to load requests. Please try again.
                        </td>
                    </tr>
                `;
            }
        }

        function updateStats(stats) {
            document.getElementById('totalRequests').textContent = stats.total;
            document.getElementById('pendingRequests').textContent = stats.pending;
            document.getElementById('inProgressRequests').textContent = stats.in_progress;
            document.getElementById('completedRequests').textContent = stats.completed;
            document.getElementById('cancelledRequests').textContent = stats.cancelled;
        }

        function renderRequests() {
            const tbody = document.getElementById('requestsTableBody');
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            const searchTerm = document.getElementById('searchRequest').value.toLowerCase();

            let filtered = requests.filter(r => {
                const matchesStatus = !statusFilter || r.status === statusFilter;
                const matchesDate = !dateFilter || r.created_at.startsWith(dateFilter);
                const matchesSearch = !searchTerm || 
                    r.id.toString().includes(searchTerm) ||
                    r.user_name.toLowerCase().includes(searchTerm) ||
                    r.material.toLowerCase().includes(searchTerm);
                return matchesStatus && matchesDate && matchesSearch;
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">
                            No requests found matching your filters.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = filtered.map(request => `
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-900 dark:text-white">#${request.id}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${request.user_name}</p>
                        <p class="text-sm text-gray-500 dark:text-slate-400">${request.user_phone}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 dark:text-white capitalize">${request.material}</p>
                        <p class="text-sm text-gray-500 dark:text-slate-400">${request.weight || 'N/A'} kg</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 dark:text-white">${request.location || 'N/A'}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 dark:text-white">${request.collector_name || 'Unassigned'}</p>
                    </td>
                    <td class="px-6 py-4">
                        ${getStatusBadge(request.status)}
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 dark:text-white">${new Date(request.created_at).toLocaleDateString()}</p>
                        <p class="text-sm text-gray-500 dark:text-slate-400">${new Date(request.created_at).toLocaleTimeString()}</p>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="viewRequest(${request.id})" 
                                class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            View
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function getStatusBadge(status) {
            const badges = {
                pending: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400">Pending</span>',
                accepted: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">Accepted</span>',
                in_progress: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">In Progress</span>',
                completed: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400">Completed</span>',
                cancelled: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400">Cancelled</span>'
            };
            return badges[status] || badges.pending;
        }

        async function viewRequest(id) {
            const modal = document.getElementById('requestModal');
            const content = document.getElementById('requestModalContent');
            
            content.innerHTML = '<div class="text-center py-8">Loading...</div>';
            modal.classList.remove('hidden');
            
            try {
                const response = await fetch(`/Scrap/api/admin/requests.php?id=${id}`, {
                    credentials: 'include'
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    const request = data.request;
                    content.innerHTML = `
                        <div class="space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">Request ID</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">#${request.id}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">Status</p>
                                    ${getStatusBadge(request.status)}
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">User</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">${request.user_name}</p>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">${request.user_phone}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">Collector</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">${request.collector_name || 'Unassigned'}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">Material</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">${request.material}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">Weight</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">${request.weight || 'N/A'} kg</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-500 dark:text-slate-400">Location</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">${request.location || 'N/A'}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-500 dark:text-slate-400">Description</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">${request.description || 'N/A'}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">Created</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">${new Date(request.created_at).toLocaleString()}</p>
                                </div>
                                ${request.completed_at ? `
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">Completed</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">${new Date(request.completed_at).toLocaleString()}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                content.innerHTML = '<div class="text-center py-8 text-red-600">Failed to load request details</div>';
            }
        }

        function closeModal() {
            document.getElementById('requestModal').classList.add('hidden');
        }

        function refreshRequests() {
            loadRequests();
        }

        function logout() {
            fetch('/Scrap/api/logout.php', { method: 'POST', credentials: 'include' })
                .then(() => {
                    sessionStorage.clear();
                    window.location.href = '/Scrap/views/auth/login.php';
                });
        }

        document.getElementById('statusFilter').addEventListener('change', renderRequests);
        document.getElementById('dateFilter').addEventListener('change', renderRequests);
        document.getElementById('searchRequest').addEventListener('input', renderRequests);

        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('ready');
            loadRequests();
        });
    </script>
</body>
</html>
