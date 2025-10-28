<?php
$pageTitle = 'Collector Requests - Kiambu Recycling & Scraps';
$requireAuth = true;
$extraHead = <<<HTML
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .skeleton { position: relative; overflow: hidden; background: linear-gradient(110deg,#f4f4f5 8%,#e4e4e7 18%,#f4f4f5 33%); background-size:200% 100%; animation: shine 1.1s linear infinite; }
        @keyframes shine { to { background-position-x: -200%; } }
    </style>
HTML;
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="min-h-screen bg-gray-50 dark:bg-slate-900 dark:text-slate-100">
  <div class="max-w-6xl mx-auto p-4 md:p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-semibold">Collection Requests</h1>
      <div class="flex items-center gap-2">
        <button id="refreshBtn" class="px-3 py-1 bg-slate-100 dark:bg-slate-700 rounded">Refresh</button>
        <a href="/collectors/dashboard.php" class="text-sm text-green-600 dark:text-green-300">Back to Dashboard</a>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-4">
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-medium">Pending Requests</h2>
            <span id="pendingCount" class="text-sm text-amber-600">0</span>
          </div>
          <div id="pendingList" class="space-y-3 min-h-[6rem]">
            <div class="skeleton h-12 rounded"></div>
            <div class="skeleton h-12 rounded"></div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-4">
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-medium">Active Requests</h2>
            <span id="activeCount" class="text-sm text-green-600">0</span>
          </div>
          <div id="activeList" class="space-y-3 min-h-[6rem]">
            <div class="skeleton h-12 rounded"></div>
            <div class="skeleton h-12 rounded"></div>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-4">
          <h3 class="font-medium mb-2">Map</h3>
          <div id="map" class="h-72 rounded"></div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-4">
          <h3 class="font-medium mb-2">Filters</h3>
          <select id="materialFilter" class="w-full rounded border-gray-200 dark:border-slate-700 p-2">
            <option value="">All materials</option>
            <option value="plastic">Plastic</option>
            <option value="paper">Paper</option>
            <option value="metal">Metal</option>
            <option value="glass">Glass</option>
            <option value="electronics">Electronics</option>
          </select>
        </div>
      </div>
    </div>

    <div id="toastContainer" class="fixed top-4 right-4 space-y-2 z-50"></div>
  </div>
</div>

<script type="module" src="/Scrap/public/js/collector-requests.js"></script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
