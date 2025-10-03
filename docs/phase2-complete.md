# Phase 2 Dashboard - COMPLETE ✅

**Built:** October 2, 2025  
**Tech Stack:** PHP 7.2 + HTMX + Tailwind (NO Alpine.js, NO React)  
**Status:** Ready for deployment and testing

---

## 🎉 What's Been Built

### 1. Marketing Website (www/)
- ✅ Beautiful landing page (based on your example.html)
- ✅ Tailwind CSS with your design system
- ✅ Static HTML (fast, simple)
- ✅ Ready to deploy to `trackveil.net`

### 2. Analytics Dashboard (dashboard/)
- ✅ Full PHP application
- ✅ Login/logout with sessions
- ✅ Dashboard home with auto-refreshing stats
- ✅ Site management page
- ✅ Account settings page
- ✅ HTMX for dynamic updates (NO Alpine.js)
- ✅ Chart.js for graphs
- ✅ Your Tailwind design system
- ✅ PHP 7.2 compatible
- ✅ Ready to deploy to `dashboard.trackveil.net`

---

## 📁 Complete File Structure

```
trackveil/
│
├── www/                          # Marketing Website
│   ├── public/                  # Deploy this to trackveil.net
│   │   ├── index.html           # Landing page
│   │   ├── img/logo_icon.png
│   │   └── .htaccess
│   ├── design_manual.md
│   └── landing_page_wire_frame.md
│
├── dashboard/                    # Analytics Dashboard
│   ├── public/                  # Deploy this to dashboard.trackveil.net
│   │   ├── index.php           # Entry (redirects to login/app)
│   │   ├── login.php           # Login page
│   │   ├── logout.php          # Logout handler
│   │   ├── app/                # Dashboard pages (protected)
│   │   │   ├── index.php       # Stats dashboard
│   │   │   ├── sites.php       # Site management
│   │   │   └── account.php     # Account settings
│   │   ├── partials/           # HTMX fragments
│   │   │   └── stats-overview.php  # Auto-refreshing stats
│   │   └── .htaccess
│   │
│   ├── src/                     # PHP application code
│   │   ├── config.php           # Configuration loader
│   │   ├── db.php               # PostgreSQL connection
│   │   ├── auth.php             # Authentication system
│   │   ├── queries.php          # SQL analytics queries
│   │   └── helpers.php          # Utility functions
│   │
│   ├── templates/               # Shared templates
│   │   ├── header.php           # HTML head
│   │   ├── nav.php              # Navigation bar
│   │   └── footer.php           # Closing scripts
│   │
│   ├── .env                     # Config (create from env.example)
│   ├── env.example
│   ├── README.md
│   └── DEPLOYMENT.md
│
├── api/                         # Go Tracking API (Phase 1)
├── tracker/                     # JavaScript Tracker (Phase 1)
├── database/                    # Migrations
│   └── migrations/
│       ├── 001_initial_schema.sql
│       ├── 002_seed_test_data.sql
│       ├── 003_change_site_id_to_hash.sql
│       └── 004_add_user_passwords.sql  ← NEW
│
└── tools/                       # CLI Tools
    └── create-site/             # Site creation tool
```

---

## 🚀 Deployment Checklist

### Database
- [x] Schema created (Phase 1)
- [ ] Run migration 004 (add user passwords)

### Marketing Site
- [x] Landing page built
- [ ] Deploy to /var/www/trackveil.net
- [ ] Configure nginx for trackveil.net
- [ ] Get SSL certificate
- [ ] Test at https://trackveil.net

### Dashboard
- [x] PHP application built
- [ ] Deploy to /var/www/dashboard.trackveil.net
- [ ] Create .env with database credentials
- [ ] Configure nginx for dashboard.trackveil.net
- [ ] Get SSL certificate
- [ ] Set file permissions
- [ ] Test at https://dashboard.trackveil.net

---

## 🎓 HTMX Primer (30 Second Version)

### What It Does

**Replaces this JavaScript:**
```javascript
fetch('/stats.php').then(r => r.text()).then(html => {
    document.getElementById('container').innerHTML = html;
});
```

**With this HTML:**
```html
<div hx-get="/stats.php" id="container"></div>
```

### The 5 Attributes You Need

