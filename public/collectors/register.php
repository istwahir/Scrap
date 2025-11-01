<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Collector Registration · Kiambu Recycling</title>
    
        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
    
        <!-- Leaflet for map -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <style>
            .hero-gradient {
                background: radial-gradient(120% 120% at 50% 0%, rgba(16, 185, 129, 0.25) 0%, transparent 60%),
                            linear-gradient(135deg, #064e3b 0%, #0f172a 60%, #020617 100%);
            }
            .glass-card {
                background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
                backdrop-filter: blur(18px);
                box-shadow: 0 20px 45px -25px rgba(15, 118, 110, 0.7);
            }
            .grid-fade {
                background-image: linear-gradient(rgba(99, 102, 241, 0.12) 1px, transparent 1px),
                                  linear-gradient(90deg, rgba(56, 189, 248, 0.12) 1px, transparent 1px);
                background-size: 32px 32px;
            }
            .chip { display:inline-flex; align-items:center; justify-content:center; border-radius:9999px; padding:0.375rem 0.75rem; font-size:0.875rem; font-weight:500; border-width:1px; transition: all 150ms ease; }
        </style>
        <script>
            // Tailwind config for nicer colors if needed
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            brand: { DEFAULT: '#10b981', dark: '#059669' }
                        }
                    }
                }
            }
        </script>
    </head>
        <body class="hero-gradient min-h-screen bg-slate-950 text-slate-100 antialiased">
            <div class="absolute inset-0 grid-fade opacity-15"></div>
        <!-- Top Nav -->
        <nav class="relative z-10 text-white">
            <div class="mx-auto max-w-6xl px-4">
                <div class="flex h-14 items-center justify-between">
                    <a href="/Scrap/" class="font-semibold">Kiambu Recycling</a>
                    <div class="flex items-center gap-4 text-sm">
                        <a href="/Scrap/login.php" class="hover:text-emerald-200">Login</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Header -->
            <header class="relative z-10">
                <div class="mx-auto max-w-6xl px-4 py-10 text-white/95">
                <h1 class="text-3xl font-bold">Become a Collector</h1>
                    <p class="mt-2 max-w-2xl text-emerald-100/85">Apply in three simple steps. We’ll review your application and get in touch shortly.</p>
            </div>
        </header>

        <!-- Main -->
        <main class="mx-auto -mt-8 max-w-6xl px-4 pb-16">
            <section class="glass-card relative z-10 rounded-3xl border border-white/10 p-6 sm:p-8">
                <!-- Stepper -->
                        <ol class="mb-8 flex items-center gap-3 text-sm text-slate-200">
                    <li class="flex items-center gap-2">
                                <span id="stepper1" class="grid h-8 w-8 place-items-center rounded-full bg-emerald-600 text-white">1</span>
                                <span class="hidden sm:inline">Personal</span>
                    </li>
                            <span class="h-px flex-1 bg-white/10"></span>
                    <li class="flex items-center gap-2">
                                <span id="stepper2" class="grid h-8 w-8 place-items-center rounded-full bg-white/10 text-slate-300">2</span>
                                <span class="hidden sm:inline">Vehicle & Areas</span>
                    </li>
                            <span class="h-px flex-1 bg-white/10"></span>
                    <li class="flex items-center gap-2">
                                <span id="stepper3" class="grid h-8 w-8 place-items-center rounded-full bg-white/10 text-slate-300">3</span>
                                <span class="hidden sm:inline">Documents</span>
                    </li>
                </ol>

                <!-- Alerts -->
            <div id="alertBox" class="mb-6 hidden rounded-xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100"></div>

                <!-- Form -->
                <form id="registrationForm" class="space-y-8">
                    <!-- Step 1 -->
                    <div id="step1" class="space-y-6">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label class="text-sm">
                                            <span class="text-white/80">Full Name</span>
                                            <input type="text" name="fullName" required class="mt-1 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-0"/>
                            </label>
                            <label class="text-sm">
                                            <span class="text-white/80">Phone Number</span>
                                            <input type="tel" name="phone" required pattern="^\+254[17]\d{8}$" placeholder="+254700000000" class="mt-1 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-0"/>
                            </label>
                            <label class="text-sm">
                                            <span class="text-white/80">ID Number</span>
                                            <input type="text" name="idNumber" required class="mt-1 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-0"/>
                            </label>
                            <label class="text-sm">
                                            <span class="text-white/80">Date of Birth</span>
                                            <input type="date" name="dateOfBirth" required class="mt-1 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-emerald-400 focus:outline-none focus:ring-0"/>
                            </label>
                        </div>
                        <label class="block text-sm">
                                        <span class="text-white/80">Residential Address</span>
                                        <textarea name="address" required rows="2" class="mt-1 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-0"></textarea>
                        </label>
                                    <div class="rounded-2xl border border-white/10 bg-white/5">
                                        <div class="flex items-center justify-between gap-4 border-b border-white/10 px-3 py-2">
                                            <p class="text-sm text-slate-200">Tap on the map to set your home base.</p>
                                            <button type="button" id="useMyLocationBtn" class="inline-flex items-center gap-2 rounded-full border border-emerald-400/40 bg-emerald-500/10 px-3 py-1.5 text-sm text-emerald-200 hover:bg-emerald-500/20">Use my location</button>
                            </div>
                            <div id="addressMap" class="h-64 w-full"></div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div id="step2" class="hidden space-y-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <label class="text-sm">
                                                <span class="text-white/80">Vehicle Type</span>
                                                <select name="vehicleType" required class="mt-1 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-emerald-400 focus:outline-none focus:ring-0">
                                    <option value="">Select Vehicle Type</option>
                                    <option value="truck">Truck</option>
                                    <option value="pickup">Pickup</option>
                                    <option value="tuktuk">Tuk-tuk</option>
                                    <option value="motorcycle">Motorcycle</option>
                                </select>
                            </label>
                                            <label class="text-sm">
                                                <span class="text-white/80">Vehicle Registration</span>
                                                <input type="text" name="vehicleReg" required placeholder="e.g., KAA 123B" class="mt-1 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-0"/>
                            </label>
                        </div>

                                        <div class="text-sm text-slate-200">
                                            <div class="mb-1 font-medium">Collection Areas</div>
                                            <div class="mt-4 grid gap-3 sm:grid-cols-3" id="areasContainer">
                                                <label class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-emerald-400/60">
                                                    <input type="checkbox" name="areas[]" value="Kiambu Town" class="h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400">
                                                    <span>Kiambu Town</span>
                                                </label>
                                                <label class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-emerald-400/60">
                                                    <input type="checkbox" name="areas[]" value="Thika" class="h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400">
                                                    <span>Thika</span>
                                                </label>
                                                <label class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-emerald-400/60">
                                                    <input type="checkbox" name="areas[]" value="Ruiru" class="h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400">
                                                    <span>Ruiru</span>
                                                </label>
                                                <label class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-emerald-400/60">
                                                    <input type="checkbox" name="areas[]" value="Juja" class="h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400">
                                                    <span>Juja</span>
                                                </label>
                                                <label class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-emerald-400/60">
                                                    <input type="checkbox" name="areas[]" value="Githunguri" class="h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400">
                                                    <span>Githunguri</span>
                                                </label>
                                                <label class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-emerald-400/60">
                                                    <input type="checkbox" name="areas[]" value="Limuru" class="h-4 w-4 rounded border-white/30 bg-transparent text-emerald-500 focus:ring-emerald-400">
                                                    <span>Limuru</span>
                                                </label>
                            </div>
                        </div>

                                        <div class="text-sm text-slate-200">
                                            <div class="mb-1 font-medium">Materials Collected</div>
                                            <div class="mt-4 grid gap-3 sm:grid-cols-3" id="materialsContainer">
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
                    </div>

                    <!-- Step 3 -->
                    <div id="step3" class="hidden space-y-6">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                            <label class="text-sm text-white/80">ID Card Photo (Front)</label>
                                            <div class="mt-2 rounded-2xl border border-dashed border-white/20 bg-white/5 p-4 text-center">
                                    <input id="idCardFront" type="file" name="idCardFront" accept="image/*" required class="hidden"/>
                                                <label for="idCardFront" class="block cursor-pointer text-emerald-300 hover:underline">Click to upload</label>
                                    <img id="previewFront" alt="Preview front" class="mx-auto mt-3 hidden h-28 rounded-lg object-cover"/>
                                </div>
                            </div>
                            <div>
                                            <label class="text-sm text-white/80">ID Card Photo (Back)</label>
                                            <div class="mt-2 rounded-2xl border border-dashed border-white/20 bg-white/5 p-4 text-center">
                                    <input id="idCardBack" type="file" name="idCardBack" accept="image/*" required class="hidden"/>
                                                <label for="idCardBack" class="block cursor-pointer text-emerald-300 hover:underline">Click to upload</label>
                                    <img id="previewBack" alt="Preview back" class="mx-auto mt-3 hidden h-28 rounded-lg object-cover"/>
                                </div>
                            </div>
                            <div>
                                            <label class="text-sm text-white/80">Vehicle Registration Document</label>
                                            <div class="mt-2 rounded-2xl border border-dashed border-white/20 bg-white/5 p-4 text-center">
                                    <input id="vehicleDoc" type="file" name="vehicleDoc" accept="image/*,application/pdf" required class="hidden"/>
                                                <label for="vehicleDoc" class="block cursor-pointer text-emerald-300 hover:underline">Click to upload</label>
                                                <p id="vehicleDocName" class="mt-3 hidden text-sm text-slate-300"></p>
                                </div>
                            </div>
                            <div>
                                            <label class="text-sm text-white/80">Good Conduct Certificate</label>
                                            <div class="mt-2 rounded-2xl border border-dashed border-white/20 bg-white/5 p-4 text-center">
                                    <input id="goodConduct" type="file" name="goodConduct" accept="image/*,application/pdf" required class="hidden"/>
                                                <label for="goodConduct" class="block cursor-pointer text-emerald-300 hover:underline">Click to upload</label>
                                                <p id="goodConductName" class="mt-3 hidden text-sm text-slate-300"></p>
                                </div>
                            </div>
                        </div>
                                    <label class="mt-2 flex items-start gap-3 text-sm">
                                        <input type="checkbox" name="agreement" required class="mt-1 rounded border-white/30 bg-white/5 text-emerald-500 focus:ring-emerald-500"/>
                                        <span class="text-slate-200">I confirm that all information provided is accurate and I agree to the <a href="#" class="text-emerald-300 hover:underline">Terms of Service</a> and <a href="#" class="text-emerald-300 hover:underline">Privacy Policy</a>.</span>
                        </label>
                    </div>

                    <!-- Sticky controls -->
                                <div class="sticky bottom-0 -mx-6 -mb-6 mt-2 border-t border-white/10 bg-slate-900/50 px-6 py-4 backdrop-blur sm:-mx-8 sm:-mb-8 sm:px-8">
                        <div class="flex items-center justify-between">
                                        <button type="button" id="prevBtn" onclick="prevStep()" class="invisible rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-100 hover:bg-white/10">Previous</button>
                            <div class="flex items-center gap-3">
                                            <button type="button" id="nextBtn" onclick="nextStep()" class="rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:opacity-95">Next</button>
                                            <button type="submit" id="submitBtn" class="hidden rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:opacity-95">Submit Application</button>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        </main>

        <script>
            // (chips removed) — using native checkboxes styled like request page

            // Initialize map
            const map = L.map('addressMap').setView([-1.1712, 36.8356], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(map);

            let marker = null;
            let selectedLocation = null;

            function setMarker(latlng) {
                if (marker) map.removeLayer(marker);
                marker = L.marker(latlng).addTo(map);
                selectedLocation = latlng;
            }

            map.on('click', function(e) { setMarker(e.latlng); });

            document.getElementById('useMyLocationBtn').addEventListener('click', () => {
                if (!navigator.geolocation) return showAlert('Geolocation is not supported by your browser');
                navigator.geolocation.getCurrentPosition(pos => {
                    const latlng = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                    map.setView(latlng, 14);
                    setMarker(latlng);
                }, () => showAlert('Unable to retrieve your location'));
            });

            // File previews
            const idFront = document.getElementById('idCardFront');
            const idBack = document.getElementById('idCardBack');
            const previewFront = document.getElementById('previewFront');
            const previewBack = document.getElementById('previewBack');
            const vehicleDoc = document.getElementById('vehicleDoc');
            const vehicleDocName = document.getElementById('vehicleDocName');
            const goodConduct = document.getElementById('goodConduct');
            const goodConductName = document.getElementById('goodConductName');

            function previewImage(input, imgEl) {
                const file = input.files && input.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => { imgEl.src = e.target.result; imgEl.classList.remove('hidden'); };
                reader.readAsDataURL(file);
            }

            idFront?.addEventListener('change', () => previewImage(idFront, previewFront));
            idBack?.addEventListener('change', () => previewImage(idBack, previewBack));
            vehicleDoc?.addEventListener('change', () => { const f = vehicleDoc.files?.[0]; if (f) { vehicleDocName.textContent = f.name; vehicleDocName.classList.remove('hidden'); }});
            goodConduct?.addEventListener('change', () => { const f = goodConduct.files?.[0]; if (f) { goodConductName.textContent = f.name; goodConductName.classList.remove('hidden'); }});

            // Alerts
            function showAlert(msg) {
                const box = document.getElementById('alertBox');
                if (!box) return alert(msg);
                box.textContent = msg;
                box.classList.remove('hidden');
                setTimeout(() => box.classList.add('hidden'), 5000);
            }

            // Form navigation
            let currentStep = 1;
            const totalSteps = 3;

            function updateStepper() {
                for (let i = 1; i <= totalSteps; i++) {
                    const el = document.getElementById('stepper' + i);
                    if (!el) continue;
                    if (i === currentStep) {
                                el.className = 'grid h-8 w-8 place-items-center rounded-full bg-emerald-600 text-white';
                    } else if (i < currentStep) {
                                el.className = 'grid h-8 w-8 place-items-center rounded-full bg-emerald-500/20 text-emerald-200';
                    } else {
                                el.className = 'grid h-8 w-8 place-items-center rounded-full bg-white/10 text-slate-300';
                    }
                }
            }

            function showStep(step) {
                for (let i = 1; i <= totalSteps; i++) {
                    const s = document.getElementById('step' + i);
                    if (s) s.classList.toggle('hidden', i !== step);
                }
                document.getElementById('prevBtn').classList.toggle('invisible', step === 1);
                document.getElementById('nextBtn').classList.toggle('hidden', step === totalSteps);
                document.getElementById('submitBtn').classList.toggle('hidden', step !== totalSteps);
                updateStepper();
            }

            function validateCurrentStep() {
                const container = document.getElementById('step' + currentStep);
                const inputs = container.querySelectorAll('input[required], select[required], textarea[required]');
                for (const input of inputs) {
                    if (!input.checkValidity()) { input.reportValidity(); return false; }
                }
                if (currentStep === 1 && !selectedLocation) {
                    showAlert('Please select your location on the map.');
                    return false;
                }
                if (currentStep === 2) {
                    const hasArea = document.querySelectorAll('input[name="areas[]"]:checked').length > 0;
                    const hasMat = document.querySelectorAll('input[name="materials[]"]:checked').length > 0;
                    if (!hasArea) { showAlert('Please select at least one collection area.'); return false; }
                    if (!hasMat) { showAlert('Please select at least one material type.'); return false; }
                }
                return true;
            }

            function nextStep() { if (validateCurrentStep() && currentStep < totalSteps) { currentStep++; showStep(currentStep); } }
            function prevStep() { if (currentStep > 1) { currentStep--; showStep(currentStep); } }

            // Expose for buttons
            window.nextStep = nextStep;
            window.prevStep = prevStep;

            // Submit
            document.getElementById('registrationForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!validateCurrentStep()) return;
                const submitBtn = document.getElementById('submitBtn');
                const nextBtn = document.getElementById('nextBtn');
                submitBtn.disabled = true; nextBtn.disabled = true; submitBtn.textContent = 'Submitting…';

                const formData = new FormData(this);
                formData.append('latitude', selectedLocation.lat);
                formData.append('longitude', selectedLocation.lng);

                try {
                    const response = await fetch('/Scrap/api/collectors/register.php', { method: 'POST', body: formData });
                    let result = null;
                    const contentType = response.headers.get('content-type') || '';
                    try {
                        if (contentType.includes('application/json')) {
                            result = await response.json();
                        } else {
                            const text = await response.text();
                            result = { status: response.ok ? 'success' : 'error', message: text };
                        }
                    } catch (parseErr) {
                        result = { status: 'error', message: 'Unexpected response from server.' };
                    }

                    if (response.ok && result.status === 'success') {
                        alert('Registration submitted successfully! We will review your application and contact you soon.');
                        window.location.href = '/Scrap/profile.php';
                    } else {
                        console.error('Register API error:', result);
                        showAlert(result && result.message ? result.message : 'Failed to submit registration. Please review all fields and try again.');
                    }
                } catch (err) {
                    console.error(err);
                    showAlert('Network error. Please try again.');
                } finally {
                    submitBtn.disabled = false; nextBtn.disabled = false; submitBtn.textContent = 'Submit Application';
                }
            });

            // Init
            showStep(1);
        </script>
    </body>
    </html>