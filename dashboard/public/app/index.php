<?php
/**
 * Dashboard Home
 */

require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/queries.php';
require_once __DIR__ . '/../../src/helpers.php';

requireLogin();

$user = currentUser();
$accountId = currentAccountId();
$sites = getSites($accountId);

// If no sites, show onboarding
if (empty($sites)) {
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
            
            <a href="/app/sites.php" class="inline-block px-6 py-3 text-white font-medium rounded-lg btn-gradient shadow hover:shadow-glow">
                Create Your First Site
            </a>
        </div>
    </main>
    
    <?php
    require __DIR__ . '/../../templates/footer.php';
    exit;
}

// Default to first site
$selectedSiteId = $_GET['site'] ?? $sites[0]['id'];
$selectedSite = null;

foreach ($sites as $site) {
    if ($site['id'] === $selectedSiteId) {
        $selectedSite = $site;
        break;
    }
}

// If site not found or doesn't belong to account, redirect to first site
if (!$selectedSite) {
    redirect('/app/?site=' . $sites[0]['id']);
}

$pageTitle = 'Dashboard';
require __DIR__ . '/../../templates/header.php';
require __DIR__ . '/../../templates/nav.php';
?>

<main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-7xl">
    <!-- Site Selector -->
    <div class="mb-6">
        <label for="site-selector" class="block text-sm font-medium text-gray-700 mb-2">
            Select Site
        </label>
        <select 
            id="site-selector" 
            onchange="window.location.href = '/app/?site=' + this.value"
            class="rounded-lg border border-gray-300 px-4 py-2 bg-white shadow-sm focus:ring-2 focus:ring-sky-500 focus:border-transparent"
        >
            <?php foreach ($sites as $site): ?>
                <option value="<?php echo e($site['id']); ?>" <?php echo $site['id'] === $selectedSiteId ? 'selected' : ''; ?>>
                    <?php echo e($site['name']); ?> (<?php echo e($site['domain']); ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- Stats Overview - Auto-refreshes every 60 seconds -->
    <div 
        id="stats-container"
        hx-get="/partials/stats-overview.php?site_id=<?php echo urlencode($selectedSiteId); ?>"
        hx-trigger="load, every 60s"
        hx-indicator="#loading-indicator"
    >
        <!-- Stats will load here -->
        <div class="text-center py-12 text-gray-500">
            <svg class="animate-spin h-8 w-8 mx-auto mb-3 text-sky-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Loading stats...
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../templates/footer.php'; ?>

