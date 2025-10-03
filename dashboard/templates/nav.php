<?php
require_once __DIR__ . '/../src/auth.php';
$user = currentUser();
?>

<nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo -->
            <a href="/app/" class="flex items-center gap-3 group">
                <img src="/assets/img/icon_64.png" alt="Trackveil" width="40" height="40" class="rounded-xl">
                <span class="font-semibold text-lg group-hover:text-sky-600 transition">Trackveil</span>
            </a>
            
            <!-- Navigation Links -->
            <div class="hidden md:flex items-center gap-6">
                <a href="/app/" class="text-sm hover:text-sky-600 transition">Dashboard</a>
                <a href="/app/sites.php" class="text-sm hover:text-sky-600 transition">Sites</a>
                <a href="/app/account.php" class="text-sm hover:text-sky-600 transition">Account</a>
            </div>
            
            <!-- User Menu -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-sm text-gray-600">
                    <?php echo e($user['name'] ?: $user['email']); ?>
                    <span class="text-xs text-gray-400">(<?php echo e($user['account_name']); ?>)</span>
                </div>
                <a href="/logout.php" 
                   onclick="return confirmLogout()"
                   class="text-sm text-gray-600 hover:text-gray-900">
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>