1. `hx-get="/url"` - Load content via GET
2. `hx-post="/url"` - Submit via POST
3. `hx-target="#id"` - Where to put response
4. `hx-trigger="load"` - When to trigger (load, click, every Xs)
5. `hx-swap="innerHTML"` - How to insert (default)

**That's 95% of what you'll use.**

### Real Example from Dashboard

**Dashboard home** (`app/index.php`):
```html
<div 
    hx-get="/partials/stats-overview.php?site_id=xxx"
    hx-trigger="load, every 60s"
>
    Loading stats...
</div>
```

Means:
- On page load, GET `/partials/stats-overview.php`
- Put response inside this div
- Repeat every 60 seconds

**stats-overview.php** just returns HTML:
```php
<?php
$stats = getStats($siteId); // SQL query
?>
<div class="grid">
    <div class="card">Views: <?= $stats['views'] ?></div>
</div>
```

**Result:** Auto-refreshing dashboard with ZERO JavaScript code written by you!

---

## 💾 Database Updates Needed

Run this migration:

```bash
PGPASSWORD='Konsult&793' psql -h pg1.trackveil.net -U markedo -d trackveil \
  -f database/migrations/004_add_user_passwords.sql
```

This adds:
- `password_hash` column
- Email verification fields (for future)
- `last_login_at` tracking
- Sets test password: "password123"

---

## 🧪 Test Credentials

**Email:** test@example.com  
**Password:** password123

**Account:** Test Account  
**Sites:** Whatever you've created (kea.gl, markedo.com, etc.)

---

## 📊 What the Dashboard Shows

### Dashboard Home (`/app/`)
- **Stats Cards:** Page views, unique visitors (auto-refresh every 60s)
- **Visitors Chart:** Last 7 days line graph
- **Top Pages:** Most visited pages (last 7 days)
- **Top Referrers:** Where traffic comes from
- **Browser Stats:** Browser breakdown with percentages
- **Device Stats:** Desktop/mobile/tablet split

### Sites Page (`/app/sites.php`)
- **List all sites** in your account
- **Quick stats** for each site (7-day views)
- **Site ID** with copy button
- **Installation instructions**

### Account Page (`/app/account.php`)
- **Account details**
- **Team members** list
- **Usage stats**

---

## 🎯 Key Features

### HTMX Magic (What You Get)

✅ **Auto-refreshing stats** - Updates every 60s without page reload  
✅ **Site selector** - Switch sites, stats update instantly  
✅ **No page reloads** - Feels like a SPA  
✅ **No build step** - Edit PHP, refresh browser  
✅ **No JSON** - PHP returns HTML directly  
✅ **Zero AJAX code** - HTMX handles it all  

### Pure PHP Benefits

✅ **All logic server-side** - Your comfort zone  
✅ **Direct SQL queries** - Full PostgreSQL power  
✅ **Session auth** - Simple, secure  
✅ **Easy debugging** - View source shows real HTML  
✅ **Fast development** - No frameworks to learn  

---

## 📖 Quick Start Guide

### 1. Run Database Migration

```bash
cd /Users/lgforsberg/Projects/trackveil

PGPASSWORD='Konsult&793' psql -h pg1.trackveil.net -U markedo -d trackveil \
  -f database/migrations/004_add_user_passwords.sql
```

### 2. Test Locally

```bash
cd dashboard/public
php -S localhost:8000

# Visit: http://localhost:8000
# Login: test@example.com / password123
```

You should see:
- Login page with your design
- After login: Dashboard with stats from Markedo account (kea.gl, markedo.com)
- Auto-refreshing stats
- Charts
- Site management

### 3. Deploy to Production

```bash
# Deploy dashboard
rsync -avz --exclude='.env' dashboard/ lg@markedo.com:/var/www/dashboard.trackveil.net/

# Create .env on server
ssh lg@markedo.com
cd /var/www/dashboard.trackveil.net
cp env.example .env
nano .env
# Add database credentials from config/config.md

# Deploy website
rsync -avz www/public/ lg@markedo.com:/var/www/trackveil.net/
```

### 4. Configure Nginx

See `dashboard/DEPLOYMENT.md` for complete nginx configuration.

### 5. Get SSL

