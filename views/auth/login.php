<?php
// Redirect to login if not authenticated
$currentFile = basename($_SERVER['PHP_SELF']);
if ($currentFile !== 'login.php' && $currentFile !== 'signup.php' && $currentFile !== 'index.php') {
    header('Location: /Scrap/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kiambu Recycling & Scraps</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom styles -->
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
    </style>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#16a34a">
    <link rel="manifest" href="/Scrap/public/manifest.json">
</head>
<body class="hero-gradient min-h-screen flex items-center justify-center p-4 bg-slate-950 text-slate-100 antialiased">
    <div class="absolute inset-0 grid-fade opacity-15"></div>
    
    <div class="glass-card relative z-10 rounded-3xl p-10 w-full max-w-md border border-white/10">
        <!-- Decorative elements -->
        <div class="absolute -left-10 -top-14 h-36 w-36 rounded-full bg-emerald-400/20 blur-3xl"></div>
        <div class="absolute -bottom-12 right-0 h-40 w-40 rounded-full bg-sky-400/20 blur-3xl"></div>
        
        <!-- Header -->
        <div class="relative text-center mb-10">
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-full flex items-center justify-center shadow-xl shadow-emerald-500/30">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-white mb-3">Welcome Back</h1>
            <p class="text-emerald-100/80">Sign in to your Kiambu Recycling account</p>
        </div>

        <!-- Login Form -->
        <div class="relative space-y-6">
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-[0.3em] text-white/60 mb-3">
                    Email Address
                </label>
                <div class="relative">
                    <input type="email" id="email" placeholder="your@email.com"
                           class="w-full px-4 py-3 rounded-2xl border border-white/15 bg-white/5 text-white placeholder:text-slate-400 shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-5 h-5 text-emerald-300/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-[0.3em] text-white/60 mb-3">
                    Password
                </label>
                <input type="password" id="password" placeholder="••••••••"
                       class="w-full px-4 py-3 rounded-2xl border border-white/15 bg-white/5 text-white placeholder:text-slate-400 shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
            </div>

            <button onclick="login()" id="loginBtn"
                    class="w-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 text-slate-900 py-3 rounded-2xl hover:shadow-xl hover:shadow-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 transition duration-200 font-semibold">
                Sign In
            </button>
        </div>

        <!-- Loading Step -->
        <div id="loadingStep" class="hidden text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-400 mx-auto mb-4"></div>
            <p class="text-emerald-100/80 font-medium">Verifying...</p>
        </div>

        <!-- Error Message -->
        <div id="errorMessage" class="hidden mt-6 p-4 bg-red-500/10 border border-red-400/30 rounded-2xl">
            <p id="errorText" class="text-red-200 text-sm"></p>
        </div>

        <!-- Footer -->
        <div class="relative mt-10 text-center">
            <p class="text-slate-300/80 text-sm">
                Don't have an account?
                <a href="/Scrap/signup.php" class="text-emerald-300 hover:text-emerald-200 font-semibold transition duration-200">
                    Sign up here
                </a>
            </p>
            <div class="mt-4">
                <a href="/Scrap/" class="text-slate-400/80 hover:text-slate-300 text-sm transition duration-200">
                    ← Back to home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Clear sessionStorage on page load if coming from logout
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('logout') === '1') {
            sessionStorage.clear();
            // Remove the logout parameter from URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Check if already authenticated and redirect to appropriate dashboard
        if (sessionStorage.getItem('user_id')) {
            const userRole = sessionStorage.getItem('user_role');
            if (userRole === 'admin') {
                window.location.href = '/Scrap/public/admin/dashboard.php';
            } else if (userRole === 'collector') {
                window.location.href = '/Scrap/public/collectors/dashboard.php';
            } else {
                window.location.href = '/Scrap/dashboard.php';
            }
        }

        // Show error message
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            errorText.textContent = message;
            errorDiv.classList.remove('hidden');
        }

        // Hide error message
        function hideError() {
            document.getElementById('errorMessage').classList.add('hidden');
        }

        // Login user
        async function login() {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const btn = document.getElementById('loginBtn');

            if (!email) {
                showError('Please enter your email address');
                return;
            }

            // Basic email validation
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('Please enter a valid email address');
                return;
            }

            if (!password) {
                showError('Please enter your password');
                return;
            }

            hideError();
            btn.disabled = true;
            btn.textContent = 'Signing in...';

            try {
                const response = await fetch('/Scrap/api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ email: email, password: password })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Store user data
                    sessionStorage.setItem('user_id', data.user.id);
                    sessionStorage.setItem('user_role', data.user.role);
                    sessionStorage.setItem('email', data.user.email);
                    sessionStorage.setItem('user_name', data.user.name);

                    // Redirect based on role
                    if (data.user.role === 'admin') {
                        window.location.href = '/Scrap/views/admin/dashboard.php';
                    } else if (data.user.role === 'collector') {
                        window.location.href = '/Scrap/views/collectors/dashboard.php';
                    } else {
                        // Regular user/citizen dashboard
                        window.location.href = '/Scrap/views/citizens/dashboard.php';
                    }
                } else {
                    showError(data.message || 'Login failed');
                }
            } catch (error) {
                showError('Network error. Please check your connection and try again.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Sign In';
            }
        }

        // Handle Enter key
        document.getElementById('email').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                login();
            }
        });

        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                login();
            }
        });

        // PWA support
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/Scrap/public/service-worker.js');
            });
        }
    </script>
</body>
</html>