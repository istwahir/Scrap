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
                <a href="/Scrap/public/collectors/dashboard.php" class="flex items-center px-3 py-2 rounded-md <?= $current_page === 'dashboard.php' ? 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60' ?>">
                    Overview
                </a>
            </li>
            <li>
                <a href="/Scrap/public/collectors/requests.php" class="flex items-center px-3 py-2 rounded-md <?= $current_page === 'requests.php' ? 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60' ?>">
                    Requests
                </a>
            </li>
            <li>
                <a href="/Scrap/public/collectors/earnings.php" class="flex items-center px-3 py-2 rounded-md <?= $current_page === 'earnings.php' ? 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60' ?>">
                    Earnings
                </a>
            </li>
            <li>
                <a href="/Scrap/public/collectors/profile.php" class="flex items-center px-3 py-2 rounded-md <?= $current_page === 'profile.php' ? 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60' ?>">
                    Profile
                </a>
            </li>
        </ul>
    </nav>
    <div class="p-4 border-t border-gray-200 dark:border-slate-700 mt-auto space-y-2">
        <a href="/Scrap/public/collectors/profile.php" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-100 shadow hover:bg-green-50 dark:hover:bg-green-900/40 border border-gray-200 dark:border-slate-700 transition-all">
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
        
        // Status change handler
        const statusSelect = document.getElementById('statusSelect');
        statusSelect.addEventListener('change', async function() {
            try {
                const response = await fetch('/Scrap/api/collectors/update_location.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ status: this.value })
                });
                if (!response.ok) {
                    throw new Error('Failed to update status');
                }
            } catch (error) {
                console.error('Error updating status:', error);
                alert('Failed to update status. Please try again.');
            }
        });
        
        // Logout handler
        document.getElementById('logoutBtn').addEventListener('click', async function() {
            if (!confirm('Are you sure you want to logout?')) return;
            
            try {
                const response = await fetch('/Scrap/api/logout.php', {
                    method: 'POST',
                    credentials: 'include'
                });
                if (response.ok) {
                    window.location.href = '/Scrap/login.php';
                }
            } catch (error) {
                console.error('Logout error:', error);
                alert('Failed to logout. Please try again.');
            }
        });
        
        // Location tracking
        if ('geolocation' in navigator) {
            navigator.geolocation.watchPosition(
                async (position) => {
                    try {
                        await fetch('/Scrap/api/collectors/update_location.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            credentials: 'include',
                            body: JSON.stringify({
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude
                            })
                        });
                        document.getElementById('locationStatus').textContent = 'Active';
                        document.getElementById('locationStatus').className = 'text-green-600 dark:text-green-400';
                    } catch (error) {
                        console.error('Error updating location:', error);
                    }
                },
                (error) => {
                    console.error('Geolocation error:', error);
                    document.getElementById('locationStatus').textContent = 'Inactive';
                    document.getElementById('locationStatus').className = 'text-red-600 dark:text-red-400';
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
        }
    });
</script>
