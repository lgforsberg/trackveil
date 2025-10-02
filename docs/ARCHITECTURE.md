# Trackveil Architecture

## Overview

Trackveil is a simple website visitor tracking system designed for easy installation and accurate analytics.

## System Architecture

```
┌─────────────────────┐
│  Website Owner's    │
│     Website         │
│                     │
│  <script async      │
│   data-site-id="*"> │
└──────────┬──────────┘
           │
           │ HTTPS POST /track
           │
           ▼
┌─────────────────────┐
│  cdn.trackveil.net  │
│   (Static Files)    │
│                     │
│  • tracker.js       │
│  • tracker.min.js   │
└─────────────────────┘
           │
           │ Track Page View
           │
           ▼
┌─────────────────────┐
│  api.trackveil.net  │
│   (Go + Gin API)    │
│                     │
│  • POST /track      │
│  • GET  /health     │
└──────────┬──────────┘
           │
           │ Store Data
           │
           ▼
┌─────────────────────┐
│   PostgreSQL RDS    │
│                     │
│  • accounts         │
│  • users            │
│  • sites            │
│  • visitors         │
│  • sessions         │
│  • page_views       │
└─────────────────────┘
```

## Components

### 1. JavaScript Tracker (`tracker/`)

**Purpose:** Collect visitor data from websites and send to API

**Technology:** Vanilla JavaScript (no dependencies)

**Key Features:**
- Asynchronous loading (doesn't block page render)
- Browser fingerprinting for unique visitor identification
- LocalStorage for persistent fingerprints
- Automatic page load time tracking
- Fallback mechanisms for older browsers

**Flow:**
1. Script loads asynchronously
2. Reads `data-site-id` from script tag
3. Generates/retrieves browser fingerprint
4. Collects page data
5. Sends POST request to API
6. Uses `sendBeacon` or `fetch` with keepalive

### 2. API Server (`api/`)

**Purpose:** Receive tracking data and store in database

**Technology:** Go 1.21+ with Gin web framework

**Key Features:**
- Fast, lightweight HTTP server
- CORS support for cross-origin requests
- Input validation and sanitization
- User agent parsing
- Session management (30-minute timeout)
- Visitor identification and deduplication

**Endpoints:**
- `POST /track` - Receive tracking data
- `GET /health` - Health check for monitoring

**Flow:**
1. Receive tracking request
2. Validate site_id exists
3. Parse user agent for browser/OS info
4. Get client IP address
5. Hash fingerprint (SHA-256)
6. Get or create visitor record
7. Get or create session (30-min window)
8. Insert page view record
9. Return success response

### 3. Database (`database/`)

**Purpose:** Store all tracking data and relationships

**Technology:** PostgreSQL (AWS RDS)

**Key Tables:**

**accounts**
- Main organizational entity
- Has many users and sites

**users**
- People who access an account
- Phase 2 will add authentication

**sites**
- Websites being tracked
- Each has a unique site_id (UUID)

**visitors**
- Unique visitors per site
- Identified by fingerprint hash
- Tracks first/last seen, total visits

**sessions**
- Visitor sessions (30-min timeout)
- Groups page views together

**page_views**
- Individual page view events
- Contains all tracking metadata
- Most queried table (heavily indexed)

## Data Flow

### Page View Tracking

```
1. User visits website with Trackveil installed
   ↓
2. Browser loads tracker.js asynchronously
   ↓
3. Tracker collects data:
   - Page URL, title, referrer
   - Screen resolution
   - Browser fingerprint
   - Load time
   ↓
4. POST request to api.trackveil.net/track
   ↓
5. API validates site_id
   ↓
6. API parses user agent
   ↓
7. API hashes fingerprint
   ↓
8. API queries for existing visitor
   ├─ Found: Update last_seen_at
   └─ Not found: Create new visitor
   ↓
9. API queries for active session
   ├─ Found: Update last_activity_at
   └─ Not found: Create new session
   ↓
10. API inserts page_view record
    ↓
11. Database triggers update visitor/session
    ↓
12. API returns success
```

## Security Considerations

### API
- CORS headers for cross-origin requests
- Rate limiting to prevent abuse
- Input validation on all fields
- UUID validation for site_id
- SQL injection prevention (parameterized queries)

### Privacy
- No cookies used
- No personal data collected
- Fingerprints are hashed (SHA-256)
- IP addresses can be anonymized (future)
- GDPR compliant as data processor

### Infrastructure
- HTTPS only (TLS 1.2+)
- Database credentials in environment variables
- AWS RDS for managed, secure database
- Connection pooling with limits

## Performance Optimizations

### JavaScript Tracker
- Async loading (non-blocking)
- Minified version ~2KB gzipped
- No external dependencies
- Uses sendBeacon (fire-and-forget)
- LocalStorage for fingerprint caching

### API
- Go's excellent concurrency
- Database connection pooling
- Efficient UUID generation
- Minimal JSON parsing
- Fast user agent library

### Database
- Strategic indexes on hot paths
- Partitioning ready (by date)
- Triggers for automatic updates
- Optimized for time-series queries

## Scaling Considerations (Phase 3)

### Database
- Partition `page_views` by month/quarter
- Read replicas for analytics queries
- Connection pool tuning
- Consider TimescaleDB extension

### API
- Horizontal scaling (stateless)
- Load balancer (AWS ALB)
- Redis for rate limiting
- Queue system for high-volume bursts

### CDN
- CloudFront for tracker.js distribution
- Edge locations for low latency
- Cache-Control headers

## Monitoring & Observability

### Health Checks
- `GET /health` endpoint
- Database connectivity check
- Response time monitoring

### Metrics to Track
- Request rate (requests/second)
- Error rate (%)
- Response time (p50, p95, p99)
- Database connection pool usage
- Page views per site
- Unique visitors per site

### Logging
- Structured logging (JSON)
- Request ID tracking
- Error tracking
- Audit logs for data access

## Development Workflow

```
1. Local Development
   - API runs on localhost:8080
   - Direct database connection
   - Test with test.html

2. Testing
   - Unit tests for handlers
   - Integration tests for DB
   - Load testing for performance

3. Staging (Future)
   - Deploy to staging environment
   - Test with real websites
   - Validate data integrity

4. Production
   - Deploy API to production server
   - Upload tracker.js to CDN
   - Update DNS records
   - Monitor metrics
```

## Future Architecture (Phase 2+)

### Dashboard UI
- Web interface for analytics
- User authentication
- Real-time stats
- Charts and graphs

### Enhanced Tracking
- Custom events
- SPA support
- E-commerce tracking
- Form tracking

### Advanced Features
- Funnel analysis
- A/B testing
- Heatmaps
- Session replay
- Alerts and notifications

