# Trackveil Dashboard - Tech Stack Research

**Date:** October 2, 2025  
**For:** Website + Dashboard (Phase 2)

## Your Preferences

✅ **You like:**
- Tailwind CSS (already using)
- VanillaJS
- jQuery  
- PHP (fluent, already on server)
- Code you can understand

❌ **You dislike:**
- React
- Modern JS frameworks
- Heavy abstractions

## Design Assets Review

**What you've created:**
- ✅ Beautiful radar/sonar logo (turquoise/blue)
- ✅ Comprehensive design manual
- ✅ Clear color palette
- ✅ Landing page wireframe
- ✅ Working example.html with Tailwind

**Assessment:** Your design is polished and production-ready. The aesthetic is modern, clean, and professional.

---

## Option 1: PHP + HTMX + Tailwind ⭐ **RECOMMENDED**

### What is HTMX?

HTMX is a library that lets you build modern, dynamic UIs using **HTML attributes** instead of JavaScript. It's the anti-React.

**Example:**
```html
<!-- Click button, load content from server, swap into #results -->
<button hx-get="/api/stats" hx-target="#results">
    Refresh Stats
</button>
<div id="#results">...</div>
```

The server (PHP) returns HTML fragments, and HTMX swaps them in. No JSON, no client-side templating, no build step.

### Why This Fits You Perfectly

**Pros:**
- ✅ **PHP is the star** - All logic stays server-side
- ✅ **Minimal JavaScript** - HTMX is 14KB, that's it
- ✅ **Templates in PHP** - Use what you know
- ✅ **No build step** - Edit PHP, refresh browser
- ✅ **Progressive enhancement** - Works without JS
- ✅ **Tailwind compatible** - Perfect pairing
- ✅ **Understandable** - HTML attributes, not JSX magic
- ✅ **Fast to develop** - Less code than React
- ✅ **Easy to debug** - View source shows real HTML

**Cons:**
- ⚠️ Less "modern" reputation (but who cares?)
- ⚠️ Need to learn HTMX attributes (1 hour)
- ⚠️ Not as trendy as React (but more maintainable)

### Tech Stack

```
Frontend:
- HTML + Tailwind CSS
- HTMX (14KB) for interactivity
- Alpine.js (15KB) for local state (optional)
- Chart.js for graphs

Backend:
- PHP 8.1+
- Simple routing (or micro-framework like Slim)
- Direct PostgreSQL connection
- Session-based auth

Database:
- PostgreSQL (already set up)
```

### Example Dashboard Code

**PHP Controller (`dashboard.php`):**
```php
<?php
// Get stats for a site
$siteId = $_GET['site_id'] ?? null;
$stats = getStats($siteId);
?>

<div class="grid grid-cols-3 gap-4">
    <div class="p-6 bg-white rounded-xl shadow">
        <div class="text-sm text-gray-600">Page Views</div>
        <div class="text-3xl font-bold"><?= number_format($stats['views']) ?></div>
    </div>
    <div class="p-6 bg-white rounded-xl shadow">
        <div class="text-sm text-gray-600">Unique Visitors</div>
        <div class="text-3xl font-bold"><?= number_format($stats['visitors']) ?></div>
    </div>
    <div class="p-6 bg-white rounded-xl shadow">
        <div class="text-sm text-gray-600">Bounce Rate</div>
        <div class="text-3xl font-bold"><?= $stats['bounce_rate'] ?>%</div>
    </div>
</div>
```

**HTML View:**
```html
<div id="stats-container" 
     hx-get="/dashboard/stats?site_id=<?= $siteId ?>"
     hx-trigger="every 30s"
     hx-swap="innerHTML">
    <!-- PHP fragment loads here -->
    <?php include 'stats.php'; ?>
</div>
```

**Result:** Stats auto-refresh every 30 seconds, all rendering done in PHP, zero client-side state management.

### Learning Curve

