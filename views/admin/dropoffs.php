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
    <title>Drop-off Points Management - Admin Dashboard</title>
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
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Drop-off Points Management</h1>
                <p class="text-gray-600 dark:text-slate-400">Manage recycling drop-off locations and monitor usage</p>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">Total Points</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalDropoffs">0</p>
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
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="activeDropoffs">0</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <div class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-400">This Month</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="monthCollections">0</p>
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
                        <input type="search" id="searchDropoff" placeholder="Search drop-offs..." 
                               class="px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-green-500 dark:text-white w-64">
                    </div>
                    <div class="flex gap-3">
                        <button onclick="openAddModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Drop-off Point
                        </button>
                        <button onclick="refreshDropoffs()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Drop-offs Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="dropoffsGrid">
                <!-- Loading state -->
                <div class="col-span-full flex items-center justify-center py-12">
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 border-3 border-green-600 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-gray-500 dark:text-slate-400">Loading drop-off points...</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add/Edit Drop-off Modal -->
    <div id="dropoffModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 p-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white" id="modalTitle">Add Drop-off Point</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="dropoffForm" class="p-6 space-y-4">
                <input type="hidden" id="dropoffId" name="id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Name *</label>
                    <input type="text" id="dropoffName" name="name" required
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Address *</label>
                    <input type="text" id="dropoffLocation" name="address" required
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Latitude</label>
                        <input type="number" step="0.00000001" id="dropoffLat" name="lat"
                               class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Longitude</label>
                        <input type="number" step="0.00000001" id="dropoffLng" name="lng"
                               class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Contact Phone</label>
                    <input type="text" id="dropoffContact" name="contact_phone"
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Operating Hours</label>
                    <input type="text" id="dropoffHours" name="operating_hours" placeholder="e.g., Mon-Fri: 8AM-5PM"
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Accepted Materials</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300">
                            <input type="checkbox" name="materials[]" value="plastic" class="rounded">
                            <span>Plastic</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300">
                            <input type="checkbox" name="materials[]" value="paper" class="rounded">
                            <span>Paper</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300">
                            <input type="checkbox" name="materials[]" value="metal" class="rounded">
                            <span>Metal</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300">
                            <input type="checkbox" name="materials[]" value="glass" class="rounded">
                            <span>Glass</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300">
                            <input type="checkbox" name="materials[]" value="electronics" class="rounded">
                            <span>Electronics</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Status</label>
                    <select id="dropoffStatus" name="status"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:text-white">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                        Save Drop-off Point
                    </button>
                    <button type="button" onclick="closeModal()" class="px-6 py-3 bg-gray-300 dark:bg-slate-700 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-400 dark:hover:bg-slate-600 transition-colors font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let dropoffs = [];
        
        if (!sessionStorage.getItem('user_id') || sessionStorage.getItem('user_role') !== 'admin') {
            window.location.href = '/Scrap/views/auth/login.php';
        }

        async function loadDropoffs() {
            try {
                const response = await fetch('/Scrap/api/admin/dropoffs.php', {
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    dropoffs = data.dropoffs;
                    updateStats(data.stats);
                    renderDropoffs();
                }
            } catch (error) {
                console.error('Failed to load drop-offs:', error);
                document.getElementById('dropoffsGrid').innerHTML = `
                    <div class="col-span-full text-center text-red-600 dark:text-red-400 py-8">
                        Failed to load drop-off points. Please try again.
                    </div>
                `;
            }
        }

        function updateStats(stats) {
            document.getElementById('totalDropoffs').textContent = stats.total;
            document.getElementById('activeDropoffs').textContent = stats.active;
            document.getElementById('totalCollections').textContent = stats.total_collections;
            document.getElementById('monthCollections').textContent = stats.month_collections;
        }

        function renderDropoffs() {
            const grid = document.getElementById('dropoffsGrid');
            const statusFilter = document.getElementById('statusFilter').value;
            const searchTerm = document.getElementById('searchDropoff').value.toLowerCase();

            let filtered = dropoffs.filter(d => {
                const matchesStatus = !statusFilter || d.status === statusFilter;
                const matchesSearch = !searchTerm || 
                    (d.name && d.name.toLowerCase().includes(searchTerm)) ||
                    (d.address && d.address.toLowerCase().includes(searchTerm)) ||
                    (d.materials && d.materials.toLowerCase().includes(searchTerm)) ||
                    (d.contact_phone && d.contact_phone.toLowerCase().includes(searchTerm));
                return matchesStatus && matchesSearch;
            });

            if (filtered.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center text-gray-500 dark:text-slate-400 py-8">
                        No drop-off points found matching your filters.
                    </div>
                `;
                return;
            }

            grid.innerHTML = filtered.map(dropoff => `
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6 space-y-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">${dropoff.name}</h3>
                                <p class="text-sm text-gray-500 dark:text-slate-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    </svg>
                                    ${dropoff.address || 'N/A'}
                                </p>
                            </div>
                            ${getStatusBadge(dropoff.status)}
                        </div>

                        ${dropoff.materials ? `
                        <div class="flex flex-wrap gap-1">
                            ${(dropoff.materials || '').split(',').filter(m => m.trim()).map(m => `
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400">${m.trim()}</span>
                            `).join('')}
                        </div>
                        ` : ''}

                        <div class="space-y-2 text-sm">
                            ${dropoff.contact_phone ? `
                            <div class="flex items-center gap-2 text-gray-600 dark:text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                ${dropoff.contact_phone}
                            </div>
                            ` : ''}
                            ${dropoff.operating_hours ? `
                            <div class="flex items-center gap-2 text-gray-600 dark:text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                ${dropoff.operating_hours}
                            </div>
                            ` : ''}
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm text-gray-600 dark:text-slate-400">Collections</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">${dropoff.collection_count || 0}</span>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="editDropoff(${dropoff.id})" 
                                        class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    Edit
                                </button>
                                <button onclick="deleteDropoff(${dropoff.id})" 
                                        class="flex-1 px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function getStatusBadge(status) {
            return status === 'active' 
                ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400">Active</span>'
                : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-900/20 text-gray-600 dark:text-gray-400">Inactive</span>';
        }

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Drop-off Point';
            document.getElementById('dropoffForm').reset();
            document.getElementById('dropoffId').value = '';
            document.getElementById('dropoffModal').classList.remove('hidden');
        }

        async function editDropoff(id) {
            const dropoff = dropoffs.find(d => d.id === id);
            if (!dropoff) {
                console.error('Dropoff not found:', id);
                return;
            }

            console.log('Editing dropoff:', dropoff); // Debug log

            // Update modal title
            document.getElementById('modalTitle').textContent = 'Edit Drop-off Point';
            
            // Populate form fields with database column names
            document.getElementById('dropoffId').value = dropoff.id || '';
            document.getElementById('dropoffName').value = dropoff.name || '';
            document.getElementById('dropoffLocation').value = dropoff.address || '';
            document.getElementById('dropoffLat').value = dropoff.lat || '';
            document.getElementById('dropoffLng').value = dropoff.lng || '';
            document.getElementById('dropoffContact').value = dropoff.contact_phone || '';
            document.getElementById('dropoffHours').value = dropoff.operating_hours || '';
            document.getElementById('dropoffStatus').value = dropoff.status || 'active';
            
            // Handle materials checkboxes
            const materials = dropoff.materials ? dropoff.materials.split(',') : [];
            document.querySelectorAll('input[name="materials[]"]').forEach(checkbox => {
                checkbox.checked = materials.includes(checkbox.value);
            });
            
            // Show modal
            document.getElementById('dropoffModal').classList.remove('hidden');
        }

        async function deleteDropoff(id) {
            if (!confirm('Are you sure you want to delete this drop-off point?')) return;

            try {
                const response = await fetch('/Scrap/api/admin/dropoffs.php', {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const data = await response.json();
                if (data.status === 'success') {
                    loadDropoffs();
                } else {
                    alert('Failed to delete drop-off point: ' + data.message);
                }
            } catch (error) {
                alert('Error deleting drop-off point');
            }
        }

        function closeModal() {
            document.getElementById('dropoffModal').classList.add('hidden');
            document.getElementById('dropoffForm').reset();
            document.getElementById('dropoffId').value = '';
        }

        document.getElementById('dropoffForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Get selected materials
            const materials = Array.from(document.querySelectorAll('input[name="materials[]"]:checked'))
                .map(cb => cb.value)
                .join(',');
            
            const formData = {
                id: document.getElementById('dropoffId').value,
                name: document.getElementById('dropoffName').value,
                address: document.getElementById('dropoffLocation').value,
                lat: document.getElementById('dropoffLat').value,
                lng: document.getElementById('dropoffLng').value,
                contact_phone: document.getElementById('dropoffContact').value,
                operating_hours: document.getElementById('dropoffHours').value,
                materials: materials,
                status: document.getElementById('dropoffStatus').value
            };

            try {
                const response = await fetch('/Scrap/api/admin/dropoffs.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                if (data.status === 'success') {
                    closeModal();
                    loadDropoffs();
                } else {
                    alert('Failed to save drop-off point: ' + data.message);
                }
            } catch (error) {
                alert('Error saving drop-off point');
            }
        });

        function refreshDropoffs() {
            loadDropoffs();
        }

        function logout() {
            fetch('/Scrap/api/logout.php', { method: 'POST', credentials: 'include' })
                .then(() => {
                    sessionStorage.clear();
                    window.location.href = '/Scrap/views/auth/login.php?logout=1';
                });
        }

        document.getElementById('statusFilter').addEventListener('change', renderDropoffs);
        document.getElementById('searchDropoff').addEventListener('input', renderDropoffs);

        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('ready');
            loadDropoffs();
        });
    </script>
</body>
</html>
