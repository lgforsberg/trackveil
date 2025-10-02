# Changelog

All notable changes to Trackveil will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Phase 2 (Planned)
- Dashboard UI for viewing analytics
- User authentication and management
- Site management interface
- GeoIP location tracking
- Custom event tracking

## [0.1.0] - 2025-10-02

### Added
- Initial release of Trackveil Phase 1
- PostgreSQL database schema with migrations
- Go API server with Gin framework
- JavaScript tracker snippet (tracker.js and tracker.min.js)
- Automatic page view tracking
- Unique visitor identification via browser fingerprinting
- Session tracking with 30-minute timeout
- User agent parsing (browser, OS, device type)
- Screen resolution tracking
- Page load time measurement
- Referrer tracking
- CORS support for cross-origin tracking
- Health check endpoint
- Comprehensive documentation
- Test HTML page for local testing
- Monorepo structure

### Database
- `accounts` table for organizational entities
- `users` table for future dashboard access
- `sites` table for tracked websites
- `visitors` table for unique visitor tracking
- `sessions` table for session grouping
- `page_views` table for individual page views
- Optimized indexes for query performance
- Database triggers for automatic updates

### API Endpoints
- `POST /track` - Receive tracking data from websites
- `GET /health` - Health check for monitoring

### Tracking Features
- Asynchronous JavaScript loading
- Browser fingerprinting with SHA-256 hashing
- LocalStorage for persistent visitor identification
- SendBeacon API for reliable tracking
- Graceful fallbacks for older browsers
- No cookies required
- GDPR-friendly data collection

### Documentation
- Main README with project overview
- Getting Started guide
- Architecture documentation
- Roadmap for future phases
- API documentation
- Tracker documentation
- Database schema documentation

### Infrastructure
- Environment-based configuration
- Database connection pooling
- Graceful shutdown handling
- Rate limiting configuration
- SSL/TLS support

[Unreleased]: https://github.com/lgforsberg/trackveil/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/lgforsberg/trackveil/releases/tag/v0.1.0

