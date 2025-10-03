    <!-- Loading indicator (shown by HTMX during requests) -->
    <div id="loading-indicator" class="htmx-indicator fixed top-4 right-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg px-4 py-2 flex items-center gap-2">
            <svg class="animate-spin h-4 w-4 text-sky-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm text-gray-700 dark:text-gray-300">Loading...</span>
        </div>
    </div>
    
    <!-- Simple VanillaJS utilities -->
    <script>
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function() {
                const html = document.documentElement;
                const isDark = html.classList.contains('dark');
                
                if (isDark) {
                    html.classList.remove('dark');
                    localStorage.setItem('trackveil-theme', 'light');
                } else {
                    html.classList.add('dark');
                    localStorage.setItem('trackveil-theme', 'dark');
                }
            });
        }
        
        // HTMX configuration
        htmx.config.defaultSwapStyle = 'innerHTML';
        htmx.config.timeout = 10000;
        
        // Add loading indicator to all HTMX requests
        document.body.addEventListener('htmx:beforeRequest', function(evt) {
            document.getElementById('loading-indicator').classList.add('htmx-indicator');
        });
        
        document.body.addEventListener('htmx:afterRequest', function(evt) {
            document.getElementById('loading-indicator').classList.remove('htmx-indicator');
        });
        
        // Simple logout confirmation
        function confirmLogout() {
            return confirm('Are you sure you want to log out?');
        }
    </script>
</body>
</html>

