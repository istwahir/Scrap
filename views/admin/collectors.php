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
    <title>Collectors Management - Admin Dashboard</title>
    <meta name="color-scheme" content="light dark" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
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
    </style>
</head>
<body class="bg-gray-50 dark:bg-slate-900">

    <?php include __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="ml-64 min-h-screen">
        <!-- Header -->
        <header class="bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 dark:from-slate-800 dark:via-slate-800 dark:to-slate-800 border-b border-gray-200 dark:border-slate-700">
            <div class="p-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Collectors Management</h1>
                <p class="text-gray-600 dark:text-slate-400">Manage collector applications, approvals, and performance</p>
            </div>
        </header>

        <!-- Content -->
        <div class="p-6 space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Total Collectors</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalCollectors">0</p>
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
                            <p class="text-sm text-gray-600 dark:text-slate-400">Active</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="activeCollectors">0</p>
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
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="pendingCollectors">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Rejected</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="rejectedCollectors">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters & Actions -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex gap-3">
                        <select id="statusFilter" class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="suspended">Suspended</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <select id="vehicleFilter" class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white">
                            <option value="">All Vehicles</option>
                            <option value="motorcycle">Motorcycle</option>
                            <option value="tuk tuk">Tuk Tuk</option>
                            <option value="pickup truck">Pickup Truck</option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <input type="search" id="searchCollector" placeholder="Search collectors..." 
                               class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white w-64">
                        <button onclick="refreshCollectors()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Collectors Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Collector</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Vehicle</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Collections</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Rating</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="collectorsTableBody" class="divide-y divide-gray-200 dark:divide-slate-700">
                            <!-- Loading state -->
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <div class="w-5 h-5 border-3 border-green-600 border-t-transparent rounded-full animate-spin"></div>
                                        <span class="text-gray-500 dark:text-slate-400">Loading collectors...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Collector Details Modal -->
    <div id="collectorModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 p-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Collector Details</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="collectorModalContent" class="p-6">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>

    <script>
        let collectors = [];
        
        // Check authentication
        const userRole = sessionStorage.getItem('user_role');
        if (!sessionStorage.getItem('user_id') || userRole !== 'admin') {
            window.location.href = '/Scrap/views/auth/login.php';
        }

        // Load collectors data
        async function loadCollectors() {
            try {
                const response = await fetch('/Scrap/api/admin/collectors.php', {
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    collectors = data.collectors;
                    updateStats(data.stats);
                    renderCollectors();
                }
            } catch (error) {
                console.error('Failed to load collectors:', error);
                document.getElementById('collectorsTableBody').innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-red-600 dark:text-red-400">
                            Failed to load collectors. Please try again.
                        </td>
                    </tr>
                `;
            }
        }

        // Update statistics
        function updateStats(stats) {
            document.getElementById('totalCollectors').textContent = stats.total;
            document.getElementById('activeCollectors').textContent = stats.active;
            document.getElementById('pendingCollectors').textContent = stats.pending;
            document.getElementById('rejectedCollectors').textContent = stats.rejected;
        }

        // Render collectors table
        function renderCollectors() {
            const tbody = document.getElementById('collectorsTableBody');
            const statusFilter = document.getElementById('statusFilter').value;
            const vehicleFilter = document.getElementById('vehicleFilter').value;
            const searchTerm = document.getElementById('searchCollector').value.toLowerCase();

            let filtered = collectors.filter(c => {
                const matchesStatus = !statusFilter || c.status === statusFilter;
                const matchesVehicle = !vehicleFilter || c.vehicle_type === vehicleFilter;
                const matchesSearch = !searchTerm || 
                    c.name.toLowerCase().includes(searchTerm) ||
                    c.email.toLowerCase().includes(searchTerm) ||
                    c.phone.includes(searchTerm);
                return matchesStatus && matchesVehicle && matchesSearch;
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">
                            No collectors found matching your filters.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = filtered.map(collector => `
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white font-semibold">
                                ${collector.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">${collector.name}</p>
                                <p class="text-sm text-gray-500 dark:text-slate-400">ID: ${collector.id}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 dark:text-white">${collector.phone}</p>
                        <p class="text-sm text-gray-500 dark:text-slate-400">${collector.email}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 dark:text-white capitalize">${collector.vehicle_type || 'N/A'}</p>
                        <p class="text-sm text-gray-500 dark:text-slate-400">${collector.vehicle_reg || 'N/A'}</p>
                    </td>
                    <td class="px-6 py-4">
                        ${getStatusBadge(collector.status)}
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">${collector.total_collections || 0}</p>
                        <p class="text-sm text-gray-500 dark:text-slate-400">collections</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">${collector.rating || '5.0'}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <button onclick="viewCollector(${collector.id})" 
                                    class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                View
                            </button>
                            ${collector.status === 'pending' ? `
                                <button onclick="approveCollector(${collector.id})" 
                                        class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    Approve
                                </button>
                                <button onclick="rejectCollector(${collector.id})" 
                                        class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors">
                                    Reject
                                </button>
                            ` : collector.status === 'active' ? `
                                <button onclick="suspendCollector(${collector.id})" 
                                        class="px-3 py-1.5 bg-yellow-600 text-white text-xs font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                                    Suspend
                                </button>
                            ` : collector.status === 'suspended' ? `
                                <button onclick="activateCollector(${collector.id})" 
                                        class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    Activate
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Get status badge HTML
        function getStatusBadge(status) {
            const badges = {
                active: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400">Active</span>',
                pending: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400">Pending</span>',
                suspended: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400">Suspended</span>',
                rejected: '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-900/20 text-gray-600 dark:text-gray-400">Rejected</span>'
            };
            return badges[status] || badges.pending;
        }

        // View collector details
        async function viewCollector(id) {
            const modal = document.getElementById('collectorModal');
            const content = document.getElementById('collectorModalContent');
            
            content.innerHTML = '<div class="text-center py-8">Loading...</div>';
            modal.classList.remove('hidden');
            
            try {
                const response = await fetch(`/Scrap/api/admin/collectors.php?id=${id}`, {
                    credentials: 'include'
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    const collector = data.collector;
                    content.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Personal Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Full Name</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">${collector.name}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Email</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">${collector.email}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Phone</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">${collector.phone}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">ID Number</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">${collector.id_number || 'N/A'}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Address</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">${collector.address || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Vehicle Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Vehicle Type</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">${collector.vehicle_type || 'N/A'}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Registration</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">${collector.vehicle_reg || 'N/A'}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Status</p>
                                        ${getStatusBadge(collector.status)}
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Member Since</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">${new Date(collector.created_at).toLocaleDateString()}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2 space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Performance</h3>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                        <p class="text-sm text-blue-600 dark:text-blue-400">Total Collections</p>
                                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">${collector.total_collections || 0}</p>
                                    </div>
                                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                        <p class="text-sm text-green-600 dark:text-green-400">Completed</p>
                                        <p class="text-2xl font-bold text-green-700 dark:text-green-300">${collector.completed_collections || 0}</p>
                                    </div>
                                    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                                        <p class="text-sm text-yellow-600 dark:text-yellow-400">Rating</p>
                                        <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">${collector.rating || '5.0'} ⭐</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                content.innerHTML = '<div class="text-center py-8 text-red-600">Failed to load collector details</div>';
            }
        }

        // Close modal
        function closeModal() {
            document.getElementById('collectorModal').classList.add('hidden');
        }

        // Action functions
        async function approveCollector(id) {
            if (!confirm('Are you sure you want to approve this collector?')) return;
            await updateCollectorStatus(id, 'active');
        }

        async function rejectCollector(id) {
            if (!confirm('Are you sure you want to reject this collector?')) return;
            await updateCollectorStatus(id, 'rejected');
        }

        async function suspendCollector(id) {
            if (!confirm('Are you sure you want to suspend this collector?')) return;
            await updateCollectorStatus(id, 'suspended');
        }

        async function activateCollector(id) {
            await updateCollectorStatus(id, 'active');
        }

        async function updateCollectorStatus(id, status) {
            try {
                const response = await fetch('/Scrap/api/admin/collectors.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, status })
                });
                
                const data = await response.json();
                if (data.status === 'success') {
                    loadCollectors();
                } else {
                    alert('Failed to update collector status: ' + data.message);
                }
            } catch (error) {
                alert('Error updating collector status');
            }
        }

        // Refresh collectors
        function refreshCollectors() {
            loadCollectors();
        }

        // Logout
        function logout() {
            fetch('/Scrap/api/logout.php', { 
                method: 'POST',
                credentials: 'include' 
            })
                .then(() => {
                    sessionStorage.clear();
                    window.location.href = '/Scrap/views/auth/login.php';
                });
        }

        // Event listeners
        document.getElementById('statusFilter').addEventListener('change', renderCollectors);
        document.getElementById('vehicleFilter').addEventListener('change', renderCollectors);
        document.getElementById('searchCollector').addEventListener('input', renderCollectors);

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('ready');
            loadCollectors();
        });
    </script>
</body>
</html>
