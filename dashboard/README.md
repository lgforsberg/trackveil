# Trackveil Dashboard

PHP-based analytics dashboard for viewing Trackveil tracking data.

## Tech Stack

- **Backend:** PHP 7.2+ (pure PHP, no framework)
- **Dynamic Updates:** HTMX 1.9
- **Styling:** Tailwind CSS
- **Charts:** Chart.js
- **Database:** PostgreSQL (direct PDO connection)
- **Auth:** PHP sessions

## Why No Alpine.js?

We're keeping it simple - just HTMX for server communication and VanillaJS for any UI interactions. Alpine.js can be added later if needed for complex UI state.

## Directory Structure

```
dashboard/
├── public/                 # Document root (point nginx here)
│   ├── index.php          # Entry point (redirects to login or app)
│   ├── login.php          # Login page
│   ├── logout.php         # Logout handler
│   ├── app/               # Main dashboard (protected)
│   │   ├── index.php      # Dashboard home with stats
│   │   ├── sites.php      # Site management
│   │   └── account.php    # Account settings
│   ├── partials/          # HTMX HTML fragments
│   │   └── stats-overview.php
│   ├── assets/            # Static files (CSS, JS, images)
│   └── .htaccess          # URL rewriting & security
│
├── src/                   # PHP application code
│   ├── config.php         # Configuration
│   ├── db.php             # Database connection & helpers
│   ├── auth.php           # Authentication system
│   ├── queries.php        # SQL analytics queries
│   └── helpers.php        # Utility functions
│
├── templates/             # Shared PHP templates
│   ├── header.php         # HTML head + nav
│   ├── nav.php            # Navigation bar
│   └── footer.php         # Closing tags + scripts
│
├── .env                   # Environment config (gitignored)
└── env.example            # Environment template
```

## Setup

### 1. Configure Environment

```bash
cd dashboard
cp env.example .env
nano .env
```

Set your database credentials from `config/config.md`.

### 2. Run Database Migration

```bash
# Add password support to users table
PGPASSWORD='your-password' psql -h pg1.trackveil.net -U markedo -d trackveil \
  -f ../database/migrations/004_add_user_passwords.sql
```

### 3. Deploy to Server

```bash
# Copy dashboard to server
rsync -avz --exclude='.env' dashboard/ lg@markedo.com:/var/www/dashboard.trackveil.net/

# Create .env on server
ssh lg@markedo.com
cd /var/www/dashboard.trackveil.net
cp env.example .env
nano .env
# Add your database credentials
```

### 4. Configure Nginx

Point `dashboard.trackveil.net` to the dashboard:

```nginx
server {
    listen 443 ssl;
    server_name dashboard.trackveil.net;
    
    root /var/www/dashboard.trackveil.net/public;
    index index.php;
    
    # SSL configuration
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
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
}
```

### 5. Test Locally (Optional)

```bash
cd dashboard/public
php -S localhost:8000

# Visit: http://localhost:8000
# Login: test@example.com / password123
```

## How HTMX Works (For Developers)

### The Pattern

**1. Full Page (app/index.php):**
- Includes HTMX library
- Has a container with `hx-get` attribute
- Container loads a partial on page load

**2. Partial (partials/stats-overview.php):**
- Returns HTML fragment (not full page)
- Uses PHP to query database
- Returns rendered HTML with data

**3. HTMX Magic:**
- Automatically loads the partial
- Swaps it into the container
- Re-loads every 60 seconds (auto-refresh!)

### Example Flow

```
User visits /app/
    ↓
index.php renders with empty container:
    <div hx-get="/partials/stats-overview.php" hx-trigger="load, every 60s">
    
    ↓
HTMX automatically calls:
    GET /partials/stats-overview.php?site_id=xxx
    
    ↓
stats-overview.php runs SQL, renders HTML, returns:
    <div class="grid">
        <div class="stat-card">Page Views: 1,234</div>
    </div>
    
    ↓
HTMX swaps the HTML into the container
    
    ↓
After 60 seconds, HTMX calls the partial again (auto-refresh!)
```

**No JavaScript needed!** HTMX handles it all via HTML attributes.

## Key Files

### Authentication
- `src/auth.php` - Login, logout, session management
- `public/login.php` - Login form
- `public/logout.php` - Logout handler

### Database
- `src/db.php` - PDO connection, query helpers
- `src/queries.php` - All SQL analytics queries

### Dashboard Pages
- `public/app/index.php` - Dashboard home (stats overview)
- `public/app/sites.php` - Site management
- `public/app/account.php` - Account settings

### HTMX Partials
- `public/partials/stats-overview.php` - Stats cards, charts, top pages

## HTMX Attributes Used

Learn these and you know 90% of what you need:

- `hx-get="/url"` - Load content from server via GET
- `hx-trigger="load"` - When to trigger (load, click, change, every Xs)
- `hx-target="#id"` - Where to put the response
- `hx-swap="innerHTML"` - How to insert (default)
- `hx-indicator="#spinner"` - Show loading indicator

That's it! Simple and powerful.

## Test Credentials

**Email:** test@example.com  
**Password:** password123

(Change these after testing!)

## Common Tasks

### Add a New Dashboard Page

1. Create `public/app/newpage.php`
2. Include auth, db, helpers at top
3. Call `requireLogin()`
4. Query data from database
5. Render HTML with Tailwind

### Add Auto-Refreshing Section

```php
<div 
    hx-get="/partials/my-section.php" 
    hx-trigger="load, every 30s"
>
    Loading...
</div>
```

### Create a Partial

1. Create `public/partials/my-section.php`
2. Include auth and db
3. Query data
4. Return HTML fragment (NOT full page)

### Debug HTMX

Add this to see what HTMX is doing:

```javascript
// In templates/footer.php
htmx.on('htmx:afterRequest', function(evt) {
    console.log('HTMX loaded:', evt.detail.xhr.responseURL);
});
```

## Performance

- **First load:** Server-rendered (fast)
- **Updates:** HTMX replaces only changed sections (faster than full reload)
- **Database:** PostgreSQL with optimized indexes
- **Charts:** Rendered client-side (Chart.js)

## Security

- ✅ Session-based authentication
- ✅ CSRF protection via sessions
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS prevention (htmlspecialchars on all output)
- ✅ Secure session cookies (httponly, secure flags)
- ✅ Protected .env file (.htaccess)

## Troubleshooting

### "Database connection failed"
Check `.env` file has correct credentials from `config/config.md`

### "Permission denied" errors
```bash
# Fix permissions on server
chown -R www-data:www-data /var/www/dashboard.trackveil.net
chmod -R 755 /var/www/dashboard.trackveil.net
chmod 600 /var/www/dashboard.trackveil.net/.env
```

### HTMX not loading content
- Check browser console for errors
- Check Network tab for 404s or 500s
- Verify partial path is correct
- Ensure partial includes auth check

### Stats not updating
- Verify HTMX library is loaded (check Network tab)
- Check `hx-trigger` attribute is correct
- Look for JavaScript errors in console

## Next Steps

Phase 2 features to add:
- Web-based site creation (no CLI needed)
- User invitation system
- Email notifications
- Data export
- Advanced analytics queries
- Real-time visitor feed

All can be added as new PHP files + HTMX partials. The foundation is solid!

