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

const state = {
  data: null,
  charts: {},
  activeMarkers: [],
  activeRouteLayer: null,
  tracker: null,
  map: null,
  loading: false
};

// Elements
const els = {};
function cacheEls() {
  els.navLinks = document.querySelectorAll('#navLinks a');
  els.sections = document.querySelectorAll('main section');
  els.collectorName = document.getElementById('collectorName');
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
}

// Initialization
function initMap() {
  state.map = L.map('map').setView([-1.1712, 36.8356], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(state.map);
  state.tracker = new CollectorTracker(state.map);
  state.tracker.startTracking();
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
    let res = await fetch('/Scrap/api/collectors/dashboard.php');
    // If the /Scrap path returns server error or not found, try a fallback without /Scrap
    if (!res.ok) {
      console.warn('Primary dashboard fetch failed', res.status, 'trying fallback /api/collectors/dashboard.php');
      try { res = await fetch('/api/collectors/dashboard.php'); } catch (e) { /* ignore */ }
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

// Rendering
function renderAll() {
  const { stats, activeRequests, pendingRequests, history, earnings } = state.data;
  renderStats(stats);
  renderActiveRequests(activeRequests);
  renderRequestsList(pendingRequests);
  renderHistory(history);
  renderEarnings(earnings);
  if (state.data?.analytics) {
    renderAnalytics(state.data.analytics);
  }
}

function renderStats(stats) {
  els.todayCollections.textContent = stats.today_collections;
  els.todayEarnings.textContent = 'KES ' + stats.today_earnings;
  els.rating.textContent = Number(stats.rating).toFixed(1);
  els.totalWeight.textContent = stats.total_weight + ' kg';
  els.collectorName.textContent = stats.name;
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
  const filter = els.requestFilter?.value || 'all';
  const filtered = requests.filter(r => filter === 'all' || (filter === 'pending' && r.status === undefined) || filter === 'accepted' && r.status === 'accepted');
  els.requestsList.innerHTML = '';
  if (!filtered.length) {
    els.requestsList.innerHTML = '<p class="text-gray-500 dark:text-slate-400 text-xs p-2">No requests found</p>';
    els.pendingBadge.classList.add('hidden');
    return;
  }
  els.pendingBadge.classList.toggle('hidden', !requests.length);
  els.pendingBadge.textContent = requests.length;
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

// (Inserted modifications for analytics rendering below)

// Extend existing renderAll to include analytics
const originalRenderAll = renderAll;
renderAll = function() { // override
  originalRenderAll();
  if (state.data?.analytics) {
    renderAnalytics(state.data.analytics);
  }
};

function ensureAnalyticsNav() {
  if (!document.querySelector('#navLinks a[href="#analytics"]')) {
    const li = document.createElement('li');
    li.innerHTML = '<a href="#analytics" class="flex items-center px-3 py-2 rounded-md text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/60">Analytics</a>';
    document.getElementById('navLinks').appendChild(li);
    li.querySelector('a').addEventListener('click', e => { e.preventDefault(); showSection('analytics'); });
  }
}

function renderAnalytics(analytics) {
  ensureAnalyticsNav();
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
  const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
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
  if (!navigator.geolocation) { toast('Geolocation unsupported','error'); return; }
  navigator.geolocation.getCurrentPosition(pos => {
    const ll = [pos.coords.latitude,pos.coords.longitude];
    L.circleMarker(ll,{ radius:6, color:'#22c55e' }).addTo(state.map).bindPopup('You are here').openPopup();
    state.map.flyTo(ll, 15);
  }, () => toast('Could not get location','error'));
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

// function guardAuth() {
//   if (!sessionStorage.getItem('user_id') || sessionStorage.getItem('role') !== 'collector') {
//     window.location.href = '/Scrap/login.php';
//   }
// }

function initYear() { const y = document.getElementById('year'); if (y) y.textContent = new Date().getFullYear(); }

// Entry
window.addEventListener('DOMContentLoaded', () => {
  cacheEls();
  // guardAuth may be disabled during development; provide a safe no-op when sessionStorage lacks required keys
  try { guardAuth(); } catch (e) { console.warn('guardAuth not available or failed, continuing in dev mode'); }
  initTheme();
  initMap();
  initNavigation();
  attachDelegates();
  initYear();
  // Set persisted status
  const st = sessionStorage.getItem('collectorStatus') || 'online';
  els.statusSelect.value = st;
  updateStatus(st);
  loadDashboardData();
  setInterval(()=>loadDashboardData({ silent: true }), 30000);
  showSection('overview');
});

window.addEventListener('beforeunload', () => {
  state.tracker?.stopTracking();
});
