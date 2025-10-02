# Getting Started with Trackveil

This guide will help you set up Trackveil for local development and testing.

## Prerequisites

- Go 1.21 or higher
- PostgreSQL (or AWS RDS instance)
- Node.js (for building the tracker)
- A text editor or IDE

## Step 1: Database Setup

### Option A: AWS RDS (Recommended)

1. Create a PostgreSQL instance on AWS RDS
2. Note your connection details:
   - Host (endpoint)
   - Port (usually 5432)
   - Username
   - Password
   - Database name

### Option B: Local PostgreSQL

```bash
# Install PostgreSQL (macOS)
brew install postgresql@15

# Start PostgreSQL
brew services start postgresql@15

# Create database
createdb trackveil
```

### Run Migrations

```bash
cd database

# Connect and run migration
psql -h your-host -U your-user -d trackveil -f migrations/001_initial_schema.sql

# Optional: Add test data
psql -h your-host -U your-user -d trackveil -f migrations/002_seed_test_data.sql
```

You should see a test site ID at the end:
```
Use this Site ID in your tracker snippet:
a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
```

**Note:** Site IDs are 32-character alphanumeric strings (not UUIDs) - much shorter and easier to work with!

## Step 2: API Setup

```bash
cd api

# Install Go dependencies
go mod download

# Create environment file
cp env.example .env

# Edit .env with your database credentials
nano .env
```

Example `.env`:
```env
DB_HOST=your-db-host.rds.amazonaws.com
DB_PORT=5432
DB_USER=postgres
DB_PASSWORD=your-password
DB_NAME=trackveil
DB_SSLMODE=require

API_PORT=8080
API_ENV=development

ALLOWED_ORIGINS=*
```

### Run the API

```bash
go run main.go
```

You should see:
```
Successfully connected to database
Starting Trackveil API on :8080
Environment: development
```

### Test the API

```bash
# Health check
curl http://localhost:8080/health

# Should return:
# {"status":"healthy","time":"2025-10-02T..."}
```

## Step 3: Tracker Setup (Optional)

The tracker is ready to use as-is, but you can build the minified version:

```bash
cd tracker

# Install dependencies
npm install

# Build minified version
npm run build
```

### Test the Tracker

1. Update `tracker.js` to point to your local API:
   ```javascript
   const API_ENDPOINT = 'http://localhost:8080/track';
   ```

2. Open `test.html` in a browser:
   ```bash
   open test.html
   # or
   python -m http.server 8000
   # then visit http://localhost:8000/test.html
   ```

3. Check your browser console and API logs for tracking requests

4. Verify data in the database:
   ```bash
   psql -h your-host -U your-user -d trackveil -c "SELECT * FROM page_views;"
   ```

## Step 4: Install on a Website

Once everything is working locally:

1. **Deploy the API** to your server (e.g., `api.trackveil.net`)

2. **Upload `tracker.min.js`** to your CDN (e.g., `cdn.trackveil.net`)

3. **Add to your website:**
   ```html
   <script async 
           src="https://cdn.trackveil.net/tracker.min.js" 
           data-site-id="YOUR_SITE_ID">
   </script>
   ```

4. **Verify tracking:**
   - Open your website
   - Check browser console for errors
   - Look for POST requests to your API
   - Query the database for page views

## Common Issues

### API can't connect to database

**Error:** `Failed to connect to database`

**Solution:** Check your `.env` file and verify:
- Database host is correct
- Port is open (check security groups on AWS)
- Username and password are correct
- SSL mode matches your database setup

### CORS errors in browser

**Error:** `Access to fetch at '...' from origin '...' has been blocked by CORS`

**Solution:** 
- Set `ALLOWED_ORIGINS=*` in your `.env` for development
- For production, set specific origins: `ALLOWED_ORIGINS=https://yoursite.com,https://www.yoursite.com`

### Tracker not sending data

**Check:**
1. Open browser DevTools → Network tab
2. Look for request to `/track`
3. Check request payload and response
4. Look for JavaScript errors in Console

**Common causes:**
- Invalid `data-site-id`
- API server not running
- CORS issues
- Ad blocker blocking requests

### No data in database

**Check:**
1. Verify site exists: `SELECT * FROM sites;`
2. Check API logs for errors
3. Verify API can connect to database
4. Check PostgreSQL logs

## Next Steps

Now that you have Trackveil running:

1. **Create your own site:**
   ```sql
   INSERT INTO accounts (name) VALUES ('My Account');
   INSERT INTO sites (account_id, name, domain) 
   VALUES (
     (SELECT id FROM accounts LIMIT 1),
     'My Website',
     'mywebsite.com'
   );
   ```

2. **Get your site ID:**
   ```sql
   SELECT id, name, domain FROM sites;
   ```

3. **Install tracker on your website** with your site ID

4. **Watch the data come in:**
   ```sql
   SELECT 
     s.name as site,
     pv.page_url,
     pv.viewed_at,
     pv.browser_name,
     pv.device_type
   FROM page_views pv
   JOIN sites s ON pv.site_id = s.id
   ORDER BY pv.viewed_at DESC
   LIMIT 10;
   ```

## Development Tips

### Useful SQL Queries

```sql
-- Page views by site
SELECT s.name, COUNT(*) as views
FROM page_views pv
JOIN sites s ON pv.site_id = s.id
GROUP BY s.name;

-- Unique visitors by site
SELECT s.name, COUNT(DISTINCT v.id) as unique_visitors
FROM visitors v
JOIN sites s ON v.site_id = s.id
GROUP BY s.name;

-- Recent page views with details
SELECT 
  pv.page_url,
  pv.page_title,
  pv.viewed_at,
  pv.browser_name,
  pv.os_name,
  pv.device_type,
  pv.ip_address
FROM page_views pv
ORDER BY pv.viewed_at DESC
LIMIT 20;

-- Active sessions
SELECT 
  s.name as site,
  COUNT(*) as active_sessions
FROM sessions ses
JOIN sites s ON ses.site_id = s.id
WHERE ses.last_activity_at > NOW() - INTERVAL '30 minutes'
AND ses.ended_at IS NULL
GROUP BY s.name;
```

### API Testing with curl

```bash
# Track a page view
curl -X POST http://localhost:8080/track \
  -H "Content-Type: application/json" \
  -d '{
    "site_id": "00000000-0000-0000-0000-000000000003",
    "page_url": "https://example.com/test",
    "page_title": "Test Page",
    "referrer": "https://google.com",
    "screen_width": 1920,
    "screen_height": 1080,
    "fingerprint": "test_fingerprint_123"
  }'
```

### Hot Reload for Development

```bash
# Install air for hot reloading
go install github.com/cosmtrek/air@latest

# Run API with hot reload
cd api
air
```

## Production Deployment

See `docs/DEPLOYMENT.md` (Phase 2) for production deployment guide.

## Need Help?

- Check the `docs/ARCHITECTURE.md` for system design
- Review the `api/README.md` for API details
- Look at `tracker/README.md` for tracker information

