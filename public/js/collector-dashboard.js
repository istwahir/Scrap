// Collector Dashboard Module (ES Module)
// Handles UI interactions, data fetching, charts, map integration, and actions.

/* Contract:
   - Fetch dashboard data from /api/collectors/dashboard.php
   - Provide actions: accept, decline, complete requests
   - Update status via CollectorTracker.updateStatus
   - Provide filtering of requests list
   - Show toasts for success/error
   - Dark mode toggle persisted in localStorage
*/

import './collector-tracker.js'; // ensure tracker class loaded
// Script loaded

const state = {
  data: null,
  charts: {},
  activeMarkers: [],
  dropoffMarkers: [], // Store drop-off point markers
  activeRouteLayer: null,
  tracker: null,
  map: null,
  myLocationMarker: null,
  myLocationWatchId: null,
  loading: false,
  loadingDropoffs: false,
  pendingStatus: null
};

// Elements
const els = {};
function cacheEls() {
  els.navLinks = document.querySelectorAll('#navLinks a');
  els.sections = document.querySelectorAll('main section');
  els.collectorName = document.getElementById('collectorName');
  els.collectorNameHeader = document.getElementById('collectorNameHeader');
  els.globalStatusBadge = document.getElementById('globalStatusBadge');
  els.statusSelect = document.getElementById('statusSelect');
  els.locationStatus = document.getElementById('locationStatus');
  els.todayCollections = document.getElementById('todayCollections');
  els.todayEarnings = document.getElementById('todayEarnings');
  els.rating = document.getElementById('rating');
  els.totalWeight = document.getElementById('totalWeight');
  els.activeRequests = document.getElementById('activeRequests');
  els.requestsList = document.getElementById('requestsList');
  els.historyList = document.getElementById('historyList');
  els.pendingBadge = document.getElementById('pendingBadge');
  els.requestFilter = document.getElementById('requestFilter');
  els.requestsSkeleton = document.getElementById('requestsSkeleton');
  els.historySkeleton = document.getElementById('historySkeleton');
  els.refreshBtn = document.getElementById('refreshBtn');
  els.reloadRequests = document.getElementById('reloadRequests');
  els.reloadHistory = document.getElementById('reloadHistory');
  els.fitActiveBtn = document.getElementById('fitActiveBtn');
  els.locateMeBtn = document.getElementById('locateMeBtn');
  els.clearRouteBtn = document.getElementById('clearRouteBtn');
  els.activeRoutePanel = document.getElementById('activeRoutePanel');
  els.routeDetails = document.getElementById('routeDetails');
  els.logoutBtn = document.getElementById('logoutBtn');
  els.themeToggle = document.getElementById('themeToggle');
  els.toastContainer = document.getElementById('toastContainer');
  els.vehicleType = document.getElementById('vehicleType');
  els.vehicleReg = document.getElementById('vehicleReg');
  els.materialsList = document.getElementById('materialsList');
  els.areasList = document.getElementById('areasList');
  els.earningsChart = document.getElementById('earningsChart');
  els.materialsChart = document.getElementById('materialsChart');
}

// Initialization
function initMap() {
  state.map = L.map('map').setView([-1.1712, 36.8356], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(state.map);
  // Load drop-offs first, then start live tracking to avoid any initial contention
  loadDropoffPoints().finally(() => {
    state.tracker = new CollectorTracker(state.map);
    state.tracker.startTracking();
    // Apply any pending status once tracker is ready
    const desired = state.pendingStatus || sessionStorage.getItem('collectorStatus') || 'online';
    updateStatus(desired);
  });
}

function initNavigation() {
  els.navLinks.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const id = link.getAttribute('href').substring(1);
      showSection(id);
    });
  });
}

function showSection(id) {
  els.sections.forEach(s => s.classList.add('hidden'));
  const target = document.getElementById(id);
  if (target) target.classList.remove('hidden');
  els.navLinks.forEach(l => {
    const match = l.getAttribute('href').substring(1) === id;
    l.classList.toggle('bg-green-50', match);
    l.classList.toggle('text-green-700', match);
    if (match) {
      l.classList.add('dark:bg-green-900/40','dark:text-green-300');
    } else {
      l.classList.remove('dark:bg-green-900/40','dark:text-green-300');
    }
  });
}

