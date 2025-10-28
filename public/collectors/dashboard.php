<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Collector Dashboard - Kiambu Recycling & Scraps</title>
    <meta name="color-scheme" content="light dark" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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
</head>
<body class="h-full bg-gray-50 dark:bg-slate-900 dark:text-slate-100 antialiased">
<div class="min-h-screen flex">
    <aside class="w-64 bg-white dark:bg-slate-800 border-r border-gray-200 dark:border-slate-700 flex flex-col">
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
            <ul class="space-y-1 text-sm" id="navLinks">
                <li><a href="#overview" class="flex items-center px-3 py-2 rounded-md bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300">Overview</a></li>
                <li><a href="#requests" class="flex items-center px-3 py-2 rounded-md text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60">Requests <span id="pendingBadge" class="ml-auto hidden bg-amber-500/20 text-amber-700 dark:text-amber-300 text-[10px] px-2 py-0.5 rounded-full">0</span></a></li>
                <li><a href="#history" class="flex items-center px-3 py-2 rounded-md text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60">History</a></li>
                <li><a href="#earnings" class="flex items-center px-3 py-2 rounded-md text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60">Earnings</a></li>
            </ul>
        </nav>
        <div class="p-4 text-[10px] text-slate-400 dark:text-slate-500 border-t border-gray-200 dark:border-slate-700">&copy; <span id="year"></span> Scrap Platform</div>
    </aside>
            </body>
            </html>
            <div class="flex items-center space-x-4 text-sm">
                <a href="/collectors/profile.html" class="text-gray-700 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white">Profile</a>
                <button id="logoutBtn" class="text-gray-700 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white">Logout</button>
            </div>
        </header>
        <main class="flex-1 overflow-auto">
            <div class="p-4 md:p-6 space-y-6">
                <section id="overview" class="space-y-6">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4 md:p-6">
                            <p class="text-xs md:text-sm text-gray-500 dark:text-slate-400">Today's Collections</p>
                            <h3 id="todayCollections" class="mt-1 text-xl md:text-2xl font-bold text-gray-900 dark:text-white">0</h3>
                        </div>
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4 md:p-6">
                            <p class="text-xs md:text-sm text-gray-500 dark:text-slate-400">Today's Earnings</p>
                            <h3 id="todayEarnings" class="mt-1 text-xl md:text-2xl font-bold text-gray-900 dark:text-white">KES 0</h3>
                        </div>
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4 md:p-6">
                            <p class="text-xs md:text-sm text-gray-500 dark:text-slate-400">Rating</p>
                            <h3 id="rating" class="mt-1 text-xl md:text-2xl font-bold text-gray-900 dark:text-white">0.0</h3>
                        </div>
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4 md:p-6">
                            <p class="text-xs md:text-sm text-gray-500 dark:text-slate-400">Total Weight Today</p>
                            <h3 id="totalWeight" class="mt-1 text-xl md:text-2xl font-bold text-gray-900 dark:text-white">0 kg</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-base md:text-lg font-semibold">Collection Route</h2>
                                    <div class="flex items-center gap-2">
                                        <button id="fitActiveBtn" class="px-2 py-1.5 text-[11px] bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded">Fit Active</button>
                                        <button id="locateMeBtn" class="px-2 py-1.5 text-[11px] bg-green-600 text-white hover:bg-green-700 rounded">My Location</button>
                                    </div>
                                </div>
                                <div id="map" class="h-80 md:h-96 rounded-lg overflow-hidden"></div>
                            </div>
                            <div id="activeRoutePanel" class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4 hidden">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-medium text-sm">Active Route Details</h3>
                                    <button id="clearRouteBtn" class="text-[11px] text-red-600 hover:underline">Clear</button>
                                </div>
                                <div id="routeDetails" class="text-xs space-y-1 text-slate-600 dark:text-slate-300"></div>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4 h-fit">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-base md:text-lg font-semibold">Active Requests</h2>
                                <button id="refreshBtn" class="text-[11px] px-2 py-1 rounded bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600">Refresh</button>
                            </div>
                            <div id="activeRequests" class="space-y-4 min-h-[4rem]">
                                <div class="skeleton h-10 rounded"></div>
                                <div class="skeleton h-10 rounded"></div>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="requests" class="hidden">
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm">
                        <div class="p-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
                            <h2 class="text-base md:text-lg font-semibold">Collection Requests</h2>
                            <div class="flex gap-2 items-center text-[11px]">
                                <select id="requestFilter" class="border-gray-300 dark:border-slate-600 dark:bg-slate-700 rounded px-2 py-1">
                                    <option value="all">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="accepted">Accepted</option>
                                </select>
                                <button id="reloadRequests" class="px-2 py-1 rounded bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600">Reload</button>
                            </div>
                        </div>
                        <div class="p-4">
                            <div id="requestsList" class="divide-y divide-gray-200 dark:divide-slate-700 min-h-[4rem]">
                                <div class="space-y-3" id="requestsSkeleton">
                                    <div class="skeleton h-12 rounded"></div>
                                    <div class="skeleton h-12 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="history" class="hidden">
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm">
                        <div class="p-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
                            <h2 class="text-base md:text-lg font-semibold">Collection History</h2>
                            <button id="reloadHistory" class="text-[11px] px-2 py-1 rounded bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600">Reload</button>
                        </div>
                        <div class="p-4">
                            <div id="historyList" class="divide-y divide-gray-200 dark:divide-slate-700 min-h-[4rem]">
                                <div class="space-y-3" id="historySkeleton">
                                    <div class="skeleton h-10 rounded"></div>
                                    <div class="skeleton h-10 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="earnings" class="hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4">
                            <h2 class="text-base md:text-lg font-semibold mb-4">Earnings Trend</h2>
                            <div class="relative h-72"><canvas id="earningsChart"></canvas></div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4">
                            <h2 class="text-base md:text-lg font-semibold mb-4">Materials Breakdown</h2>
                            <div class="relative h-72"><canvas id="materialsChart"></canvas></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
                <section id="analytics" class="hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4 lg:col-span-2">
                            <h2 class="text-base md:text-lg font-semibold mb-4">Request Volume (Last 14 Days)</h2>
                            <div class="relative h-72"><canvas id="requestsTrendChart"></canvas></div>
                        </div>
                        <div class="space-y-6">
                            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4">
                                <h2 class="text-sm font-semibold mb-3">Status Distribution</h2>
                                <div class="relative h-56"><canvas id="statusPieChart"></canvas></div>
                            </div>
                            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4">
                                <h2 class="text-sm font-semibold mb-3">Materials (Last 30 Days)</h2>
                                <div class="relative h-56"><canvas id="materialsBarChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>
<script src="/Scrap/public/js/collector-tracker.js"></script>
<script type="module" src="/Scrap/public/js/collector-dashboard.js"></script>
<div id="toastContainer" class="fixed top-4 right-4 space-y-2 z-50"></div>
</div>
<?php 
// Pass extra scripts placeholder if needed later
$extraScripts = '';
?>