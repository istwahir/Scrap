<?php
// Determine active page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="w-64 min-h-screen h-full bg-white dark:bg-slate-800 border-r border-gray-200 dark:border-slate-700 flex flex-col fixed top-0 left-0 z-30">
    <div class="h-16 bg-green-600 flex items-center justify-between px-4">
        <h1 class="text-white font-bold text-sm leading-tight">Collector<br/>Dashboard</h1>
        <button id="themeToggle" class="text-white/80 hover:text-white text-xs border border-white/30 rounded px-2 py-1">Dark</button>
    </div>
    <div class="p-4 border-b border-gray-200 dark:border-slate-700 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Status</label>
            <select id="statusSelect" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                <option value="online">Available</option>
                <option value="on_job">On Job</option>
                <option value="offline">Offline</option>
            </select>
        </div>
        <div class="flex items-center justify-between text-xs">
            <span class="font-medium text-gray-700 dark:text-slate-300">Location</span>
            <span id="locationStatus" class="text-green-600 dark:text-green-400">Active</span>
        </div>
    </div>
    <nav class="p-4 flex-1 overflow-y-auto">
        <ul class="space-y-1 text-sm">
            <li>
                <a href="/Scrap/views/collectors/dashboard.php" class="flex items-center px-3 py-2 rounded-md <?= $current_page === 'dashboard.php' ? 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60' ?>">
                    Overview
                </a>
            </li>
            <li>
                <a href="/Scrap/views/collectors/requests.php" class="flex items-center px-3 py-2 rounded-md <?= $current_page === 'requests.php' ? 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60' ?>">
                    Requests
                </a>
            </li>
            <li>
                <a href="/Scrap/views/collectors/earnings.php" class="flex items-center px-3 py-2 rounded-md <?= $current_page === 'earnings.php' ? 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60' ?>">
                    Earnings
                </a>
            </li>
            <li>
                <a href="/Scrap/views/collectors/dropoff_points.php" class="flex items-center px-3 py-2 rounded-md <?= $current_page === 'dropoff_points.php' ? 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60' ?>">
                    My Drop-off Points
                </a>
            </li>
            <li>
                <a href="/Scrap/views/collectors/profile.php" class="flex items-center px-3 py-2 rounded-md <?= $current_page === 'profile.php' ? 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60' ?>">
                    Profile
                </a>
            </li>
        </ul>
    </nav>
    <div class="p-4 border-t border-gray-200 dark:border-slate-700 mt-auto space-y-2">
        <a href="/Scrap/views/collectors/profile.php" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-100 shadow hover:bg-green-50 dark:hover:bg-green-900/40 border border-gray-200 dark:border-slate-700 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.657 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            <span class="font-medium">Profile</span>
        </a>
        <button id="logoutBtn" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-100 shadow hover:bg-red-50 dark:hover:bg-red-900/40 border border-gray-200 dark:border-slate-700 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" /></svg>
            <span class="font-medium">Logout</span>
        </button>
    </div>
    <div class="p-4 text-[10px] text-slate-400 dark:text-slate-500 border-t border-gray-200 dark:border-slate-700">&copy; <span id="year"></span> Scrap Platform</div>
</aside>