// Data Loading
async function loadDashboardData({ silent=false } = {}) {
  if (state.loading) return;
  state.loading = true;
  try {
    let res = await fetch('/Scrap/api/collectors/dashboard.php', {
      credentials: 'same-origin'
    });
    // If the /Scrap path returns server error or not found, try a fallback without /Scrap
    if (!res.ok) {
      console.warn('Primary dashboard fetch failed', res.status, 'trying fallback /api/collectors/dashboard.php');
      try { res = await fetch('/api/collectors/dashboard.php', { credentials: 'same-origin' }); } catch (e) { /* ignore */ }
    }
    let json;
    try { json = await res.json(); }
    catch (parseErr) {
      const txt = await res.text().catch(()=>'<no body>');
      console.error('Failed to parse JSON from dashboard API', parseErr, txt);
      toast('Invalid server response (see console)', 'error');
      return;
    }
    if (json.status === 'success') {
      state.data = json;
      renderAll();
      loadDropoffPoints(); // Load drop-off points after dashboard data
      if (!silent) toast('Dashboard updated', 'success');
    } else {
      toast(json.message || 'Failed to load', 'error');
    }
  } catch (e) {
    console.error(e);
    toast('Network error loading dashboard', 'error');
  } finally {
    state.loading = false;
  }
}

// Load collector's drop-off points (clean version)
async function loadDropoffPoints() {
  try {
    if (state.loadingDropoffs) { return; }
    state.loadingDropoffs = true;
    if (!state.map) {
      // Map not initialized yet, retry shortly
      setTimeout(loadDropoffPoints, 1000);
      return;
    }

    const url = BASE_DROPOFF_URL();
    const controller = new AbortController();
    const timeoutMs = 8000;
    const timeoutId = setTimeout(() => controller.abort(), timeoutMs);
    let res;
    try {
      res = await fetch(url + '?v=' + Date.now(), { 
        credentials: 'same-origin', 
        signal: controller.signal,
        cache: 'no-store',
        headers: { 'Accept': 'application/json' }
      });
    } catch (err) {
      if (err.name === 'AbortError') {
        console.warn('Drop-off fetch aborted after timeout');
      } else {
        console.warn('Drop-off fetch error:', err);
      }
    } finally { clearTimeout(timeoutId); }

    if (!res) {
      toast('Drop-off points fetch timed out', 'warning');
      return;
    }
    const contentType = res.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
      toast('Auth/session issue loading drop-off points', 'warning');
      return;
    }
    let json;
    try { json = await res.json(); } catch (parseErr) {
      console.error('Drop-off JSON parse failed', parseErr);
      toast('Invalid drop-off points response', 'error');
      return;
    }

    if (json.status === 'error') {
      console.warn('Drop-off API error:', json.message);
      toast(json.message || 'Drop-off load error', 'warning');
      return;
    }

    if (json.status === 'success' && Array.isArray(json.dropoffs)) {
      plotDropoffPointsOnMap(json.dropoffs);
    } else {
      console.warn('Unexpected drop-off payload shape');
      toast('Unexpected drop-off response', 'warning');
    }
  } catch (e) {
    console.error('Load drop-off points exception:', e);
    toast('Could not load drop-off points', 'error');
  }
  finally {
    state.loadingDropoffs = false;
  }
}

// Quick session ping (diagnostics removed)

// Helper to get base API URL robustly
function BASE_DROPOFF_URL() {
  // If site served under /Scrap keep path, else relative fallback
  const origin = window.location.origin;
  if (window.location.pathname.startsWith('/Scrap')) {
    return origin + '/Scrap/api/collectors/get_dropoff_points.php';
  }
  return origin + '/Scrap/api/collectors/get_dropoff_points.php'; // Force Scrap path to avoid 404
}

