<?php
// Get current page filename for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Fixed Sidebar -->
<aside class="fixed left-0 top-0 h-screen w-64 bg-white dark:bg-slate-900 shadow-lg z-40 flex flex-col">
    <div class="flex-1 overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center space-x-3 mb-8">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Admin Panel</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kiambu Recycling</p>
                </div>
            </div>

            <nav class="space-y-1">
                <a href="/Scrap/public/admin/dashboard.php" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all <?php echo $current_page === 'dashboard.php' ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="/Scrap/public/admin/collectors.php" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all <?php echo $current_page === 'collectors.php' ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="font-medium">Collectors</span>
                </a>

                <a href="/Scrap/public/admin/requests.php" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all <?php echo $current_page === 'requests.php' ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="font-medium">Requests</span>
                </a>

                <a href="/Scrap/public/admin/dropoffs.php" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all <?php echo $current_page === 'dropoffs.php' ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="font-medium">Drop-off Points</span>
                </a>

                <a href="/Scrap/public/admin/rewards.php" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all <?php echo $current_page === 'rewards.php' ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                    <span class="font-medium">Rewards</span>
                </a>

                <a href="/Scrap/public/admin/reports.php" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all <?php echo $current_page === 'reports.php' ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="font-medium">Reports</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- User Section - Fixed at Bottom -->
    <div class="p-6 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
        <div class="flex items-center space-x-3 px-4 py-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                <span class="text-white font-semibold text-sm"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800 dark:text-white truncate">
                    <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Administrator</p>
            </div>
        </div>
        <button onclick="handleLogout()" 
           class="w-full flex items-center space-x-3 px-4 py-3 mt-2 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            <span class="font-medium">Logout</span>
        </button>
        
        <script>
        async function handleLogout() {
            try {
                const response = await fetch('/Scrap/api/logout.php', {
                    method: 'POST',
                    credentials: 'include'
                });
                
                // Clear all session storage
                sessionStorage.clear();
                
                if (response.ok) {
                    window.location.href = '/Scrap/index.php';
                } else {
                    console.error('Logout failed');
                    // Still redirect to index page
                    window.location.href = '/Scrap/index.php';
                }
            } catch (error) {
                console.error('Error during logout:', error);
                // Clear session storage even on error
                sessionStorage.clear();
                // Still redirect to index page
                window.location.href = '/Scrap/index.php';
            }
        }
        </script>
    </div>
</aside>
