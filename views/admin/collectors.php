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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
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
            <!-- Applications Section -->
            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 rounded-xl p-6 border border-amber-200 dark:border-amber-800">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pending Applications</h2>
                        <p class="text-sm text-gray-600 dark:text-slate-400">Review and approve new collector applications</p>
                    </div>
                    <span id="pendingApplicationsCount" class="px-3 py-1 bg-amber-600 text-white rounded-full text-sm font-semibold">0</span>
                </div>
                <div id="applicationsContainer" class="space-y-4">
                    <!-- Loading state -->
                    <div class="text-center py-4 text-gray-500">Loading applications...</div>
                </div>
            </div>

            <!-- Rejected Applications Section -->
            <div class="bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-xl p-6 border border-red-200 dark:border-red-800">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Rejected Applications</h2>
                        <p class="text-sm text-gray-600 dark:text-slate-400">Previously rejected collector applications (users can reapply)</p>
                    </div>
                    <span id="rejectedApplicationsCount" class="px-3 py-1 bg-red-600 text-white rounded-full text-sm font-semibold">0</span>
                </div>
                <div id="rejectedApplicationsContainer" class="space-y-4">
                    <!-- Loading state -->
                    <div class="text-center py-4 text-gray-500">Loading...</div>
                </div>
            </div>

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
                    <div class="flex gap-3 flex-wrap items-center">
                        <select id="statusFilter" class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="suspended">Suspended</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <select id="vehicleFilter" class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white">
                            <option value="">All Vehicles</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                        <!-- Active Filters Badge -->
                        <div id="activeFiltersCount" class="hidden px-3 py-1.5 bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-lg text-xs font-medium border border-blue-200 dark:border-blue-800">
                            <i class="fas fa-filter mr-1"></i>
                            <span id="filterCount">0</span> filters active
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <input type="search" id="searchCollector" placeholder="Search collectors..." 
                               class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white w-64">
                        <button onclick="clearFilters()" class="px-4 py-2 bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors text-sm font-medium">
                            <i class="fas fa-times mr-1"></i>Clear
                        </button>
                        <button onclick="refreshCollectors()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                            <i class="fas fa-sync-alt mr-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Collectors Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <!-- Results Summary -->
                <div class="px-6 py-3 bg-gray-50 dark:bg-slate-700/50 border-b border-gray-200 dark:border-slate-600">
                    <p class="text-sm text-gray-600 dark:text-slate-400">
                        Showing <span id="resultsCount" class="font-semibold text-gray-900 dark:text-white">0</span> 
                        of <span id="totalCount" class="font-semibold text-gray-900 dark:text-white">0</span> collectors
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Collector</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Vehicle</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Active</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Collections</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Rating</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="collectorsTableBody" class="divide-y divide-gray-200 dark:divide-slate-700">
                            <!-- Loading state -->
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
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

    <!-- Application Details Modal -->
    <div id="applicationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 p-6 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Application Details</h3>
                <button onclick="closeApplicationModal()" class="text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="applicationDetailsContent" class="p-6 space-y-6">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

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

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full" id="confirmationIcon">
                    <!-- Icon will be injected -->
                </div>
                <h3 class="text-xl font-bold text-center text-gray-900 dark:text-white mb-2" id="confirmationTitle">Confirm Action</h3>
                <p class="text-center text-gray-600 dark:text-slate-400 mb-6" id="confirmationMessage">Are you sure you want to proceed?</p>
                
                <!-- Rejection reason (shown only for reject action) -->
                <div id="rejectionReasonContainer" class="hidden mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Reason for rejection (optional)</label>
                    <textarea id="rejectionReason" 
                              rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-slate-700 dark:text-white"
                              placeholder="Provide a reason..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button onclick="closeConfirmationModal()" 
                            class="flex-1 px-4 py-2.5 bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-white font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors">
                        Cancel
                    </button>
                    <button id="confirmationButton" 
                            onclick="confirmAction()" 
                            class="flex-1 px-4 py-2.5 font-medium rounded-lg transition-colors text-white">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspend Collector Modal -->
    <div id="suspendModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/20">
                    <i class="fas fa-ban text-3xl text-red-600 dark:text-red-400"></i>
                </div>
                <h3 class="text-xl font-bold text-center text-gray-900 dark:text-white mb-2">Suspend Collector</h3>
                <p class="text-center text-gray-600 dark:text-slate-400 mb-6">Are you sure you want to suspend this collector? This action will temporarily disable their account.</p>
                
                <div class="flex gap-3">
                    <button onclick="closeSuspendModal()" 
                            class="flex-1 px-4 py-2.5 bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-white font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors">
                        Cancel
                    </button>
                    <button id="confirmSuspendButton" 
                            onclick="confirmSuspend()" 
                            class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        Suspend
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Toast -->
    <div id="toast" class="hidden fixed top-4 right-4 z-50 max-w-sm w-full">
        <div class="rounded-lg shadow-lg p-4 flex items-start gap-3" id="toastContent">
            <div id="toastIcon" class="flex-shrink-0 w-6 h-6"></div>
            <div class="flex-1">
                <p id="toastMessage" class="text-sm font-medium"></p>
            </div>
            <button onclick="closeToast()" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
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
                
                console.log('API Response:', data); // Debug log
                
                if (data.status === 'success') {
                    collectors = data.collectors;
                    updateStats(data.stats);
                    console.log('Applications:', data.applications); // Debug log
                    renderApplications(data.applications || [], data.applicationStats || {});
                    renderRejectedApplications(data.applications || []);
                    populateVehicleFilter(); // Populate vehicle filter from data
                    renderCollectors();
                } else {
                    console.error('API error:', data.message);
                }
            } catch (error) {
                console.error('Failed to load collectors:', error);
                document.getElementById('collectorsTableBody').innerHTML = `
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-red-600 dark:text-red-400">
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

        // Populate vehicle filter with unique vehicle types from collectors
        function populateVehicleFilter() {
            const vehicleFilter = document.getElementById('vehicleFilter');
            const currentValue = vehicleFilter.value; // Preserve current selection
            
            // Get unique vehicle types from collectors
            const vehicleTypes = new Set();
            collectors.forEach(collector => {
                const type = collector.vehicle_type;
                if (type && type !== 'N/A') {
                    vehicleTypes.add(type.toLowerCase());
                }
            });
            
            // Sort vehicle types alphabetically
            const sortedTypes = Array.from(vehicleTypes).sort();
            
            // Clear current options except "All Vehicles"
            vehicleFilter.innerHTML = '<option value="">All Vehicles</option>';
            
            // Add vehicle type options
            sortedTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = capitalizeWords(type);
                vehicleFilter.appendChild(option);
            });
            
            // Add N/A option if there are collectors without vehicles
            const hasNA = collectors.some(c => !c.vehicle_type || c.vehicle_type === 'N/A');
            if (hasNA) {
                const naOption = document.createElement('option');
                naOption.value = 'n/a';
                naOption.textContent = 'No Vehicle';
                vehicleFilter.appendChild(naOption);
            }
            
            // Restore previous selection
            vehicleFilter.value = currentValue;
        }

        // Get emoji icon for vehicle type
        function getVehicleEmoji(vehicleType) {
            const type = vehicleType?.toLowerCase();
            const emojis = {
                'motorcycle': '🏍️',
                'tuk tuk': '🚕',
                'pickup truck': '🚚',
                'truck': '🚛',
                'van': '🚐',
                'car': '🚗',
                'bicycle': '🚲'
            };
            return emojis[type] || '🚗';
        }

        // Capitalize words helper function
        function capitalizeWords(str) {
            return str.split(' ').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
        }

        // Render applications
        function renderApplications(applications, stats) {
            console.log('renderApplications called with:', applications, stats); // Debug log
            const container = document.getElementById('applicationsContainer');
            const countBadge = document.getElementById('pendingApplicationsCount');
            
            const pendingApps = applications.filter(app => app.status === 'pending');
            console.log('Pending apps:', pendingApps); // Debug log
            countBadge.textContent = pendingApps.length;
            
            if (pendingApps.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500 dark:text-slate-400">
                        <i class="fas fa-check-circle text-4xl mb-2"></i>
                        <p>No pending applications</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = pendingApps.map(app => `
                <div class="bg-white dark:bg-slate-800 rounded-lg p-5 border border-gray-200 dark:border-slate-700 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Personal Info -->
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-500 to-yellow-600 flex items-center justify-center text-white font-bold text-lg">
                                        ${app.full_name.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">${app.full_name}</p>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">${app.phone_number}</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    <i class="fas fa-calendar mr-1"></i>${new Date(app.created_at).toLocaleDateString()}
                                </p>
                            </div>
                            
                            <!-- Vehicle Info -->
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Vehicle</p>
                                <p class="font-medium text-gray-900 dark:text-white capitalize flex items-center gap-2">
                                    ${getVehicleIcon(app.vehicle_type)}
                                    <span>${app.vehicle_type}</span>
                                </p>
                                <p class="text-sm text-gray-600 dark:text-slate-300 font-mono">${app.vehicle_reg}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                    <i class="fas fa-map-marker-alt mr-1"></i>${app.residential_area || 'N/A'}
                                </p>
                            </div>
                            
                            <!-- Areas & Materials -->
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Coverage</p>
                                <p class="text-sm text-gray-900 dark:text-white mb-1">
                                    <i class="fas fa-map mr-1 text-blue-600"></i>${app.service_areas || 'N/A'}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <i class="fas fa-recycle mr-1 text-green-600"></i>${app.materials_collected || 'N/A'}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex flex-col gap-2">
                            <button onclick="viewApplication(${app.id})" 
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap">
                                <i class="fas fa-eye mr-1"></i>View Details
                            </button>
                            <button onclick="approveApplication(${app.id})" 
                                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap">
                                <i class="fas fa-check mr-1"></i>Approve
                            </button>
                            <button onclick="rejectApplication(${app.id})" 
                                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors whitespace-nowrap">
                                <i class="fas fa-times mr-1"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Render rejected applications
        function renderRejectedApplications(applications) {
            const container = document.getElementById('rejectedApplicationsContainer');
            const countBadge = document.getElementById('rejectedApplicationsCount');
            
            const rejectedApps = applications.filter(app => app.status === 'rejected');
            countBadge.textContent = rejectedApps.length;
            
            if (rejectedApps.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500 dark:text-slate-400">
                        <i class="fas fa-check-circle text-4xl mb-2"></i>
                        <p>No rejected applications</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = rejectedApps.map(app => `
                <div class="bg-white dark:bg-slate-800 rounded-lg p-5 border border-gray-200 dark:border-slate-700 hover:shadow-lg transition-shadow opacity-75">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Personal Info -->
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white font-bold text-lg">
                                        ${app.full_name.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">${app.full_name}</p>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">${app.phone_number}</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    <i class="fas fa-calendar mr-1"></i>Applied: ${new Date(app.created_at).toLocaleDateString()}
                                </p>
                                <span class="inline-flex items-center px-2 py-1 mt-2 rounded-md text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    <i class="fas fa-times-circle mr-1"></i>Rejected
                                </span>
                            </div>
                            
                            <!-- Vehicle Info -->
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Vehicle</p>
                                <p class="font-medium text-gray-900 dark:text-white capitalize flex items-center gap-2">
                                    ${getVehicleIcon(app.vehicle_type)}
                                    <span>${app.vehicle_type}</span>
                                </p>
                                <p class="text-sm text-gray-600 dark:text-slate-300 font-mono">${app.vehicle_reg}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                    <i class="fas fa-map-marker-alt mr-1"></i>${app.residential_area || 'N/A'}
                                </p>
                            </div>
                            
                            <!-- Areas & Materials -->
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Coverage</p>
                                <p class="text-sm text-gray-900 dark:text-white mb-1">
                                    <i class="fas fa-map mr-1 text-blue-600"></i>${app.service_areas || 'N/A'}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <i class="fas fa-recycle mr-1 text-green-600"></i>${app.materials_collected || 'N/A'}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex flex-col gap-2">
                            <button onclick="viewApplication(${app.id})" 
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap">
                                <i class="fas fa-eye mr-1"></i>View Details
                            </button>
                            <button onclick="deleteApplication(${app.id})" 
                                    class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors whitespace-nowrap">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // View application details
        async function viewApplication(id) {
            const modal = document.getElementById('applicationModal');
            const content = document.getElementById('applicationDetailsContent');
            
            // Find application in loaded data
            const response = await fetch('/Scrap/api/admin/collectors.php', {
                credentials: 'include'
            });
            const data = await response.json();
            const app = data.applications.find(a => a.id === id);
            
            if (!app) {
                alert('Application not found');
                return;
            }
            
            content.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Information -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white text-lg">Personal Information</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Full Name</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${app.full_name}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Phone Number</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${app.phone_number}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Date of Birth</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${app.date_of_birth || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-slate-400">ID Number</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${app.id_number || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Residential Area</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${app.residential_area || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vehicle & Service Information -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white text-lg">Vehicle & Service</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Vehicle Type</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">${app.vehicle_type}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Vehicle Registration</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${app.vehicle_reg}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Service Areas</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${app.service_areas || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Materials Collected</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${app.materials_collected || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Documents -->
                <div class="mt-6 space-y-4">
                    <h4 class="font-semibold text-gray-900 dark:text-white text-lg">Documents</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${app.id_card_front ? `
                            <div class="border border-gray-200 dark:border-slate-700 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-slate-300 mb-3">ID Card (Front)</p>
                                <img src="/Scrap/public/uploads/collectors/${app.id_card_front}" 
                                     alt="ID Card Front" 
                                     class="w-full h-48 object-contain bg-gray-100 dark:bg-slate-700 rounded-lg mb-2"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 200%22%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22%3EImage not found%3C/text%3E%3C/svg%3E';" />
                                <a href="/Scrap/public/uploads/collectors/${app.id_card_front}" target="_blank" class="text-blue-600 hover:text-blue-700 text-xs">
                                    <i class="fas fa-external-link-alt mr-1"></i>Open in new tab
                                </a>
                            </div>
                        ` : ''}
                        ${app.id_card_back ? `
                            <div class="border border-gray-200 dark:border-slate-700 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-slate-300 mb-3">ID Card (Back)</p>
                                <img src="/Scrap/public/uploads/collectors/${app.id_card_back}" 
                                     alt="ID Card Back" 
                                     class="w-full h-48 object-contain bg-gray-100 dark:bg-slate-700 rounded-lg mb-2"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 200%22%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22%3EImage not found%3C/text%3E%3C/svg%3E';" />
                                <a href="/Scrap/public/uploads/collectors/${app.id_card_back}" target="_blank" class="text-blue-600 hover:text-blue-700 text-xs">
                                    <i class="fas fa-external-link-alt mr-1"></i>Open in new tab
                                </a>
                            </div>
                        ` : ''}
                        ${app.vehicle_document ? `
                            <div class="border border-gray-200 dark:border-slate-700 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-slate-300 mb-3">Vehicle Document</p>
                                ${app.vehicle_document.endsWith('.pdf') ? `
                                    <div class="w-full h-48 bg-gray-100 dark:bg-slate-700 rounded-lg mb-2 flex items-center justify-center">
                                        <div class="text-center">
                                            <i class="fas fa-file-pdf text-red-500 text-5xl mb-2"></i>
                                            <p class="text-sm text-gray-600 dark:text-slate-400">PDF Document</p>
                                        </div>
                                    </div>
                                ` : `
                                    <img src="/Scrap/public/uploads/collectors/${app.vehicle_document}" 
                                         alt="Vehicle Document" 
                                         class="w-full h-48 object-contain bg-gray-100 dark:bg-slate-700 rounded-lg mb-2"
                                         onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 200%22%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22%3EImage not found%3C/text%3E%3C/svg%3E';" />
                                `}
                                <a href="/Scrap/public/uploads/collectors/${app.vehicle_document}" target="_blank" class="text-blue-600 hover:text-blue-700 text-xs">
                                    <i class="fas fa-external-link-alt mr-1"></i>Open in new tab
                                </a>
                            </div>
                        ` : ''}
                        ${app.good_conduct ? `
                            <div class="border border-gray-200 dark:border-slate-700 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-slate-300 mb-3">Certificate of Good Conduct</p>
                                ${app.good_conduct.endsWith('.pdf') ? `
                                    <div class="w-full h-48 bg-gray-100 dark:bg-slate-700 rounded-lg mb-2 flex items-center justify-center">
                                        <div class="text-center">
                                            <i class="fas fa-file-pdf text-red-500 text-5xl mb-2"></i>
                                            <p class="text-sm text-gray-600 dark:text-slate-400">PDF Document</p>
                                        </div>
                                    </div>
                                ` : `
                                    <img src="/Scrap/public/uploads/collectors/${app.good_conduct}" 
                                         alt="Good Conduct Certificate" 
                                         class="w-full h-48 object-contain bg-gray-100 dark:bg-slate-700 rounded-lg mb-2"
                                         onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 200%22%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22%3EImage not found%3C/text%3E%3C/svg%3E';" />
                                `}
                                <a href="/Scrap/public/uploads/collectors/${app.good_conduct}" target="_blank" class="text-blue-600 hover:text-blue-700 text-xs">
                                    <i class="fas fa-external-link-alt mr-1"></i>Open in new tab
                                </a>
                            </div>
                        ` : ''}
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-6 flex gap-3 justify-end">
                    <button onclick="closeApplicationModal()" 
                            class="px-6 py-2.5 bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-white font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors">
                        Close
                    </button>
                    <button onclick="approveApplication(${app.id}); closeApplicationModal();" 
                            class="px-6 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>Approve
                    </button>
                    <button onclick="rejectApplication(${app.id}); closeApplicationModal();" 
                            class="px-6 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-times mr-2"></i>Reject
                    </button>
                </div>
            `;
            
            modal.classList.remove('hidden');
        }

        // Close application modal
        function closeApplicationModal() {
            document.getElementById('applicationModal').classList.add('hidden');
        }

        // Confirmation modal state
        let pendingAction = null;

        // Show confirmation modal
        function showConfirmationModal(title, message, confirmText, confirmClass, iconClass, iconBg, action, showReasonInput = false) {
            const modal = document.getElementById('confirmationModal');
            const titleEl = document.getElementById('confirmationTitle');
            const messageEl = document.getElementById('confirmationMessage');
            const buttonEl = document.getElementById('confirmationButton');
            const iconEl = document.getElementById('confirmationIcon');
            const reasonContainer = document.getElementById('rejectionReasonContainer');
            const reasonInput = document.getElementById('rejectionReason');
            
            titleEl.textContent = title;
            messageEl.textContent = message;
            buttonEl.textContent = confirmText;
            buttonEl.className = `flex-1 px-4 py-2.5 font-medium rounded-lg transition-colors text-white ${confirmClass}`;
            iconEl.className = `flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full ${iconBg}`;
            iconEl.innerHTML = `<i class="${iconClass} text-3xl"></i>`;
            
            // Show/hide rejection reason input
            if (showReasonInput) {
                reasonContainer.classList.remove('hidden');
                reasonInput.value = '';
            } else {
                reasonContainer.classList.add('hidden');
            }
            
            pendingAction = action;
            modal.classList.remove('hidden');
        }

        // Close confirmation modal
        function closeConfirmationModal() {
            document.getElementById('confirmationModal').classList.add('hidden');
            document.getElementById('rejectionReason').value = '';
            pendingAction = null;
        }

        // Confirm action
        async function confirmAction() {
            if (pendingAction) {
                await pendingAction();
                closeConfirmationModal();
            }
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const content = document.getElementById('toastContent');
            const icon = document.getElementById('toastIcon');
            const messageEl = document.getElementById('toastMessage');
            
            messageEl.textContent = message;
            
            if (type === 'success') {
                content.className = 'rounded-lg shadow-lg p-4 flex items-start gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800';
                icon.innerHTML = '<i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>';
                messageEl.className = 'text-sm font-medium text-green-800 dark:text-green-200';
            } else {
                content.className = 'rounded-lg shadow-lg p-4 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800';
                icon.innerHTML = '<i class="fas fa-exclamation-circle text-red-600 dark:text-red-400"></i>';
                messageEl.className = 'text-sm font-medium text-red-800 dark:text-red-200';
            }
            
            toast.classList.remove('hidden');
            setTimeout(() => closeToast(), 5000);
        }

        // Close toast
        function closeToast() {
            document.getElementById('toast').classList.add('hidden');
        }

        // Approve application
        async function approveApplication(id) {
            showConfirmationModal(
                'Approve Application',
                'Are you sure you want to approve this collector application? This will allow them to start collecting recyclables.',
                'Approve',
                'bg-green-600 hover:bg-green-700',
                'fas fa-check-circle text-white',
                'bg-green-100 dark:bg-green-900/20',
                async () => {
                    try {
                        const response = await fetch('/Scrap/api/admin/collectors.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            credentials: 'include',
                            body: JSON.stringify({
                                application_id: id,
                                action: 'approve'
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            showToast('Application approved successfully!', 'success');
                            loadCollectors();
                        } else {
                            showToast(data.message || 'Failed to approve application', 'error');
                        }
                    } catch (error) {
                        console.error('Error approving application:', error);
                        showToast('Failed to approve application. Please try again.', 'error');
                    }
                }
            );
        }

        // Reject application
        async function rejectApplication(id) {
            showConfirmationModal(
                'Reject Application',
                'Are you sure you want to reject this collector application? This action cannot be undone.',
                'Reject',
                'bg-red-600 hover:bg-red-700',
                'fas fa-times-circle text-white',
                'bg-red-100 dark:bg-red-900/20',
                async () => {
                    const reason = document.getElementById('rejectionReason').value;
                    
                    try {
                        const response = await fetch('/Scrap/api/admin/collectors.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            credentials: 'include',
                            body: JSON.stringify({
                                application_id: id,
                                action: 'reject',
                                reason: reason
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            showToast('Application rejected.', 'success');
                            loadCollectors();
                        } else {
                            showToast(data.message || 'Failed to reject application', 'error');
                        }
                    } catch (error) {
                        console.error('Error rejecting application:', error);
                        showToast('Failed to reject application. Please try again.', 'error');
                    }
                },
                true // Show rejection reason input
            );
        }

        // Delete rejected application
        async function deleteApplication(id) {
            showConfirmationModal(
                'Delete Application',
                'Are you sure you want to permanently delete this rejected application? This action cannot be undone.',
                'Delete',
                'bg-red-600 hover:bg-red-700',
                'fas fa-trash text-white',
                'bg-red-100 dark:bg-red-900/20',
                async () => {
                    try {
                        const response = await fetch(`/Scrap/api/admin/collectors.php?application_id=${id}`, {
                            method: 'DELETE',
                            credentials: 'include'
                        });
                        
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            showToast('Application deleted successfully.', 'success');
                            loadCollectors();
                        } else {
                            showToast(data.message || 'Failed to delete application', 'error');
                        }
                    } catch (error) {
                        console.error('Error deleting application:', error);
                        showToast('Failed to delete application. Please try again.', 'error');
                    }
                }
            );
        }

        // Render collectors table
        function renderCollectors() {
            const tbody = document.getElementById('collectorsTableBody');
            const statusFilter = document.getElementById('statusFilter').value;
            const vehicleFilter = document.getElementById('vehicleFilter').value.toLowerCase();
            const searchTerm = document.getElementById('searchCollector').value.toLowerCase();

            // Update filter count badge
            updateFilterCount();

            let filtered = collectors.filter(c => {
                const matchesStatus = !statusFilter || c.status === statusFilter;
                const matchesVehicle = !vehicleFilter || 
                    (vehicleFilter === 'n/a' && (!c.vehicle_type || c.vehicle_type === 'N/A')) ||
                    (c.vehicle_type && c.vehicle_type.toLowerCase() === vehicleFilter);
                const matchesSearch = !searchTerm || 
                    (c.name && c.name.toLowerCase().includes(searchTerm)) ||
                    (c.email && c.email.toLowerCase().includes(searchTerm)) ||
                    (c.phone && c.phone.includes(searchTerm));
                return matchesStatus && matchesVehicle && matchesSearch;
            });

            // Update results count
            document.getElementById('resultsCount').textContent = filtered.length;
            document.getElementById('totalCount').textContent = collectors.length;

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500 dark:text-slate-400">
                            <i class="fas fa-search text-3xl mb-2"></i>
                            <p class="font-medium">No collectors found matching your filters.</p>
                            <button onclick="clearFilters()" class="mt-3 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                                Clear Filters
                            </button>
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
                        <div class="flex items-center gap-2">
                            ${getVehicleIcon(collector.vehicle_type)}
                            <div>
                                <p class="text-sm text-gray-900 dark:text-white capitalize font-medium">${collector.vehicle_type || 'N/A'}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400 font-mono">${collector.vehicle_reg || 'N/A'}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            ${getStatusBadge(collector.status)}
                            ${collector.verified == 1 ? `
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs font-medium rounded-full">
                                    <i class="fas fa-check-circle"></i>
                                    Verified
                                </span>
                            ` : ''}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        ${getActiveStatusBadge(collector.active_status)}
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
                        <p class="text-sm text-gray-900 dark:text-white">${collector.created_at ? new Date(collector.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A'}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">${collector.created_at ? new Date(collector.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : ''}</p>
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
                active: '<span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800"><i class="fas fa-check-circle"></i> Active</span>',
                pending: '<span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800"><i class="fas fa-clock"></i> Pending</span>',
                suspended: '<span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800"><i class="fas fa-ban"></i> Suspended</span>',
                rejected: '<span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-900/20 text-gray-700 dark:text-gray-400 border border-gray-200 dark:border-gray-800"><i class="fas fa-times-circle"></i> Rejected</span>'
            };
            return badges[status] || badges.pending;
        }

        // Get active status badge (online/offline/on_job)
        function getActiveStatusBadge(activeStatus) {
            if (!activeStatus) {
                return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Offline</span>';
            }
            
            const badges = {
                online: '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300"><span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Online</span>',
                offline: '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Offline</span>',
                on_job: '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> On Job</span>'
            };
            return badges[activeStatus] || badges.offline;
        }

        // Get vehicle icon HTML
        function getVehicleIcon(vehicleType) {
            const icons = {
                'motorcycle': '<i class="fas fa-motorcycle text-orange-600 dark:text-orange-400"></i>',
                'tuk tuk': '<i class="fas fa-taxi text-yellow-600 dark:text-yellow-400"></i>',
                'pickup truck': '<i class="fas fa-truck text-blue-600 dark:text-blue-400"></i>',
                'truck': '<i class="fas fa-truck text-blue-600 dark:text-blue-400"></i>',
                'van': '<i class="fas fa-shuttle-van text-purple-600 dark:text-purple-400"></i>'
            };
            return icons[vehicleType?.toLowerCase()] || '<i class="fas fa-truck text-gray-600 dark:text-gray-400"></i>';
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
                                        <p class="text-sm font-medium text-gray-900 dark:text-white capitalize flex items-center gap-2">
                                            ${getVehicleIcon(collector.vehicle_type)}
                                            <span>${collector.vehicle_type || 'N/A'}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Registration</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white font-mono">${collector.vehicle_reg || 'N/A'}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Status</p>
                                        <div class="flex items-center gap-2">
                                            ${getStatusBadge(collector.status)}
                                            ${collector.verified == 1 ? `
                                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs font-medium rounded-full">
                                                    <i class="fas fa-check-circle"></i>
                                                    Verified
                                                </span>
                                            ` : `
                                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-medium rounded-full">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    Not Verified
                                                </span>
                                            `}
                                        </div>
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

        let collectorToSuspend = null;

        function suspendCollector(id) {
            collectorToSuspend = id;
            console.log('Suspending collector:', id); // Debug log
            document.getElementById('suspendModal').classList.remove('hidden');
        }

        function closeSuspendModal() {
            collectorToSuspend = null;
            document.getElementById('suspendModal').classList.add('hidden');
        }

        async function confirmSuspend() {
            if (!collectorToSuspend) {
                console.error('No collector ID set');
                return;
            }
            
            const collectorId = collectorToSuspend;
            closeSuspendModal();
            await updateCollectorStatus(collectorId, 'suspended');
        }

        async function activateCollector(id) {
            await updateCollectorStatus(id, 'active');
        }

        async function updateCollectorStatus(id, status, reason = '') {
            try {
                // Validate inputs before sending
                if (!id || !status) {
                    console.error('Invalid parameters:', { id, status, reason });
                    alert('Error: Missing collector ID or status');
                    return;
                }
                
                const payload = { id, status, reason };
                console.log('Sending status update:', payload);
                
                const response = await fetch('/Scrap/api/admin/collectors.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const data = await response.json();
                console.log('API response:', data);
                
                if (data.status === 'success') {
                    // Show success message
                    showToast(data.message || 'Status updated successfully', 'success');
                    loadCollectors();
                } else {
                    showToast('Failed: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error updating collector status:', error);
                showToast('Error updating status: ' + error.message, 'error');
            }
        }

        // Refresh collectors
        function refreshCollectors() {
            loadCollectors();
        }

        // Update filter count badge
        function updateFilterCount() {
            const statusFilter = document.getElementById('statusFilter').value;
            const vehicleFilter = document.getElementById('vehicleFilter').value;
            const searchTerm = document.getElementById('searchCollector').value;
            
            let count = 0;
            if (statusFilter) count++;
            if (vehicleFilter) count++;
            if (searchTerm) count++;
            
            const badge = document.getElementById('activeFiltersCount');
            const countSpan = document.getElementById('filterCount');
            
            if (count > 0) {
                badge.classList.remove('hidden');
                countSpan.textContent = count;
            } else {
                badge.classList.add('hidden');
            }
        }

        // Clear all filters
        function clearFilters() {
            document.getElementById('statusFilter').value = '';
            document.getElementById('vehicleFilter').value = '';
            document.getElementById('searchCollector').value = '';
            updateFilterCount();
            renderCollectors();
        }

        // Logout
        function logout() {
            fetch('/Scrap/api/logout.php', { 
                method: 'POST',
                credentials: 'include' 
            })
                .then(() => {
                    sessionStorage.clear();
                    window.location.href = '/Scrap/views/auth/login.php?logout=1';
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
            
            // Auto-refresh every 30 seconds to show real-time status updates
            setInterval(function() {
                loadCollectors();
            }, 30000);
        });
    </script>
</body>
</html>
