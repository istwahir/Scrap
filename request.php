<?php
require_once 'includes/auth.php';
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Collection - Kiambu Recycling & Scraps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .glass-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
            backdrop-filter: blur(18px);
            box-shadow: 0 20px 45px -25px rgba(15, 118, 110, 0.7);
        }
        .hero-gradient {
            background: radial-gradient(120% 120% at 50% 0%, rgba(16, 185, 129, 0.25) 0%, transparent 60%),
                        linear-gradient(135deg, #064e3b 0%, #0f172a 60%, #020617 100%);
        }
        .grid-fade {
            background-image: linear-gradient(rgba(99, 102, 241, 0.12) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(56, 189, 248, 0.12) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        #map {
            height: 320px;
        }
        #loadingOverlay {
            backdrop-filter: blur(14px);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased">
    <?php include 'includes/header.php'; ?>

    <header class="hero-gradient relative overflow-hidden pb-24">
        <div class="absolute inset-0 grid-fade opacity-15"></div>
        <div class="relative z-10 mx-auto max-w-6xl px-6">
            <div class="grid gap-12 pt-24 lg:grid-cols-[1.1fr,0.9fr] lg:items-center">
                <div class="space-y-10">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1 text-sm text-emerald-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Instant scheduling • Verified collectors
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                            Schedule your next pickup in under two minutes.
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg text-emerald-100/80">
                            Choose what you are recycling, pin your location, and lock a preferred slot.
                            Our collectors handle the rest while you watch rewards grow in real time.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <a href="#requestForm" class="inline-flex items-center gap-3 rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900 shadow-2xl shadow-emerald-500/30 transition hover:scale-[1.02]">
                            Create request
                            <span aria-hidden="true">↓</span>
                        </a>
                        <a href="guide.php#steps" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:border-emerald-300/60">
                            View prep checklist
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>

                <div class="glass-card relative overflow-hidden rounded-3xl p-8">
                    <div class="absolute -left-10 -top-14 h-36 w-36 rounded-full bg-emerald-400/20 blur-3xl"></div>
                    <div class="absolute -bottom-12 right-0 h-40 w-40 rounded-full bg-sky-400/20 blur-3xl"></div>
                    <div class="relative grid gap-6">
                        <div class="text-xs uppercase tracking-[0.35em] text-emerald-200/70">Live platform pulse</div>
                        <div class="grid gap-5 sm:grid-cols-3">
                            <div class="space-y-1.5">
                                <div class="text-3xl font-semibold" data-counter data-target="482">0</div>
                                <div class="text-xs uppercase tracking-wider text-emerald-100/80">Active routes today</div>
                            </div>
                            <div class="space-y-1.5">
                                <div class="text-3xl font-semibold" data-counter data-target="31">0</div>
                                <div class="text-xs uppercase tracking-wider text-emerald-100/80">Average response (mins)</div>
                            </div>
                            <div class="space-y-1.5">
                                <div class="text-3xl font-semibold" data-counter data-target="9680">0</div>
                                <div class="text-xs uppercase tracking-wider text-emerald-100/80">Points issued this week</div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-emerald-100/80">
                            “Collectors arrive exactly when scheduled. I drop a pin, upload a photo, and the pickup is done before lunch.”
                            <span class="text-white font-medium">— Sam, Thindigua</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="relative z-10 -mt-16 space-y-16 pb-24">
        <section class="mx-auto max-w-6xl px-6">
            <div class="grid gap-8 lg:grid-cols-[0.95fr,1.05fr]">
                <aside class="space-y-6">
                    <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 shadow-2xl shadow-emerald-950/40">
                        <div class="text-xs uppercase tracking-[0.35em] text-emerald-300/80">Quick prep</div>
                        <ul class="mt-5 space-y-4 text-sm text-slate-300">
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-emerald-400"></span>
                                Take clear photos of your sorted materials (max 10MB).
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-sky-400"></span>
                                Use the map or the locate button to drop an accurate pin for collectors.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-amber-400"></span>
                                Morning slots fill up fast—book 24 hours ahead to secure your preferred window.
                            </li>
                        </ul>
                        <a href="guide.php#materials" class="mt-6 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.25em] text-emerald-200 transition hover:text-emerald-100">
                            Material sorting guide
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                    <div class="rounded-3xl border border-emerald-500/20 bg-emerald-500/10 p-6 shadow-xl shadow-emerald-950/30">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-white">Need recurring pickups?</h3>
                                <p class="mt-2 text-sm text-emerald-100/80">Add a note to set weekly or biweekly pickups—we’ll pair you with a dedicated collector.</p>
                            </div>
                            <span class="grid h-12 w-12 place-items-center rounded-full bg-white/10 text-emerald-200">♻️</span>
                        </div>
                        <p class="mt-4 text-xs uppercase tracking-[0.25em] text-emerald-200/80">Pro tip</p>
                        <p class="mt-2 text-sm text-slate-100/80">Tag neighbours in the notes to merge requests and unlock bonus reward multipliers.</p>
                    </div>
                </aside>

                <section class="rounded-3xl border border-white/10 bg-slate-900/70 p-8 shadow-2xl shadow-emerald-950/40">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-white">Request collection</h2>
                            <p class="text-sm text-slate-300">Fill in the details below. Collectors see this instantly and confirm within minutes.</p>
                        </div>
                        <div class="flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-400/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-emerald-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live dispatcher online
                        </div>
                    </div>

                    <form id="requestForm" class="mt-10 space-y-8">
                        <div>
                            <span class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Step 1</span>
                            <h3 class="mt-2 text-lg font-semibold text-white">What are we collecting?</h3>
                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <label class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-emerald-400/60">
                                    <input type="checkbox" name="materials[]" value="plastic" class="h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400">
                                    <span>Plastic</span>
                                </label>
                                <label class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-emerald-400/60">
                                    <input type="checkbox" name="materials[]" value="paper" class="h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400">
                                    <span>Paper</span>
                                </label>
                                <label class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-emerald-400/60">
                                    <input type="checkbox" name="materials[]" value="metal" class="h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400">
                                    <span>Metal</span>
                                </label>
                                <label class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-emerald-400/60">
                                    <input type="checkbox" name="materials[]" value="glass" class="h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400">
                                    <span>Glass</span>
                                </label>
                                <label class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-emerald-400/60">
                                    <input type="checkbox" name="materials[]" value="electronics" class="h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400">
                                    <span>Electronics</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <span class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Step 2</span>
                                <h3 class="mt-2 text-lg font-semibold text-white">Estimate the drop</h3>
                                <input type="number" id="weight" name="weight" min="1" max="1000" step="0.1" placeholder="Estimated weight (kg)" class="mt-4 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-0">
                            </div>
                            <div>
                                <span class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Optional snapshot</span>
                                <h3 class="mt-2 text-lg font-semibold text-white">Upload a photo</h3>
                                <input type="file" id="photo" name="photo" accept="image/*" class="mt-4 w-full rounded-2xl border border-dashed border-white/15 bg-white/5 px-4 py-3 text-sm file:mr-4 file:rounded-full file:border-0 file:bg-emerald-500/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-200 focus:border-emerald-400 focus:outline-none">
                                <p class="mt-2 text-xs text-slate-400">Optional, max 10MB. Helps collectors prep capacity.</p>
                            </div>
                        </div>

                        <div>
                            <span class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Step 3</span>
                            <h3 class="mt-2 text-lg font-semibold text-white">Where should we arrive?</h3>
                            <label for="address" class="sr-only">Pickup address</label>
                            <textarea id="address" name="address" rows="2" required placeholder="Enter your pickup address" class="mt-4 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-0"></textarea>
                            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <button type="button" id="locateBtn" class="inline-flex items-center gap-2 rounded-full border border-emerald-400/40 bg-emerald-500/10 px-5 py-2 text-sm font-semibold text-emerald-200 transition hover:border-emerald-300/70">
                                    <span aria-hidden="true">📍</span> Use my location
                                </button>
                                <p class="text-xs text-slate-400">Or tap on the map to drop a pin at your gate.</p>
                            </div>
                            <div class="mt-4 overflow-hidden rounded-3xl border border-white/10 bg-slate-950/60">
                                <div id="map"></div>
                            </div>
                            <input type="hidden" id="lat" name="lat" required>
                            <input type="hidden" id="lng" name="lng" required>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <span class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Step 4</span>
                                <h3 class="mt-2 text-lg font-semibold text-white">When works best?</h3>
                                <input type="date" id="date" name="date" required class="mt-4 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-emerald-400 focus:outline-none focus:ring-0">
                            </div>
                            <div>
                                <span class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Pick a slot</span>
                                <h3 class="mt-2 text-lg font-semibold text-white">Preferred time</h3>
                                <select id="time" name="time" required class="mt-4 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-emerald-400 focus:outline-none focus:ring-0">
                                    <option value="">Select time slot</option>
                                    <option value="morning">Morning (8AM - 11AM)</option>
                                    <option value="afternoon">Afternoon (12PM - 3PM)</option>
                                    <option value="evening">Evening (4PM - 7PM)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <span class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Extra context</span>
                            <h3 class="mt-2 text-lg font-semibold text-white">Anything else collectors should know?</h3>
                            <textarea id="notes" name="notes" rows="3" placeholder="Gate code, bulky items, neighbours joining…" class="mt-4 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-0"></textarea>
                        </div>

                        <button type="submit" class="w-full rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-6 py-3 text-sm font-semibold text-slate-900 shadow-xl shadow-emerald-900/50 transition hover:shadow-emerald-800/40">
                            Submit collection request
                        </button>
                    </form>
                </section>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-6">
            <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-8 shadow-2xl shadow-emerald-950/40">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-white">What happens after you submit?</h2>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">Track every milestone from the moment a collector accepts to when your rewards drop.</p>
                    </div>
                    <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/80">Real-time notifications enabled</div>
                </div>
                <div class="mt-8 grid gap-5 md:grid-cols-4">
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">01</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Collector confirms</h3>
                        <p class="mt-3 text-sm text-slate-300">You’ll get an instant push + SMS with collector details once a slot is locked.</p>
                    </article>
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">02</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Live tracking</h3>
                        <p class="mt-3 text-sm text-slate-300">Follow the collector on the map and chat if anything changes before arrival.</p>
                    </article>
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">03</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Weight verification</h3>
                        <p class="mt-3 text-sm text-slate-300">Collector weighs and uploads proof. You approve or flag issues instantly.</p>
                    </article>
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <div class="text-xs uppercase tracking-[0.3em] text-emerald-300/90">04</div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Rewards credited</h3>
                        <p class="mt-3 text-sm text-slate-300">Points hit your wallet. Redeem for M-Pesa cash, airtime, or partner deals.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <div id="loadingOverlay" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80">
        <div class="glass-card relative w-[90%] max-w-md rounded-3xl border border-white/10 p-8 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/10">
                <svg class="h-6 w-6 animate-spin text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-white">Submitting your request…</h3>
            <p class="mt-2 text-sm text-slate-300">Hang tight while we sync with the dispatch team.</p>
        </div>
    </div>

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

        let marker = null;
        const locateBtn = document.getElementById('locateBtn');
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('lng');
        const addressInput = document.getElementById('address');
        const dateInput = document.getElementById('date');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const form = document.getElementById('requestForm');

        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;

        map.on('click', (event) => {
            updateMarker(event.latlng);
            reverseGeocode(event.latlng); // Update address input when map is clicked
        });

        locateBtn.addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            navigator.geolocation.getCurrentPosition(position => {
                const latlng = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                updateMarker(latlng);
                map.setView(latlng, 16);
                reverseGeocode(latlng);
            }, error => {
                console.error('Error getting location:', error);
                alert('Could not get your location. Please allow permission or pin the spot manually.');
            });
        });

        function updateMarker(latlng) {
            if (marker) {
                marker.setLatLng(latlng);
            } else {
                marker = L.marker(latlng).addTo(map);
            }
            latInput.value = latlng.lat;
            lngInput.value = latlng.lng;
        }

        async function reverseGeocode(latlng) {
            try {
                // Use a CORS-friendly geocoding service or fallback to coordinates
                const response = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latlng.lat}&longitude=${latlng.lng}&localityLanguage=en`);
                const data = await response.json();
                if (data.locality && data.countryName) {
                    addressInput.value = `${data.locality}, ${data.countryName}`;
                } else {
                    // Fallback to coordinates if geocoding fails
                    addressInput.value = `${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`;
                }
            } catch (error) {
                console.error('Reverse geocoding failed:', error);
                // Fallback to coordinates
                addressInput.value = `${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`;
            }
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            
            const selectedMaterials = document.querySelectorAll('input[name="materials[]"]:checked');
            if (!selectedMaterials.length) {
                alert('Please select at least one material type.');
                return;
            }

            if (!marker) {
                alert('Please drop a pin on the map so the collector knows where to go.');
                return;
            }

            loadingOverlay.classList.remove('hidden');
            const formData = new FormData(form);
            const photoInput = document.getElementById('photo');
            if (photoInput.files.length > 0) {
                formData.append('photo', photoInput.files[0]);
            }

            try {
                const response = await fetch('/Scrap/api/create_request.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    alert('Request submitted successfully!');
                    window.location.href = `/Scrap/request.php?id=${data.request_id}`;
                } else {
                    alert(data.message || 'Failed to submit request.');
                }
            } catch (error) {
                console.error('Request submission failed:', error);
                alert('Network error. Please try again.');
            } finally {
                loadingOverlay.classList.add('hidden');
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        const dropoffId = urlParams.get('dropoff');
        if (dropoffId) {
            fetch(`/api/get_dropoff.php?id=${dropoffId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const latlng = {
                            lat: parseFloat(data.dropoff.lat),
                            lng: parseFloat(data.dropoff.lng)
                        };
                        updateMarker(latlng);
                        map.setView(latlng, 16);
                        addressInput.value = data.dropoff.address;
                    }
                })
                .catch(error => console.error('Failed to load drop-off point:', error));
        }

        map.on('click', function(e) {
            var latlng = e.latlng;
            document.getElementById('arrivalInput').value = latlng.lat + ', ' + latlng.lng;
        });
    </script>
</body>
</html>