// Collector Requests page module
import './collector-tracker.js'; // tracker exposes CollectorTracker class for location updates

const state = {
  map: null,
  markers: [],
  data: null
};

const els = {};
function cache() {
  els.pendingList = document.getElementById('pendingList');
  els.activeList = document.getElementById('activeList');
  els.pendingCount = document.getElementById('pendingCount');
  els.activeCount = document.getElementById('activeCount');
  els.refreshBtn = document.getElementById('refreshBtn');
  els.materialFilter = document.getElementById('materialFilter');
  els.toastContainer = document.getElementById('toastContainer');
}

function toast(msg, type='info'){
  if (!els.toastContainer) return;
  const el = document.createElement('div');
  el.className = 'px-3 py-2 rounded text-white text-sm ' + (type==='success'? 'bg-green-600': type==='error'? 'bg-red-600':'bg-slate-700');
  el.textContent = msg;
  els.toastContainer.appendChild(el);
  setTimeout(()=>{ el.remove(); }, 3000);
}

function initMap(){
  state.map = L.map('map').setView([-1.1712, 36.8356], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(state.map);
}

function clearMarkers(){
  state.markers.forEach(m=>m.remove()); state.markers = [];
}

function addMarker(lat, lng, popupHtml){
  const m = L.marker([lat,lng]).addTo(state.map).bindPopup(popupHtml);
  state.markers.push(m);
}

async function load(){
  try{
    let res = await fetch('/Scrap/api/collectors/dashboard.php');
    if (!res.ok) {
      try { res = await fetch('/api/collectors/dashboard.php'); } catch(e){}
    }
    let json;
    try { json = await res.json(); }
    catch (pe) { const txt = await res.text().catch(()=>'<no body>'); console.error('Invalid JSON from dashboard API', pe, txt); toast('Invalid server response (see console)','error'); return; }
  if (json.status !== 'success') { toast(json.message || 'Failed to load'); return; }
    state.data = json;
    renderLists();
    renderMap();
  }catch(e){ console.error(e); toast('Network error'); }
}

function renderLists(){
  const pending = (state.data.pendingRequests || []).filter(r => filterByMaterial(r));
  const active = (state.data.activeRequests || []).filter(r => filterByMaterial(r));

  els.pendingList.innerHTML = '';
  if (!pending.length) els.pendingList.innerHTML = '<p class="text-xs text-gray-500">No pending requests</p>';
  pending.forEach(r=>{
    const div = document.createElement('div');
    div.className = 'p-3 border-b border-gray-100 text-sm flex items-start justify-between';
    div.innerHTML = `<div><strong>${escapeHtml(r.customer_name)}</strong><div class="text-slate-500 text-xs">${escapeHtml(r.material_type)} • ${escapeHtml(r.address)}</div></div>
      <div class="flex flex-col gap-2">
        <button data-accept="${r.id}" class="px-3 py-1 bg-green-600 text-white rounded text-xs">Accept</button>
        <button data-decline="${r.id}" class="px-3 py-1 bg-red-600 text-white rounded text-xs">Decline</button>
      </div>`;
    els.pendingList.appendChild(div);
  });

  els.activeList.innerHTML = '';
  if (!active.length) els.activeList.innerHTML = '<p class="text-xs text-gray-500">No active requests</p>';
  active.forEach(r=>{
    const div = document.createElement('div');
    div.className = 'p-3 border-b border-gray-100 text-sm flex items-start justify-between';
    div.innerHTML = `<div><strong>${escapeHtml(r.customer_name)}</strong><div class="text-slate-500 text-xs">${escapeHtml(r.material_type)} • ${escapeHtml(r.address)}</div></div>
      <div class="flex flex-col gap-2">
        <button data-complete="${r.id}" class="px-3 py-1 bg-blue-600 text-white rounded text-xs">Complete</button>
        <button data-focus="${r.id}" class="px-3 py-1 bg-slate-200 rounded text-xs">Locate</button>
      </div>`;
    els.activeList.appendChild(div);
  });

  els.pendingCount.textContent = pending.length;
  els.activeCount.textContent = active.length;
}

function filterByMaterial(r){
  const mf = els.materialFilter.value;
  if (!mf) return true;
  return String(r.material_type).toLowerCase() === mf.toLowerCase();
}

function renderMap(){
  clearMarkers();
  const all = [...(state.data.activeRequests || []), ...(state.data.pendingRequests || [])];
  all.forEach(r=>{
    if (r.latitude && r.longitude) addMarker(r.latitude, r.longitude, `<strong>${escapeHtml(r.customer_name)}</strong><br/>${escapeHtml(r.address)}`);
  });
}

function escapeHtml(s){ return String(s||'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;'); }

async function postJSON(url, payload){
  const res = await fetch(url, { method:'POST', headers:{ 'Content-Type':'application/json' }, body: JSON.stringify(payload) });
  const json = await res.json().catch(()=>({status:'error', message:'Invalid response'}));
  if (!res.ok || json.status === 'error') throw new Error(json.message || 'API error');
  return json;
}

async function acceptRequest(id){
  try{ await postJSON('/Scrap/api/collectors/accept_request.php',{ request_id: id }); toast('Accepted', 'success'); load(); }catch(e){ toast(e.message || 'Failed', 'error'); }
}
async function declineRequest(id){
  try{ await postJSON('/Scrap/api/collectors/decline_request.php',{ request_id: id }); toast('Declined', 'info'); load(); }catch(e){ toast(e.message || 'Failed', 'error'); }
}
async function completeRequest(id){
  const weight = prompt('Enter weight collected in kg');
  if (!weight) return; const n = parseFloat(weight); if (isNaN(n) || n<=0) { toast('Invalid weight','error'); return; }
  try{ await postJSON('/Scrap/api/collectors/complete_collection.php',{ request_id: id, weight: n }); toast('Completed', 'success'); load(); }catch(e){ toast(e.message || 'Failed', 'error'); }
}

function attach(){
  document.addEventListener('click', e=>{
    const t = e.target;
    if (t.matches('[data-accept]')) acceptRequest(t.getAttribute('data-accept'));
    if (t.matches('[data-decline]')) declineRequest(t.getAttribute('data-decline'));
    if (t.matches('[data-complete]')) completeRequest(t.getAttribute('data-complete'));
    if (t.matches('[data-focus]')) focusOnMap(t.getAttribute('data-focus'));
  });
  els.refreshBtn.addEventListener('click', load);
  els.materialFilter.addEventListener('change', ()=>{ renderLists(); renderMap(); });
}

function focusOnMap(id){
  const req = (state.data.activeRequests||[]).find(r=>String(r.id)===String(id));
  if (!req) return toast('Request not active','info');
  if (req.latitude && req.longitude) state.map.flyTo([req.latitude, req.longitude], 15);
}

window.addEventListener('DOMContentLoaded', ()=>{
  cache();
  initMap();
  attach();
  load();
});
