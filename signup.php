<?php
$requireAuth = false;
$pageTitle = 'Sign Up - Kiambu Recycling & Scraps';
include 'includes/header.php';
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="auth-card rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Join Kiambu Recycling</h1>
            <p class="text-gray-600">Create your account and start making a difference</p>
        </div>

        <!-- Registration Form -->
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Full Name
                </label>
                <input type="text" id="name" placeholder="John Doe"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200">
                <p class="text-sm text-gray-500 mt-1">Enter your full name</p>
            </div>

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
                <p class="text-sm text-gray-500 mt-1">Choose a strong password (min 6 characters)</p>
            </div>

            <div>
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm Password
                </label>
                <input type="password" id="confirmPassword" placeholder="••••••••"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200">
                <p class="text-sm text-gray-500 mt-1">Re-enter your password</p>
            </div>

            <button onclick="register()" id="registerBtn"
                    class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200 font-medium">
                Create Account
            </button>
        </div>

        <!-- Loading Step -->
        <div id="loadingStep" class="hidden text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium">Creating your account...</p>
        </div>

        <!-- Success Message -->
        <div id="successMessage" class="hidden text-center py-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Account Created!</h3>
            <p class="text-gray-600 mb-4">Welcome to Kiambu Recycling. Redirecting to dashboard...</p>
        </div>

        <!-- Error Message -->
        <div id="errorMessage" class="hidden mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p id="errorText" class="text-red-700 text-sm"></p>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-gray-600 text-sm">
                Already have an account?
                <a href="/Scrap/login.php" class="text-green-600 hover:text-green-700 font-medium transition duration-200">
                    Sign in here
                </a>
            </p>
            <div class="mt-4">
                <a href="/" class="text-gray-500 hover:text-gray-700 text-sm transition duration-200">
                    ← Back to home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Check if already authenticated
        if (sessionStorage.getItem('user_id')) {
            window.location.href = '/Scrap/dashboard.html';
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
                        window.location.href = '/Scrap/dashboard.html';
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


        // PWA support
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/Scrap/public/service-worker.js');
            });
        }
    </script>

<?php include 'includes/footer.php'; ?>