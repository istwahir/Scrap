<!-- Footer -->
    <footer class="glass-nav border-t border-white/10 text-slate-200 py-12 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-white text-lg font-semibold mb-4">Kiambu Recycling</h3>
                    <p class="text-sm">
                        Sustainable waste management platform for Kiambu County,
                        connecting residents with recycling opportunities.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/Scrap/map.html" class="hover:text-white transition duration-200">Find Drop-off Points</a></li>
                        <li><a href="/Scrap/guide.html" class="hover:text-white transition duration-200">Recycling Guide</a></li>
                        <li><a href="/Scrap/request.html" class="hover:text-white transition duration-200">Request Pickup</a></li>
                        <li><a href="/Scrap/tracking.html" class="hover:text-white transition duration-200">Live Tracking</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/Scrap/guide.html" class="hover:text-white transition duration-200">How to Recycle</a></li>
                        <li><a href="/Scrap/guide.html#faq" class="hover:text-white transition duration-200">FAQ</a></li>
                        <li><a href="tel:+254700000000" class="hover:text-white transition duration-200">Contact Us</a></li>
                        <li><a href="/Scrap/guide.html#materials" class="hover:text-white transition duration-200">Accepted Materials</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Connect</h4>
                    <p class="text-sm mb-4">
                        Follow us for tips, updates, and environmental news.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">📘</a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">🐦</a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">📷</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Global utilities script -->
    <script>
        // Global utility functions
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            } text-white`;

            notification.innerHTML = `
                <div class="flex items-center">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        ×
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // CSRF token handling
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        }
    </script>

    <!-- Additional scripts -->
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>