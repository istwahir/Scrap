<?php
// Redirect authenticated users to their dashboard
session_start();
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'user';
    if ($role === 'admin') {
        header('Location: /Scrap/views/admin/dashboard.php');
    } elseif ($role === 'collector') {
        header('Location: /Scrap/views/collectors/dashboard.php');
    } else {
        header('Location: /Scrap/views/citizens/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Kiambu Recycling & Scraps</title>

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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-white mb-3">Join Kiambu Recycling</h1>
            <p class="text-emerald-100/80">Create your account and start making a difference</p>
        </div>

        <!-- Registration Form -->
        <div class="relative space-y-6">
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-[0.3em] text-white/60 mb-3">
                    Full Name
                </label>
                <input type="text" id="name" placeholder="John Doe"
                       class="w-full px-4 py-3 rounded-2xl border border-white/15 bg-white/5 text-white placeholder:text-slate-400 shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
            </div>

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
                <p class="text-xs text-slate-400/80 mt-2">Choose a strong password (min 6 characters)</p>
            </div>

            <div>
                <label for="confirmPassword" class="block text-xs font-semibold uppercase tracking-[0.3em] text-white/60 mb-3">
                    Confirm Password
                </label>
                <input type="password" id="confirmPassword" placeholder="••••••••"
                       class="w-full px-4 py-3 rounded-2xl border border-white/15 bg-white/5 text-white placeholder:text-slate-400 shadow-inner shadow-black/20 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
            </div>

            <button onclick="register()" id="registerBtn"
                    class="w-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-sky-400 text-slate-900 py-3 rounded-2xl hover:shadow-xl hover:shadow-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 transition duration-200 font-semibold">
                Create Account
            </button>
        </div>

        <!-- Loading Step -->
        <div id="loadingStep" class="hidden text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-400 mx-auto mb-4"></div>
            <p class="text-emerald-100/80 font-medium">Creating your account...</p>
        </div>

        <!-- Success Message -->
        <div id="successMessage" class="hidden text-center py-8">
            <div class="w-16 h-16 bg-emerald-500/15 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">Account Created!</h3>
            <p class="text-emerald-100/80 mb-4">Welcome to Kiambu Recycling. Redirecting to dashboard...</p>
        </div>

        <!-- Error Message -->
        <div id="errorMessage" class="hidden mt-6 p-4 bg-red-500/10 border border-red-400/30 rounded-2xl">
            <p id="errorText" class="text-red-200 text-sm"></p>
        </div>

        <!-- Footer -->
        <div class="relative mt-10 text-center">
            <p class="text-slate-300/80 text-sm">
                Already have an account?
                <a href="/Scrap/views/auth/login.php" class="text-emerald-300 hover:text-emerald-200 font-semibold transition duration-200">
                    Sign in here
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

        // Register user
        async function register() {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const btn = document.getElementById('registerBtn');

            if (!name) {
                showError('Please enter your full name');
                return;
            }

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
                showError('Please enter a password');
                return;
            }

            if (password.length < 6) {
                showError('Password must be at least 6 characters long');
                return;
            }

            if (password !== confirmPassword) {
                showError('Passwords do not match');
                return;
            }

            hideError();
            btn.disabled = true;
            btn.textContent = 'Creating...';

            try {
                const response = await fetch('/Scrap/api/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ name: name, email: email, password: password })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Show success message
                    document.getElementById('successMessage').classList.remove('hidden');

                    // Store user data
                    sessionStorage.setItem('user_id', data.user.id);
                    sessionStorage.setItem('user_role', data.user.role);
                    sessionStorage.setItem('email', data.user.email);

                    // Redirect after a short delay
                    setTimeout(() => {
                        window.location.href = '/Scrap/views/citizens/dashboard.php';
                    }, 2000);
                } else {
                    showError(data.message || 'Registration failed');
                }
            } catch (error) {
                showError('Network error. Please check your connection and try again.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Create Account';
            }
        }

        // Handle Enter key
        document.getElementById('email').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                register();
            }
        });

        document.getElementById('confirmPassword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                register();
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