# Site Overview Feature

Inspired by Plausible Analytics - shows all your sites with mini sparkline graphs.

## How It Works

### Smart Redirect on Login

```
Login → Check number of sites
         ├─ 0 sites: Show onboarding
         ├─ 1 site: Go directly to site dashboard
         └─ 2+ sites: Show site overview
```

### Site Overview (`/app/sites-overview.php`)

**Displays:**
- **Combined stats** (top row)
  - Total views today (all sites)
  - Unique visitors today (all sites)
  - Active sites today
  - Total sites in account

- **Site grid** (cards)
  - Site name & domain
  - Mini sparkline chart (7 days of visitors)
  - Today's visitors with % change
  - Click → detailed dashboard

### Site Detail Dashboard (`/app/site-detail.php`)

**Navigation:**
- If multiple sites: "← All Sites" breadcrumb
- Site name & domain as header
- Quick switcher dropdown (top right)

**Content:**
- Full stats for selected site
- Auto-refreshing every 60s
- Charts, top pages, referrers, etc.

## User Experience

### Scenario 1: Single Site User

```
Login → Directly to site dashboard
(No overview needed - only one site)
```

### Scenario 2: Multi-Site User

```
Login → Site Overview (grid with sparklines)
Click "kea.gl" card → Detailed dashboard for kea.gl
Click "← All Sites" → Back to overview
```

### Scenario 3: New User

```
Login → Onboarding page
(Instructions to create first site)
```

## Features

### Combined Stats Cards

Colored gradient cards showing:
- **Blue:** Total views today
- **Turquoise:** Unique visitors
- **Purple:** Active sites
- **Gray:** Total sites

### Site Cards

Each card shows:
- Site name (bold, clickable)
- Domain (smaller, clickable link)
- **Mini sparkline graph** (Chart.js)
- **Today's visitors** (large number)
- **Change indicator** (↑ green, ↓ red, → gray)

### Sparkline Charts

- Line chart with gradient fill
- 7 days of visitor data
- No axes (minimal design)
- Tooltip on hover
- Matches your turquoise color scheme

## SQL Queries

### Combined Stats (All Sites)
```sql
SELECT 
    COUNT(*) FILTER (WHERE viewed_at::date = CURRENT_DATE) as views_today,
    COUNT(DISTINCT visitor_id) FILTER (WHERE viewed_at::date = CURRENT_DATE) as visitors_today,
    COUNT(DISTINCT site_id) FILTER (WHERE viewed_at::date = CURRENT_DATE) as active_sites_today
FROM page_views
WHERE site_id IN (SELECT id FROM sites WHERE account_id = ?)
```

### Per-Site Sparklines
```sql
-- Runs for each site
SELECT 
    DATE(viewed_at) as date,
    COUNT(DISTINCT visitor_id) as visitors
FROM page_views
WHERE site_id = ? 
AND viewed_at > CURRENT_DATE - INTERVAL '7 days'
GROUP BY DATE(viewed_at)
ORDER BY date
```

## Chart.js Configuration

Minimal sparkline setup:
```javascript
new Chart(ctx, {
    type: 'line',
    data: { ... },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { 
            x: { display: false },
            y: { display: false }
        }
    }
});
```

## File Structure

```
dashboard/public/app/
├── index.php              # Smart redirect based on site count
├── sites-overview.php     # NEW: Multi-site overview with sparklines
├── site-detail.php        # RENAMED: Detailed dashboard (was index.php)
├── sites.php              # Site management (existing)
└── account.php            # Account settings (existing)
```

## Navigation Flow

```
/app/
  ↓
  Check site count
  ├─ 1 site → /app/site-detail.php?site=xxx
  └─ 2+ sites → /app/sites-overview.php
                  ↓ Click site card
                /app/site-detail.php?site=xxx
                  ↓ Click "← All Sites"
                /app/sites-overview.php
```

## Future Enhancements

Phase 2+:
- [ ] Add "All Sites Combined" detailed view
  - Combined charts
  - Cross-site comparisons
  - Top pages across all sites
  
- [ ] Site groups/tags
  - Organize sites by project/client
  - Filter overview by group

- [ ] Customizable overview
  - Choose which stats to show on cards
  - Drag-and-drop reordering

- [ ] Quick actions on cards
  - Three-dot menu
  - Quick settings
  - Copy tracking code
  - Pause/archive site

## Performance

- **Overview page**: 1 query for combined stats + 1 query per site (fast)
- **Sparklines**: Lightweight Chart.js instances (minimal config)
- **Can scale to**: 50+ sites before needing optimization (pagination, lazy loading)

## Comparison with Plausible

**What we match:**
- ✅ Grid layout of site cards
- ✅ Mini sparkline graphs
- ✅ Today's visitors with change %
- ✅ Click card → detailed view

**What we add:**
- ✅ Combined stats at top (all sites aggregated)
- ✅ Gradient colored stat cards
- ✅ Breadcrumb navigation
- ✅ Quick switcher dropdown

**What's different:**
- We show all sites at once (Plausible paginates at ~10 sites)
- We have combined account stats
- We auto-redirect single-site users (better UX)

Much better than a dropdown! 🎯

