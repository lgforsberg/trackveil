# Deployment Guide

Quick guide for deploying Trackveil API to production.

## First Time Setup (On Production Server)

```bash
# SSH to server
ssh lg@markedo.com

# Navigate to directory
cd /home/lg/git/trackveil/api

# Install dependencies
make deps

# Create .env file
cp env.example .env
nano .env
```

Edit `.env` with production settings:
```env
DB_HOST=pg1.trackveil.net
DB_PORT=5432
DB_USER=markedo
DB_PASSWORD=your-password
DB_NAME=trackveil
DB_SSLMODE=require

API_PORT=8080
API_ENV=production

ALLOWED_ORIGINS=*
```

```bash
# Build for production
make build

# Create logs directory
mkdir -p logs

# Test run
./bin/trackveil-api
# Press Ctrl+C after verifying it starts

# Set up systemd service (optional but recommended)
# See api/README.md for systemd configuration
```

## Regular Deployment

When you push changes to the repository:

```bash
# SSH to server
ssh lg@markedo.com

# Navigate to repo
cd /home/lg/git/trackveil

# Pull latest changes
git pull

# Navigate to API directory
cd api

# Download dependencies (if go.mod changed)
make deps

# Rebuild
make build

# Restart the API
# If using systemd:
sudo systemctl restart trackveil-api

# If running manually:
make restart

# Check status
make status

# View logs
make logs
```

## Quick Deployment One-Liner

```bash
ssh lg@markedo.com "cd /home/lg/git/trackveil && git pull && cd api && make deps && make build && sudo systemctl restart trackveil-api"
```

## Troubleshooting

### "module not found" errors

If you see errors like:
```
module github.com/lgforsberg/trackveil/api@latest found, but does not contain package...
```

This means Go is trying to fetch the module from GitHub. Solution:

```bash
# Clean Go cache
go clean -modcache

# Ensure you're in the api directory
cd /home/lg/git/trackveil/api

# Rebuild
make deps
make build
```

### Port already in use

```bash
# Check what's using port 8080
lsof -i :8080

# Stop the API
make stop

# Or kill the process
kill <PID>
```

### Database connection issues

```bash
# Test database connection
psql -h pg1.trackveil.net -U markedo -d trackveil -c "SELECT 1;"

# Check .env file
cat .env
```

## Rollback

If something goes wrong:

```bash
# Go back to previous version
cd /home/lg/git/trackveil
git log --oneline -10  # Find the commit hash you want
git checkout <commit-hash>

# Rebuild
cd api
make build
make restart
```

## Monitoring

```bash
# Check if API is running
make status

# View real-time logs
make logs

# Check last 50 lines
tail -50 logs/api.log

# Health check
curl http://localhost:8080/health
```

## Environment Variables

Key environment variables you might need to change:

- `DB_HOST` - Database host
- `DB_PASSWORD` - Database password  
- `API_PORT` - Port the API listens on (default: 8080)
- `API_ENV` - Environment (development/production)
- `ALLOWED_ORIGINS` - CORS origins (* for all, or comma-separated list)

## Production Checklist

Before going live:

- [ ] Database migrations run
- [ ] `.env` file configured with production values
- [ ] API builds successfully
- [ ] API starts and health check works
- [ ] Systemd service configured (optional)
- [ ] Nginx reverse proxy configured
- [ ] SSL certificates in place
- [ ] Firewall allows traffic on API port
- [ ] DNS records point to server
- [ ] Test tracking with real site

## Support

See full documentation:
- `api/README.md` - Complete API documentation
- `api/COMMANDS.md` - Quick command reference
- `docs/GETTING_STARTED.md` - Initial setup guide

