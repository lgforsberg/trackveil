# Deployment Guide

Complete guide for deploying all Trackveil components to production.

## Production Architecture

```
trackveil.net              → Marketing website (static HTML)
dashboard.trackveil.net    → Analytics dashboard (PHP)
api.trackveil.net          → Tracking API (Go)
cdn.trackveil.net          → Tracker script (static JS)
pg1.trackveil.net          → Database (PostgreSQL)
```

## Database Setup

```bash
# Run migrations
psql -h pg1.trackveil.net -U markedo -d trackveil -f database/migrations/001_initial_schema.sql
psql -h pg1.trackveil.net -U markedo -d trackveil -f database/migrations/002_seed_test_data.sql
psql -h pg1.trackveil.net -U markedo -d trackveil -f database/migrations/003_change_site_id_to_hash.sql
psql -h pg1.trackveil.net -U markedo -d trackveil -f database/migrations/004_add_user_passwords.sql

# Set up automated backups (AWS RDS)
aws rds modify-db-instance \
  --db-instance-identifier your-instance \
  --backup-retention-period 7 \
  --preferred-backup-window "03:00-04:00"
```

## API Deployment

### First Time Setup

```bash
# SSH to server
ssh lg@markedo.com
cd /home/lg/bin/trackveil/api

# Create .env file
cat > .env << 'EOF'
DB_HOST=pg1.trackveil.net
DB_PORT=5432
DB_USER=markedo
DB_PASSWORD=your-password
DB_NAME=trackveil
DB_SSLMODE=require

API_PORT=8080
API_ENV=production

ALLOWED_ORIGINS=*
EOF

# Create logs directory
mkdir -p logs

# Build
cd /home/lg/git/trackveil/api
make deps
make build

# Copy binary to deployment location
cp bin/trackveil-api /home/lg/bin/trackveil/api/

# Install as systemd service
cd /home/lg/git/trackveil/api
make install-service

# Start the service
sudo systemctl start trackveil-api
sudo systemctl status trackveil-api
```

### Regular Updates

```bash
# One-liner deployment
ssh lg@markedo.com "cd /home/lg/git/trackveil/api && git pull && make build && cp bin/trackveil-api /home/lg/bin/trackveil/api/ && sudo systemctl restart trackveil-api"

# Or step by step:
ssh lg@markedo.com
cd /home/lg/git/trackveil/api
git pull
make deps  # Only if go.mod changed
make build
cp bin/trackveil-api /home/lg/bin/trackveil/api/
sudo systemctl restart trackveil-api
sudo systemctl status trackveil-api
```

### Monitoring

```bash
# Check status
sudo systemctl status trackveil-api

# View logs
sudo journalctl -u trackveil-api -f
# or
tail -f /home/lg/bin/trackveil/api/logs/api.log

# Health check
curl http://localhost:8080/health
curl https://api.trackveil.net/health
```

## Tracker Deployment

```bash
# Build tracker locally
cd /Users/lgforsberg/Projects/trackveil/tracker
npm install
npm run build

# Deploy to CDN
scp tracker.min.js lg@markedo.com:/var/www/cdn.trackveil.net/
scp tracker.debug.js lg@markedo.com:/var/www/cdn.trackveil.net/

# Verify
curl -I https://cdn.trackveil.net/tracker.min.js
```

## Dashboard Deployment

```bash
# Deploy dashboard
rsync -avz --exclude='.env' dashboard/ lg@markedo.com:/var/www/dashboard.trackveil.net/

# Create .env on server
ssh lg@markedo.com
cd /var/www/dashboard.trackveil.net
cat > .env << 'EOF'
DB_HOST=pg1.trackveil.net
DB_PORT=5432
DB_USER=markedo
DB_PASSWORD=your-password
DB_NAME=trackveil
DB_SSLMODE=require
EOF

# Set permissions
sudo chown -R www-data:www-data /var/www/dashboard.trackveil.net
chmod 600 .env

# No restart needed - PHP reads files on each request
```

## Marketing Website Deployment

```bash
# Deploy website
cd /Users/lgforsberg/Projects/trackveil/www
rsync -avz public/ lg@markedo.com:/var/www/trackveil.net/

# No restart needed - static files
```

## Nginx Configuration