```bash
sudo certbot --nginx -d dashboard.trackveil.net -d trackveil.net
```

---

## 🔍 HTMX in Action - Real Examples

### Auto-Refreshing Stats

**File:** `dashboard/public/app/index.php`

```html
<div 
    hx-get="/partials/stats-overview.php?site_id=xxx"
    hx-trigger="load, every 60s"
>
    Loading...
</div>
```

**What happens:**
1. Page loads → HTMX calls `stats-overview.php`
2. PHP queries database, returns HTML
3. HTMX swaps HTML into div
4. After 60s → repeat steps 2-3
5. User sees auto-updating stats without any JavaScript!

### Site Selector (VanillaJS)

**File:** `dashboard/public/app/index.php`

```html
<select onchange="window.location.href='/app/?site=' + this.value">
    <option value="site1">Site 1</option>
</select>
```

Simple VanillaJS - no HTMX needed for navigation.

---

## 💡 Development Workflow

### Adding a New Feature

**Example: Add "Recent Visitors" section**

**Step 1:** Create SQL query (`src/queries.php`):
```php
function getRecentVisitors($siteId, $limit = 10) {
    return queryAll("
        SELECT DISTINCT ON (visitor_id)
            visitor_id,
            page_url,
            viewed_at,
            browser_name,
            device_type
        FROM page_views
        WHERE site_id = ?
        ORDER BY visitor_id, viewed_at DESC
        LIMIT ?
    ", [$siteId, $limit]);
}
```

**Step 2:** Create partial (`partials/recent-visitors.php`):
```php
<?php
require_once '../../src/auth.php';
require_once '../../src/db.php';
require_once '../../src/queries.php';

requireLogin();

$siteId = $_GET['site_id'];
$visitors = getRecentVisitors($siteId);
?>

<div class="space-y-2">
    <?php foreach ($visitors as $v): ?>
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-sm"><?= e($v['page_url']) ?></div>
            <div class="text-xs text-gray-500">
                <?= e($v['browser_name']) ?> · <?= timeAgo($v['viewed_at']) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
```

**Step 3:** Add to dashboard (`app/index.php`):
```html
<div class="bg-white rounded-xl p-6">
    <h3>Recent Visitors</h3>
    <div 
        hx-get="/partials/recent-visitors.php?site_id=xxx"
        hx-trigger="load, every 30s"
    >
        Loading...
    </div>
</div>
```

**Done!** Auto-refreshing recent visitors with ~30 lines of code.

---

## 🎨 Design System Integration

Your design from `design_manual.md` is already integrated:

- ✅ Turquoise/Sky gradient buttons
- ✅ Navy backgrounds for cards
- ✅ Radar icon in navigation
- ✅ Rounded corners (2xl)
- ✅ Shadow/glow effects
- ✅ Clean, minimal aesthetic

All Tailwind classes match your color palette.

---

## 📈 What You Can Do Now

### Immediately (Local Testing)
```bash
cd dashboard/public
php -S localhost:8000

# Login and explore:
# - Dashboard with real kea.gl data
# - Auto-refreshing stats
# - Site management
# - Account info
```

### This Week (Production Deploy)
1. Run database migration
2. Deploy dashboard to server
3. Configure nginx
4. Get SSL certificate
5. Test with real data

### Next Month (Enhancements)
- Add more chart types
- Real-time visitor feed
- Export data features
- Email reports
- User invitation system

---

## 🔑 Key Points About This Implementation

### Why This Stack?

1. **PHP 7.2 compatible** - Works on your existing server
2. **No frameworks** - Pure PHP with helpers
3. **HTMX instead of React** - Modern UX without complexity
4. **Direct database access** - No API layer needed
5. **Your design system** - Tailwind + your colors
6. **Understandable code** - View source shows real HTML

### What Makes It Modern?

- ✅ Auto-refreshing stats (every 60s)
- ✅ No page reloads
- ✅ Smooth transitions
- ✅ Beautiful charts
- ✅ Responsive design
- ✅ Feels like a SPA

**But built with PHP and HTMX** - not React!

### What's Different from Traditional PHP?

**Traditional PHP:**
- Click link → full page reload
- Static pages
- No dynamic updates

**This (HTMX + PHP):**
- Click link → HTMX loads new content
- Dynamic sections
- Auto-refreshing stats
- Feels modern