<script>
    // Sidebar functionality - Theme toggle, Status update, Location tracking, Logout
    document.addEventListener('DOMContentLoaded', function() {
        // Year display
        document.getElementById('year').textContent = new Date().getFullYear();
        
        // Toast notification function
        function showStatusToast(message, type = 'info') {
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-green-600',
                error: 'bg-red-600',
                info: 'bg-blue-600'
            };
            toast.className = `fixed top-4 right-4 ${colors[type] || colors.info} text-white px-4 py-3 rounded-lg shadow-lg text-sm z-50 transition-opacity duration-300`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        
        if (savedTheme === 'dark') {
            htmlElement.classList.add('dark');
            themeToggle.textContent = 'Light';
        }
        
        themeToggle.addEventListener('click', function() {
            if (htmlElement.classList.contains('dark')) {
                htmlElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                themeToggle.textContent = 'Dark';
            } else {
                htmlElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                themeToggle.textContent = 'Light';
            }
        });
        
        // Load initial status from server
        const statusSelect = document.getElementById('statusSelect');
        
        // Function to load status from database
        async function loadStatusFromDB() {
            try {
                const response = await fetch('/Scrap/api/collectors/dashboard.php', {
                    credentials: 'include'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.status === 'success' && data.stats && data.stats.active_status) {
                        const dbStatus = data.stats.active_status;
                        statusSelect.value = dbStatus;
                        sessionStorage.setItem('collectorStatus', dbStatus);
                        localStorage.setItem('collectorStatus', dbStatus);
                        
                        // Update location status indicator
                        const locationStatusEl = document.getElementById('locationStatus');
                        if (locationStatusEl) {
                            if (dbStatus === 'offline') {
                                locationStatusEl.textContent = 'Inactive';
                                locationStatusEl.className = 'text-red-600 dark:text-red-400';
                            } else {
                                locationStatusEl.textContent = 'Active';
                                locationStatusEl.className = 'text-green-600 dark:text-green-400';
                            }
                        }
                        
                        console.log('Loaded status from DB:', dbStatus);
                        return dbStatus;
                    }
                }
            } catch (error) {
                console.error('Error loading status from DB:', error);
            }
            
            // Fallback to localStorage, then sessionStorage, or default
            const fallbackStatus = localStorage.getItem('collectorStatus') 
                || sessionStorage.getItem('collectorStatus') 
                || 'online';
            statusSelect.value = fallbackStatus;
            
            // Update location status indicator for fallback
            const locationStatusEl = document.getElementById('locationStatus');
            if (locationStatusEl) {
                if (fallbackStatus === 'offline') {
                    locationStatusEl.textContent = 'Inactive';
                    locationStatusEl.className = 'text-red-600 dark:text-red-400';
                } else {
                    locationStatusEl.textContent = 'Active';
                    locationStatusEl.className = 'text-green-600 dark:text-green-400';
                }
            }
            
            return fallbackStatus;
        }
        
        // Load status on page load
        loadStatusFromDB();
        
        // Status change handler
        statusSelect.addEventListener('change', async function() {
            const selectedStatus = statusSelect.value;
            const previousStatus = sessionStorage.getItem('collectorStatus') || 'online';
            
            // Show loading state
            statusSelect.disabled = true;
            statusSelect.style.opacity = '0.6';
            
            // Function to dispatch status change event
            const dispatchStatusChange = (success) => {
                // Re-enable the select
                statusSelect.disabled = false;
                statusSelect.style.opacity = '1';
                
                if (success) {
                    // Update location status indicator immediately
                    const locationStatusEl = document.getElementById('locationStatus');
                    if (locationStatusEl) {
                        if (selectedStatus === 'offline') {
                            locationStatusEl.textContent = 'Inactive';
                            locationStatusEl.className = 'text-red-600 dark:text-red-400';
                        } else {
                            locationStatusEl.textContent = 'Active';
                            locationStatusEl.className = 'text-green-600 dark:text-green-400';
                        }
                    }
                    
                    // Dispatch custom event for dashboard to listen
                    window.dispatchEvent(new CustomEvent('collectorStatusChanged', {
                        detail: { status: selectedStatus }
                    }));
                    
                    // Update both sessionStorage and localStorage for persistence
                    sessionStorage.setItem('collectorStatus', selectedStatus);
                    localStorage.setItem('collectorStatus', selectedStatus);
                    
                    // Show success feedback
                    showStatusToast('Status updated successfully', 'success');
                } else {
                    // Revert to previous status on failure
                    statusSelect.value = previousStatus;
                    showStatusToast('Failed to update status', 'error');
                }
            };
            
            // Get current location first if available
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        try {
                            const response = await fetch('/Scrap/api/collectors/update_location.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                credentials: 'include',
                                body: JSON.stringify({ 
                                    status: selectedStatus,
                                    latitude: position.coords.latitude,
                                    longitude: position.coords.longitude
                                })
                            });
                            if (!response.ok) {
                                throw new Error('Failed to update status');
                            }
                            dispatchStatusChange(true);
                        } catch (error) {
                            console.error('Error updating status:', error);
                            dispatchStatusChange(false);
                        }
                    },
                    async (error) => {
                        console.warn('Could not get location, updating status only:', error.message);
                        // Status update without location
                        try {
                            const response = await fetch('/Scrap/api/collectors/update_location.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                credentials: 'include',
                                body: JSON.stringify({ status: selectedStatus })
                            });
                            if (!response.ok) {
                                throw new Error('Failed to update status');
                            }
                            dispatchStatusChange(true);
                        } catch (error) {
                            console.error('Error updating status without location:', error);
                            dispatchStatusChange(false);
                        }
                    },
                    {
                        enableHighAccuracy: false,
                        timeout: 15000,
                        maximumAge: 60000
                    }
                );
            } else {
                // No geolocation support - update status only
                try {
                    const response = await fetch('/Scrap/api/collectors/update_location.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'include',
                        body: JSON.stringify({ status: selectedStatus })
                    });
                    if (!response.ok) {
                        throw new Error('Failed to update status');
                    }
                    dispatchStatusChange(true);
                } catch (error) {
                    console.error('Error updating status:', error);
                    dispatchStatusChange(false);
                }
            }
        });
        
        // Logout confirmation modal functions
        function showLogoutModal() {
            const modal = document.createElement('div');
            modal.id = 'logoutModal';
            modal.className = 'fixed inset-0 bg-black/50 dark:bg-black/70 flex items-center justify-center z-50 backdrop-blur-sm';
            modal.innerHTML = `
                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl p-6 max-w-sm mx-4 transform transition-all">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Confirm Logout</h3>
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-slate-300 mb-6">Are you sure you want to logout?</p>
                    <div class="flex gap-3 justify-end">
                        <button id="cancelLogout" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors font-medium">
                            Cancel
                        </button>
                        <button id="confirmLogout" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors font-medium">
                            Logout
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Add click handlers
            document.getElementById('cancelLogout').addEventListener('click', () => {
                modal.remove();
            });
            
            document.getElementById('confirmLogout').addEventListener('click', async () => {
                modal.remove();
                await performLogout();
            });
            
            // Close on background click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
            
            // Close on Escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape') {
                    modal.remove();
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);
        }
        
        async function performLogout() {
            try {
                // Show loading toast
                showStatusToast('Logging out...', 'info');
                
                const response = await fetch('/Scrap/api/logout.php', {
                    method: 'POST',
                    credentials: 'include'
                });
                
                if (response.ok) {
                    // Clear stored data
                    sessionStorage.clear();
                    localStorage.removeItem('collectorStatus');
                    
                    // Redirect to login
                    window.location.href = '/Scrap/views/auth/login.php';
                } else {
                    throw new Error('Logout request failed');
                }
            } catch (error) {
                console.error('Logout error:', error);
                showStatusToast('Failed to logout. Please try again.', 'error');
            }
        }
        
        // Logout handler
        document.getElementById('logoutBtn').addEventListener('click', function() {
            showLogoutModal();
        });
        
        // Location tracking
        if ('geolocation' in navigator) {
            navigator.geolocation.watchPosition(
                async (position) => {
                    try {
                        const response = await fetch('/Scrap/api/collectors/update_location.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            credentials: 'include',
                            body: JSON.stringify({
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude
                            })
                        });
                        if (response.ok) {
                            document.getElementById('locationStatus').textContent = 'Active';
                            document.getElementById('locationStatus').className = 'text-green-600 dark:text-green-400';
                        }
                    } catch (error) {
                        // Silently log location update errors
                        console.debug('Location update skipped:', error.message);
                    }
                },
                (error) => {
                    console.debug('Geolocation error:', error.message);
                    document.getElementById('locationStatus').textContent = 'Inactive';
                    document.getElementById('locationStatus').className = 'text-red-600 dark:text-red-400';
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        }
    });
</script>
