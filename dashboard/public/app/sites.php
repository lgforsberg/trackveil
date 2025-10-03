<?php
/**
 * Site Management Page
 */

require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/queries.php';
require_once __DIR__ . '/../../src/helpers.php';

requireLogin();

$user = currentUser();
$accountId = currentAccountId();
$sites = getSites($accountId);

$pageTitle = 'Sites';
require __DIR__ . '/../../templates/header.php';
require __DIR__ . '/../../templates/nav.php';
?>

<main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-7xl">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Your Sites</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your tracked websites</p>
        </div>
        <!-- Phase 2: Add button to create sites via web UI -->
        <div class="text-sm text-gray-500">
            Use CLI tool to add sites (Phase 2: web UI)
        </div>
    </div>
    
    <?php if (empty($sites)): ?>
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-12 text-center border border-gray-100 dark:border-gray-700">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No sites yet</h3>
            <p class="text-gray-600 mb-6">Create your first site using the CLI tool</p>
            <code class="bg-gray-100 text-gray-800 px-4 py-2 rounded-lg text-sm block max-w-2xl mx-auto">
                ./tools/create-site -account "<?php echo e($user['account_name']); ?>" -name "My Site" -domain "example.com"
            </code>
        </div>
    <?php else: ?>
        <!-- Sites Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($sites as $site): ?>
                <?php 
                // Get basic stats for this site
                $siteStats = getStats($site['id'], 7); 
                ?>
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-sm p-6 hover:shadow-md transition">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">
                                <?php echo e($site['name']); ?>
                            </h3>
                            <a href="https://<?php echo e($site['domain']); ?>" 
                               target="_blank"
                               class="text-sm text-sky-600 hover:text-sky-700 hover:underline">
                                <?php echo e($site['domain']); ?>
                            </a>
                        </div>
                        <a href="/app/?site=<?php echo urlencode($site['id']); ?>" 
                           class="text-sky-600 hover:text-sky-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </a>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Page Views (7d):</span>
                            <span class="font-semibold text-gray-900 dark:text-white"><?php echo formatNumber($siteStats['page_views']); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Unique Visitors (7d):</span>
                            <span class="font-semibold text-gray-900 dark:text-white"><?php echo formatNumber($siteStats['unique_visitors']); ?></span>
                        </div>
                    </div>
                    
                    <!-- Site ID (for installation) -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Site ID:</div>
                        <div class="flex items-center gap-2">
                            <code class="flex-1 text-xs bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-300 px-2 py-1 rounded font-mono truncate">
                                <?php echo e($site['id']); ?>
                            </code>
                            <button 
                                onclick="copyToClipboard('<?php echo e($site['id']); ?>')"
                                class="text-xs text-sky-600 hover:text-sky-700 whitespace-nowrap"
                            >
                                Copy
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Instructions -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h4 class="font-semibold text-blue-900 mb-2">Installation Instructions</h4>
            <p class="text-sm text-blue-800 mb-3">
                Add this snippet to your website (replace SITE_ID with your actual site ID):
            </p>
            <code class="block bg-blue-900 text-blue-100 px-4 py-3 rounded-lg text-sm overflow-x-auto">
&lt;script async src="https://cdn.trackveil.net/tracker.min.js" 
        data-site-id="<span class="text-yellow-300">YOUR_SITE_ID</span>"&gt;&lt;/script&gt;
            </code>
        </div>
    <?php endif; ?>
</main>

<script>
// Copy to clipboard helper
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Site ID copied to clipboard!');
    }, function() {
        prompt('Copy this site ID:', text);
    });
}
</script>

<?php require __DIR__ . '/../../templates/footer.php'; ?>

