# Trackveil Web Architecture Plan

## Overview

Two separate applications with clear boundaries:

1. **Marketing Website** (www/)
2. **Analytics Dashboard** (dashboard/)

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     trackveil.net                            │
│                   (Marketing Website)                        │
│                                                              │
│  Static HTML + Tailwind CSS                                 │
│  - index.html (your example.html)                           │
│  - No backend needed                                        │
│  - Deployed to: /var/www/trackveil.net/                    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│              dashboard.trackveil.net                         │
│                 (Analytics Dashboard)                        │
│                                                              │
│  PHP 8.1 + HTMX + Tailwind                                  │
│  ├── Server-side rendering (PHP)                            │
│  ├── Dynamic updates (HTMX)                                 │
│  ├── UI widgets (Alpine.js - optional)                      │
│  ├── Charts (Chart.js)                                      │
│  └── Auth (PHP sessions)                                    │
│                                                              │
│  Deployed to: /var/www/dashboard.trackveil.net/            │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ Direct Connection (PDO)
                     ▼
┌─────────────────────────────────────────────────────────────┐
│              PostgreSQL (pg1.trackveil.net)                  │
│                                                              │
│  Tables:                                                     │
│  - accounts, users, sites                                   │
│  - page_views, visitors, sessions                           │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│            api.trackveil.net (Go Tracking API)              │
│                                                              │
│  Used ONLY by tracker.js                                    │
│  Dashboard does NOT use this                                │
│  Keeps tracking separate from analytics                     │
└─────────────────────────────────────────────────────────────┘
```

## File Structure

```
trackveil/
│
├── www/                          # Marketing website
│   ├── public/
│   │   ├── index.html           # Landing page (your example.html)
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   └── styles.css   # Custom CSS if needed
│   │   │   ├── js/
│   │   │   │   └── (none - static site)
│   │   │   └── img/
│   │   │       └── logo_icon.png
│   │   ├── privacy.html         # Static pages
│   │   └── terms.html
│   └── README.md
│
├── dashboard/                    # Analytics dashboard
│   ├── public/                  # Document root
│   │   ├── index.php           # Redirects to /login or /app
│   │   ├── login.php           # Login page
│   │   ├── logout.php          # Logout handler
│   │   │
│   │   ├── app/                # Main dashboard (protected)
│   │   │   ├── index.php      # Dashboard home
│   │   │   ├── sites.php      # Site management
│   │   │   ├── account.php    # Account settings
│   │   │   └── users.php      # User management
│   │   │
│   │   ├── partials/           # HTMX HTML fragments
│   │   │   ├── stats-overview.php
│   │   │   ├── visitors-chart.php
│   │   │   ├── pages-list.php
│   │   │   ├── referrers-list.php
│   │   │   ├── browsers-chart.php
│   │   │   └── site-selector.php
│   │   │
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   └── tailwind.min.css
│   │   │   ├── js/
│   │   │   │   ├── htmx.min.js       # 14KB
│   │   │   │   ├── alpine.min.js     # 15KB (optional)
│   │   │   │   └── chart.min.js      # For graphs
│   │   │   └── img/
│   │   │       └── logo_icon.png
│   │   │
│   │   └── .htaccess            # URL rewriting
│   │
│   ├── src/                     # PHP application code
│   │   ├── config.php          # Database connection
│   │   ├── auth.php            # Authentication functions
│   │   ├── db.php              # Database helpers
│   │   ├── queries.php         # SQL queries
│   │   └── helpers.php         # Utility functions
│   │
│   ├── templates/               # Shared PHP templates
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── nav.php
│   │
│   └── .env                     # Config (gitignored)
│
├── api/                         # Go tracking API (existing)
│   └── (keep as-is)
│
├── tracker/                     # JS tracker (existing)
│   └── (keep as-is)
│
└── database/                    # Migrations (existing)
    └── (keep as-is)
