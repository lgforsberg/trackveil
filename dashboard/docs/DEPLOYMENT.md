# Dashboard Deployment Guide

## Prerequisites

- PHP 7.2+ with PDO PostgreSQL extension
- Nginx web server
- PostgreSQL database (already set up)
- SSL certificate for dashboard.trackveil.net

## Deployment Steps

### 1. Run Database Migration

```bash
# From your local machine
cd /Users/lgforsberg/Projects/trackveil

PGPASSWORD='Konsult&793' psql -h pg1.trackveil.net -U markedo -d trackveil \
  -f database/migrations/004_add_user_passwords.sql
```

This adds:
- `password_hash` column to users
- Sets test user password to "password123"
- Email verification columns (for future)

### 2. Deploy Dashboard Files

```bash
# From your local machine
cd /Users/lgforsberg/Projects/trackveil

# Deploy dashboard
rsync -avz --exclude='.env' --exclude='*.md' dashboard/ \
  lg@markedo.com:/var/www/dashboard.trackveil.net/

# Deploy marketing site
rsync -avz www/public/ lg@markedo.com:/var/www/trackveil.net/
```

### 3. Configure Environment

```bash
# SSH to server
ssh lg@markedo.com

# Create dashboard .env
cd /var/www/dashboard.trackveil.net
cp env.example .env
nano .env
```

Edit `.env`:
```env
DB_HOST=pg1.trackveil.net
DB_PORT=5432
DB_USER=markedo
DB_PASSWORD=Konsult&793
DB_NAME=trackveil

SESSION_NAME=trackveil_dashboard
SESSION_LIFETIME=86400

APP_ENV=production
APP_DEBUG=false
```

### 4. Set Permissions

```bash
# On server
sudo chown -R www-data:www-data /var/www/dashboard.trackveil.net
sudo chown -R www-data:www-data /var/www/trackveil.net

sudo chmod -R 755 /var/www/dashboard.trackveil.net
sudo chmod 600 /var/www/dashboard.trackveil.net/.env
```

### 5. Configure Nginx

Create `/etc/nginx/sites-available/dashboard.trackveil.net`:

```nginx
server {
    listen 443 ssl http2;
    server_name dashboard.trackveil.net;
    
    root /var/www/dashboard.trackveil.net/public;
    index index.php;
    
    # SSL configuration
    ssl_certificate /etc/letsencrypt/live/dashboard.trackveil.net/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/dashboard.trackveil.net/privkey.pem;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
    
    # Deny access to sensitive files
    location ~ \.(env|md|log)$ {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/dashboard.trackveil.net /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 6. Get SSL Certificate

```bash
sudo certbot --nginx -d dashboard.trackveil.net
```

### 7. Test

Visit: `https://dashboard.trackveil.net`

Login with:
- Email: test@example.com
- Password: password123

## How HTMX Works (Quick Primer)

### The Concept

Instead of writing JavaScript AJAX code, you add HTML attributes:

**Old way (jQuery):**
```javascript
$('#refresh').click(function() {
    $.get('/stats.php', function(html) {
        $('#container').html(html);
    });
});
```

**HTMX way:**
```html
<button hx-get="/stats.php" hx-target="#container">Refresh</button>
```

### Example in Dashboard

**File:** `dashboard/public/app/index.php`

```html
<!-- This div auto-loads stats on page load and every 60 seconds -->
<div 
    hx-get="/partials/stats-overview.php?site_id=xxx"
    hx-trigger="load, every 60s"
>
    Loading...
</div>
```

**What happens:**
1. Page loads
2. HTMX sees `hx-get` attribute
3. Calls `/partials/stats-overview.php`
4. PHP returns HTML (stats cards, charts)
5. HTMX puts HTML into the div
6. After 60 seconds, repeats steps 3-5

**Result:** Auto-refreshing dashboard without writing any AJAX code!

## File Organization

### Full Pages vs Partials

**Full Pages** (`public/app/*.php`):
- Include header, nav, footer
- Set up page structure
- Have HTMX containers

**Partials** (`public/partials/*.php`):
- Return HTML fragments only
- No header/footer
- Pure data rendering

### Example

**Full page:**
```php
<?php require 'header.php'; ?>
<div hx-get="/partials/stats.php"></div>
<?php require 'footer.php'; ?>
```

**Partial:**
```php
<?php
// No header/footer!
$stats = getStats($siteId);
?>
<div class="stat-card">
    Views: <?= $stats['views'] ?>
</div>
```

## Updating the Dashboard

```bash
# Make changes locally
cd /Users/lgforsberg/Projects/trackveil/dashboard

# Deploy changes
rsync -avz --exclude='.env' . lg@markedo.com:/var/www/dashboard.trackveil.net/

# No restart needed - PHP is interpreted!
# Just refresh your browser
```

## Database Queries

All SQL queries are in `src/queries.php`. Easy to modify:

```php
// src/queries.php
function getStats($siteId) {
    return queryOne("
        SELECT 
            COUNT(*) as page_views,
            COUNT(DISTINCT visitor_id) as unique_visitors
        FROM page_views
        WHERE site_id = ?
        AND viewed_at > CURRENT_DATE - INTERVAL '30 days'
    ", [$siteId]);
}
```

Change the SQL, save, refresh dashboard. That's it!

## Troubleshooting

### Login not working
- Check database migration ran
- Verify test user password is set
- Check `src/auth.php` logs in error_log

### Stats not showing
- Check `src/queries.php` SQL queries
- Verify site_id is correct
- Check PostgreSQL connection in `.env`

### HTMX not loading content
- Check browser console for errors
- Check Network tab for 404s
- Verify partial path is correct
- Ensure partial includes `requireLogin()`

### Charts not rendering
- Check Chart.js is loaded (view source)
- Check data format in PHP (json_encode)
- Look for JavaScript console errors

## Phase 2 Features (Future)

Easy to add:
- [ ] Web-based site creation
- [ ] User invitation
- [ ] Real-time visitor feed
- [ ] Export data (CSV, PDF)
- [ ] Email reports
- [ ] API keys management

All just new PHP files + partials!