// Plot drop-off points on map with custom markers and tooltips
function plotDropoffPointsOnMap(dropoffs) {
  if (!state.map) { console.error('Map not available'); return; }
  // Clear existing markers
  state.dropoffMarkers.forEach(m => state.map.removeLayer(m));
  state.dropoffMarkers = [];
  if (!dropoffs || dropoffs.length === 0) { return; }
  const dropoffIcon = L.divIcon({
    className: 'custom-dropoff-marker',
    html: `<div style="background-color:#10b981;width:30px;height:30px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;">
      <svg style="transform:rotate(45deg);width:16px;height:16px;fill:white" viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg>
    </div>`,
    iconSize: [30,42],
    iconAnchor: [15,42],
    popupAnchor: [0,-42]
  });
  dropoffs.forEach((dropoff, index) => {
    if (!dropoff.lat || !dropoff.lng) { console.warn('Missing coords for', dropoff.name); return; }
    const latlng = [parseFloat(dropoff.lat), parseFloat(dropoff.lng)];
    const tooltipContent = `<div style=\"min-width:200px;\">\n      <h3 style=\"font-weight:600;font-size:14px;margin-bottom:6px;color:#1f2937;\">${dropoff.name}</h3>\n      <p style=\"font-size:12px;color:#6b7280;margin-bottom:4px;\">${dropoff.address}</p>\n      <p style=\"font-size:11px;color:#9ca3af;margin-bottom:6px;\"><strong>Materials:</strong> ${dropoff.materials.join(', ')}</p>\n      ${dropoff.operating_hours ? `<p style=\"font-size:11px;color:#9ca3af;margin-bottom:4px;\"><strong>Hours:</strong> ${dropoff.operating_hours}</p>` : ''}\n      ${dropoff.contact_phone ? `<p style=\"font-size:11px;color:#9ca3af;margin-bottom:4px;\"><strong>Phone:</strong> ${dropoff.contact_phone}</p>` : ''}\n      <p style=\"font-size:11px;margin-top:6px;\">\n        <span style=\"background-color:${dropoff.status==='active'?'#dcfce7':'#f3f4f6'};color:${dropoff.status==='active'?'#166534':'#6b7280'};padding:2px 8px;border-radius:9999px;font-weight:500;\">${dropoff.status==='active'?'✓ Active':'Inactive'}</span>\n        ${dropoff.collection_count>0 ? `<span style=\"margin-left:6px;color:#6b7280;\">${dropoff.collection_count} collection${dropoff.collection_count!==1?'s':''}</span>`:''}\n      </p>\n    </div>`;
    try {
      const marker = L.marker(latlng, { icon: dropoffIcon })
        .bindTooltip(tooltipContent, { permanent:false, direction:'top', offset:[0,-35], className:'custom-dropoff-tooltip' })
        .addTo(state.map);
      state.dropoffMarkers.push(marker);
    } catch(err) { console.error('Marker error', err); }
  });
  // Fit bounds to markers for a better initial view
  if (state.dropoffMarkers.length > 0) {
    const group = L.featureGroup(state.dropoffMarkers);
    state.map.fitBounds(group.getBounds().pad(0.25));
  }
}


// Rendering
function renderAll() {
  const { stats, activeRequests, pendingRequests, history, earnings, vehicle, areas, analytics } = state.data;
  renderStats(stats);
  renderActiveRequests(activeRequests);
  renderRequestsList(pendingRequests);
  renderHistory(history);
  renderEarnings(earnings);
  if (vehicle) renderVehicle(vehicle);
  if (areas) renderAreas(areas);
  if (analytics) renderAnalytics(analytics);
}

function renderStats(stats) {
  els.todayCollections.textContent = stats.today_collections;
  els.todayEarnings.textContent = 'KES ' + stats.today_earnings;
  els.rating.textContent = Number(stats.rating).toFixed(1);
  els.totalWeight.textContent = stats.total_weight + ' kg';
  
  // Sync status from server if available
  if (stats.active_status) {
    const serverStatus = stats.active_status;
    const currentStatus = sessionStorage.getItem('collectorStatus');
    
    // Update UI and sessionStorage if server status differs
    if (serverStatus !== currentStatus) {
      sessionStorage.setItem('collectorStatus', serverStatus);
      if (els.statusSelect) {
        els.statusSelect.value = serverStatus;
      }
    }
    
    // Update location status text
    if (els.locationStatus) {
      els.locationStatus.textContent = serverStatus !== 'offline' ? 'Active' : 'Inactive';
      els.locationStatus.className = serverStatus !== 'offline' 
        ? 'text-green-600 dark:text-green-400' 
        : 'text-red-600 dark:text-red-400';
    }
    
    // Update global status badge with colors
    if (els.globalStatusBadge) {
      const statusText = serverStatus.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
      els.globalStatusBadge.textContent = statusText;
      els.globalStatusBadge.classList.remove('hidden');
      
      // Remove all status color classes
      els.globalStatusBadge.classList.remove(
        'bg-green-100', 'dark:bg-green-900/30', 'text-green-700', 'dark:text-green-300',
        'bg-blue-100', 'dark:bg-blue-900/30', 'text-blue-700', 'dark:text-blue-300',
        'bg-gray-100', 'dark:bg-gray-900/30', 'text-gray-700', 'dark:text-gray-300'
      );
      
      // Add appropriate color classes based on status
      if (serverStatus === 'online') {
        els.globalStatusBadge.classList.add('bg-green-100', 'dark:bg-green-900/30', 'text-green-700', 'dark:text-green-300');
      } else if (serverStatus === 'on_job') {
        els.globalStatusBadge.classList.add('bg-blue-100', 'dark:bg-blue-900/30', 'text-blue-700', 'dark:text-blue-300');
      } else if (serverStatus === 'offline') {
        els.globalStatusBadge.classList.add('bg-gray-100', 'dark:bg-gray-900/30', 'text-gray-700', 'dark:text-gray-300');
      }
    }
  }
  
  if (els.collectorName) els.collectorName.textContent = stats.name;
  if (els.collectorNameHeader) els.collectorNameHeader.textContent = stats.name;
}

