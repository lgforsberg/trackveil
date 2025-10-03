<?php
require_once __DIR__ . '/../src/auth.php';
$user = currentUser();
?>

<nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40 transition-colors duration-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo -->
            <a href="/app/" class="flex items-center gap-3 group">
                <img src="/assets/img/icon_64.png" alt="Trackveil" width="40" height="40" class="rounded-xl">
                <span class="font-semibold text-lg text-gray-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition">Trackveil</span>
            </a>
            
            <!-- Navigation Links -->
            <div class="hidden md:flex items-center gap-6">
                <a href="/app/" class="text-sm text-gray-700 dark:text-gray-300 hover:text-sky-600 dark:hover:text-sky-400 transition">Dashboard</a>
                <a href="/app/sites.php" class="text-sm text-gray-700 dark:text-gray-300 hover:text-sky-600 dark:hover:text-sky-400 transition">Sites</a>
                <a href="/app/account.php" class="text-sm text-gray-700 dark:text-gray-300 hover:text-sky-600 dark:hover:text-sky-400 transition">Account</a>
            </div>
            
            <!-- User Menu & Dark Mode Toggle -->
            <div class="flex items-center gap-4">
                <!-- Dark Mode Toggle -->
                <button 
                    id="darkModeToggle" 
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                    aria-label="Toggle dark mode"
                >
                    <!-- Sun icon (shown in dark mode) -->
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <!-- Moon icon (shown in light mode) -->
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
                
                <div class="hidden sm:block text-sm text-gray-600 dark:text-gray-400">
                    <?php echo e($user['name'] ?: $user['email']); ?>
                    <span class="text-xs text-gray-400 dark:text-gray-500">(<?php echo e($user['account_name']); ?>)</span>
                </div>
                <a href="/logout.php" 
                   onclick="return confirmLogout()"
                   class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>

