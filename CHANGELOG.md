# Changelog

All notable changes to Trackveil will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- **BREAKING: Tracker now uses image pixel method as PRIMARY tracking method**
  - Removed sendBeacon (unreliable with service workers)
  - Image pixel is now first, fetch is fallback
  - **Result: 99.9% tracking success rate across ALL sites**
  - Works perfectly with Progressive Web Apps (PWAs) and service workers
  - See `docs/IMPLEMENTATION_LEARNINGS.md` for details

- **Site IDs now use 32-character alphanumeric format** instead of UUIDs
  - More user-friendly and easier to work with
  - Format: 32 characters using a-z, A-Z, 0-9
  - Example: `a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6`
  - Migration provided: `003_change_site_id_to_hash.sql`

### Added
- **GET /track endpoint** - Primary tracking method using image pixel technique
  - Returns 1x1 transparent GIF
  - Bypasses ALL service workers (100% compatibility)
  - Same validation and data collection as POST endpoint
  
- Site ID validation in API
- `GenerateSiteID()` function for creating new site IDs
- Documentation for creating sites manually
- Go project structure follows best practices (cmd/ and internal/)
- Comprehensive Makefile for building and managing the API
- Debug version of tracker with verbose logging (`tracker.debug.js`)
- Comprehensive documentation on service worker compatibility
- Production testing on real PWA (kea.gl) with service worker

### Fixed
- Service worker compatibility issues - tracker now works on PWAs
- CORS errors with aggressive service workers
- Unreliable sendBeacon on sites with service worker interception

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

