<?php
require_once '../../config.php';
require_once '../../includes/auth.php';

// Check if user is logged in and is a collector
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'collector') {
    header('Location: /Scrap/views/auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Drop-off Points - Collector Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media (prefers-color-scheme: dark) {
            html:not(.light) {
                color-scheme: dark;
            }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-slate-900 transition-colors duration-200">
    <?php include '../../includes/collector_sidebar.php'; ?>
    
    <div class="ml-64 min-h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-slate-800 shadow-sm border-b border-gray-200 dark:border-slate-700">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Drop-off Points</h1>
                        <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">Manage drop-off points you've added</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-700 dark:text-slate-300"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Collector') ?></p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">Collector</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-slate-400">Total Points Added</p>
                            <p id="totalPoints" class="text-3xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-slate-400">Active Points</p>
                            <p id="activePoints" class="text-3xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-slate-400">Total Collections</p>
                            <p id="totalCollections" class="text-3xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drop-off Points Grid -->
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700">
                <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Your Drop-off Points</h2>
                </div>
                <div id="dropoffsContainer" class="p-6">
                    <!-- Loading state -->
                    <div id="loadingState" class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                            <p class="text-sm text-gray-600 dark:text-slate-400 mt-3">Loading drop-off points...</p>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div id="emptyState" class="hidden text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Drop-off Points Yet</h3>
                        <p class="text-sm text-gray-600 dark:text-slate-400 mb-4">You haven't added any drop-off points yet.</p>
                        <a href="/Scrap/views/collectors/profile.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Your First Drop-off Point
                        </a>
                    </div>

                    <!-- Drop-off points grid -->
                    <div id="dropoffsGrid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Drop-off Point Modal -->
    <div id="editDropoffModal" class="hidden fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl max-w-3xl w-full max-h-[95vh] overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between flex-shrink-0 bg-white dark:bg-slate-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Drop-off Point</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="editDropoffForm" class="p-4 space-y-3 overflow-y-auto flex-1">
                <input type="hidden" id="edit_dropoff_id">
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Name *</label>
                        <input type="text" id="edit_name" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-slate-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Status</label>
                        <select id="edit_status" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-slate-700 dark:text-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                        <span>Address *</span>
                        <button type="button" onclick="getEditLocation()" class="text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 flex items-center gap-1 text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Get Location
                        </button>
                    </label>
                    <input type="text" id="edit_address" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-slate-700 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Latitude *</label>
                        <input type="number" step="any" id="edit_lat" min="-90" max="90" required placeholder="-1.2921000" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-slate-700 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Range: -90 to 90 (e.g., -1.2921000)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Longitude *</label>
                        <input type="number" step="any" id="edit_lng" min="-180" max="180" required placeholder="36.8219000" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-slate-700 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Range: -180 to 180 (e.g., 36.8219000)</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Contact Phone</label>
                        <input type="tel" id="edit_contact_phone" placeholder="+254" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-slate-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Operating Hours</label>
                        <select id="edit_operating_hours" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-slate-700 dark:text-white">
                            <option value="">Select operating hours</option>
                            <option value="24/7">24/7 - Open All Day</option>
                            <option value="Mon-Fri: 8AM-5PM">Mon-Fri: 8AM-5PM</option>
                            <option value="Mon-Fri: 8AM-6PM">Mon-Fri: 8AM-6PM</option>
                            <option value="Mon-Sat: 8AM-5PM">Mon-Sat: 8AM-5PM</option>
                            <option value="Mon-Sat: 8AM-6PM">Mon-Sat: 8AM-6PM</option>
                            <option value="Mon-Sun: 8AM-5PM">Mon-Sun: 8AM-5PM</option>
                            <option value="Mon-Sun: 8AM-6PM">Mon-Sun: 8AM-6PM</option>
                            <option value="Mon-Fri: 9AM-5PM">Mon-Fri: 9AM-5PM</option>
                            <option value="Mon-Sat: 9AM-5PM">Mon-Sat: 9AM-5PM</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Accepted Materials *</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="edit_materials" value="plastic" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm text-gray-700 dark:text-slate-300">Plastic</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="edit_materials" value="paper" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm text-gray-700 dark:text-slate-300">Paper</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="edit_materials" value="metal" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm text-gray-700 dark:text-slate-300">Metal</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="edit_materials" value="glass" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm text-gray-700 dark:text-slate-300">Glass</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer col-span-2">
                            <input type="checkbox" name="edit_materials" value="electronics" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm text-gray-700 dark:text-slate-300">Electronics</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Photo</label>
                    <div id="edit_current_photo" class="mb-2 hidden">
                        <img id="edit_photo_preview" src="" alt="Current photo" class="w-full h-24 object-cover rounded-lg border border-gray-300 dark:border-slate-600">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Current photo (upload new to replace)</p>
                    </div>
                    <input type="file" id="edit_photo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-slate-700 dark:text-white">
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Max 5MB. JPG, PNG, GIF, WEBP</p>
                    <div id="edit_photo_new_preview" class="mt-2 hidden">
                        <img id="edit_photo_new_preview_img" src="" alt="New photo preview" class="w-full h-24 object-cover rounded-lg border border-gray-300 dark:border-slate-600">
                    </div>
                </div>

                <div class="flex gap-3 pt-3 border-t border-gray-200 dark:border-slate-700 sticky bottom-0 bg-white dark:bg-slate-800">
                    <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 font-medium text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium text-sm">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Drop-off Point</h3>
                </div>
            </div>
            <p class="text-gray-600 dark:text-slate-300 mb-2">Are you sure you want to delete <strong id="deleteName" class="text-gray-900 dark:text-white"></strong>?</p>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">This action cannot be undone.</p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 font-medium">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-green-600',
                error: 'bg-red-600',
                info: 'bg-blue-600'
            };
            toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Material color mapping
        const materialColors = {
            plastic: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
            paper: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
            metal: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
            glass: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            electronics: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400'
        };

        // Fetch and display drop-off points
        async function loadDropoffPoints() {
            try {
                const response = await fetch('/Scrap/api/collectors/get_dropoff_points.php', {
                    credentials: 'include'
                });

                const data = await response.json();

                if (data.status === 'success') {
                    const dropoffs = data.dropoffs;
                    
                    // Store dropoffs globally for edit/delete operations
                    dropoffsData = dropoffs;
                    
                    // Update stats
                    document.getElementById('totalPoints').textContent = dropoffs.length;
                    document.getElementById('activePoints').textContent = dropoffs.filter(d => d.status === 'active').length;
                    document.getElementById('totalCollections').textContent = dropoffs.reduce((sum, d) => sum + parseInt(d.collection_count), 0);

                    // Hide loading state
                    document.getElementById('loadingState').classList.add('hidden');

                    if (dropoffs.length === 0) {
                        // Show empty state
                        document.getElementById('emptyState').classList.remove('hidden');
                    } else {
                        // Show and populate grid
                        const grid = document.getElementById('dropoffsGrid');
                        grid.classList.remove('hidden');
                        grid.innerHTML = dropoffs.map(dropoff => createDropoffCard(dropoff)).join('');
                    }
                } else {
                    throw new Error(data.message || 'Failed to load drop-off points');
                }
            } catch (error) {
                console.error('Error loading drop-off points:', error);
                document.getElementById('loadingState').innerHTML = `
                    <div class="text-center text-red-600 dark:text-red-400">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="font-medium">Failed to load drop-off points</p>
                        <p class="text-sm mt-1">${error.message}</p>
                        <button onclick="loadDropoffPoints()" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            Try Again
                        </button>
                    </div>
                `;
            }
        }

        // Create drop-off point card HTML
        function createDropoffCard(dropoff) {
            const materialsHtml = dropoff.materials.map(material => 
                `<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400">${material.trim()}</span>`
            ).join('');

            const statusBadge = dropoff.status === 'active' 
                ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400">Active</span>'
                : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-900/20 text-gray-600 dark:text-gray-400">Inactive</span>';

            return `
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6 space-y-4">
                        ${dropoff.photo_url ? `
                        <div class="mb-3">
                            <img src="/Scrap/public/${dropoff.photo_url}" alt="${dropoff.name}" class="w-full h-40 object-cover rounded-md border border-gray-100 dark:border-slate-700">
                        </div>
                        ` : ''}
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
                            ${statusBadge}
                        </div>

                        ${dropoff.materials.length > 0 ? `
                        <div class="flex flex-wrap gap-1">
                            ${materialsHtml}
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
                                <button onclick="confirmDelete(${dropoff.id}, '${dropoff.name.replace(/'/g, "\\'")}')\" 
                                        class="flex-1 px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Load drop-off points on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDropoffPoints();
            
            // Setup edit form submission
            document.getElementById('editDropoffForm').addEventListener('submit', handleEditSubmit);
            
            // Setup photo preview for edit
            document.getElementById('edit_photo').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('edit_photo_new_preview_img').src = e.target.result;
                        document.getElementById('edit_photo_new_preview').classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        // Store dropoffs data globally for easy access
        let dropoffsData = [];
        let isGettingLocation = false; // Flag to prevent multiple simultaneous requests

        // Get location for edit form
        function getEditLocation() {
            // Prevent multiple simultaneous location requests
            if (isGettingLocation) {
                showToast('Already getting location, please wait...', 'info');
                return;
            }

            if (!navigator.geolocation) {
                showToast('Geolocation is not supported by your browser', 'error');
                return;
            }

            isGettingLocation = true; // Set flag
            showToast('Getting your location...', 'info');
            
            // First try with high accuracy (GPS)
            let timeoutId = setTimeout(() => {
                showToast('Location taking longer than expected. Please wait...', 'info');
            }, 3000);

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    clearTimeout(timeoutId);
                    isGettingLocation = false; // Clear flag on success
                    
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    document.getElementById('edit_lat').value = lat;
                    document.getElementById('edit_lng').value = lng;

                    // Get address from coordinates
                    try {
                        const response = await fetch(`/Scrap/api/geocode.php?lat=${lat}&lng=${lng}`);
                        const data = await response.json();
                        if (data.address) {
                            document.getElementById('edit_address').value = data.address;
                        }
                    } catch (error) {
                        console.error('Geocoding error:', error);
                    }

                    showToast('Location updated!', 'success');
                },
                (error) => {
                    clearTimeout(timeoutId);
                    let errorMessage = 'Unable to get location';
                    let shouldRetry = false;
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                        case 1: // Explicit permission denied code
                            errorMessage = 'Location permission denied. Please enable location access in your browser settings.';
                            shouldRetry = false; // Never retry on permission denied
                            isGettingLocation = false; // Clear flag immediately
                            break;
                        case error.POSITION_UNAVAILABLE:
                        case 2: // Position unavailable
                            errorMessage = 'Location unavailable. Move to an area with better GPS/Wi-Fi signal or enter coordinates manually.';
                            shouldRetry = true;
                            break;
                        case error.TIMEOUT:
                        case 3: // Timeout
                            errorMessage = 'Location request timed out. Trying with lower accuracy...';
                            shouldRetry = true;
                            break;
                        default:
                            errorMessage = 'Location error: ' + error.message + '. Please enter coordinates manually.';
                            shouldRetry = false; // Don't retry unknown errors
                            isGettingLocation = false; // Clear flag
                    }
                    
                    console.error('Geolocation error:', error);
                    showToast(errorMessage, 'error');
                    
                    // Only retry with lower accuracy for specific errors (not permission denied)
                    if (shouldRetry) {
                        showToast('Retrying with network-based location...', 'info');
                        setTimeout(() => {
                            retryLocationWithLowerAccuracy();
                        }, 2000);
                    } else {
                        isGettingLocation = false; // Clear flag if not retrying
                    }
                },
                {
                    enableHighAccuracy: true,  // Try GPS first
                    timeout: 15000,             // Increased timeout to 15s
                    maximumAge: 0               // Don't use cached position
                }
            );
        }
        
        // Retry with network-based positioning (less accurate but faster)
        function retryLocationWithLowerAccuracy() {
            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    isGettingLocation = false; // Clear flag on success
                    
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    document.getElementById('edit_lat').value = lat;
                    document.getElementById('edit_lng').value = lng;

                    // Get address from coordinates
                    try {
                        const response = await fetch(`/Scrap/api/geocode.php?lat=${lat}&lng=${lng}`);
                        const data = await response.json();
                        if (data.address) {
                            document.getElementById('edit_address').value = data.address;
                        }
                    } catch (error) {
                        console.error('Geocoding error:', error);
                    }

                    showToast('Location updated (approximate)', 'success');
                },
                (error) => {
                    isGettingLocation = false; // Always clear flag on retry error
                    
                    // Don't show error message for permission denied on retry (already shown)
                    if (error.code === 1 || error.code === error.PERMISSION_DENIED) {
                        console.log('Location permission still denied on retry');
                        return;
                    }
                    showToast('Could not determine location. Please enter coordinates manually.', 'error');
                    console.error('Retry geolocation error:', error);
                },
                {
                    enableHighAccuracy: false,  // Use network-based positioning
                    timeout: 10000,
                    maximumAge: 60000           // Accept cached position up to 1 minute old
                }
            );
        }

        // Edit dropoff function
        function editDropoff(id) {
            const dropoff = dropoffsData.find(d => d.id === id);
            if (!dropoff) {
                showToast('Drop-off point not found', 'error');
                return;
            }

            // Populate form
            document.getElementById('edit_dropoff_id').value = dropoff.id;
            document.getElementById('edit_name').value = dropoff.name;
            document.getElementById('edit_address').value = dropoff.address || '';
            document.getElementById('edit_lat').value = dropoff.lat || '';
            document.getElementById('edit_lng').value = dropoff.lng || '';
            document.getElementById('edit_contact_phone').value = dropoff.contact_phone || '';
            document.getElementById('edit_operating_hours').value = dropoff.operating_hours || '';
            document.getElementById('edit_status').value = dropoff.status;

            // Set materials checkboxes
            const materialCheckboxes = document.querySelectorAll('input[name="edit_materials"]');
            materialCheckboxes.forEach(cb => {
                cb.checked = dropoff.materials.includes(cb.value);
            });

            // Show current photo if exists
            if (dropoff.photo_url) {
                document.getElementById('edit_photo_preview').src = `/Scrap/public/${dropoff.photo_url}`;
                document.getElementById('edit_current_photo').classList.remove('hidden');
            } else {
                document.getElementById('edit_current_photo').classList.add('hidden');
            }

            // Hide new photo preview
            document.getElementById('edit_photo_new_preview').classList.add('hidden');

            // Show modal
            document.getElementById('editDropoffModal').classList.remove('hidden');
        }

        // Close edit modal
        function closeEditModal() {
            document.getElementById('editDropoffModal').classList.add('hidden');
            document.getElementById('editDropoffForm').reset();
            document.getElementById('edit_current_photo').classList.add('hidden');
            document.getElementById('edit_photo_new_preview').classList.add('hidden');
        }

        // Handle edit form submission
        async function handleEditSubmit(e) {
            e.preventDefault();

            const id = document.getElementById('edit_dropoff_id').value;
            const name = document.getElementById('edit_name').value.trim();
            const address = document.getElementById('edit_address').value.trim();
            const lat = document.getElementById('edit_lat').value.trim();
            const lng = document.getElementById('edit_lng').value.trim();
            const contactPhone = document.getElementById('edit_contact_phone').value.trim();
            const operatingHours = document.getElementById('edit_operating_hours').value;
            const status = document.getElementById('edit_status').value;

            // Get selected materials
            const materials = Array.from(document.querySelectorAll('input[name="edit_materials"]:checked'))
                .map(cb => cb.value);

            if (materials.length === 0) {
                showToast('Please select at least one material', 'error');
                return;
            }

            // Validate coordinates
            if (!lat || !lng) {
                showToast('Please provide latitude and longitude', 'error');
                return;
            }

            // Validate latitude range
            const latNum = parseFloat(lat);
            const lngNum = parseFloat(lng);
            
            if (isNaN(latNum) || latNum < -90 || latNum > 90) {
                showToast('Latitude must be between -90 and 90 (e.g., -1.2921000)', 'error');
                return;
            }
            
            if (isNaN(lngNum) || lngNum < -180 || lngNum > 180) {
                showToast('Longitude must be between -180 and 180 (e.g., 36.8219000)', 'error');
                return;
            }

            // Get photo file if uploaded
            const photoFile = document.getElementById('edit_photo').files[0];

            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('name', name);
                formData.append('address', address);
                formData.append('lat', lat);
                formData.append('lng', lng);
                formData.append('contact_phone', contactPhone);
                formData.append('operating_hours', operatingHours);
                formData.append('status', status);
                materials.forEach(m => formData.append('materials[]', m));
                
                // Add photo if selected
                if (photoFile) {
                    formData.append('photo', photoFile);
                }

                const response = await fetch('/Scrap/api/collectors/update_dropoff.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showToast('Drop-off point updated successfully!', 'success');
                    closeEditModal();
                    loadDropoffPoints(); // Reload the list
                } else {
                    showToast(data.message || 'Failed to update drop-off point', 'error');
                }
            } catch (error) {
                console.error('Error updating drop-off point:', error);
                showToast('An error occurred while updating', 'error');
            }
        }

        // Confirm delete
        let deleteDropoffId = null;
        function confirmDelete(id, name) {
            deleteDropoffId = id;
            document.getElementById('deleteName').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        // Close delete modal
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            deleteDropoffId = null;
        }

        // Handle delete confirmation
        document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
            if (!deleteDropoffId) return;

            try {
                const response = await fetch('/Scrap/api/collectors/delete_dropoff.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: deleteDropoffId }),
                    credentials: 'include'
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showToast('Drop-off point deleted successfully!', 'success');
                    closeDeleteModal();
                    loadDropoffPoints(); // Reload the list
                } else {
                    showToast(data.message || 'Failed to delete drop-off point', 'error');
                }
            } catch (error) {
                console.error('Error deleting drop-off point:', error);
                showToast('An error occurred while deleting', 'error');
            }
        });
    </script>
</body>
</html>
