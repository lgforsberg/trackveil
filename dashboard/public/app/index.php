<?php
/**
 * Dashboard Entry Point
 * Redirects based on number of sites:
 * - 1 site: Go directly to site dashboard
 * - 2+ sites: Show site overview
 */

require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/queries.php';

requireLogin();

$accountId = currentAccountId();
$sites = getSites($accountId);

// No sites: Show onboarding
if (empty($sites)) {
    require __DIR__ . '/../../src/helpers.php';
    $pageTitle = 'Welcome';
    require __DIR__ . '/../../templates/header.php';
    require __DIR__ . '/../../templates/nav.php';
    ?>
    
    <main class="container mx-auto px-4 py-12 max-w-4xl text-center">
        <div class="bg-white rounded-xl shadow-sm p-12">
            <div class="inline-flex p-4 rounded-2xl bg-gray-100 mb-6">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-3">Welcome to Trackveil!</h2>
            <p class="text-gray-600 mb-8">
                You don't have any sites set up yet. Create your first site to start tracking visitors.
            </p>
            
            <div class="bg-gray-100 rounded-lg p-4 mb-6 text-left max-w-xl mx-auto">
                <div class="text-sm font-medium text-gray-700 mb-2">Use the CLI tool:</div>
                <code class="text-xs bg-white px-3 py-2 rounded block">
                    ./tools/create-site -account "<?php echo e($user['account_name']); ?>" -name "My Site" -domain "example.com"
                </code>
            </div>
            
            <a href="/app/sites.php" class="inline-block px-6 py-3 text-white font-medium rounded-lg btn-gradient shadow hover:shadow-glow">
                View Sites
            </a>
        </div>
    </main>
    
    <?php
    require __DIR__ . '/../../templates/footer.php';
    exit;
}

// One site: Go directly to detailed dashboard
if (count($sites) === 1) {
    header('Location: /app/site-detail.php?site=' . urlencode($sites[0]['id']));
    exit;
}

// Multiple sites: Show overview
header('Location: /app/sites-overview.php');
exit;

