<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Offline — Kiambu Recycling</title>
    <meta name="theme-color" content="#064e3b">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Small visual helpers for offline page */
        .pulse-dot { width:10px; height:10px; border-radius:9999px; background:#34d399; box-shadow:0 0 0 0 rgba(52,211,153,0.7); animation:pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow:0 0 0 0 rgba(52,211,153,0.6);} 70% { box-shadow:0 0 0 12px rgba(52,211,153,0);} 100% { box-shadow:0 0 0 0 rgba(52,211,153,0);} }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased flex items-center justify-center">
    <main class="w-full max-w-3xl p-6">
        <div class="mx-auto rounded-2xl bg-white shadow-lg ring-1 ring-slate-900/5 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center p-8">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center h-14 w-14 rounded-lg bg-emerald-50">
                            <!-- Offline SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2 2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-semibold text-slate-900">You're offline</h1>
                            <p class="text-sm text-slate-500">Kiambu Recycling is unable to reach the network right now.</p>
                        </div>
                    </div>

                    <p class="text-sm text-slate-600">Some features are available from cache — you can still view saved pages, previously loaded maps, and queued requests. Try reconnecting or use the buttons below.</p>

                    <div class="flex flex-wrap gap-3">
                        <button id="retryBtn" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-500 focus:outline-none">Retry connection</button>
                        <a href="/Scrap/" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Open cached home</a>
                        <a href="/Scrap/views/citizens/map.php" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Open cached map</a>
                    </div>

                    <div class="mt-4 text-xs text-slate-400">
                        <div class="flex items-center gap-2"><span class="pulse-dot"></span><span>Background sync available — queued requests will be sent when you're back online.</span></div>
                    </div>
                </div>

                <div class="px-4 py-6 md:py-0">
                    <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                        <h3 class="text-sm font-medium text-slate-800">What you can try</h3>
                        <ul class="mt-3 space-y-2 text-sm text-slate-600">
                            <li>• Check your device's network settings and reconnect to Wi‑Fi or mobile data.</li>
                            <li>• If on mobile, try switching airplane mode on/off.</li>
                            <li>• Clear cached data in your browser if problems persist.</li>
                        </ul>

                        <div class="mt-4 border-t pt-4 text-xs text-slate-500">
                            Need help? <a href="/Scrap/views/auth/login.php" class="text-emerald-600 hover:underline">Sign in</a> or <a href="/Scrap/views/auth/signup.php" class="text-emerald-600 hover:underline">contact support</a>.
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-100 bg-slate-50 px-6 py-3 text-xs text-slate-500 flex items-center justify-between">
                <div>
                    <strong class="text-slate-700">Kiambu Recycling</strong> — Offline mode
                </div>
                <div id="status" class="text-emerald-600">Status: <span id="onlineState">offline</span></div>
            </div>
        </div>
    </main>

        <script>
            const retryBtn = document.getElementById('retryBtn');
            const onlineState = document.getElementById('onlineState');

            // Helper: fetch with timeout
            function fetchWithTimeout(url, options = {}, timeout = 4000) {
                return new Promise((resolve, reject) => {
                    const timer = setTimeout(() => reject(new Error('timeout')), timeout);
                    fetch(url, options).then(res => {
                        clearTimeout(timer);
                        resolve(res);
                    }).catch(err => {
                        clearTimeout(timer);
                        reject(err);
                    });
                });
            }

            // Check actual connectivity by requesting a lightweight resource.
            // We avoid auto-reloading on initial load to prevent loops; only reload when
            // we detect recovery from an explicit user retry or an "online" event.
            async function checkConnectivity({ reloadOnSuccess = false } = {}) {
                onlineState.textContent = 'checking...';
                try {
                    // Same-origin HEAD request to the app root. Adjust path if your app is mounted elsewhere.
                    const url = window.location.origin + '/Scrap/';
                    const res = await fetchWithTimeout(url, { method: 'HEAD', cache: 'no-store' }, 4000);
                    // Treat OK or any response as success (service worker may return opaque responses)
                    if (res && (res.ok || res.type === 'opaque' || res.status === 0)) {
                        onlineState.textContent = 'online';
                        if (reloadOnSuccess) {
                            // Give a short delay so the UI updates before reload
                            setTimeout(() => window.location.reload(), 200);
                        }
                        return true;
                    }
                } catch (err) {
                    // fallthrough to offline
                }
                onlineState.textContent = 'offline';
                return false;
            }

            retryBtn.addEventListener('click', async () => {
                retryBtn.disabled = true;
                retryBtn.textContent = 'Checking...';
                try {
                    const ok = await checkConnectivity({ reloadOnSuccess: true });
                    if (!ok) alert('Still offline — try again in a moment.');
                } finally {
                    retryBtn.disabled = false;
                    retryBtn.textContent = 'Retry connection';
                }
            });

            // When the browser fires an "online" event, attempt a real connectivity check
            window.addEventListener('online', () => checkConnectivity({ reloadOnSuccess: true }));
            // Update visual state when the browser reports offline quickly
            window.addEventListener('offline', () => { onlineState.textContent = 'offline'; });

            // Initial status check (do not auto-reload here)
            checkConnectivity({ reloadOnSuccess: false });
        </script>
</body>
</html>