```

## Technology Choices Explained

### Frontend

**Tailwind CSS** ✅ You're already using this
- Utility-first CSS
- Your example.html looks great
- Same design system for both site and dashboard

**HTMX** 🆕 Learn this (1-2 hours)
- Replaces jQuery AJAX
- HTML attributes instead of JS
- 14KB library
- Makes PHP server-rendered apps feel like SPAs

**Alpine.js** 🆕 Optional (30 mins to learn)
- For UI widgets (dropdowns, modals, tabs)
- Like mini-Vue in HTML attributes
- 15KB
- Only if you need client-side state

**Chart.js** ✅ Standard charting library
- You've probably used it
- Works perfectly with PHP data

### Backend

**PHP 8.1+** ✅ Your strength
- Server-side rendering
- All business logic
- Session-based auth
- Direct PostgreSQL connection

**No Framework** OR **Slim** (your choice)
- No framework: Pure PHP with `.htaccess` routing
- Slim: Tiny routing library, nothing else
- NOT Laravel (too heavy for your needs)

**PDO → PostgreSQL** ✅ Direct connection
- Native PHP database access
- Prepared statements (secure)
- No Go API needed for dashboard

### Why NOT Use Go API for Dashboard?

**Go API is optimized for:**
- High-volume tracking (1000s req/min)
- Simple CRUD operations
- Stateless requests
- Minimal processing

**Dashboard needs:**
- Complex analytics queries
- Aggregations, joins, grouping
- Flexible ad-hoc queries
- Low volume (10-100 req/min)

**PHP + SQL is perfect for this.** You write SQL, PHP renders HTML. Simple.

---

## 🎯 Deployment Plan

### Server Organization

```
markedo.com server:
│
├── /var/www/trackveil.net/            # Marketing site (static)
│   └── index.html (your example.html)
│
├── /var/www/dashboard.trackveil.net/  # Dashboard app (PHP)
│   ├── public/                        # Document root
│   └── src/                           # PHP application
│
└── /home/lg/bin/trackveil/            # Go tracking API
    └── api/
