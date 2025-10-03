<?php
require_once __DIR__ . '/../src/auth.php';
$user = currentUser();
?>

<nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-2xl bg-navy text-sky-300 shadow">
                    <svg viewBox="0 0 64 64" width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="32" r="20" stroke="url(#g1)" stroke-width="4"/>
                        <circle cx="32" cy="32" r="12" stroke="url(#g1)" stroke-width="4" opacity=".6"/>
                        <path d="M32 32 L50 20" stroke="url(#g1)" stroke-width="6" stroke-linecap="round"/>
                        <circle cx="26" cy="38" r="3" fill="#38BDF8"/>
                        <circle cx="36" cy="28" r="3" fill="#2DD4BF"/>
                        <circle cx="44" cy="36" r="3" fill="#38BDF8"/>
                        <defs>
                            <linearGradient id="g1" x1="12" y1="52" x2="52" y2="12" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2DD4BF"/>
                                <stop offset="1" stop-color="#38BDF8"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <span class="font-semibold text-lg">Trackveil</span>
            </div>
            
            <!-- Navigation Links -->
            <div class="hidden md:flex items-center gap-6">
                <a href="/app/" class="text-sm hover:text-sky-600">Dashboard</a>
                <a href="/app/sites.php" class="text-sm hover:text-sky-600">Sites</a>
                <a href="/app/account.php" class="text-sm hover:text-sky-600">Account</a>
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

