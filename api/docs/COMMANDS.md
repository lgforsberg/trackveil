# Trackveil API - Quick Command Reference

## Development Workflow

```bash
# First time setup
make deps              # Download dependencies
cp env.example .env   # Create config file
# Edit .env with your database credentials

# Development
make run              # Run in foreground (Ctrl+C to stop)
make dev              # Run with auto-reload (requires air)

# Production
make build            # Build binary
make start            # Build and start in background
make stop             # Stop the running API
make restart          # Restart API
make status           # Check if running

# Monitoring
make logs             # Tail application logs
curl localhost:8080/health  # Health check

# Testing
make test             # Run tests
make test-coverage    # Tests with coverage report

# Code quality
make check            # Run go fmt and go vet

# Cleanup
make clean            # Remove build artifacts
```

## Deployment to Production

```bash
# 1. Build for Linux
make build-linux

# 2. Deploy to server
scp bin/trackveil-api-linux lg@markedo.com:/home/lg/bin/trackveil/
scp .env lg@markedo.com:/home/lg/bin/trackveil/

# 3. On the server
ssh lg@markedo.com
cd /home/lg/bin/trackveil
./trackveil-api-linux

# Or set up as systemd service (see api/README.md)
```

## Project Structure

```
api/
├── cmd/trackveil-api/      # Main application entry point
├── internal/               # Private application code
│   ├── config/            # Configuration
│   ├── database/          # DB connection
│   ├── handlers/          # HTTP handlers
│   ├── middleware/        # HTTP middleware
│   └── models/            # Data models
├── bin/                   # Compiled binaries (gitignored)
├── logs/                  # Logs (gitignored)
├── Makefile              # Build automation
├── go.mod                # Go module
└── .env                  # Environment config (gitignored)
```

## Common Tasks

### Start API for development
```bash
cd /Users/lgforsberg/Projects/trackveil/api
make run
```

### Start API in background
```bash
cd /Users/lgforsberg/Projects/trackveil/api
make start
```

### Stop API
```bash
cd /Users/lgforsberg/Projects/trackveil/api
make stop
```

### Check if API is running
```bash
cd /Users/lgforsberg/Projects/trackveil/api
make status
```

### View logs
```bash
cd /Users/lgforsberg/Projects/trackveil/api
make logs
# or
tail -f logs/api.log
```

### Test tracking endpoint
```bash
curl -X POST http://localhost:8080/track \
  -H "Content-Type: application/json" \
  -d '{
    "site_id": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
    "page_url": "https://example.com/test",
    "page_title": "Test Page",
    "fingerprint": "test123"
  }'
```

**Note:** Site IDs are now 32-character alphanumeric strings (shorter and more user-friendly than UUIDs).

## Troubleshooting

### Port 8080 already in use
```bash
# Check what's using the port
lsof -i :8080

# Stop the API properly
make stop

# Or kill the process
kill <PID>
```

### Can't connect to database
```bash
# Test database connection
PGPASSWORD='your-password' psql -h pg1.trackveil.net -U markedo -d trackveil -c "SELECT 1;"

# Check .env file has correct credentials
cat .env
```

### Build errors
```bash
# Clean and rebuild
make clean
make deps
make build
```

