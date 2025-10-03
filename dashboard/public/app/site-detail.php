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

// If no sites, redirect to home (will show onboarding)
if (empty($sites)) {
    header('Location: /app/');
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

$pageTitle = $selectedSite['name'] . ' - Dashboard';
require __DIR__ . '/../../templates/header.php';
require __DIR__ . '/../../templates/nav.php';
?>

<main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-7xl">
    <!-- Breadcrumb / Site Header -->
    <div class="mb-6">
        <?php if (count($sites) > 1): ?>
            <a href="/app/sites-overview.php" class="text-sm text-gray-600 hover:text-gray-900 inline-flex items-center mb-3">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                All Sites
            </a>
        <?php endif; ?>
        
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?php echo e($selectedSite['name']); ?></h1>
                <a href="https://<?php echo e($selectedSite['domain']); ?>" 
                   target="_blank"
                   class="text-sm text-sky-600 hover:text-sky-700 hover:underline">
                    <?php echo e($selectedSite['domain']); ?> ↗
                </a>
            </div>
            
            <?php if (count($sites) > 1): ?>
                <!-- Quick Site Switcher -->
                <select 
                    onchange="window.location.href = '/app/site-detail.php?site=' + this.value"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white shadow-sm hover:border-gray-400 focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                >
                    <?php foreach ($sites as $site): ?>
                        <option value="<?php echo e($site['id']); ?>" <?php echo $site['id'] === $selectedSiteId ? 'selected' : ''; ?>>
                            <?php echo e($site['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
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

