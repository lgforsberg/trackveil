<?php
/**
 * Stats Overview Partial
 * This is an HTML fragment loaded by HTMX - returns HTML only, not a full page
 */

require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/queries.php';
require_once __DIR__ . '/../../src/helpers.php';

requireLogin();

$siteId = $_GET['site_id'] ?? null;
$accountId = currentAccountId();

// Verify site belongs to account
$site = getSite($siteId, $accountId);
if (!$site) {
    echo '<div class="text-red-600">Site not found or access denied.</div>';
    exit;
}

// Get stats
$stats = getStats($siteId);
$chartData = getVisitorsChartData($siteId, 7);
$topPages = getTopPages($siteId, 5);
$topReferrers = getTopReferrers($siteId, 5);
$browserStats = getBrowserStats($siteId);
$deviceStats = getDeviceStats($siteId);
?>

<!-- Stats Cards - Using gradients like site overview -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Page Views -->
    <div class="rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%);">
        <div class="flex items-center justify-between">
            <div class="text-sm opacity-90">Page Views</div>
            <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
        </div>
        <div class="mt-3">
            <div class="text-3xl font-bold">
                <?php echo formatNumber($stats['page_views_today']); ?>
            </div>
            <div class="text-sm mt-1 opacity-90">
                <?php echo changeIndicator($stats['views_change'], true); ?>
                <span class="opacity-75">vs yesterday</span>
            </div>
        </div>
        <div class="mt-3 text-xs opacity-75">
            <?php echo formatNumber($stats['page_views']); ?> total (30 days)
        </div>
    </div>
    
    <!-- Unique Visitors -->
    <div class="rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #2DD4BF 0%, #14B8A6 100%);">
        <div class="flex items-center justify-between">
            <div class="text-sm opacity-90">Unique Visitors</div>
            <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </div>
        <div class="mt-3">
            <div class="text-3xl font-bold">
                <?php echo formatNumber($stats['unique_visitors_today']); ?>
            </div>
            <div class="text-sm mt-1 opacity-75">today</div>
        </div>
        <div class="mt-3 text-xs opacity-75">
            <?php echo formatNumber($stats['unique_visitors']); ?> total (30 days)
        </div>
    </div>
    
    <!-- Avg. Session - Placeholder -->
    <div class="bg-white dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl shadow-sm p-6">
        <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. Session</div>
        <div class="text-3xl font-bold text-gray-400 dark:text-gray-500 mt-3">-</div>
        <div class="text-sm mt-1 text-gray-500 dark:text-gray-400">Coming soon</div>
    </div>
    
    <!-- Bounce Rate - Placeholder -->
    <div class="bg-white dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl shadow-sm p-6">
        <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Bounce Rate</div>
        <div class="text-3xl font-bold text-gray-400 dark:text-gray-500 mt-3">-</div>
        <div class="text-sm mt-1 text-gray-500 dark:text-gray-400">Coming soon</div>
    </div>
</div>

<!-- Visitors Chart -->
<div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-sm p-6 mb-8">
    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Visitors (Last 7 Days)</h3>
    <canvas id="visitors-chart"></canvas>
</div>

<!-- Two Column Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Top Pages -->
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Top Pages</h3>
        <div class="space-y-3">
            <?php if (empty($topPages)): ?>
                <div class="text-center py-6 text-gray-500 text-sm">
                    No page views yet
                </div>
            <?php else: ?>
                <?php foreach ($topPages as $page): ?>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                <?php echo e($page['page_title'] ?: 'Untitled'); ?>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                <?php echo e($page['page_url']); ?>
                            </div>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                <?php echo formatNumber($page['views']); ?>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                <?php echo formatNumber($page['unique_visitors']); ?> unique
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Top Referrers -->
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Top Referrers</h3>
        <div class="space-y-3">
            <?php if (empty($topReferrers)): ?>
                <div class="text-center py-6 text-gray-500 text-sm">
                    No referrer data yet
                </div>
            <?php else: ?>
                <?php foreach ($topReferrers as $ref): ?>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo e($ref['source'] === 'Direct' ? 'Direct' : getDomain($ref['source'])); ?>
                            </div>
                            <?php if ($ref['source'] !== 'Direct'): ?>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    <?php echo e(truncate($ref['source'], 40)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4 text-sm font-semibold text-gray-900 dark:text-white">
                            <?php echo formatNumber($ref['views']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Browser & Device Stats -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Browsers -->
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Browsers</h3>
        <div class="space-y-2">
            <?php foreach ($browserStats as $browser): ?>
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e($browser['browser']); ?></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400"><?php echo $browser['percentage']; ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full" style="width: <?php echo $browser['percentage']; ?>%; background: linear-gradient(to right, #2DD4BF, #38BDF8);"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Devices -->
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Devices</h3>
        <div class="space-y-2">
            <?php foreach ($deviceStats as $device): ?>
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize"><?php echo e($device['device']); ?></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400"><?php echo $device['percentage']; ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full" style="width: <?php echo $device['percentage']; ?>%; background: linear-gradient(to right, #A855F7, #EC4899);"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Initialize Chart -->
<script>
(function() {
    const ctx = document.getElementById('visitors-chart');
    if (!ctx) return;
    
    // Detect dark mode
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? '#374151' : '#F1F5F9';
    const textColor = isDark ? '#D1D5DB' : '#6B7280';
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($chartData, 'date')); ?>,
            datasets: [{
                label: 'Unique Visitors',
                data: <?php echo json_encode(array_column($chartData, 'visitors')); ?>,
                borderColor: '#38BDF8',
                backgroundColor: 'rgba(56, 189, 248, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#38BDF8',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 3,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: isDark ? '#1F2937' : '#0F172A',
                    padding: 12,
                    cornerRadius: 8,
                    titleColor: '#fff',
                    bodyColor: '#fff'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: textColor
                    },
                    grid: {
                        color: gridColor
                    }
                },
                x: {
                    ticks: {
                        color: textColor
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
})();
</script>

