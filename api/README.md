# Trackveil API

Go + Gin REST API for receiving and storing website tracking data.

## Setup

1. **Install dependencies**
   ```bash
   go mod download
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Run the API**
   ```bash
   go run main.go
   ```

   The API will start on `http://localhost:8080` by default.

## API Endpoints

### `POST /track`
Receives tracking data from the JavaScript snippet.

**Request Body:**
```json
{
  "site_id": "00000000-0000-0000-0000-000000000003",
  "page_url": "https://example.com/page",
  "page_title": "Page Title",
  "referrer": "https://google.com",
  "screen_width": 1920,
  "screen_height": 1080,
  "fingerprint": "unique-browser-fingerprint",
  "load_time": 1234
}
```

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

### Project Structure
```
api/
├── main.go              # Application entry point
├── config/              # Configuration management
├── database/            # Database connection
├── handlers/            # HTTP request handlers
├── middleware/          # HTTP middleware (CORS, etc.)
└── models/              # Data models
```

### Building for Production
```bash
CGO_ENABLED=0 GOOS=linux go build -o trackveil-api main.go
```

### Testing
```bash
# Run all tests
go test ./...

# Test with coverage
go test -cover ./...
```

## Deployment

1. Build the binary
2. Set environment variables
3. Run behind a reverse proxy (nginx, caddy) with SSL
4. Point `api.trackveil.net` to your server

Example nginx config:
```nginx
server {
    listen 443 ssl;
    server_name api.trackveil.net;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

