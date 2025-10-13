<?php
require_once __DIR__ . '/../config.php';

// Check authentication if required
if (!isset($requireAuth) || $requireAuth) {
    require_once __DIR__ . '/auth.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Kiambu Recycling & Scraps'; ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom styles -->
    <style>
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .stat-number {
            background: linear-gradient(135deg, #16a34a, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#16a34a">
    <link rel="manifest" href="/Scrap/manifest.json">
    <link rel="apple-touch-icon" href="/Scrap/images/icon-192x192.png">

    <!-- Additional head content -->
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-green-600">Kiambu Recycling</h1>
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Authenticated user menu -->
                        <a href="/Scrap/dashboard.php" class="text-gray-700 hover:text-green-600 transition duration-200">Dashboard</a>
                        <a href="/Scrap/map.php" class="text-gray-700 hover:text-green-600 transition duration-200">Map</a>
                        <a href="/Scrap/request.php" class="text-gray-700 hover:text-green-600 transition duration-200">New Request</a>
                        <a href="/Scrap/guide.php" class="text-gray-700 hover:text-green-600 transition duration-200">Guide</a>

                        <div class="relative">
                            <button onclick="toggleProfileMenu()" class="flex items-center text-gray-700 hover:text-green-600 transition duration-200">
                                <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="/Scrap/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile Settings</a>
                                <a href="/Scrap/history.html" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Request History</a>
                                <a href="/Scrap/rewards.html" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Rewards</a>
                                <hr class="my-1">
                                <button onclick="logout()" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    Logout
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Non-authenticated user menu -->
                        <a href="#features" class="text-gray-700 hover:text-green-600 transition duration-200">Features</a>
                        <a href="#how-it-works" class="text-gray-700 hover:text-green-600 transition duration-200">How It Works</a>
                        <a href="#impact" class="text-gray-700 hover:text-green-600 transition duration-200">Impact</a>
                        <a href="/Scrap/guide.html" class="text-gray-700 hover:text-green-600 transition duration-200">Guide</a>
                        <a href="/Scrap/login.php" class="text-gray-700 hover:text-green-600 transition duration-200">Login</a>
                        <a href="/Scrap/signup.php" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                            Get Started
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.classList.toggle('hidden');
        }

        function logout() {
            fetch('/api/logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            }).then(() => {
                sessionStorage.clear();
                window.location.href = '/Scrap/';
            });
        }

        // Close profile menu when clicking outside
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('profileMenu');
            const button = e.target.closest('button');
            if (menu && button && !button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>