function renderActiveRequests(requests) {
  els.activeRequests.innerHTML = '';
  if (!requests.length) {
    els.activeRequests.innerHTML = '<p class="text-gray-500 dark:text-slate-400 text-xs">No active requests</p>';
    return;
  }
  requests.forEach(r => {
    const div = document.createElement('div');
    div.className = 'bg-gray-50 dark:bg-slate-700/40 rounded-lg p-3 text-xs';
    div.innerHTML = `
      <div class="flex justify-between items-start gap-2">
        <div class="min-w-0">
          <h3 class="font-medium truncate">${r.customer_name}</h3>
          <p class="text-gray-600 dark:text-slate-400">${r.material_type}</p>
          <p class="text-gray-600 dark:text-slate-400 truncate">${r.address}</p>
        </div>
        <div class="flex flex-col gap-1">
          <button data-complete="${r.id}" class="px-2 py-1 bg-green-600 text-white rounded text-[11px] hover:bg-green-700">Complete</button>
          <button data-focus="${r.id}" class="px-2 py-1 bg-slate-200 dark:bg-slate-600 rounded text-[11px] hover:bg-slate-300 dark:hover:bg-slate-500">Locate</button>
        </div>
      </div>`;
    els.activeRequests.appendChild(div);
  });
}

function renderRequestsList(requests) {
  if (els.requestsSkeleton) els.requestsSkeleton.remove();
  
  if (!els.requestsList) {
    console.warn('requestsList element not found in DOM');
    return;
  }
  
  const filter = els.requestFilter?.value || 'all';
  const filtered = requests.filter(r => filter === 'all' || (filter === 'pending' && r.status === undefined) || filter === 'accepted' && r.status === 'accepted');
  els.requestsList.innerHTML = '';
  if (!filtered.length) {
    els.requestsList.innerHTML = '<p class="text-gray-500 dark:text-slate-400 text-xs p-2">No requests found</p>';
    if (els.pendingBadge) els.pendingBadge.classList.add('hidden');
    return;
  }
  if (els.pendingBadge) {
    els.pendingBadge.classList.toggle('hidden', !requests.length);
    els.pendingBadge.textContent = requests.length;
  }
  filtered.forEach(r => {
    const row = document.createElement('div');
    row.className = 'py-3 text-xs';
    row.innerHTML = `
      <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2">
        <div class="min-w-0">
          <h3 class="font-medium">${r.customer_name}</h3>
          <p class="text-gray-600 dark:text-slate-400">${r.material_type}</p>
          <p class="text-gray-600 dark:text-slate-400">${r.address}</p>
          <p class="text-gray-500 dark:text-slate-500">Requested: ${r.created_at}</p>
        </div>
        <div class="flex items-center gap-2 md:justify-end">
          <button data-accept="${r.id}" class="px-3 py-1 bg-green-600 text-white rounded text-[11px] hover:bg-green-700">Accept</button>
          <button data-decline="${r.id}" class="px-3 py-1 bg-red-600 text-white rounded text-[11px] hover:bg-red-700">Decline</button>
        </div>
      </div>`;
    els.requestsList.appendChild(row);
  });
}

