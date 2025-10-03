<?php
/**
 * Dashboard Analytics Queries
 * PHP 7.2 compatible
 */

require_once __DIR__ . '/db.php';

/**
 * Get all sites for an account
 */
function getSites($accountId) {
    return queryAll("
        SELECT 
            id,
            name,
            domain,
            created_at
        FROM sites
        WHERE account_id = ?
        ORDER BY name
    ", [$accountId]);
}

/**
 * Get a single site
 */
function getSite($siteId, $accountId) {
    return queryOne("
        SELECT 
            s.id,
            s.name,
            s.domain,
            s.created_at,
            s.account_id
        FROM sites s
        WHERE s.id = ? AND s.account_id = ?
    ", [$siteId, $accountId]);
}

/**
 * Get stats overview for a site
 */
function getStats($siteId, $days = 30) {
    $db = getDB();
    
    // Page views (last 30 days and today)
    $views = queryOne("
        SELECT 
            COUNT(*) as total,
            COUNT(*) FILTER (WHERE viewed_at::date = CURRENT_DATE) as today,
            COUNT(*) FILTER (WHERE viewed_at::date = CURRENT_DATE - 1) as yesterday
        FROM page_views
        WHERE site_id = ? AND viewed_at > CURRENT_DATE - INTERVAL '30 days'
    ", [$siteId]);
    
    // Unique visitors
    $visitors = queryOne("
        SELECT 
            COUNT(DISTINCT visitor_id) as total,
            COUNT(DISTINCT visitor_id) FILTER (WHERE viewed_at::date = CURRENT_DATE) as today
        FROM page_views
        WHERE site_id = ? AND viewed_at > CURRENT_DATE - INTERVAL '30 days'
    ", [$siteId]);
    
    // Calculate change percentages
    $viewsChange = 0;
    if ($views['yesterday'] > 0) {
        $viewsChange = round((($views['today'] - $views['yesterday']) / $views['yesterday']) * 100, 1);
    }
    
    return [
        'page_views' => (int)$views['total'],
        'page_views_today' => (int)$views['today'],
        'views_change' => $viewsChange,
        'unique_visitors' => (int)$visitors['total'],
        'unique_visitors_today' => (int)$visitors['today'],
    ];
}

/**
 * Get daily visitor chart data
 */
function getVisitorsChartData($siteId, $days = 7) {
    return queryAll("
        SELECT 
            DATE(viewed_at) as date,
            COUNT(DISTINCT visitor_id) as visitors,
            COUNT(*) as views
        FROM page_views
        WHERE site_id = ? 
        AND viewed_at > CURRENT_DATE - INTERVAL '7 days'
        GROUP BY DATE(viewed_at)
        ORDER BY date
    ", [$siteId]);
}

/**
 * Get top pages
 */
function getTopPages($siteId, $limit = 10) {
    return queryAll("
        SELECT 
            page_url,
            page_title,
            COUNT(*) as views,
            COUNT(DISTINCT visitor_id) as unique_visitors
        FROM page_views
        WHERE site_id = ?
        AND viewed_at > CURRENT_DATE - INTERVAL '7 days'
        GROUP BY page_url, page_title
        ORDER BY views DESC
        LIMIT ?
    ", [$siteId, $limit]);
}

/**
 * Get top referrers
 */
function getTopReferrers($siteId, $limit = 10) {
    return queryAll("
        SELECT 
            CASE 
                WHEN referrer IS NULL OR referrer = '' THEN 'Direct'
                ELSE referrer
            END as source,
            COUNT(*) as views,
            COUNT(DISTINCT visitor_id) as visitors
        FROM page_views
        WHERE site_id = ?
        AND viewed_at > CURRENT_DATE - INTERVAL '7 days'
        GROUP BY referrer
        ORDER BY views DESC
        LIMIT ?
    ", [$siteId, $limit]);
}

/**
 * Get browser statistics
 */
function getBrowserStats($siteId) {
    return queryAll("
        SELECT 
            COALESCE(browser_name, 'Unknown') as browser,
            COUNT(*) as count,
            ROUND(100.0 * COUNT(*) / SUM(COUNT(*)) OVER (), 1) as percentage
        FROM page_views
        WHERE site_id = ?
        AND viewed_at > CURRENT_DATE - INTERVAL '7 days'
        GROUP BY browser_name
        ORDER BY count DESC
        LIMIT 10
    ", [$siteId]);
}

/**
 * Get device statistics
 */
function getDeviceStats($siteId) {
    return queryAll("
        SELECT 
            COALESCE(device_type, 'Unknown') as device,
            COUNT(*) as count,
            ROUND(100.0 * COUNT(*) / SUM(COUNT(*)) OVER (), 1) as percentage
        FROM page_views
        WHERE site_id = ?
        AND viewed_at > CURRENT_DATE - INTERVAL '7 days'
        GROUP BY device_type
        ORDER BY count DESC
    ", [$siteId]);
}

/**
 * Get recent page views
 */
function getRecentPageViews($siteId, $limit = 20) {
    return queryAll("
        SELECT 
            page_url,
            page_title,
            referrer,
            browser_name,
            device_type,
            viewed_at
        FROM page_views
        WHERE site_id = ?
        ORDER BY viewed_at DESC
        LIMIT ?
    ", [$siteId, $limit]);
}

