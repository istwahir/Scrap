<?php
require_once 'includes/auth.php';
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop-off Map - Kiambu Recycling & Scraps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .glass-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
            backdrop-filter: blur(18px);
            box-shadow: 0 20px 45px -25px rgba(15, 118, 110, 0.7);
        }
        .hero-gradient {
            background: radial-gradient(120% 120% at 50% 0%, rgba(16, 185, 129, 0.3) 0%, transparent 65%),
                        linear-gradient(135deg, #047857 0%, #0f172a 60%, #020617 100%);
        }
        .grid-fade {
            background-image: linear-gradient(rgba(99, 102, 241, 0.12) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(56, 189, 248, 0.12) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        #map {
            height: 520px;
        }
        .custom-popup .leaflet-popup-content {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased">
    <?php include 'includes/header.php'; ?>

    <header class="hero-gradient relative overflow-hidden pb-24">
        <div class="absolute inset-0 grid-fade opacity-20"></div>
        <div class="relative z-10 mx-auto max-w-6xl px-6">
            <div class="grid gap-12 pt-24 lg:grid-cols-[1.05fr,0.95fr] lg:items-center">
                <div class="space-y-10">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1 text-sm text-emerald-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        41 verified drop-off zones • Updated hourly
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                            Map every recycling drop-off spot across Kiambu.
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg text-emerald-100/80">
                            Filter by materials, pinpoint your location, and request a pickup straight from the map.
                            Collectors sync with these hubs to streamline pickups and maximize rewards.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <button id="locateBtn" class="inline-flex items-center gap-3 rounded-full border border-emerald-400/40 bg-emerald-500/10 px-6 py-3 text-sm font-semibold text-emerald-100 transition hover:border-emerald-300/70">
                            <span aria-hidden="true">📍</span> Use my location
                        </button>
                        <a href="request.php" class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition hover:scale-[1.02]">
                            Schedule collection
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>

                <div class="glass-card relative overflow-hidden rounded-3xl p-8">
                    <div class="absolute -left-10 -top-14 h-36 w-36 rounded-full bg-emerald-400/25 blur-3xl"></div>
                    <div class="absolute -bottom-12 right-0 h-40 w-40 rounded-full bg-sky-400/25 blur-3xl"></div>
                    <div class="relative grid gap-6">
                        <div class="text-xs uppercase tracking-[0.35em] text-emerald-200/70">Network insights</div>
                        <div class="grid gap-5 sm:grid-cols-3">
                            <div class="space-y-1.5">
                                <div class="text-3xl font-semibold" data-counter data-target="41">0</div>
                                <div class="text-xs uppercase tracking-wider text-emerald-100/80">Drop-off partners</div>
                            </div>
                            <div class="space-y-1.5">
                                <div class="text-3xl font-semibold" data-counter data-target="18">0</div>
                                <div class="text-xs uppercase tracking-wider text-emerald-100/80">Rapid collection hubs</div>
                            </div>
                            <div class="space-y-1.5">
                                <div class="text-3xl font-semibold" data-counter data-target="7">0</div>
                                <div class="text-xs uppercase tracking-wider text-emerald-100/80">Material categories</div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-emerald-100/80">
                            “Drop-off search is effortless—I filter plastics, get distance estimates, then book a pickup right away.”
                            <span class="text-white font-medium">— Faith, Ruaka</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="relative z-10 -mt-16 space-y-16 pb-24">
        <section class="mx-auto max-w-6xl px-6">
            <div class="grid gap-8 lg:grid-cols-[0.85fr,1.15fr]">
                <aside class="space-y-6">
                    <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 shadow-2xl shadow-emerald-950/40">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <h2 class="text-lg font-semibold text-white">Filter materials</h2>
                                <p class="text-xs text-slate-300">Toggle what you’re dropping off to highlight relevant hubs.</p>
                            </div>
                            <span class="rounded-full border border-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.25em] text-emerald-200">Live</span>
                        </div>
                        <div class="mt-6 grid gap-3 text-sm text-slate-200">
                            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 transition hover:border-emerald-400/60">
                                <input type="checkbox" class="material-filter h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400" value="plastic" checked>
                                <span class="flex-1">Plastic</span>
                            </label>
                            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 transition hover:border-emerald-400/60">
                                <input type="checkbox" class="material-filter h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400" value="paper" checked>
                                <span class="flex-1">Paper</span>
                            </label>
                            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 transition hover:border-emerald-400/60">
                                <input type="checkbox" class="material-filter h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400" value="metal" checked>
                                <span class="flex-1">Metal</span>
                            </label>
                            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 transition hover:border-emerald-400/60">
                                <input type="checkbox" class="material-filter h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400" value="glass" checked>
                                <span class="flex-1">Glass</span>
                            </label>
                            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 transition hover:border-emerald-400/60">
                                <input type="checkbox" class="material-filter h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400" value="electronics" checked>
                                <span class="flex-1">Electronics</span>
                            </label>
                        </div>
                        <div class="mt-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-xs text-emerald-100/80">
                            Tip: Drop a pin on your exact location first to unlock distance estimates and fastest routes.
                        </div>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 shadow-2xl shadow-emerald-950/40">
                        <h3 class="text-lg font-semibold text-white">Why hubs matter</h3>
                        <ul class="mt-4 space-y-4 text-sm text-slate-300">
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-emerald-400"></span>
                                Verified partners log weights instantly, preventing delays in your reward balance.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-sky-400"></span>
                                High-capacity hubs accept bulk metals and glass without prior notice.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-amber-400"></span>
                                Community-led drop points earn neighbourhood bonus multipliers every quarter.
                            </li>
                        </ul>
                        <a href="guide.php#materials" class="mt-6 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.25em] text-emerald-200 transition hover:text-emerald-100">
                            Review preparation rules
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </aside>

                <section class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 shadow-2xl shadow-emerald-950/40">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-white">Explore the map</h2>
                            <p class="text-sm text-slate-300">Tap a marker to view materials, hours, and initiate a pickup request.</p>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-emerald-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live data feed
                        </div>
                    </div>
                    <div class="mt-6 overflow-hidden rounded-3xl border border-white/10 bg-slate-950/60">
                        <div id="map"></div>
                    </div>
                </section>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-6">
            <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-8 shadow-2xl shadow-emerald-950/40">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-white">From map to pickup in minutes</h2>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">Here’s how residents use drop-off intel to schedule same-day collections.</p>
                    </div>
                    <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Optimised for 8KM radius</div>
                </div>
                <div class="mt-8 grid gap-5 md:grid-cols-4">
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">01</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Filter hubs</h3>
                        <p class="mt-3 text-sm text-slate-300">Lock onto partners that match the materials you’ve prepared.</p>
                    </article>
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">02</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Drop your pin</h3>
                        <p class="mt-3 text-sm text-slate-300">Share your exact location to unlock distance and ETA calculations.</p>
                    </article>
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">03</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Book pickup</h3>
                        <p class="mt-3 text-sm text-slate-300">Launch the request form straight from the hub popup in one tap.</p>
                    </article>
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">04</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Track & earn</h3>
                        <p class="mt-3 text-sm text-slate-300">Monitor collector progress, approve weights, and redeem instant rewards.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const counters = document.querySelectorAll('[data-counter]');
        if (counters.length) {
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const target = parseInt(el.dataset.target, 10);
                        let current = 0;
                        const increment = Math.max(1, Math.ceil(target / 120));
                        const update = () => {
                            current += increment;
                            if (current >= target) {
                                el.textContent = target.toLocaleString();
                            } else {
                                el.textContent = current.toLocaleString();
                                requestAnimationFrame(update);
                            }
                        };
                        requestAnimationFrame(update);
                        observer.unobserve(el);
                    }
                });
            }, { threshold: 0.6 });
            counters.forEach(counter => observer.observe(counter));
        }

        const map = L.map('map').setView([-1.171315, 36.835372], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const dropoffIcon = L.icon({
            iconUrl: '/public/images/markers/pickup.svg',
            iconSize: [40, 40],
            iconAnchor: [20, 36],
            popupAnchor: [0, -28],
            className: 'dropoff-icon'
        });

        const collectorIcons = {
            truck: L.icon({
                iconUrl: '/public/images/markers/truck.svg',
                iconSize: [40, 40],
                iconAnchor: [20, 36],
                popupAnchor: [0, -28]
            }),
            pickup: L.icon({
                iconUrl: '/public/images/markers/pickup.svg',
                iconSize: [40, 40],
                iconAnchor: [20, 36],
                popupAnchor: [0, -28]
            }),
            tuktuk: L.icon({
                iconUrl: '/public/images/markers/tuktuk.svg',
                iconSize: [40, 40],
                iconAnchor: [20, 36],
                popupAnchor: [0, -28]
            }),
            motorcycle: L.icon({
                iconUrl: '/public/images/markers/motorcycle.svg',
                iconSize: [40, 40],
                iconAnchor: [20, 36],
                popupAnchor: [0, -28]
            })
        };

        let markers = [];
        const collectorMarkers = new Map();
        let collectorsEventSource = null;
        let collectorReconnectTimeout = null;
        let userMarker = null;
        let userPosition = null;

        async function loadDropoffs() {
            try {
                const filters = Array.from(document.querySelectorAll('.material-filter:checked'))
                    .map(cb => cb.value)
                    .join(',');

                let url = '/api/get_dropoffs.php';
                const params = new URLSearchParams();
                if (filters) {
                    params.append('materials', filters);
                }
                if (userPosition) {
                    params.append('lat', userPosition.lat);
                    params.append('lng', userPosition.lng);
                }
                if (params.toString()) {
                    url += `?${params.toString()}`;
                }

                const response = await fetch(url);
                const data = await response.json();

                markers.forEach(marker => marker.remove());
                markers = [];

                if (data.status === 'success') {
                    data.data.forEach(point => {
                        const marker = L.marker([point.lat, point.lng], { icon: dropoffIcon }).addTo(map);
                        const popupContent = `
                            <div class="min-w-[220px] rounded-2xl bg-slate-950/90 text-white">
                                <div class="border-b border-white/10 px-4 py-3">
                                    <h3 class="text-base font-semibold">${point.name}</h3>
                                    <p class="mt-1 text-xs text-emerald-200/80">${point.address}</p>
                                </div>
                                <div class="px-4 py-3 text-xs text-slate-200">
                                    <p class="mb-2">
                                        <span class="font-semibold text-emerald-200">Materials:</span><br>
                                        ${point.materials.join(', ')}
                                    </p>
                                    <p class="mb-2">
                                        <span class="font-semibold text-emerald-200">Hours:</span><br>
                                        ${point.operating_hours}
                                    </p>
                                    <p>
                                        <span class="font-semibold text-emerald-200">Contact:</span><br>
                                        ${point.contact_phone || 'N/A'}
                                    </p>
                                    ${point.distance ? `<p class="mt-3 text-emerald-300 font-semibold">${point.distance} km away</p>` : ''}
                                </div>
                                <div class="border-t border-white/10 px-4 py-3">
                                    <button data-dropoff="${point.id}" class="launch-request w-full rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-4 py-2 text-xs font-semibold text-slate-900">Request pickup</button>
                                </div>
                            </div>`;
                        marker.bindPopup(popupContent, { className: 'custom-popup' });
                        markers.push(marker);
                    });
                }
            } catch (error) {
                console.error('Failed to load drop-off points:', error);
            }
        }

        document.addEventListener('click', event => {
            if (event.target.matches('.launch-request')) {
                const dropoffId = event.target.getAttribute('data-dropoff');
                createRequest(dropoffId);
                return;
            }

            if (event.target.matches('.launch-collector-request')) {
                const collectorId = event.target.getAttribute('data-collector');
                requestCollectorPickup(collectorId);
            }
        });

        document.querySelectorAll('.material-filter').forEach(checkbox => {
            checkbox.addEventListener('change', loadDropoffs);
        });

        document.getElementById('locateBtn').addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            navigator.geolocation.getCurrentPosition(position => {
                userPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                if (userMarker) {
                    userMarker.remove();
                }
                userMarker = L.circleMarker([userPosition.lat, userPosition.lng], {
                    radius: 8,
                    color: '#34d399',
                    fillColor: '#34d399',
                    fillOpacity: 0.8
                }).addTo(map).bindPopup('You are here').openPopup();

                map.setView([userPosition.lat, userPosition.lng], 13);
                loadDropoffs();
            }, error => {
                console.error('Error getting location:', error);
                alert('Could not get your location. Please allow permission or drop a pin manually.');
            });
        });

        function normaliseListSource(source) {
            if (Array.isArray(source)) {
                return source.filter(Boolean);
            }
            if (typeof source === 'string' && source.trim().length) {
                return source.split(',').map(item => item.trim()).filter(Boolean);
            }
            return [];
        }

        function renderCollectors(collectors) {
            const activeIds = new Set();

            collectors.forEach(collector => {
                const position = collector.position || {};
                const lat = typeof position.lat === 'number' ? position.lat : parseFloat(position.lat);
                const lng = typeof position.lng === 'number' ? position.lng : parseFloat(position.lng);

                if (Number.isNaN(lat) || Number.isNaN(lng)) {
                    return;
                }

                activeIds.add(collector.id);

                const iconKey = (collector.vehicle || '').toLowerCase();
                const icon = collectorIcons[iconKey] || collectorIcons.pickup;
                const materials = normaliseListSource(collector.materials);
                const areas = normaliseListSource(collector.areas);

                let lastActiveLabel = '';
                if (collector.lastActive) {
                    const timestamp = collector.lastActive.replace(' ', 'T');
                    const parsed = new Date(timestamp);
                    if (!Number.isNaN(parsed.getTime())) {
                        lastActiveLabel = parsed.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    }
                }

                const popupContent = `
                    <div class="min-w-[220px] rounded-2xl bg-slate-950/90 text-white">
                        <div class="border-b border-white/10 px-4 py-3">
                            <h3 class="text-base font-semibold">${collector.name || 'Collector'}</h3>
                            <p class="mt-1 text-xs text-emerald-200/80 capitalize">${collector.status || 'online'}${lastActiveLabel ? ` • Active ${lastActiveLabel}` : ''}</p>
                        </div>
                        <div class="px-4 py-3 text-xs text-slate-200">
                            <p class="mb-2"><span class="font-semibold text-emerald-200">Vehicle:</span> ${collector.vehicle || 'N/A'}</p>
                            ${materials.length ? `<p class="mb-2"><span class="font-semibold text-emerald-200">Materials:</span><br>${materials.join(', ')}</p>` : ''}
                            ${areas.length ? `<p class="mb-2"><span class="font-semibold text-emerald-200">Areas:</span><br>${areas.join(', ')}</p>` : ''}
                        </div>
                        <div class="border-t border-white/10 px-4 py-3">
                            <button data-collector="${collector.id}" class="launch-collector-request w-full rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-4 py-2 text-xs font-semibold text-slate-900">Request pickup</button>
                        </div>
                    </div>`;

                const existingMarker = collectorMarkers.get(collector.id);
                if (existingMarker) {
                    existingMarker.setLatLng([lat, lng]);
                    existingMarker.setPopupContent(popupContent);
                } else {
                    const marker = L.marker([lat, lng], { icon }).addTo(map);
                    marker.bindPopup(popupContent);
                    collectorMarkers.set(collector.id, marker);
                }
            });

            collectorMarkers.forEach((marker, id) => {
                if (!activeIds.has(id)) {
                    map.removeLayer(marker);
                    collectorMarkers.delete(id);
                }
            });
        }

        function startCollectorStream() {
            if (collectorsEventSource) {
                collectorsEventSource.close();
            }
            if (collectorReconnectTimeout) {
                clearTimeout(collectorReconnectTimeout);
            }

            collectorsEventSource = new EventSource('/api/collectors/get_locations.php');

            collectorsEventSource.addEventListener('update', event => {
                try {
                    const payload = JSON.parse(event.data);
                    if (payload.status === 'success' && Array.isArray(payload.collectors)) {
                        renderCollectors(payload.collectors);
                    }
                } catch (error) {
                    console.error('Failed to parse collectors update', error);
                }
            });

            collectorsEventSource.addEventListener('error', () => {
                if (collectorsEventSource) {
                    collectorsEventSource.close();
                    collectorsEventSource = null;
                }
                collectorReconnectTimeout = setTimeout(startCollectorStream, 5000);
            });
        }

        function stopCollectorStream() {
            if (collectorsEventSource) {
                collectorsEventSource.close();
                collectorsEventSource = null;
            }
            if (collectorReconnectTimeout) {
                clearTimeout(collectorReconnectTimeout);
                collectorReconnectTimeout = null;
            }
            collectorMarkers.forEach(marker => map.removeLayer(marker));
            collectorMarkers.clear();
        }

        function requestCollectorPickup(collectorId) {
            const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            if (!isLoggedIn) {
                window.location.href = `/login.php?redirect=map&collector=${collectorId}`;
                return;
            }
            window.location.href = `/request.php?collector=${collectorId}`;
        }

        function createRequest(dropoffId) {
            const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            if (!isLoggedIn) {
                window.location.href = `/login.php?redirect=map&dropoff=${dropoffId}`;
                return;
            }
            window.location.href = `/request.php?dropoff=${dropoffId}`;
        }

        loadDropoffs();
        startCollectorStream();
        window.addEventListener('beforeunload', stopCollectorStream);

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(registration => {
                        console.log('ServiceWorker registered:', registration);
                    })
                    .catch(error => {
                        console.log('ServiceWorker registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>