function renderHistory(history) {
  if (els.historySkeleton) els.historySkeleton.remove();
  
  if (!els.historyList) {
    console.warn('historyList element not found in DOM');
    return;
  }
  
  els.historyList.innerHTML = '';
  if (!history.length) {
    els.historyList.innerHTML = '<p class="text-gray-500 dark:text-slate-400 text-xs p-2">No collection history</p>';
    return;
  }
  history.forEach(item => {
    const div = document.createElement('div');
    div.className = 'py-3 text-xs';
    div.innerHTML = `
      <div class="flex justify-between items-start">
        <div>
          <h3 class="font-medium">${item.customer_name}</h3>
          <p class="text-gray-600 dark:text-slate-400">${item.material_type} - ${item.weight}kg</p>
          <p class="text-gray-600 dark:text-slate-400">${item.address}</p>
          <p class="text-gray-500 dark:text-slate-500">Collected: ${item.completed_at}</p>
        </div>
        <span class="text-green-600 dark:text-green-400 font-medium">KES ${item.amount}</span>
      </div>`;
    els.historyList.appendChild(div);
  });
}

function renderEarnings(earnings) {
  // Trend
  const trendEl = document.getElementById('earningsChart');
  if (trendEl) {
    state.charts.earnings?.destroy?.();
    state.charts.earnings = new Chart(trendEl.getContext('2d'), {
      type: 'line',
      data: { labels: earnings.trend.labels, datasets: [{ label: 'Daily Earnings', data: earnings.trend.values, borderColor: 'rgb(34,197,94)', tension: 0.25, fill: false }] },
      options: { responsive: true, maintainAspectRatio: false, scales: { y: { ticks: { callback: v => 'KES ' + v }}} }
    });
  }
  const matEl = document.getElementById('materialsChart');
  if (matEl) {
    state.charts.materials?.destroy?.();
    state.charts.materials = new Chart(matEl.getContext('2d'), {
      type: 'doughnut',
      data: { labels: earnings.materials.labels, datasets: [{ data: earnings.materials.values, backgroundColor: ['#3b82f6','#eab308','#4b5563','#22c55e','#a855f7'] }] },
      options: { responsive: true, maintainAspectRatio: false }
    });
  }
}

function renderVehicle(vehicle) {
  if (els.vehicleType) els.vehicleType.textContent = vehicle.type || 'N/A';
  if (els.vehicleReg) els.vehicleReg.textContent = vehicle.registration || 'N/A';
  
  if (els.materialsList) {
    els.materialsList.innerHTML = '';
    if (vehicle.materials && vehicle.materials.length) {
      vehicle.materials.forEach(material => {
        const badge = document.createElement('span');
        badge.className = 'px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-slate-200';
        badge.textContent = material;
        els.materialsList.appendChild(badge);
      });
    } else {
      els.materialsList.innerHTML = '<span class="text-xs text-gray-500 dark:text-slate-400">No materials listed</span>';
    }
  }
}

function renderAreas(areas) {
  if (els.areasList) {
    els.areasList.innerHTML = '';
    if (areas && areas.length) {
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
        els.areasList.appendChild(div);
      });
    } else {
      els.areasList.innerHTML = '<span class="text-xs text-gray-500 dark:text-slate-400">No service areas</span>';
    }
  }
}

function renderAnalytics(analytics) {
  // Requests Trend
  const rt = document.getElementById('requestsTrendChart');
  if (rt) {
    state.charts.requestsTrend?.destroy?.();
    state.charts.requestsTrend = new Chart(rt.getContext('2d'), {
      type: 'bar',
      data: { labels: analytics.requests_trend.labels, datasets: [{ label: 'Requests', data: analytics.requests_trend.values, backgroundColor: '#10b981' }] },
      options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true }}}
    });
  }
  // Status Distribution
  const sp = document.getElementById('statusPieChart');
  if (sp) {
    state.charts.statusPie?.destroy?.();
    state.charts.statusPie = new Chart(sp.getContext('2d'), {
      type: 'doughnut',
      data: { labels: analytics.status_distribution.labels, datasets: [{ data: analytics.status_distribution.values, backgroundColor: ['#f59e0b','#3b82f6','#10b981','#64748b','#ef4444'] }] },
      options: { responsive: true, maintainAspectRatio: false }
    });
  }
  // Request Materials Bar
  const mb = document.getElementById('materialsBarChart');
  if (mb) {
    state.charts.materialsBar?.destroy?.();
    state.charts.materialsBar = new Chart(mb.getContext('2d'), {
      type: 'bar',
      data: { labels: analytics.request_materials.labels, datasets: [{ label: 'Requests', data: analytics.request_materials.values, backgroundColor: '#6366f1' }] },
      options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true }}}
    });
  }
}