**Time to productivity:**
- HTMX basics: 1-2 hours
- Alpine.js (if needed): 1 hour
- Building dashboard: Same speed as pure PHP, but with modern UX

**Resources:**
- htmx.org (excellent docs)
- "Hypermedia Systems" book (free online)
- Examples look like your PHP code

---

## Option 2: Plain PHP + Vanilla JS + Tailwind

### Pure Traditional Approach

**Stack:**
- PHP for server-side rendering
- VanillaJS for interactivity
- jQuery for DOM manipulation (if you want)
- Tailwind for styling
- Chart.js for graphs

### Pros
- ✅ You already know everything
- ✅ Zero learning curve
- ✅ Complete control
- ✅ No dependencies to learn

### Cons
- ⚠️ More manual DOM manipulation
- ⚠️ More JavaScript than HTMX approach
- ⚠️ Have to handle AJAX manually
- ⚠️ More code overall

### Example

**PHP (`dashboard.php`):**
```php
<?php
$sites = getSites();
include 'header.php';
?>

<div class="container mx-auto p-6">
    <select id="site-selector" class="rounded-lg border px-4 py-2">
        <?php foreach ($sites as $site): ?>
            <option value="<?= $site['id'] ?>"><?= $site['name'] ?></option>
        <?php endforeach; ?>
    </select>
    
    <div id="stats-container" class="mt-6">
        <!-- Stats load here via AJAX -->
    </div>
</div>

<script>
// VanillaJS
document.getElementById('site-selector').addEventListener('change', function(e) {
    fetch('/api/stats?site_id=' + e.target.value)
        .then(r => r.json())
        .then(data => {
            document.getElementById('stats-container').innerHTML = `
                <div class="grid grid-cols-3 gap-4">
                    <div class="p-6 bg-white rounded-xl">
                        <div>Page Views</div>
                        <div class="text-3xl">${data.views}</div>
                    </div>
                    ...
                </div>
            `;
        });
});
</script>
```

**Works, but more code and more manual work than HTMX.**

---

## Option 3: PHP Micro-Framework + Alpine.js + Tailwind

### Stack
- **Slim Framework** (PHP micro-framework, very lightweight)
- **Alpine.js** (15KB, like VueJS but tiny)
- **Tailwind CSS**
- **Chart.js**

### Why Alpine?

It's "Tailwind for JavaScript" - add interactivity via HTML attributes:

```html
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Content</div>
</div>
```

### Pros
- ✅ Very lightweight (15KB)
- ✅ Learn in 30 minutes
- ✅ Plays well with PHP
- ✅ No build step
- ✅ Tailwind-like syntax

### Cons
- ⚠️ Still a framework (though tiny)
- ⚠️ Another thing to learn

### When to Use

Good for **local UI state** (dropdowns, modals, tabs) while keeping server logic in PHP.

---

## Option 4: Go Backend for Dashboard (Full Go Stack)