### API (api.trackveil.net)

```nginx
server {
    listen 443 ssl http2;
    server_name api.trackveil.net;

    ssl_certificate /etc/letsencrypt/live/api.trackveil.net/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.trackveil.net/privkey.pem;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Dashboard (dashboard.trackveil.net)

```nginx
server {
    listen 443 ssl http2;
    server_name dashboard.trackveil.net;
    
    root /var/www/dashboard.trackveil.net/public;
    index index.php;
    
    ssl_certificate /etc/letsencrypt/live/dashboard.trackveil.net/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/dashboard.trackveil.net/privkey.pem;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\. {
        deny all;
    }
}
```

### CDN & Website (cdn.trackveil.net, trackveil.net)

```nginx
server {
    listen 443 ssl http2;
    server_name cdn.trackveil.net;
    
    root /var/www/cdn.trackveil.net;
    
    ssl_certificate /etc/letsencrypt/live/cdn.trackveil.net/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/cdn.trackveil.net/privkey.pem;
    
    # Cache static files aggressively
    location ~* \.(js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Access-Control-Allow-Origin "*";
    }
}

server {
    listen 443 ssl http2;
    server_name trackveil.net www.trackveil.net;
    
    root /var/www/trackveil.net;
    index index.html;
    
    ssl_certificate /etc/letsencrypt/live/trackveil.net/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/trackveil.net/privkey.pem;
    
    location / {
        try_files $uri $uri/ =404;
    }
}
```

## SSL Certificates

```bash
# Install certbot
sudo apt install certbot python3-certbot-nginx

# Get certificates
sudo certbot --nginx -d api.trackveil.net
sudo certbot --nginx -d dashboard.trackveil.net
sudo certbot --nginx -d cdn.trackveil.net
sudo certbot --nginx -d trackveil.net -d www.trackveil.net

# Auto-renewal (already configured)
sudo certbot renew --dry-run
```

## Troubleshooting

### API won't start

```bash
# Check logs
sudo journalctl -u trackveil-api -n 50

# Common issues:
# 1. Database connection - check .env credentials
# 2. Port in use - check: lsof -i :8080
# 3. Permissions - check: ls -la /home/lg/bin/trackveil/api/
```

### Dashboard shows errors

```bash
# Check PHP logs
sudo tail -f /var/log/nginx/error.log

# Common issues:
# 1. Database connection - check .env
# 2. Permissions - check: ls -la /var/www/dashboard.trackveil.net/
# 3. PHP-FPM not running - check: sudo systemctl status php7.4-fpm
```

### Tracker not loading

```bash
# Check CDN
curl -I https://cdn.trackveil.net/tracker.min.js

# Should return:
# HTTP/2 200
# content-type: application/javascript
# access-control-allow-origin: *
```

## Rollback Procedure

```bash
# If deployment fails, rollback:
ssh lg@markedo.com
cd /home/lg/git/trackveil

# Find previous working commit
git log --oneline -10

# Checkout previous version
git checkout <commit-hash>

# Rebuild and restart
cd api
make build
cp bin/trackveil-api /home/lg/bin/trackveil/api/
sudo systemctl restart trackveil-api
```

## Pre-Launch Checklist

Before going live:

- [ ] Database migrations run successfully
- [ ] API builds and starts without errors
- [ ] API health check returns 200 OK
- [ ] Systemd service enabled and running
- [ ] Nginx configs in place for all domains
- [ ] SSL certificates installed and valid
- [ ] Tracker loads from CDN with CORS headers
- [ ] Dashboard accessible and connects to database
- [ ] Test site tracks successfully
- [ ] DNS records pointing correctly
- [ ] Automated backups configured
- [ ] Monitoring/alerting set up (optional but recommended)

## Production Endpoints

After deployment, verify all endpoints:

```bash
# Marketing site
curl -I https://trackveil.net

# API health
curl https://api.trackveil.net/health

# Tracker
curl -I https://cdn.trackveil.net/tracker.min.js

# Dashboard (requires auth)
curl -I https://dashboard.trackveil.net

# Test tracking
curl "https://api.trackveil.net/track?site_id=test&page_url=https://test.com&fingerprint=test"
```

---

**Production is ready when all components deploy cleanly and tracking works end-to-end.**

