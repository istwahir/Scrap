<?php
$requireAuth = true;
$pageTitle = 'Dashboard - Kiambu Recycling & Scraps';
include 'includes/header.php';
?>

<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
</style>
    <!-- Navigation -->
    <nav class="bg-green-600 text-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="font-bold text-xl">Kiambu Recycling</a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="/Scrap/map.php" class="hover:text-green-200">Map</a>
                    <a href="/Scrap/request.php" class="hover:text-green-200">New Request</a>
                    <a href="/Scrap/reward.php" class="hover:text-green-200">Rewards</a>
                    <button id="notificationBtn" class="relative hover:text-green-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span id="notificationCount" class="hidden absolute -top-1 -right-1 bg-red-500 rounded-full w-4 h-4 text-xs flex items-center justify-center">0</span>
                    </button>
                    <button id="profileBtn" class="flex items-center hover:text-green-200">
                        <span id="userName">Loading...</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Points</p>
                        <h3 id="totalPoints" class="text-2xl font-bold text-green-600">0</h3>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Active Requests</p>
                        <h3 id="activeRequests" class="text-2xl font-bold text-blue-600">0</h3>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Completed Pickups</p>
                        <h3 id="completedPickups" class="text-2xl font-bold text-purple-600">0</h3>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Impact Score</p>
                        <h3 id="impactScore" class="text-2xl font-bold text-yellow-600">0</h3>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Requests -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Recent Requests</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3">ID</th>
                                    <th class="text-left py-3">Date</th>
                                    <th class="text-left py-3">Materials</th>
                                    <th class="text-left py-3">Status</th>
                                    <th class="text-left py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="recentRequests">
                                <tr>
                                    <td colspan="5" class="text-center py-4">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                    <div class="space-y-4">
                        <a href="/Scrap/request.php" class="block w-full py-2 px-4 bg-green-600 text-white rounded-lg text-center hover:bg-green-700 transition duration-200">
                            New Request
                        </a>
                        <a href="/Scrap/map.php" class="block w-full py-2 px-4 bg-blue-600 text-white rounded-lg text-center hover:bg-blue-700 transition duration-200">
                            Find Drop-off Points
                        </a>
                        <a href="/Scrap/reward.php" class="block w-full py-2 px-4 bg-purple-600 text-white rounded-lg text-center hover:bg-purple-700 transition duration-200">
                            Redeem Points
                        </a>
                        <a href="/Scrap/guide.php" class="block w-full py-2 px-4 bg-yellow-600 text-white rounded-lg text-center hover:bg-yellow-700 transition duration-200">
                            Recycling Guide
                        </a>
                    </div>
                </div>

                <!-- Environmental Impact -->
                <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                    <h2 class="text-lg font-semibold mb-4">Environmental Impact</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">CO₂ Reduced</p>
                            <div class="flex items-center mt-1">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div id="co2Progress" class="bg-green-500 rounded-full h-2" style="width: 0%"></div>
                                </div>
                                <span id="co2Amount" class="ml-2 text-sm font-medium">0 kg</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Trees Saved</p>
                            <div class="flex items-center mt-1">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div id="treesProgress" class="bg-green-500 rounded-full h-2" style="width: 0%"></div>
                                </div>
                                <span id="treesAmount" class="ml-2 text-sm font-medium">0</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Water Saved</p>
                            <div class="flex items-center mt-1">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div id="waterProgress" class="bg-green-500 rounded-full h-2" style="width: 0%"></div>
                                </div>
                                <span id="waterAmount" class="ml-2 text-sm font-medium">0 L</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Profile Menu -->
    <div id="profileMenu" class="hidden absolute right-4 mt-2 w-48 bg-white rounded-md shadow-lg py-1">
        <a href="/Scrap/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile Settings</a>
        <a href="/Scrap/history.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Request History</a>
        <a href="/Scrap/reward.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Rewards</a>
        <hr class="my-1">
        <button onclick="logout()" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
            Logout
        </button>
    </div>

    <!-- Notification Panel -->
    <div id="notificationPanel" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
        <div class="absolute right-4 top-20 w-96 bg-white rounded-lg shadow-lg">
            <div class="p-4 border-b">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Notifications</h3>
                    <button onclick="closeNotifications()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="notificationList" class="max-h-96 overflow-y-auto p-4">
                <!-- Notifications will be inserted here -->
            </div>
        </div>
    </div>

    <script>
        // Check authentication
        if (!sessionStorage.getItem('user_id')) {
            window.location.href = '/Scrap/login.php';
        }

        // Load user data
        async function loadUserData() {
            try {
                const response = await fetch('/Scrap/api/user_profile.php');
                const data = await response.json();
                
                if (data.status === 'success') {
                    document.getElementById('userName').textContent = data.user.name || data.user.phone;
                    document.getElementById('totalPoints').textContent = data.user.points;
                    updateEnvironmentalImpact(data.user.impact);
                }
            } catch (error) {
                console.error('Failed to load user data:', error);
            }
        }

        // Load recent requests
        async function loadRecentRequests() {
            try {
                const response = await fetch('/Scrap/api/get_requests.php');
                const data = await response.json();
                
                if (data.status === 'success') {
                    const tbody = document.getElementById('recentRequests');
                    tbody.innerHTML = '';
                    
                    data.requests.forEach(request => {
                        tbody.innerHTML += `
                            <tr class="border-b">
                                <td class="py-3">#${request.id}</td>
                                <td class="py-3">${new Date(request.created_at).toLocaleDateString()}</td>
                                <td class="py-3">${request.materials.join(', ')}</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 rounded-full text-xs ${getStatusColor(request.status)}">
                                        ${request.status}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <button onclick="viewRequest(${request.id})" 
                                            class="text-blue-600 hover:text-blue-800">
                                        View
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
            } catch (error) {
                console.error('Failed to load recent requests:', error);
            }
        }

        // Update environmental impact stats
        function updateEnvironmentalImpact(impact) {
            document.getElementById('co2Progress').style.width = `${impact.co2_percentage}%`;
            document.getElementById('co2Amount').textContent = `${impact.co2_reduced} kg`;
            
            document.getElementById('treesProgress').style.width = `${impact.trees_percentage}%`;
            document.getElementById('treesAmount').textContent = impact.trees_saved;
            
            document.getElementById('waterProgress').style.width = `${impact.water_percentage}%`;
            document.getElementById('waterAmount').textContent = `${impact.water_saved} L`;
        }

        // Get status color class
        function getStatusColor(status) {
            const colors = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'assigned': 'bg-blue-100 text-blue-800',
                'en_route': 'bg-purple-100 text-purple-800',
                'completed': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }

        // Toggle profile menu
        document.getElementById('profileBtn').addEventListener('click', () => {
            const menu = document.getElementById('profileMenu');
            menu.classList.toggle('hidden');
        });

        // Toggle notifications panel
        document.getElementById('notificationBtn').addEventListener('click', () => {
            const panel = document.getElementById('notificationPanel');
            panel.classList.toggle('hidden');
            loadNotifications();
        });

        // Close notifications
        function closeNotifications() {
            document.getElementById('notificationPanel').classList.add('hidden');
        }

        // Load notifications
        async function loadNotifications() {
            try {
                const response = await fetch('/Scrap/api/get_notifications.php');
                const data = await response.json();
                
                if (data.status === 'success') {
                    const list = document.getElementById('notificationList');
                    list.innerHTML = '';
                    
                    data.notifications.forEach(notification => {
                        list.innerHTML += `
                            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                <p class="font-medium">${notification.title}</p>
                                <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    ${new Date(notification.created_at).toLocaleString()}
                                </p>
                            </div>
                        `;
                    });

                    // Update notification count
                    const count = data.notifications.filter(n => !n.read).length;
                    const badge = document.getElementById('notificationCount');
                    if (count > 0) {
                        badge.textContent = count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            } catch (error) {
                console.error('Failed to load notifications:', error);
            }
        }

        // Logout function
        function logout() {
            fetch('/Scrap/logout.php')
                .then(() => {
                    sessionStorage.clear();
                    window.location.href = '/Scrap/login.php';
                })
                .catch(error => console.error('Logout failed:', error));
        }

        // View request details
        function viewRequest(id) {
            window.location.href = `/Scrap/request.php?id=${id}`;
        }

        // Initialize
        loadUserData();
        loadRecentRequests();
        loadNotifications();

        // Setup WebSocket for real-time updates
        if ('WebSocket' in window) {
            const ws = new WebSocket('ws://localhost:8080');
            
            ws.onmessage = function(event) {
                const data = JSON.parse(event.data);
                
                if (data.type === 'request_update') {
                    loadRecentRequests();
                } else if (data.type === 'notification') {
                    loadNotifications();
                }
            };
        }
    </script>

<?php include 'includes/footer.php'; ?>