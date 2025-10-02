# Trackveil API

Go + Gin REST API for receiving and storing website tracking data.

## Project Structure

Following Go best practices with standard project layout:

```
api/
├── cmd/
│   └── trackveil-api/    # Application entry point
│       └── main.go
├── internal/             # Private application code
│   ├── config/          # Configuration management
│   ├── database/        # Database connection
│   ├── handlers/        # HTTP request handlers
│   ├── middleware/      # HTTP middleware (CORS, etc.)
│   └── models/          # Data models
├── bin/                 # Compiled binaries (gitignored)
├── logs/                # Application logs (gitignored)
├── Makefile            # Build automation
├── go.mod              # Go module definition
└── .env                # Environment config (gitignored)
```

## Quick Start

### Using Makefile (Recommended)

```bash
# Install dependencies
make deps

# Run in development mode
make run

# Build the binary
make build

# Build and start in background
make start

# Check status
make status

# View logs
make logs

# Stop the API
make stop

# Restart
make restart

# See all available commands
make help
```

## Setup

1. **Install dependencies**
   ```bash
   make deps
   ```

2. **Configure environment**
   ```bash
   cp env.example .env
   # Edit .env with your database credentials
   ```

3. **Run the API**
   ```bash
   make run
   ```

   The API will start on `http://localhost:8080` by default.

## API Endpoints

### `POST /track`
Receives tracking data from the JavaScript snippet.

**Request Body:**
```json
{
  "site_id": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
  "page_url": "https://example.com/page",
  "page_title": "Page Title",
  "referrer": "https://google.com",
  "screen_width": 1920,
  "screen_height": 1080,
  "fingerprint": "unique-browser-fingerprint",
  "load_time": 1234
}
```

**Note:** Site IDs are 32-character alphanumeric strings (a-zA-Z0-9), not UUIDs.

**Response:**
```json
{
  "status": "success"
}
```

### `GET /health`
Health check endpoint.

**Response:**
```json
{
  "status": "healthy",
  "time": "2025-10-02T12:00:00Z"
}
```

## Development

### Available Make Commands

- `make help` - Show all available commands
- `make deps` - Download Go dependencies
- `make build` - Build the binary
- `make build-linux` - Build for Linux (deployment)
- `make run` - Run in development mode
- `make dev` - Run with auto-reload (requires air)
- `make start` - Build and start in background
- `make stop` - Stop the running API
- `make restart` - Restart the API
- `make status` - Check if API is running
- `make logs` - Tail the application logs
- `make test` - Run tests
- `make test-coverage` - Run tests with coverage report
- `make clean` - Clean build artifacts
- `make check` - Run go fmt and go vet
- `make deploy-build` - Build for production deployment

### Building for Production
```bash
# Build for Linux server
make build-linux

# Binary will be at: bin/trackveil-api-linux
```

### Testing
```bash
# Run all tests
make test

# Run tests with coverage
make test-coverage
```

## Deployment

### Production Build

```bash
# Build for Linux
make build-linux

# Copy binary to server
scp bin/trackveil-api-linux lg@markedo.com:/home/lg/bin/trackveil/

# Copy .env file (with production settings)
scp .env lg@markedo.com:/home/lg/bin/trackveil/
```

### Running on Production Server

```bash
# SSH to server
ssh lg@markedo.com

# Navigate to directory
cd /home/lg/bin/trackveil

# Start the API
./trackveil-api-linux
```

### Setting up as a Service

Create `/etc/systemd/system/trackveil-api.service`:

```ini
[Unit]
Description=Trackveil API
After=network.target

[Service]
Type=simple
User=lg
WorkingDirectory=/home/lg/bin/trackveil
ExecStart=/home/lg/bin/trackveil/trackveil-api-linux
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl daemon-reload
sudo systemctl enable trackveil-api
sudo systemctl start trackveil-api
sudo systemctl status trackveil-api
```

### Nginx Configuration

Point `api.trackveil.net` to your server with this nginx config:

```nginx
server {
    listen 443 ssl;
    server_name api.trackveil.net;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