```

### Nginx Configuration

**trackveil.net:**
```nginx
server {
    listen 443 ssl;
    server_name trackveil.net www.trackveil.net;
    root /var/www/trackveil.net;
    index index.html;
    
    # Static files only
    location / {
        try_files $uri $uri/ =404;
    }
}
```

**dashboard.trackveil.net:**
```nginx
server {
    listen 443 ssl;
    server_name dashboard.trackveil.net;
    root /var/www/dashboard.trackveil.net/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

---

## 📚 HTMX Learning Path (Just for You)

### Concept 1: Attributes Replace JavaScript

**jQuery way:**
```javascript
$('#load-more').click(function() {
    $.get('/more-data.php', function(html) {
        $('#content').append(html);
    });
});
```

**HTMX way:**
```html
<button hx-get="/more-data.php" 
        hx-target="#content" 
        hx-swap="beforeend">
    Load More
</button>
```

**Same result, zero JavaScript.**

### Concept 2: Server Returns HTML (Not JSON)

**React/API way:**
```javascript
fetch('/api/stats')
    .then(r => r.json())
    .then(data => {
        // Build HTML in JavaScript
        const html = `<div>${data.views}</div>`;
        container.innerHTML = html;
    });
```

**HTMX way:**
```php
<!-- stats.php returns HTML -->
<div class="p-6 bg-white rounded-xl">
    <div>Page Views</div>
    <div class="text-3xl"><?= number_format($views) ?></div>
</div>
```

```html
<!-- HTML just requests it -->
<div hx-get="/stats.php" hx-trigger="load"></div>
```

**Server renders HTML (what PHP is great at), HTMX puts it on the page.**

### Concept 3: Triggers Control When Things Happen

```html
<!-- On click -->
<button hx-get="/data.php" hx-trigger="click">

<!-- On load -->
<div hx-get="/data.php" hx-trigger="load">

<!-- Every 30 seconds -->
<div hx-get="/data.php" hx-trigger="every 30s">

<!-- On form change -->
<select hx-get="/filter.php" hx-trigger="change">
```

**Like jQuery event listeners, but in HTML.**

### Concept 4: Targets Control Where Results Go

```html
<!-- Put result inside this div (innerHTML) -->
<div hx-get="/data.php" hx-target="#results" hx-swap="innerHTML">

<!-- Append to end -->
<div hx-get="/data.php" hx-target="#list" hx-swap="beforeend">

<!-- Replace the element itself -->
<div hx-get="/data.php" hx-target="this" hx-swap="outerHTML">
```

**Like jQuery's `.html()`, `.append()`, `.replaceWith()` - but declarative.**

### The 7 HTMX Attributes You Need

That's **literally all you need** for 90% of use cases:

1. `hx-get="/url"` - Make GET request
2. `hx-post="/url"` - Make POST request
3. `hx-target="#id"` - Where to put the response
4. `hx-swap="innerHTML"` - How to insert (innerHTML, outerHTML, beforeend, etc.)
5. `hx-trigger="click"` - What event triggers it (click, load, change, every Xs)
6. `hx-vals='{"key":"value"}'` - Extra data to send
7. `hx-indicator="#spinner"` - Show loading spinner

**Learn these 7, you're 90% done.**

---

## 🏗️ Proposed Dashboard Architecture

### Database Connection

**dashboard/src/config.php:**
```php
<?php
// Load from .env or config file
return [
    'db' => [
        'host' => 'pg1.trackveil.net',
        'port' => 5432,
        'database' => 'trackveil',
        'username' => 'markedo',
        'password' => getenv('DB_PASSWORD'), // From .env
    ],
    'session' => [
        'name' => 'trackveil_session',
        'lifetime' => 86400, // 24 hours
    ]
];
```

**dashboard/src/db.php:**
```php
<?php
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        $config = require __DIR__ . '/config.php';
        $dsn = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $config['db']['host'],
            $config['db']['port'],
            $config['db']['database']
        );
        
        $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    
    return $pdo;
}
```

### Example Dashboard Page

**dashboard/public/app/index.php:**
```php
<?php
session_start();
require '../../src/auth.php';
require '../../src/db.php';

requireLogin(); // Redirect to /login if not authenticated

$accountId = $_SESSION['account_id'];
$sites = getSites($accountId);
$selectedSite = $_GET['site'] ?? $sites[0]['id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trackveil Dashboard</title>
    <link href="/assets/css/tailwind.min.css" rel="stylesheet">
    <script src="/assets/js/htmx.min.js"></script>
    <script src="/assets/js/alpine.min.js" defer></script>
</head>
<body class="bg-gray-50">
    <?php include '../../templates/nav.php'; ?>
    
    <main class="container mx-auto p-6">
        <!-- Site Selector -->
        <select hx-get="/partials/stats-overview.php" 
                hx-target="#stats-container"
                hx-trigger="change"
                class="rounded-lg border px-4 py-2 mb-6">
            <?php foreach ($sites as $site): ?>
                <option value="<?= $site['id'] ?>" <?= $site['id'] === $selectedSite ? 'selected' : '' ?>>
                    <?= htmlspecialchars($site['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <!-- Stats Container - Auto-refreshes every 30s -->
        <div id="stats-container"
             hx-get="/partials/stats-overview.php?site_id=<?= $selectedSite ?>"
             hx-trigger="load, every 30s"
             hx-indicator="#spinner">
            <!-- PHP partial loads here -->
            <div class="text-center py-12">Loading...</div>
        </div>
        
        <!-- Loading spinner -->
        <div id="spinner" class="htmx-indicator fixed top-4 right-4">
            <div class="bg-white rounded-lg shadow px-4 py-2">
                Refreshing...
            </div>
        </div>
    </main>
</body>
</html>
```

**dashboard/public/partials/stats-overview.php:**
```php
<?php
require '../../src/db.php';
require '../../src/queries.php';

$siteId = $_GET['site_id'];
$stats = getStats($siteId); // SQL query returns array

// Return HTML fragment (not full page)
?>

<div class="grid grid-cols-4 gap-6 mb-6">
    <!-- Card 1: Page Views -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-sm text-gray-600">Page Views</div>
        <div class="text-3xl font-bold mt-2"><?= number_format($stats['page_views']) ?></div>
        <div class="text-sm text-green-600 mt-1">
            <?= $stats['views_change'] > 0 ? '+' : '' ?><?= $stats['views_change'] ?>% vs yesterday
        </div>
    </div>
    
    <!-- Card 2: Unique Visitors -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-sm text-gray-600">Unique Visitors</div>
        <div class="text-3xl font-bold mt-2"><?= number_format($stats['unique_visitors']) ?></div>
    </div>
    
    <!-- Card 3: Avg Session -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-sm text-gray-600">Avg. Session</div>
        <div class="text-3xl font-bold mt-2"><?= formatDuration($stats['avg_session']) ?></div>
    </div>
    
    <!-- Card 4: Bounce Rate -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-sm text-gray-600">Bounce Rate</div>
        <div class="text-3xl font-bold mt-2"><?= number_format($stats['bounce_rate'], 1) ?>%</div>
    </div>
</div>

<!-- Visitors Chart -->
<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold mb-4">Visitors Over Time</h3>
    <canvas id="visitors-chart-<?= uniqid() ?>"></canvas>
</div>

<script>
// Chart.js rendering (simple VanillaJS)
const ctx = document.getElementById('visitors-chart-<?= uniqid() ?>');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($stats['chart_labels']) ?>,
        datasets: [{
            label: 'Visitors',
            data: <?= json_encode($stats['chart_data']) ?>,
            borderColor: '#38BDF8',
            backgroundColor: 'rgba(56, 189, 248, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>
```

### SQL Queries Example

**dashboard/src/queries.php:**
```php
<?php
function getStats($siteId) {
    $db = getDB();
    
    // Page views today
    $views = $db->query("
        SELECT COUNT(*) as count
        FROM page_views
        WHERE site_id = ? AND viewed_at::date = CURRENT_DATE
    ", [$siteId])->fetch()['count'];
    
    // Unique visitors today
    $visitors = $db->query("
        SELECT COUNT(DISTINCT visitor_id) as count
        FROM page_views
        WHERE site_id = ? AND viewed_at::date = CURRENT_DATE
    ", [$siteId])->fetch()['count'];
    
    // Chart data (last 7 days)
    $chartData = $db->query("
        SELECT 
            DATE(viewed_at) as date,
            COUNT(DISTINCT visitor_id) as visitors
        FROM page_views
        WHERE site_id = ? 
        AND viewed_at > CURRENT_DATE - INTERVAL '7 days'
        GROUP BY DATE(viewed_at)
        ORDER BY date
    ", [$siteId])->fetchAll();
    
    return [
        'page_views' => $views,
        'unique_visitors' => $visitors,
        'chart_labels' => array_column($chartData, 'date'),
        'chart_data' => array_column($chartData, 'visitors'),
        // ... more stats
    ];
}
```

**This is what you're good at!** SQL queries, PHP arrays, HTML rendering.

---

## 🔐 Authentication Strategy

### Simple PHP Sessions

**dashboard/src/auth.php:**
```php
<?php
function login($email, $password) {
    $db = getDB();
    
    // Get user
    $user = $db->query("
        SELECT u.*, a.id as account_id, a.name as account_name
        FROM users u
        JOIN accounts a ON u.account_id = a.id
        WHERE u.email = ?
    ", [$email])->fetch();
    
    if (!$user) {
        return false;
    }
    
    // Verify password (Phase 2: add password column to users table)
    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['account_id'] = $user['account_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    
    return true;
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: /login.php');
}
```

**Simple, secure, understandable.**

---

## 🎨 Styling Approach

### Use Your Design System

You've already defined:
- Colors (turquoise #2DD4BF, sky #38BDF8, navy #0F172A)
- Rounded corners
- Shadow/glow effects
- Clean, minimal aesthetic

### Tailwind Config

**tailwind.config.js** (optional, if you want to compile custom builds):
```javascript
module.exports = {
  theme: {
    extend: {
      colors: {
        navy: '#0F172A',
        slate2: '#1E293B',
        offwhite: '#F8FAFC',
        teal: '#14B8A6',
        sky: '#38BDF8',
        turquoise: '#2DD4BF',
      },
      boxShadow: {
        glow: '0 0 0 2px rgba(56,189,248,.25), 0 0 40px rgba(20,184,166,.25)'
      }
    }
  }
}
```

Or just use Tailwind CDN with custom classes in your HTML (what you're doing in example.html).

---

## 💭 My Answers to Your Questions

### 1. Separation of www/ and dashboard/?
**Yes, absolutely.** Clean, maintainable, different concerns.

### 2. PHP directly to database?
**Yes, 100%.** No need for the Go API. That's for tracking only.

**Architecture:**
```
Tracker JS  →  Go API  →  PostgreSQL  (high-volume writes)
Dashboard   →  PostgreSQL              (low-volume reads)
```

### 3. Should we try HTMX?
**Yes, with a proof-of-concept.** I'll help you through it. If after seeing it work you don't like it, we'll use plain PHP + VanillaJS instead. **No commitment until you see it in action.**

---

## 🎬 Proposed Next Steps

### 1. Create Simple HTMX Demo
I'll build you a single-page dashboard example:
- Your Tailwind design
- Live stats that auto-refresh
- Site selector that loads different data
- One chart
- Maybe 100 lines of PHP total

**Time:** 30 minutes for me to build, 5 minutes for you to review

### 2. You Decide
After seeing it work:
- **Like it?** → Continue with HTMX approach
- **Meh?** → Fall back to PHP + VanillaJS
- **Questions?** → I'll explain any part

### 3. Build Dashboard MVP
Once we agree on approach:
- Login page
- Dashboard home (stats overview)
- Site management page
- Account settings

**Estimated:** 1-2 weeks of development

---

## 🤔 Questions for You

Before I build the demo:

1. **Database setup:** Should I add a password column to the users table now for authentication?

2. **URL preference:**
   - `dashboard.trackveil.net` (separate subdomain)
   - `trackveil.net/dashboard` (path-based)

3. **PHP framework:**
   - None (pure PHP + .htaccess)
   - Slim (just routing, 5 minute setup)

4. **Demo content:**
   - Should I use kea.gl's real data for the demo?
   - Or use the test site data?

5. **Want me to build the HTMX demo now?**
   - Or more questions first?

---

**TL;DR:** PHP + HTMX + Tailwind is perfect for you. HTMX is basically "jQuery AJAX as HTML attributes" + "server renders everything". You'll understand it immediately, and it plays to your PHP strengths while giving modern UX. Let me build you a quick demo?
