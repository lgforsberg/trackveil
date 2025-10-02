# Trackveil

A simplistic website visitor tracker that's easy to install and use.

## Project Structure

```
trackveil/
├── api/              # Go + Gin REST API
├── tracker/          # JavaScript tracking snippet
├── database/         # PostgreSQL schema and migrations
├── docs/            # Documentation
└── README.md
```

## Quick Start

👉 **See [docs/GETTING_STARTED.md](docs/GETTING_STARTED.md) for detailed setup instructions**

### TL;DR

1. **Database Setup**
   ```bash
   psql -h your-rds-endpoint -U your-user -d trackveil -f database/migrations/001_initial_schema.sql
   psql -h your-rds-endpoint -U your-user -d trackveil -f database/migrations/002_seed_test_data.sql
   ```

2. **API Setup**
   ```bash
   cd api
   cp env.example .env
   # Edit .env with your database credentials
   go mod download
   go run main.go
   ```

3. **Test Locally**
   ```bash
   cd tracker
   # Update API_ENDPOINT in tracker.js to http://localhost:8080/track
   open test.html
   ```

## Usage

Add this snippet to any website:

```html
<script async src="https://cdn.trackveil.net/tracker.js" data-site-id="YOUR_SITE_ID"></script>
```

Your site ID is a short 32-character code (e.g., `a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6`)

## Development Roadmap

### Phase 1 (Current)
- ✅ Data collection infrastructure
- ✅ PostgreSQL database schema
- ✅ Go API for receiving tracking data
- ✅ JavaScript tracker snippet

### Phase 2 (Future)
- Dashboard UI for viewing analytics
- User authentication
- Custom event tracking

### Phase 3 (Future)
- Scaling optimizations
- Advanced analytics features

## Documentation

- **[Getting Started Guide](docs/GETTING_STARTED.md)** - Step-by-step setup instructions
- **[Architecture Overview](docs/ARCHITECTURE.md)** - System design and data flow
- **[Roadmap](docs/ROADMAP.md)** - Future features and timeline
- **[API Documentation](api/README.md)** - API endpoints and usage
- **[Tracker Documentation](tracker/README.md)** - JavaScript snippet details
- **[Database Schema](database/README.md)** - Database structure

## Project Status

**Phase 1: ✅ Complete** - Core data collection infrastructure is ready

Currently tracking:
- ✅ Page views with full metadata
- ✅ Unique visitors (browser fingerprinting)
- ✅ Sessions (30-minute timeout)
- ✅ Browser/device information
- ✅ Screen resolution
- ✅ Referrer sources
- ✅ Page load times

**Phase 2: 🚧 Planned** - Dashboard UI and user management (Q4 2025)

See [ROADMAP.md](docs/ROADMAP.md) for detailed future plans.

## Tech Stack

- **Database:** PostgreSQL (AWS RDS)
- **API:** Go 1.21+ with Gin framework
- **Tracker:** Vanilla JavaScript (no dependencies)
- **Infrastructure:** AWS (RDS, EC2, CloudFront)

## Contributing

This is currently a personal project. Ideas and feedback are welcome!

## License

TBD (likely MIT)

