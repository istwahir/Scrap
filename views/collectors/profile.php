<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
requireCollector();

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
    <title>Collector Profile - Kiambu Recycling & Scraps</title>
    <meta name="color-scheme" content="light dark" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <script src="https://cdn.tailwindcss.com"></script>
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
        <?php include '../../includes/collector_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <div class="p-4 md:p-6 space-y-6">
                <!-- Profile Header Card -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-slate-800 dark:to-slate-700 rounded-lg shadow-sm p-6 border border-green-100 dark:border-slate-600">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 rounded-full border-2 border-white dark:border-slate-800 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h1 id="collectorName" class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Loading...</h1>
                                <p id="collectorPhone" class="text-sm text-gray-600 dark:text-slate-300 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span></span>
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Verified
                        </span>
                    </div>
                </div>

                <!-- Personal Details Card -->
                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                            </svg>
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Personal Details</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="suggestDropoffBtn" class="flex items-center gap-2 px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Add Drop-off Point
                            </button>
                            <button id="editProfileBtn" class="flex items-center gap-2 px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Profile
                            </button>
                        </div>
                    </div>
                    <div id="profileView" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">Email Address</p>
                            <p id="collectorEmail" class="font-medium text-gray-900 dark:text-white">—</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">ID Number</p>
                            <p id="collectorIdNumber" class="font-medium text-gray-900 dark:text-white">—</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">Age</p>
                            <p id="collectorAge" class="font-medium text-gray-900 dark:text-white">—</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">Home Address</p>
                            <p id="collectorAddress" class="font-medium text-gray-900 dark:text-white">—</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">Status</p>
                            <p id="collectorStatus" class="font-medium">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-slate-200">
                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                    Offline
                                </span>
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-gray-500 dark:text-slate-400 text-xs">Member Since</p>
                            <p id="collectorJoinedDate" class="font-medium text-gray-900 dark:text-white">—</p>
                        </div>
                    </div>
                    
                    <!-- Edit Form (Hidden by default) -->
                    <form id="profileEditForm" class="hidden space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Full Name</label>
                                <input type="text" id="editName" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Email Address</label>
                                <input type="email" id="editEmail" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Phone Number</label>
                                <input type="tel" id="editPhone" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">ID Number</label>
                                <input type="text" id="editIdNumber" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Date of Birth</label>
                                <input type="date" id="editDateOfBirth" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Home Address</label>
                                <textarea id="editAddress" rows="2" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white"></textarea>
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end">
                            <button type="button" id="cancelEditBtn" class="px-4 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Vehicle & Service Areas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Vehicle Info -->
                    <!-- Vehicle Information -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                </svg>
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Vehicle Information</h2>
                            </div>
                            <button id="editVehicleBtn" class="flex items-center gap-1 px-2 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </button>
                        </div>
                        <div id="vehicleView" class="space-y-3 text-sm">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-slate-700">
                                <span class="text-gray-600 dark:text-slate-400">Vehicle Type</span>
                                <span id="vehicleType" class="font-medium text-gray-900 dark:text-white"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-slate-700">
                                <span class="text-gray-600 dark:text-slate-400">Registration</span>
                                <span id="vehicleReg" class="font-medium text-gray-900 dark:text-white"></span>
                            </div>
                            <div class="pt-2">
                                <span class="text-gray-600 dark:text-slate-400 block mb-2">Materials Collected</span>
                                <div id="materialsList" class="flex flex-wrap gap-2">
                                    <!-- Will be populated dynamically -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vehicle Edit Form -->
                        <form id="vehicleEditForm" class="hidden space-y-4">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Vehicle Type</label>
                                <select id="editVehicleType" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white" required>
                                    <option value="">Select vehicle type</option>
                                    <option value="truck">Truck</option>
                                    <option value="pickup">Pickup</option>
                                    <option value="tuktuk">Tuk-Tuk</option>
                                    <option value="motorcycle">Motorcycle</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Vehicle Registration</label>
                                <input type="text" id="editVehicleReg" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white uppercase" placeholder="KXX 123X">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-2">Materials Collected</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" value="plastic" class="material-checkbox rounded border-gray-300 dark:border-slate-600">
                                        <span class="text-sm">Plastic</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" value="paper" class="material-checkbox rounded border-gray-300 dark:border-slate-600">
                                        <span class="text-sm">Paper</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" value="metal" class="material-checkbox rounded border-gray-300 dark:border-slate-600">
                                        <span class="text-sm">Metal</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" value="glass" class="material-checkbox rounded border-gray-300 dark:border-slate-600">
                                        <span class="text-sm">Glass</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" value="electronics" class="material-checkbox rounded border-gray-300 dark:border-slate-600">
                                        <span class="text-sm">Electronics</span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex gap-2 justify-end">
                                <button type="button" id="cancelVehicleBtn" class="px-3 py-1.5 text-xs border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700">
                                    Cancel
                                </button>
                                <button type="submit" class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Service Areas -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Service Areas</h2>
                            </div>
                            <button id="editAreasBtn" class="flex items-center gap-1 px-2 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </button>
                        </div>
                        <div id="areasView" class="grid grid-cols-2 gap-2 text-sm">
                            <!-- Will be populated dynamically -->
                        </div>
                        
                        <!-- Areas Edit Form -->
                        <form id="areasEditForm" class="hidden space-y-4">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-2">Service Areas (Select all that apply)</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" value="Kiambu Town" class="area-checkbox rounded border-gray-300 dark:border-slate-600">
                                        <span class="text-sm">Kiambu Town</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" value="Thika" class="area-checkbox rounded border-gray-300 dark:border-slate-600">
                                        <span class="text-sm">Thika</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" value="Ruiru" class="area-checkbox rounded border-gray-300 dark:border-slate-600">
                                        <span class="text-sm">Ruiru</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" value="Juja" class="area-checkbox rounded border-gray-300 dark:border-slate-600">
                                        <span class="text-sm">Juja</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" value="Githunguri" class="area-checkbox rounded border-gray-300 dark:border-slate-600">
                                        <span class="text-sm">Githunguri</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" value="Limuru" class="area-checkbox rounded border-gray-300 dark:border-slate-600">
                                        <span class="text-sm">Limuru</span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex gap-2 justify-end">
                                <button type="button" id="cancelAreasBtn" class="px-3 py-1.5 text-xs border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700">
                                    Cancel
                                </button>
                                <button type="submit" class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                    Save
                                </button>
                            </div>
                        </form>                    <!-- Service Areas -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-5 border border-gray-100 dark:border-slate-700">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Service Areas</h2>
                        </div>
                        <div id="areasList" class="grid grid-cols-2 gap-2 text-sm">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Drop-off Point Modal -->
    <div id="suggestDropoffModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 p-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Add Drop-off Point</h2>
                <button onclick="closeSuggestModal()" class="text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="suggestDropoffForm" class="p-6 space-y-4" enctype="multipart/form-data">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Name *</label>
                    <input type="text" id="suggestDropoffName" name="name" required
                           placeholder="e.g., Kiambu Recycling Center"
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:text-white">
                    <p id="nameWarning" class="hidden mt-1 text-sm text-yellow-600 dark:text-yellow-400">
                        <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Similar drop-off point name exists
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Address *</label>
                    <div class="relative">
                        <input type="text" id="suggestDropoffAddress" name="address" required
                               placeholder="e.g., Kiambu Town, Main Street"
                               class="w-full px-4 py-2 pr-10 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <button type="button" onclick="getMyLocation()" title="Use my current location"
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-slate-400">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        Click the location icon to auto-fill coordinates (you'll need to allow location access)
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Latitude *</label>
                        <input type="number" step="0.00000001" id="suggestDropoffLat" name="lat" required
                               placeholder="-1.171315"
                               class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Longitude *</label>
                        <input type="number" step="0.00000001" id="suggestDropoffLng" name="lng" required
                               placeholder="36.835372"
                               class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <p id="locationWarning" class="hidden mt-1 text-sm text-yellow-600 dark:text-yellow-400">
                            <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Similar location within 1km exists
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Contact Phone</label>
                    <input type="tel" id="suggestDropoffContact" name="contact_phone" 
                           value="+254" placeholder="+254712345678"
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Operating Hours</label>
                    <select id="suggestDropoffHours" name="operating_hours"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:text-white">
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Accepted Materials *</label>
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Photo (optional)</label>
                    <div class="mt-2">
                        <input type="file" id="dropoffPhoto" name="photo" accept="image/*"
                               class="hidden" onchange="previewDropoffPhoto(event)">
                        <button type="button" onclick="document.getElementById('dropoffPhoto').click()"
                                class="w-full px-4 py-3 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors flex flex-col items-center gap-2 text-gray-600 dark:text-slate-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm">Click to upload drop-off point photo</span>
                        </button>
                        <div id="photoPreview" class="hidden mt-3">
                            <img id="previewImage" class="w-full h-48 object-cover rounded-lg border border-gray-200 dark:border-slate-600">
                            <button type="button" onclick="removeDropoffPhoto()" class="mt-2 text-sm text-red-600 hover:text-red-700 dark:text-red-400">Remove photo</button>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Add Drop-off Point
                    </button>
                    <button type="button" onclick="closeSuggestModal()" class="px-6 py-3 bg-gray-300 dark:bg-slate-700 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-400 dark:hover:bg-slate-600 transition-colors font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Store current profile data for editing
        let currentProfileData = null;
        let currentVehicleData = null;
        let currentAreasData = null;

        // Load profile data
        async function loadProfileData() {
            try {
                const response = await fetch('/Scrap/api/collectors/profile.php', { credentials: 'include' });
                if (response.status === 401) {
                    window.location.href = '/Scrap/login.php';
                    return;
                }
                if (response.status === 403) {
                    // Not a collector yet – send to collector register
                    window.location.href = '/Scrap/public/collectors/register.php';
                    return;
                }
                const data = await response.json();
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Failed to load profile');
                }
                currentProfileData = data.profile; // Store for editing
                currentVehicleData = data.vehicle; // Store vehicle data
                currentAreasData = data.areas; // Store areas data
                updateProfileHeader(data.profile);
                updateVehicleInfo(data.vehicle);
                updateServiceAreas(data.areas);
            } catch (error) {
                console.error('Failed to load profile data:', error);
            }
        }

        // Update profile header
        function updateProfileHeader(profile) {
            document.getElementById('collectorName').textContent = profile.name;
            document.getElementById('collectorPhone').querySelector('span').textContent = profile.phone;
            
            // Update personal details
            document.getElementById('collectorEmail').textContent = profile.email || '—';
            document.getElementById('collectorIdNumber').textContent = profile.id_number || '—';
            document.getElementById('collectorAge').textContent = profile.age ? `${profile.age} years` : '—';
            document.getElementById('collectorAddress').textContent = profile.home_address || '—';
            document.getElementById('collectorJoinedDate').textContent = profile.joined_date || '—';
            
            // Update status with color indicator
            const statusEl = document.getElementById('collectorStatus');
            const statusMap = {
                'online': { text: 'Available', color: 'bg-green-500', bgColor: 'bg-green-100 dark:bg-green-900/30', textColor: 'text-green-700 dark:text-green-300' },
                'on_job': { text: 'On Job', color: 'bg-blue-500', bgColor: 'bg-blue-100 dark:bg-blue-900/30', textColor: 'text-blue-700 dark:text-blue-300' },
                'offline': { text: 'Offline', color: 'bg-gray-400', bgColor: 'bg-gray-100 dark:bg-slate-700', textColor: 'text-gray-700 dark:text-slate-200' }
            };
            const status = statusMap[profile.active_status] || statusMap['offline'];
            statusEl.innerHTML = `
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs ${status.bgColor} ${status.textColor}">
                    <span class="w-2 h-2 rounded-full ${status.color}"></span>
                    ${status.text}
                </span>
            `;
        }

        // Update vehicle information
        function updateVehicleInfo(vehicle) {
            document.getElementById('vehicleType').textContent = vehicle.type;
            document.getElementById('vehicleReg').textContent = vehicle.registration;
            
            const materialsList = document.getElementById('materialsList');
            materialsList.innerHTML = '';
            
            vehicle.materials.forEach(material => {
                const badge = document.createElement('span');
                badge.className = 'px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-slate-200';
                badge.textContent = material;
                materialsList.appendChild(badge);
            });
        }

        // Update service areas
        function updateServiceAreas(areas) {
            const areasList = document.getElementById('areasView');
            areasList.innerHTML = '';
            
            areas.forEach(area => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-slate-700 rounded text-gray-700 dark:text-slate-200';
                div.innerHTML = `
                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-sm">${area}</span>
                `;
                areasList.appendChild(div);
            });
        }

        // Profile edit functionality
        function toggleEditMode(show) {
            const profileView = document.getElementById('profileView');
            const profileEditForm = document.getElementById('profileEditForm');
            const editBtn = document.getElementById('editProfileBtn');
            
            if (show) {
                profileView.classList.add('hidden');
                profileEditForm.classList.remove('hidden');
                editBtn.classList.add('hidden');
                populateEditForm();
            } else {
                profileView.classList.remove('hidden');
                profileEditForm.classList.add('hidden');
                editBtn.classList.remove('hidden');
            }
        }

        function populateEditForm() {
            if (!currentProfileData) return;
            
            document.getElementById('editName').value = currentProfileData.name || '';
            document.getElementById('editEmail').value = currentProfileData.email || '';
            document.getElementById('editPhone').value = currentProfileData.phone || '';
            document.getElementById('editIdNumber').value = currentProfileData.id_number || '';
            document.getElementById('editDateOfBirth').value = currentProfileData.date_of_birth || '';
            document.getElementById('editAddress').value = currentProfileData.home_address || '';
        }

        async function handleProfileUpdate(e) {
            e.preventDefault();
            
            const formData = {
                name: document.getElementById('editName').value,
                email: document.getElementById('editEmail').value,
                phone: document.getElementById('editPhone').value,
                id_number: document.getElementById('editIdNumber').value,
                date_of_birth: document.getElementById('editDateOfBirth').value,
                address: document.getElementById('editAddress').value
            };

            try {
                const response = await fetch('/Scrap/api/collectors/update_profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showToast('Profile updated successfully!', 'success');
                    await loadProfileData();
                    toggleEditMode(false);
                } else {
                    throw new Error(data.message || 'Failed to update profile');
                }
            } catch (error) {
                console.error('Profile update error:', error);
                showToast(error.message || 'Failed to update profile', 'error');
            }
        }

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Vehicle edit functionality
        function toggleVehicleEdit(show) {
            const vehicleView = document.getElementById('vehicleView');
            const vehicleEditForm = document.getElementById('vehicleEditForm');
            const editBtn = document.getElementById('editVehicleBtn');
            
            if (show) {
                vehicleView.classList.add('hidden');
                vehicleEditForm.classList.remove('hidden');
                editBtn.classList.add('hidden');
                populateVehicleForm();
            } else {
                vehicleView.classList.remove('hidden');
                vehicleEditForm.classList.add('hidden');
                editBtn.classList.remove('hidden');
            }
        }

        function populateVehicleForm() {
            if (!currentVehicleData) return;
            
            document.getElementById('editVehicleType').value = currentVehicleData.type || '';
            document.getElementById('editVehicleReg').value = currentVehicleData.registration || '';
            
            // Populate materials checkboxes
            const materials = currentVehicleData.materials || [];
            
            document.querySelectorAll('.material-checkbox').forEach(cb => {
                // Check if the material exists in the array (case-insensitive)
                cb.checked = materials.some(m => m.toLowerCase() === cb.value.toLowerCase());
            });
        }

        async function handleVehicleUpdate(e) {
            e.preventDefault();
            
            const selectedMaterials = Array.from(document.querySelectorAll('.material-checkbox:checked'))
                .map(cb => cb.value);
            
            const formData = {
                vehicle_type: document.getElementById('editVehicleType').value,
                vehicle_registration: document.getElementById('editVehicleReg').value,
                materials: selectedMaterials
            };

            try {
                const response = await fetch('/Scrap/api/collectors/update_profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showToast('Vehicle info updated successfully!', 'success');
                    await loadProfileData();
                    toggleVehicleEdit(false);
                } else {
                    throw new Error(data.message || 'Failed to update vehicle info');
                }
            } catch (error) {
                console.error('Vehicle update error:', error);
                showToast(error.message || 'Failed to update vehicle info', 'error');
            }
        }

        // Service areas edit functionality
        function toggleAreasEdit(show) {
            const areasView = document.getElementById('areasView');
            const areasEditForm = document.getElementById('areasEditForm');
            const editBtn = document.getElementById('editAreasBtn');
            
            if (show) {
                areasView.classList.add('hidden');
                areasEditForm.classList.remove('hidden');
                editBtn.classList.add('hidden');
                populateAreasForm();
            } else {
                areasView.classList.remove('hidden');
                areasEditForm.classList.add('hidden');
                editBtn.classList.remove('hidden');
            }
        }

        function populateAreasForm() {
            if (!currentAreasData) return;
            
            // Populate service areas checkboxes
            const areas = Array.isArray(currentAreasData) ? currentAreasData : [];
            
            document.querySelectorAll('.area-checkbox').forEach(cb => {
                cb.checked = areas.includes(cb.value);
            });
        }

        async function handleAreasUpdate(e) {
            e.preventDefault();
            
            const selectedAreas = Array.from(document.querySelectorAll('.area-checkbox:checked'))
                .map(cb => cb.value);
            
            const formData = {
                service_areas: selectedAreas
            };

            try {
                const response = await fetch('/Scrap/api/collectors/update_profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showToast('Service areas updated successfully!', 'success');
                    await loadProfileData();
                    toggleAreasEdit(false);
                } else {
                    throw new Error(data.message || 'Failed to update service areas');
                }
            } catch (error) {
                console.error('Areas update error:', error);
                showToast(error.message || 'Failed to update service areas', 'error');
            }
        }

        // Logout function
        async function logout() {
            try {
                await fetch('/Scrap/api/logout.php', { 
                    method: 'POST',
                    credentials: 'include' 
                });
                sessionStorage.clear();
                window.location.href = '/Scrap/views/auth/login.php?logout=1';
            } catch (error) {
                console.error('Logout failed:', error);
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadProfileData();
            
            // Add event listeners for profile editing
            const editBtn = document.getElementById('editProfileBtn');
            const cancelBtn = document.getElementById('cancelEditBtn');
            const editForm = document.getElementById('profileEditForm');
            
            if (editBtn) {
                editBtn.addEventListener('click', () => toggleEditMode(true));
            }
            
            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => toggleEditMode(false));
            }
            
            if (editForm) {
                editForm.addEventListener('submit', handleProfileUpdate);
            }

            // Vehicle edit listeners
            const editVehicleBtn = document.getElementById('editVehicleBtn');
            const cancelVehicleBtn = document.getElementById('cancelVehicleBtn');
            const vehicleForm = document.getElementById('vehicleEditForm');
            
            if (editVehicleBtn) {
                editVehicleBtn.addEventListener('click', () => toggleVehicleEdit(true));
            }
            
            if (cancelVehicleBtn) {
                cancelVehicleBtn.addEventListener('click', () => toggleVehicleEdit(false));
            }
            
            if (vehicleForm) {
                vehicleForm.addEventListener('submit', handleVehicleUpdate);
            }

            // Service areas edit listeners
            const editAreasBtn = document.getElementById('editAreasBtn');
            const cancelAreasBtn = document.getElementById('cancelAreasBtn');
            const areasForm = document.getElementById('areasEditForm');
            
            if (editAreasBtn) {
                editAreasBtn.addEventListener('click', () => toggleAreasEdit(true));
            }
            
            if (cancelAreasBtn) {
                cancelAreasBtn.addEventListener('click', () => toggleAreasEdit(false));
            }
            
            if (areasForm) {
                areasForm.addEventListener('submit', handleAreasUpdate);
            }

            // Drop-off suggestion listeners
            const suggestDropoffBtn = document.getElementById('suggestDropoffBtn');
            if (suggestDropoffBtn) {
                suggestDropoffBtn.addEventListener('click', openSuggestModal);
            }

            const suggestForm = document.getElementById('suggestDropoffForm');
            if (suggestForm) {
                suggestForm.addEventListener('submit', handleDropoffSuggestion);
            }

            // Phone input validation - ensure +254 prefix
            const phoneInput = document.getElementById('suggestDropoffContact');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value;
                    // Always ensure it starts with +254
                    if (!value.startsWith('+254')) {
                        e.target.value = '+254';
                    }
                    // Prevent deletion of +254
                    if (value.length < 4) {
                        e.target.value = '+254';
                    }
                });
                
                phoneInput.addEventListener('keydown', function(e) {
                    // Prevent backspace/delete if cursor is before position 4
                    if ((e.key === 'Backspace' || e.key === 'Delete') && 
                        e.target.selectionStart <= 4) {
                        e.preventDefault();
                    }
                });
            }

            // Check for similar drop-offs as user types
            const nameInput = document.getElementById('suggestDropoffName');
            const latInput = document.getElementById('suggestDropoffLat');
            const lngInput = document.getElementById('suggestDropoffLng');
            
            if (nameInput) {
                nameInput.addEventListener('input', debounce(checkSimilarDropoffs, 500));
            }
            if (latInput && lngInput) {
                latInput.addEventListener('input', debounce(checkSimilarDropoffs, 500));
                lngInput.addEventListener('input', debounce(checkSimilarDropoffs, 500));
            }
        });

        // Drop-off suggestion functions
        function openSuggestModal() {
            document.getElementById('suggestDropoffModal').classList.remove('hidden');
            // Reset phone to +254 if empty
            const phoneInput = document.getElementById('suggestDropoffContact');
            if (!phoneInput.value || phoneInput.value === '') {
                phoneInput.value = '+254';
            }
        }

        function closeSuggestModal() {
            document.getElementById('suggestDropoffModal').classList.add('hidden');
            document.getElementById('suggestDropoffForm').reset();
            document.getElementById('photoPreview').classList.add('hidden');
            document.getElementById('nameWarning').classList.add('hidden');
            document.getElementById('locationWarning').classList.add('hidden');
            // Reset phone to +254
            document.getElementById('suggestDropoffContact').value = '+254';
        }

        function getMyLocation() {
            if (!navigator.geolocation) {
                showToast('Geolocation is not supported by your browser', 'error');
                return;
            }

            // Show loading state
            const addressInput = document.getElementById('suggestDropoffAddress');
            const latInput = document.getElementById('suggestDropoffLat');
            const lngInput = document.getElementById('suggestDropoffLng');
            
            if (!addressInput || !latInput || !lngInput) {
                console.error('Location input fields not found');
                return;
            }
            
            const originalPlaceholder = addressInput.placeholder;
            addressInput.placeholder = 'Getting location...';
            addressInput.disabled = true;

            // Geolocation options for better accuracy
            const options = {
                enableHighAccuracy: true,
                timeout: 10000, // 10 seconds
                maximumAge: 0 // Don't use cached position
            };

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    console.log('Location obtained:', { lat, lng, accuracy: position.coords.accuracy });
                    
                    // Fill in coordinates
                    latInput.value = lat.toFixed(8);
                    lngInput.value = lng.toFixed(8);
                    
                    // Set a default address with coordinates
                    addressInput.value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                    addressInput.placeholder = 'Fetching address...';
                    
                    // Try to get address from coordinates using our server-side proxy
                    try {
                        const response = await fetch(
                            `/Scrap/api/geocode.php?lat=${lat}&lng=${lng}`,
                            { credentials: 'include' }
                        );
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (data.status === 'success' && data.address) {
                                addressInput.value = data.address;
                                if (!data.fallback) {
                                    showToast('Address retrieved successfully!', 'success');
                                } else {
                                    showToast('Location captured! (Address unavailable)', 'success');
                                }
                            }
                        } else {
                            console.warn('Geocoding response not OK:', response.status);
                        }
                    } catch (error) {
                        console.log('Reverse geocoding unavailable, using coordinates only', error);
                        // Keep the coordinate-based address already set
                    }
                    
                    addressInput.placeholder = originalPlaceholder;
                    addressInput.disabled = false;
                    
                    // Trigger similarity check
                    checkSimilarDropoffs();
                    
                    // Only show success toast if we haven't already shown one
                    if (!addressInput.value.includes('Lat:')) {
                        showToast('Location captured successfully!', 'success');
                    }
                },
                (error) => {
                    addressInput.placeholder = originalPlaceholder;
                    addressInput.disabled = false;
                    
                    console.error('Geolocation error:', error);
                    
                    let errorMessage = 'Unable to get your location';
                    let helpText = '';
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'Location access denied';
                            helpText = 'Please enable location permissions in your browser settings and try again.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'Location information unavailable';
                            helpText = 'Your device could not determine your location. Try moving to an area with better signal.';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'Location request timed out';
                            helpText = 'The request took too long. Please try again.';
                            break;
                        default:
                            errorMessage = 'Unable to get location';
                            helpText = 'An unknown error occurred. Error code: ' + error.code;
                    }
                    
                    showToast(errorMessage + '. ' + helpText, 'error');
                },
                options
            );
        }

        function previewDropoffPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('photoPreview').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        function removeDropoffPhoto() {
            document.getElementById('dropoffPhoto').value = '';
            document.getElementById('photoPreview').classList.add('hidden');
        }

        async function checkSimilarDropoffs() {
            const name = document.getElementById('suggestDropoffName').value;
            const lat = document.getElementById('suggestDropoffLat').value;
            const lng = document.getElementById('suggestDropoffLng').value;

            if (!name && (!lat || !lng)) return;

            try {
                const params = new URLSearchParams();
                if (name) params.append('name', name);
                if (lat && lng) {
                    params.append('lat', lat);
                    params.append('lng', lng);
                }

                const response = await fetch(`/Scrap/api/collectors/check_dropoff_similarity.php?${params}`, {
                    credentials: 'include'
                });
                const data = await response.json();

                if (data.status === 'success') {
                    // Show warnings if similar dropoffs found
                    if (data.similar_name && name) {
                        document.getElementById('nameWarning').classList.remove('hidden');
                    } else {
                        document.getElementById('nameWarning').classList.add('hidden');
                    }

                    if (data.similar_location && lat && lng) {
                        document.getElementById('locationWarning').classList.remove('hidden');
                    } else {
                        document.getElementById('locationWarning').classList.add('hidden');
                    }
                }
            } catch (error) {
                console.error('Error checking similar dropoffs:', error);
            }
        }

        async function handleDropoffSuggestion(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            
            // Get selected materials
            const materials = Array.from(document.querySelectorAll('input[name="materials[]"]:checked'))
                .map(cb => cb.value);
            
            if (materials.length === 0) {
                showToast('Please select at least one material type', 'error');
                return;
            }

            // Remove old materials[] entries and add new one
            formData.delete('materials[]');
            formData.append('materials', materials.join(','));

            try {
                const response = await fetch('/Scrap/api/collectors/suggest_dropoff.php', {
                    method: 'POST',
                    credentials: 'include',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showToast(data.message || 'Drop-off point added successfully!', 'success');
                    closeSuggestModal();
                } else {
                    throw new Error(data.message || 'Failed to add drop-off point');
                }
            } catch (error) {
                console.error('Add dropoff error:', error);
                showToast(error.message || 'Failed to add drop-off point', 'error');
            }
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>
</body>
</html>