// Actions
async function postJSON(url, payload) {
  const res = await fetch(url, { 
    method: 'POST', 
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' }, 
    body: JSON.stringify(payload) 
  });
  const json = await res.json().catch(()=>({}));
  if (!res.ok || json.status === 'error') throw new Error(json.message || 'Request failed');
  return json;
}

async function acceptRequest(id) {
  try { await postJSON('/Scrap/api/collectors/accept_request.php', { request_id: id }); toast('Request accepted', 'success'); await loadDashboardData({ silent: true }); } catch(e){ toast(e.message,'error'); }
}
async function declineRequest(id) {
  try { await postJSON('/Scrap/api/collectors/decline_request.php', { request_id: id }); toast('Request declined', 'info'); await loadDashboardData({ silent: true }); } catch(e){ toast(e.message,'error'); }
}
async function completeCollection(id) {
  const weight = prompt('Enter collected weight in kg:');
  if (!weight) return;
  const n = parseFloat(weight);
  if (isNaN(n)|| n<=0) { toast('Invalid weight','error'); return; }
  try { await postJSON('/Scrap/api/collectors/complete_collection.php', { request_id: id, weight: n }); toast('Collection completed','success'); await loadDashboardData({ silent: true }); } catch(e){ toast(e.message,'error'); }
}

// Status handling
async function updateStatus(value) {
  // If tracker not yet initialized, queue the status without erroring
  if (!state.tracker) {
    state.pendingStatus = value;
    sessionStorage.setItem('collectorStatus', value);
    if (els.statusSelect) els.statusSelect.value = value;
    // Update basic UI elements (without server confirmation)
    if (els.locationStatus) {
      els.locationStatus.textContent = value !== 'offline' ? 'Active' : 'Inactive';
      els.locationStatus.className = value !== 'offline'
        ? 'text-green-600 dark:text-green-400'
        : 'text-red-600 dark:text-red-400';
    }
    if (els.globalStatusBadge) {
      const statusText = value.replace('_',' ').replace(/\b\w/g,c=>c.toUpperCase());
      els.globalStatusBadge.textContent = statusText;
      els.globalStatusBadge.classList.remove('hidden');
    }
    return; // Will be applied once tracker is ready
  }
  try {
    await state.tracker.updateStatus(value);
    els.locationStatus.textContent = value !== 'offline' ? 'Active' : 'Inactive';
    els.globalStatusBadge.textContent = value.replace('_',' ').replace(/\b\w/g,c=>c.toUpperCase());
    els.globalStatusBadge.classList.remove('hidden');
    sessionStorage.setItem('collectorStatus', value);
    toast('Status updated','success');
  } catch (e) {
    toast('Status update failed','error');
    els.statusSelect.value = sessionStorage.getItem('collectorStatus') || 'online';
  }
}

// Toasts
function toast(message, type='info') {
  if (!els.toastContainer) return;
  const colors = { success: 'bg-green-600', error: 'bg-red-600', info: 'bg-slate-700', warning: 'bg-amber-600' };
  const div = document.createElement('div');
  div.className = `${colors[type]||colors.info} text-white text-xs md:text-sm rounded shadow px-3 py-2 animate-fadeIn`;
  div.textContent = message;
  els.toastContainer.appendChild(div);
  setTimeout(()=>{ div.classList.add('opacity-0','transition-opacity','duration-500'); setTimeout(()=>div.remove(),600); }, 3500);
}

// Dark mode
function initTheme() {
  const pref = localStorage.getItem('theme');
  if (pref === 'dark' || (!pref && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
    els.themeToggle.textContent = 'Light';
  } else {
    document.documentElement.classList.remove('dark');
    els.themeToggle.textContent = 'Dark';
  }
  els.themeToggle.addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    const isDark = document.documentElement.classList.contains('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    els.themeToggle.textContent = isDark ? 'Light' : 'Dark';
  });
}

// Event delegation
const doLogout = async () => {
  try { await fetch('/Scrap/api/logout.php', { method: 'POST' }); } catch(e) { /* ignore */ }
  sessionStorage.clear();
  window.location.href = '/Scrap/login.php';
};

