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
        .auth-bg {
            background: linear-gradient(135deg, #16a34a 0%, #059669 100%);
        }
        .auth-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
    </style>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#16a34a">
    <link rel="manifest" href="/Scrap/public/manifest.json">
</head>
<body class="auth-bg min-h-screen flex items-center justify-center p-4">
    <div class="auth-card rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h1>
            <p class="text-gray-600">Sign in to your Kiambu Recycling account</p>
        </div>

        <!-- Login Form -->
        <div class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Address
                </label>
                <div class="relative">
                    <input type="email" id="email" placeholder="your@email.com"
                           class="w-full pl-4 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-1">Enter your email address</p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password
                </label>
                <input type="password" id="password" placeholder="••••••••"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200">
                <p class="text-sm text-gray-500 mt-1">Enter your password</p>
            </div>

            <button onclick="login()" id="loginBtn"
                    class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200 font-medium">
                Sign In
            </button>
        </div>

        <!-- Loading Step -->
        <div id="loadingStep" class="hidden text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium">Verifying...</p>
        </div>

        <!-- Error Message -->
        <div id="errorMessage" class="hidden mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p id="errorText" class="text-red-700 text-sm"></p>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-gray-600 text-sm">
                Don't have an account?
                <a href="/Scrap/signup.php" class="text-green-600 hover:text-green-700 font-medium transition duration-200">
                    Sign up here
                </a>
            </p>
            <div class="mt-4">
                <a href="/Scrap/dashboard.html" class="text-gray-500 hover:text-gray-700 text-sm transition duration-200">
                    ← Back to home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Check if already authenticated
        if (sessionStorage.getItem('user_id')) {
            window.location.href = '/Scrap/dashboard.php';
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
                    body: JSON.stringify({ email: email, password: password })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Store user data
                    sessionStorage.setItem('user_id', data.user.id);
                    sessionStorage.setItem('user_role', data.user.role);
                    sessionStorage.setItem('email', data.user.email);

                    // Redirect to dashboard
                    window.location.href = '/Scrap/dashboard.php';
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


        // PWA support
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/Scrap/public/service-worker.js');
            });
        }
    </script>
</body>
</html>