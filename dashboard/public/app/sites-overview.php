<?php
/**
 * Sites Overview - Multi-site dashboard
 * Shows all sites with mini sparklines (like Plausible)
 */

require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/queries.php';
require_once __DIR__ . '/../../src/helpers.php';

requireLogin();

$user = currentUser();
$accountId = currentAccountId();
$sites = getSites($accountId);

// Get combined stats for all sites
$combinedStats = queryOne("
    SELECT 
        COUNT(*) FILTER (WHERE viewed_at::date = CURRENT_DATE) as views_today,
        COUNT(*) FILTER (WHERE viewed_at::date = CURRENT_DATE - 1) as views_yesterday,
        COUNT(DISTINCT visitor_id) FILTER (WHERE viewed_at::date = CURRENT_DATE) as visitors_today,
        COUNT(DISTINCT site_id) FILTER (WHERE viewed_at::date = CURRENT_DATE) as active_sites_today
    FROM page_views
    WHERE site_id IN (
        SELECT id FROM sites WHERE account_id = ?
    )
", [$accountId]);

// Calculate change
$viewsChange = 0;
if ($combinedStats['views_yesterday'] > 0) {
    $viewsChange = round((($combinedStats['views_today'] - $combinedStats['views_yesterday']) / $combinedStats['views_yesterday']) * 100, 1);
}

// Get sparkline data for each site (last 7 days)
$siteSparklines = [];
foreach ($sites as $site) {
    $sparklineData = queryAll("
        SELECT 
            DATE(viewed_at) as date,
            COUNT(DISTINCT visitor_id) as visitors
        FROM page_views
        WHERE site_id = ? 
        AND viewed_at > CURRENT_DATE - INTERVAL '7 days'
        GROUP BY DATE(viewed_at)
        ORDER BY date
    ", [$site['id']]);
    
    $siteSparklines[$site['id']] = $sparklineData;
}

// Get today's stats for each site
$siteTodayStats = [];
foreach ($sites as $site) {
    $todayStats = queryOne("
        SELECT 
            COUNT(DISTINCT visitor_id) FILTER (WHERE viewed_at::date = CURRENT_DATE) as visitors_today,
            COUNT(DISTINCT visitor_id) FILTER (WHERE viewed_at::date = CURRENT_DATE - 1) as visitors_yesterday
        FROM page_views
        WHERE site_id = ?
    ", [$site['id']]);
    
    $change = 0;
    if ($todayStats['visitors_yesterday'] > 0) {
        $change = round((($todayStats['visitors_today'] - $todayStats['visitors_yesterday']) / $todayStats['visitors_yesterday']) * 100);
    }
    
    $siteTodayStats[$site['id']] = [
        'visitors_today' => (int)$todayStats['visitors_today'],
        'change' => $change
    ];
}

$pageTitle = 'All Sites';
require __DIR__ . '/../../templates/header.php';
require __DIR__ . '/../../templates/nav.php';
?>

<main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-7xl">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Your Sites</h1>
    <p class="text-gray-600 dark:text-gray-400 mb-8">Overview of all tracked websites</p>
    
    <!-- Combined Stats (All Sites) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Views Today -->
        <div class="rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%);">
            <div class="text-sm opacity-90 mb-1">Total Views Today</div>
            <div class="text-3xl font-bold"><?php echo formatNumber($combinedStats['views_today']); ?></div>
            <div class="text-sm mt-1 opacity-75">
                <?php echo $viewsChange > 0 ? '↑' : ($viewsChange < 0 ? '↓' : '→'); ?> 
                <?php echo abs($viewsChange); ?>% vs yesterday
            </div>
        </div>
        
        <!-- Unique Visitors Today -->
        <div class="rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #2DD4BF 0%, #14B8A6 100%);">
            <div class="text-sm opacity-90 mb-1">Unique Visitors Today</div>
            <div class="text-3xl font-bold"><?php echo formatNumber($combinedStats['visitors_today']); ?></div>
            <div class="text-sm mt-1 opacity-75">across all sites</div>
        </div>
        
        <!-- Active Sites -->
        <div class="rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #A855F7 0%, #9333EA 100%);">
            <div class="text-sm opacity-90 mb-1">Active Sites</div>
            <div class="text-3xl font-bold"><?php echo $combinedStats['active_sites_today']; ?></div>
            <div class="text-sm mt-1 opacity-75">received traffic today</div>
        </div>
        
        <!-- Total Sites -->
        <div class="rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #64748B 0%, #475569 100%);">
            <div class="text-sm opacity-90 mb-1">Total Sites</div>
            <div class="text-3xl font-bold"><?php echo count($sites); ?></div>
            <div class="text-sm mt-1 opacity-75">in your account</div>
        </div>
    </div>
    
    <!-- Sites Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($sites as $site): ?>
            <?php 
            $sparkline = $siteSparklines[$site['id']];
            $todayStats = $siteTodayStats[$site['id']];
            $chartId = 'chart-' . substr($site['id'], 0, 8);
            ?>
            
            <a href="/app/site-detail.php?site=<?php echo urlencode($site['id']); ?>" 
               class="block bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md dark:hover:shadow-lg transition p-6 group border border-gray-100 dark:border-gray-700">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 dark:text-white truncate group-hover:text-sky-600 dark:group-hover:text-sky-400 transition">
                            <?php echo e($site['name']); ?>
                        </h3>
                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate">
                            <?php echo e($site['domain']); ?>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
                
                <!-- Mini Sparkline Chart -->
                <div class="h-20 mb-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-2">
                    <canvas id="<?php echo $chartId; ?>"></canvas>
                </div>
                
                <!-- Today's Stats -->
                <div class="flex items-baseline justify-between">
                    <div>
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?php echo $todayStats['visitors_today']; ?>
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">visitors today</span>
                    </div>
                    <div class="text-sm font-medium <?php echo $todayStats['change'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                        <?php if ($todayStats['change'] > 0): ?>
                            ↑ <?php echo $todayStats['change']; ?>%
                        <?php elseif ($todayStats['change'] < 0): ?>
                            ↓ <?php echo abs($todayStats['change']); ?>%
                        <?php else: ?>
                            → 0%
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            
            <!-- Initialize sparkline chart -->
            <script>
            (function() {
                const ctx = document.getElementById('<?php echo $chartId; ?>');
                if (!ctx) return;
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_column($sparkline, 'date')); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_column($sparkline, 'visitors')); ?>,
                            borderColor: '#38BDF8',
                            backgroundColor: 'rgba(56, 189, 248, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { 
                                enabled: true,
                                displayColors: false,
                                callbacks: {
                                    title: function(items) {
                                        return items[0].label;
                                    },
                                    label: function(item) {
                                        return item.parsed.y + ' visitors';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { display: false },
                            y: { display: false }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            })();
            </script>
        <?php endforeach; ?>
    </div>
</main>

<?php require __DIR__ . '/../../templates/footer.php'; ?>