function attachDelegates() {
  document.addEventListener('click', e => {
    const t = e.target;
    if (t.matches('[data-accept]')) acceptRequest(t.getAttribute('data-accept'));
    else if (t.matches('[data-decline]')) declineRequest(t.getAttribute('data-decline'));
    else if (t.matches('[data-complete]')) completeCollection(t.getAttribute('data-complete'));
    else if (t.matches('[data-focus]')) focusRequestOnMap(t.getAttribute('data-focus'));
  });
  els.statusSelect.addEventListener('change', e => updateStatus(e.target.value));
  els.refreshBtn?.addEventListener('click', () => loadDashboardData());
  els.reloadRequests?.addEventListener('click', () => loadDashboardData());
  els.reloadHistory?.addEventListener('click', () => loadDashboardData());
  els.requestFilter?.addEventListener('change', () => state.data && renderRequestsList(state.data.pendingRequests));
  els.fitActiveBtn?.addEventListener('click', fitActiveMarkers);
  els.locateMeBtn?.addEventListener('click', locateMe);
  els.clearRouteBtn?.addEventListener('click', clearRoutePanel);
  els.logoutBtn?.addEventListener('click', doLogout);
}

function focusRequestOnMap(id) {
  if (!state.data) return;
  const req = state.data.activeRequests.find(r=>String(r.id)===String(id));
  if (!req) { toast('Request not active', 'info'); return; }
  const latlng = [req.latitude, req.longitude];
  L.marker(latlng).addTo(state.map).bindPopup(`<strong>${req.customer_name}</strong><br>${req.address}`).openPopup();
  state.map.flyTo(latlng, 15);
  showRouteDetails(req);
}

function fitActiveMarkers() {
  if (!state.data || !state.data.activeRequests.length) { toast('No active requests','info'); return; }
  const bounds = L.latLngBounds(state.data.activeRequests.map(r=>[r.latitude,r.longitude]));
  state.map.fitBounds(bounds.pad(0.2));
}

function locateMe() {
  if (!navigator.geolocation) { 
    toast('Geolocation unsupported','error'); 
    return; 
  }

  // Toggle tracking: if already watching, stop
  if (state.myLocationWatchId !== null) {
    navigator.geolocation.clearWatch(state.myLocationWatchId);
    state.myLocationWatchId = null;
    if (state.myLocationMarker) {
      state.map.removeLayer(state.myLocationMarker);
      state.myLocationMarker = null;
    }
    const btn = document.getElementById('locateMeBtn');
    if (btn) btn.textContent = 'My Location';
    toast('Stopped location tracking','info');
    return;
  }

  toast('Tracking your location...','info');
  const btn = document.getElementById('locateMeBtn');
  if (btn) btn.textContent = 'Stop Tracking';

  // Watch and update marker position
  state.myLocationWatchId = navigator.geolocation.watchPosition(
    pos => {
      const ll = [pos.coords.latitude, pos.coords.longitude];
      const vehicleType = state.data?.vehicle?.type?.toLowerCase() || 'truck';
      const icon = L.divIcon({
        className: 'vehicle-pulse',
        html: `<div class="pulse-ring"></div><img src="/Scrap/public/images/markers/${vehicleType}.svg" alt="${vehicleType}" />`,
        iconSize: [40,40],
        iconAnchor: [20,20]
      });
      // Create or move marker
      if (!state.myLocationMarker) {
        state.myLocationMarker = L.marker(ll, { icon })
          .addTo(state.map)
          .bindPopup('Your Location');
      } else {
        state.myLocationMarker.setLatLng(ll);
      }
      // Only open popup first time
      if (!state.myLocationMarker._popup || !state.myLocationMarker._popup.isOpen()) {
        state.myLocationMarker.openPopup();
      }
      // Smooth fly for first few updates only
      if (!state._didInitialFly) {
        state.map.flyTo(ll, 15);
        state._didInitialFly = true;
      }
    },
    error => {
      let message = 'Could not get location';
      switch(error.code) {
        case error.PERMISSION_DENIED: message = 'Permission denied – enable location access.'; break;
        case error.POSITION_UNAVAILABLE: message = 'Location unavailable – check device.'; break;
        case error.TIMEOUT: message = 'Location timeout – retry.'; break;
        default: message = 'Unknown geolocation error.';
      }
      toast(message,'error');
      // Stop tracking if no permission
      if (error.code === error.PERMISSION_DENIED) {
        if (state.myLocationWatchId !== null) navigator.geolocation.clearWatch(state.myLocationWatchId);
        state.myLocationWatchId = null;
        const b = document.getElementById('locateMeBtn');
        if (b) b.textContent = 'My Location';
      }
    },
    { enableHighAccuracy: false, timeout: 15000, maximumAge: 60000 }
  );
}