### Stack
- Go API (you're already building)
- Go templates (html/template)
- Tailwind CSS
- VanillaJS for interactivity

### Pros
- ✅ Single language stack (Go)
- ✅ Fast server-side rendering
- ✅ Type safety
- ✅ Excellent performance

### Cons
- ⚠️ Have to learn Go templating
- ⚠️ More complex than PHP for HTML generation
- ⚠️ Requires compile step

### Assessment

**Not recommended** - PHP is easier for HTML/templating, and you're already fluent in it.

---

## 🎯 My Recommendation: PHP + HTMX + Tailwind

### The Stack

```
Landing Page:
- Static HTML + Tailwind
- No backend needed
- Your example.html is already perfect

Dashboard:
- PHP 8.1+ (server-side rendering)
- HTMX (14KB) for dynamic updates
- Alpine.js (15KB) for UI widgets (optional)
- Tailwind CSS (already using)
- Chart.js for analytics graphs
- Simple session-based auth
```

### Why This is Perfect For You

1. **Plays to your strengths**
   - PHP is your comfort zone
   - All business logic in PHP
   - Templates in PHP (what you know)
   - SQL queries in PHP

2. **Modern UX without framework complexity**
   - HTMX gives you SPA-like experience
   - No JSON munging
   - No client-side state management
   - Server returns HTML, browser shows it

3. **Simple to understand**
   - View source = see actual code
   - No build step for development
   - No transpiling, bundling, webpack, etc.
   - Edit PHP, save, refresh

4. **Fast development**
   - Less code than React
   - Less abstraction
   - Direct control
   - Easy debugging

5. **Works with your design**
   - Tailwind already in place
   - example.html is a perfect start
   - Just add HTMX attributes

### File Structure

```
www/
├── public/
│   ├── index.html              # Landing page (static)
│   ├── css/
│   │   └── tailwind.min.css    # Compiled Tailwind
│   ├── js/
│   │   ├── htmx.min.js         # HTMX library
│   │   ├── alpine.min.js       # Alpine (optional)
│   │   └── chart.min.js        # Charts
│   └── img/
│       └── logo_icon.png
│
├── dashboard/
│   ├── index.php               # Dashboard entry
│   ├── login.php               # Auth
│   ├── sites.php               # Site management
│   ├── stats.php               # Analytics view
│   │
│   ├── partials/               # HTML fragments for HTMX
│   │   ├── stats-card.php
│   │   ├── visitors-chart.php
│   │   ├── pages-list.php
│   │   └── site-selector.php
│   │
│   ├── lib/
│   │   ├── db.php              # PostgreSQL connection
│   │   ├── auth.php            # Session auth
│   │   └── queries.php         # SQL queries
│   │
│   └── config.php              # Database config
│
└── .htaccess                   # URL rewriting
```

### Example Dashboard Page

**dashboard/index.php:**
```php
<?php
session_start();
require 'lib/auth.php';
require 'lib/db.php';

if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$sites = getSites($_SESSION['account_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trackveil Dashboard</title>
    <link href="/css/tailwind.min.css" rel="stylesheet">
    <script src="/js/htmx.min.js"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white border-b">
        <!-- Your navigation -->
    </nav>
    
    <main class="container mx-auto p-6">
        <!-- Site selector -->
        <select hx-get="/dashboard/partials/stats.php" 
                hx-target="#stats-container"
                hx-trigger="change"
                class="rounded-lg border px-4 py-2">
            <?php foreach ($sites as $site): ?>
                <option value="<?= $site['id'] ?>"><?= $site['name'] ?></option>
            <?php endforeach; ?>
        </select>
        
        <!-- Stats container - updates via HTMX when site changes -->
        <div id="stats-container" 
             hx-get="/dashboard/partials/stats.php?site_id=<?= $sites[0]['id'] ?>"
             hx-trigger="load, every 30s"
             class="mt-6">
            <!-- PHP partial loads here -->
        </div>
    </main>
</body>
</html>
```

**dashboard/partials/stats.php:**
```php
<?php
require '../lib/db.php';
require '../lib/queries.php';

$siteId = $_GET['site_id'];
$stats = getStats($siteId);
?>

<div class="grid grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow p-6">
        <div class="text-sm text-gray-600">Page Views</div>
        <div class="text-3xl font-bold"><?= number_format($stats['page_views']) ?></div>
        <div class="text-sm text-green-600 mt-1">+12% from yesterday</div>
    </div>
    
    <div class="bg-white rounded-xl shadow p-6">
        <div class="text-sm text-gray-600">Unique Visitors</div>
        <div class="text-3xl font-bold"><?= number_format($stats['visitors']) ?></div>
    </div>
    
    <div class="bg-white rounded-xl shadow p-6">
        <div class="text-sm text-gray-600">Avg. Session</div>
        <div class="text-3xl font-bold"><?= formatDuration($stats['avg_session']) ?></div>
    </div>
</div>

<!-- Chart -->
<div class="bg-white rounded-xl shadow p-6 mt-6">
    <canvas id="visitors-chart"></canvas>
</div>

<script>
// Simple Chart.js rendering
const ctx = document.getElementById('visitors-chart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($stats['dates']) ?>,
        datasets: [{
            label: 'Visitors',
            data: <?= json_encode($stats['visitor_counts']) ?>,
            borderColor: '#38BDF8',
            tension: 0.4
        }]
    }
});
</script>
```

**That's it!** Server-rendered PHP, HTMX handles the dynamic updates, Chart.js for graphs.

### Why HTMX Rocks

1. **HTML-centric** - You write HTML attributes, not JavaScript
2. **Server-driven** - PHP renders everything
3. **No build step** - Edit and refresh
4. **Tiny** - 14KB vs React's 150KB+
5. **Progressive** - Works without JS (graceful degradation)
6. **Simple** - 20 attributes to learn, that's it
7. **Fast** - Less JavaScript = faster page loads

### Learning Resources

- **htmx.org** - Excellent docs, examples
- **"Hypermedia Systems"** book - Free online
- **Time to learn:** 1-2 hours to be productive

### Real-World Examples

- **GitHub:** Uses similar approach (server-rendered + progressive enhancement)
- **Basecamp:** Built by same people who made HTMX
- **Hey.com:** Email service, HTMX + server-side rendering

---

## Option 2: Pure PHP + VanillaJS + Tailwind

### Classic LAMP Stack Approach

**Stack:**
- PHP (server-side rendering + API)
- VanillaJS for dynamic updates
- jQuery (if you want DOM helpers)
- Tailwind CSS
- Chart.js

### Pros
- ✅ Zero learning curve
- ✅ You know everything already
- ✅ Complete control
- ✅ No dependencies to learn

### Cons
- ⚠️ More JavaScript to write
- ⚠️ Manual AJAX handling
- ⚠️ Manual DOM manipulation
- ⚠️ More code overall
- ⚠️ Callback hell potential

### Example

```php
<!-- dashboard.php -->
<div id="stats"></div>

<script>
// Load stats via AJAX
function loadStats(siteId) {
    fetch('/api/stats.php?site_id=' + siteId)
        .then(r => r.json())
        .then(data => {
            // Manually build HTML
            document.getElementById('stats').innerHTML = `
                <div class="grid grid-cols-3 gap-4">
                    <div class="p-6 bg-white rounded-xl">
                        <div class="text-sm">Page Views</div>
                        <div class="text-3xl">${data.views}</div>
                    </div>
                    ...
                </div>
            `;
            
            // Manually init charts
            initChart(data.chartData);
        });
}

// Call on page load
loadStats(<?= $initialSiteId ?>);

// Poll for updates
setInterval(() => loadStats(currentSiteId), 30000);
</script>
```

**Works perfectly fine**, just more code than HTMX approach.

---

## Option 3: Alpine.js + PHP + Tailwind

### Alpine-First Approach

**Stack:**
- PHP (server-side data)
- Alpine.js (client-side reactivity)
- Tailwind CSS
- Chart.js

### Why Alpine?

"Vue-like" but 15KB. Good for SPAs that still render server-side.

```html
<div x-data="{ stats: <?= json_encode($stats) ?> }">
    <div class="grid grid-cols-3 gap-4">
        <div class="p-6 bg-white rounded-xl">
            <div>Page Views</div>
            <div class="text-3xl" x-text="stats.views.toLocaleString()"></div>
        </div>
    </div>
</div>

<script>
// Reactivity
Alpine.data('dashboard', () => ({
    stats: {},
    async refreshStats() {
        const res = await fetch('/api/stats');
        this.stats = await res.json();
    }
}));
</script>
```

### Pros
- ✅ Reactive like Vue but tiny
- ✅ HTML attributes (like HTMX)
- ✅ Good for complex UI state
- ✅ Tailwind-like learning curve

### Cons
- ⚠️ Client-side heavy (fetch JSON, render client-side)
- ⚠️ Less suited for SEO
- ⚠️ More JavaScript than HTMX

**Best for:** Rich, interactive UIs with lots of client-side state

**Your case:** Dashboard doesn't need that much client state, so HTMX is simpler

---

## Option 4: Static HTML + API Calls (SPA-lite)

### Stack
- Static HTML pages
- Tailwind CSS
- VanillaJS
- Calls to Go API for data (JSON)

### Pros
- ✅ Can host on CDN (super fast)
- ✅ Separate frontend/backend
- ✅ Use your existing Go API

### Cons
- ⚠️ All rendering in JavaScript
- ⚠️ More client-side code
- ⚠️ JSON template strings (messy)
- ⚠️ Have to build Go API for dashboard data

**Assessment:** More work, less use of PHP skills.

---

## 📊 Comparison Matrix

| Feature | HTMX + PHP | Plain PHP + VanillaJS | Alpine + PHP | Static + API |
|---------|------------|---------------------|-------------|--------------|
| **Learning Curve** | Low (1-2hrs) | None | Low (1hr) | Medium |
| **PHP Usage** | High ✅ | High ✅ | Medium | None |
| **JavaScript Amount** | Minimal | Medium | Medium | High |
| **Build Step** | None ✅ | None ✅ | None ✅ | Optional |
| **Development Speed** | Fast ✅ | Medium | Fast | Slow |
| **Code Understandability** | High ✅ | High ✅ | Medium | Medium |
| **Maintenance** | Easy ✅ | Easy ✅ | Medium | Medium |
| **Modern UX** | Excellent ✅ | Good | Excellent | Excellent |
| **SEO** | Excellent ✅ | Excellent ✅ | Good | Poor |
| **Server Load** | Low | Low | Low | Very Low |

---

## 🎯 Final Recommendation

### For Landing Page (trackveil.net)
**Use:** Static HTML + Tailwind (your `example.html` is perfect!)

**Why:**
- No backend needed
- Super fast (CDN)
- Your example.html already looks great
- Just deploy as-is

### For Dashboard (dashboard.trackveil.net or /dashboard)
**Use:** PHP + HTMX + Tailwind + Alpine.js (for UI widgets)

**Why:**
1. **Minimal new learning** - HTMX is 20 attributes, Alpine is similar
2. **Maximum PHP usage** - All logic server-side
3. **Modern UX** - Feels like a SPA
4. **No build step** - Edit PHP, refresh browser
5. **Understandable** - View source shows real code
6. **Perfect for dashboards** - Server renders data, HTMX updates dynamically
7. **Chart-friendly** - Chart.js works perfectly with this stack

### Detailed Stack Breakdown

```
📄 Landing Page (www/public/)
- index.html (static)
- Tailwind CSS (CDN or compiled)
- Your example.html as starting point

📊 Dashboard (www/dashboard/)
- PHP 8.1+ (FastCGI or mod_php)
- HTMX 1.9+ (dynamic updates)
- Alpine.js 3.x (UI widgets like dropdowns, modals)
- Tailwind CSS (same as landing page)
- Chart.js (analytics graphs)
- PostgreSQL (via PDO)

🔐 Authentication
- PHP sessions (simple, secure)
- Password hashing (password_hash/verify)
- No JWT complexity

📁 File Structure
www/
├── public/
│   ├── index.html          # Landing page
│   └── assets/
├── dashboard/
│   ├── index.php           # Dashboard home
│   ├── partials/           # HTMX HTML fragments  
│   └── lib/                # PHP utilities
└── api/                    # Thin PHP wrapper for Go API (optional)
```

---

## 📚 Resource List

### HTMX
- **Docs:** https://htmx.org/docs/
- **Examples:** https://htmx.org/examples/
- **Book:** https://hypermedia.systems/ (free)
- **Videos:** "HTMX in 100 Seconds" on YouTube

### Alpine.js (Optional)
- **Docs:** https://alpinejs.dev/
- **Examples:** Component library
- **Time:** 30 mins to learn

### Chart.js
- **Docs:** https://www.chartjs.org/
- **You probably already know this**

### PHP Best Practices
- PHP 8.1+ features (modern PHP)
- PDO for database (prepared statements)
- Slim framework if you want routing (optional)

---

## 💭 Alternative: If You Want Even Simpler

If HTMX feels like "another thing to learn," you can absolutely build with:

**Pure PHP + VanillaJS + jQuery**
- It will work
- You know it all
- Just more manual code
- Totally valid approach

**But I think you'll find HTMX actually REDUCES code complexity** while giving you modern UX.

---

## 🎬 Proposed Phase 2 Architecture

### Simple Version (Recommended)

```
┌─────────────────────┐
│  trackveil.net      │  Static HTML + Tailwind
│  (Landing page)     │  → Your example.html
└─────────────────────┘

┌─────────────────────┐
│  dashboard.         │  PHP + HTMX + Tailwind
│  trackveil.net      │  ├── Server renders HTML
│                     │  ├── HTMX updates sections
│                     │  ├── Chart.js for graphs
│                     │  └── Sessions for auth
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  PostgreSQL         │  Direct connection from PHP
│                     │  (No need for Go API for dashboard)
└─────────────────────┘

┌─────────────────────┐
│  Go Tracking API    │  Keep separate (tracker.js uses this)
│  (Existing)         │  Dashboard queries DB directly
└─────────────────────┘
```

### Benefits
- PHP talks directly to PostgreSQL (you're good at SQL)
- No API layer needed for dashboard
- Go API only for tracking (existing)
- Simple, fast, understandable

---

## ⏰ Development Time Estimates

| Approach | Landing Page | Dashboard MVP | Total |
|----------|-------------|---------------|-------|
| **HTMX + PHP** | 1 day | 7-10 days | **2 weeks** |
| **Plain PHP + VanillaJS** | 1 day | 10-14 days | **3 weeks** |
| **Alpine + PHP** | 1 day | 8-12 days | **2-3 weeks** |
| **React** | 1 day | 14-21 days | **4-5 weeks** |

HTMX is actually FASTER than pure VanillaJS because you write less code.

---

## 🤔 Questions for You

1. **Server setup:** Do you want the dashboard on the same server as the Go API, or separate?

2. **URL structure:** 
   - Option A: `trackveil.net` (landing) + `dashboard.trackveil.net` (app)
   - Option B: `trackveil.net` (landing) + `trackveil.net/dashboard` (app)

3. **PHP framework:**
   - None (pure PHP with .htaccess routing)
   - Slim (micro-framework, just routing)
   - Laravel (full framework - NOT recommended for you)

4. **Authentication:**
   - Simple sessions (email/password)
   - Add social login later (Google, GitHub)

5. **Database connection:**
   - PHP talks directly to PostgreSQL
   - Or PHP calls Go API (adds complexity)

---

## 🎯 My Specific Recommendation

**Stack:**
- Landing: Your `example.html` + Tailwind (static)
- Dashboard: PHP 8.1 + HTMX + Alpine.js + Tailwind
- Auth: PHP sessions (simple)
- Database: PHP → PostgreSQL (direct PDO connection)
- Charts: Chart.js
- Framework: None (or Slim for routing only)

**Why:**
1. Uses your PHP skills
2. Minimal JavaScript
3. No build complexity
4. Fast to develop
5. Easy to maintain
6. Modern UX feel
7. Perfect for dashboards

**Try HTMX for 30 minutes.** If you hate it, fall back to plain PHP + VanillaJS. But I think you'll love how little code you need to write.

---

Want me to create a simple HTMX proof-of-concept with your design? Just a single-page dashboard example to see if you like the approach?