**But you're still writing PHP!** HTMX just handles the AJAX for you.

---

## 📚 Documentation

**For deployment:**
- `dashboard/DEPLOYMENT.md` - Complete deployment guide
- `dashboard/README.md` - How the dashboard works
- `www/README.md` - Marketing site deployment

**For understanding HTMX:**
- `www/TECH_STACK_RESEARCH.md` - Why we chose this stack
- `www/ARCHITECTURE_PLAN.md` - System architecture
- Official: https://htmx.org/docs/

**For design:**
- `www/design_manual.md` - Your design guidelines
- `www/landing_page_wire_frame.md` - Page structure

---

## 🎯 Test It Now

### Local Test (5 Minutes)

```bash
# 1. Run migration
cd /Users/lgforsberg/Projects/trackveil

PGPASSWORD='Konsult&793' psql -h pg1.trackveil.net -U markedo -d trackveil \
  -f database/migrations/004_add_user_passwords.sql

# 2. Start PHP server
cd dashboard/public
php -S localhost:8000

# 3. Open browser
open http://localhost:8000

# 4. Login
# Email: test@example.com
# Password: password123

# 5. Explore
# - See stats from kea.gl and markedo.com
# - Watch stats auto-refresh (check timestamp)
# - Switch between sites
# - Check site management page
```

### What You'll See

**Login Page:**
- Clean design with your logo
- Turquoise gradient button
- Error handling

**Dashboard:**
- Stats cards (page views, visitors)
- Auto-refreshing (every 60s)
- Visitors chart (last 7 days)
- Top pages list
- Top referrers
- Browser/device breakdowns

**Sites Page:**
- All sites in Markedo account
- Quick stats per site
- Copy site ID button
- Installation instructions

**Account Page:**
- Account info
- Team members list
- Account stats

---

## 💬 How to Use HTMX (Practical Guide)

### Pattern 1: Load Content on Page Load

```html
<div hx-get="/partials/content.php" hx-trigger="load">
    Loading...
</div>
```

### Pattern 2: Auto-Refresh

```html
<div hx-get="/partials/stats.php" hx-trigger="every 30s">
    Stats here
</div>
```

### Pattern 3: Click to Load

```html
<button hx-get="/partials/more.php" hx-target="#results">
    Load More
</button>
<div id="results"></div>
```

### Pattern 4: Form Submit

```html
<form hx-post="/save.php" hx-target="#message">
    <input name="data">
    <button type="submit">Save</button>
</form>
<div id="message"></div>
```

**That's it!** 4 patterns cover 99% of use cases.

---

## 🐛 Troubleshooting

### Dashboard shows "Loading..." forever

**Check:**
1. Is migration 004 run? (users table needs password_hash column)
2. Is .env configured correctly?
3. Check PHP error log: `tail -f /var/log/php_errors.log`

**Common issue:** PDO PostgreSQL extension not installed

```bash
# Install on Ubuntu
sudo apt-get install php7.2-pgsql
sudo systemctl restart php7.2-fpm
```

### HTMX not loading partials

**Check browser console:**
- Look for 404 errors (wrong path)
- Look for 500 errors (PHP error in partial)
- Check Network tab for the request

**Debug mode:** Add to footer.php:
```javascript
htmx.on('htmx:afterRequest', function(evt) {
    console.log('HTMX loaded:', evt.detail.xhr.responseURL);
});
```

### Charts not rendering

**Check:**
- Is Chart.js loaded? (view page source)
- Is data properly formatted? (`json_encode` in PHP)
- Any JavaScript errors in console?

---

## 🎊 Summary

**You now have:**
- ✅ Beautiful marketing website (ready to deploy)
- ✅ Full analytics dashboard (ready to deploy)  
- ✅ Auto-refreshing stats
- ✅ HTMX for modern UX without React
- ✅ Pure PHP (your strength)
- ✅ PHP 7.2 compatible (works on your server)
- ✅ Complete documentation

**No Alpine.js, no React, no build complexity - just PHP + HTMX + Tailwind.**

**Ready to test locally and deploy!** 🚀

---

**Next:** Test locally, then deploy to production. Let me know if you have questions about any part of the implementation!