function showRouteDetails(req) {
  els.activeRoutePanel.classList.remove('hidden');
  els.routeDetails.innerHTML = `
    <p><span class='font-medium'>Customer:</span> ${req.customer_name}</p>
    <p><span class='font-medium'>Address:</span> ${req.address}</p>
    <p><span class='font-medium'>Material:</span> ${req.material_type}</p>
    <p class='text-[10px] text-slate-500 dark:text-slate-400'>Lat: ${req.latitude} Lng: ${req.longitude}</p>`;
}

function clearRoutePanel() {
  els.activeRoutePanel.classList.add('hidden');
  els.routeDetails.innerHTML = '';
}

// keep named function as well for any in-flight references
async function logout() { return doLogout(); }

function guardAuth() {
  const userId = sessionStorage.getItem('user_id');
  const userRole = sessionStorage.getItem('user_role'); // Changed from 'role' to 'user_role'
  
  if (!userId || userRole !== 'collector') {
    console.warn('guardAuth: User not authenticated as collector, redirecting to login');
    window.location.href = '/Scrap/views/auth/login.php';
    return false;
  }
  return true;
}

function initYear() { const y = document.getElementById('year'); if (y) y.textContent = new Date().getFullYear(); }

// Entry
window.addEventListener('DOMContentLoaded', () => {
  cacheEls();
  // Check authentication before proceeding
  if (!guardAuth()) {
    return; // Stop execution if not authenticated
  }
  // Quick session ping once at startup for diagnostics
  initTheme();
  initMap();
  initNavigation();
  attachDelegates();
  initYear();
  // Set persisted status
  const st = sessionStorage.getItem('collectorStatus') || 'online';
  els.statusSelect.value = st;
  // Defer actual server update until tracker is ready (handled in initMap)
  state.pendingStatus = st;
  loadDashboardData();
  setInterval(()=>loadDashboardData({ silent: true }), 30000);
  showSection('overview');
  
  // Listen for status changes from sidebar
  window.addEventListener('collectorStatusChanged', (event) => {
    const newStatus = event.detail.status;
    
    // Update status select if it exists
    if (els.statusSelect) {
      els.statusSelect.value = newStatus;
    }
    
    // Update location status text
    if (els.locationStatus) {
      els.locationStatus.textContent = newStatus !== 'offline' ? 'Active' : 'Inactive';
      els.locationStatus.className = newStatus !== 'offline' 
        ? 'text-green-600 dark:text-green-400' 
        : 'text-red-600 dark:text-red-400';
    }
    
    // Update global status badge if it exists with appropriate colors
    if (els.globalStatusBadge) {
      const statusText = newStatus.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
      els.globalStatusBadge.textContent = statusText;
      els.globalStatusBadge.classList.remove('hidden');
      
      // Remove all status color classes
      els.globalStatusBadge.classList.remove(
        'bg-green-100', 'dark:bg-green-900/30', 'text-green-700', 'dark:text-green-300',
        'bg-blue-100', 'dark:bg-blue-900/30', 'text-blue-700', 'dark:text-blue-300',
        'bg-gray-100', 'dark:bg-gray-900/30', 'text-gray-700', 'dark:text-gray-300'
      );
      
      // Add appropriate color classes based on status
      if (newStatus === 'online') {
        els.globalStatusBadge.classList.add('bg-green-100', 'dark:bg-green-900/30', 'text-green-700', 'dark:text-green-300');
      } else if (newStatus === 'on_job') {
        els.globalStatusBadge.classList.add('bg-blue-100', 'dark:bg-blue-900/30', 'text-blue-700', 'dark:text-blue-300');
      } else if (newStatus === 'offline') {
        els.globalStatusBadge.classList.add('bg-gray-100', 'dark:bg-gray-900/30', 'text-gray-700', 'dark:text-gray-300');
      }
    }
    
    // Update sessionStorage
    sessionStorage.setItem('collectorStatus', newStatus);
    
    // Show toast notification
    toast(`Status changed to ${newStatus.replace('_', ' ')}`, 'success');
    
    // Optionally reload dashboard data to get updated stats
    loadDashboardData({ silent: true });
  });
});

window.addEventListener('beforeunload', () => {
  state.tracker?.stopTracking();
});
