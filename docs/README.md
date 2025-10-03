# Trackveil Documentation

Complete documentation for the Trackveil analytics platform.

## Getting Started

- **[Getting Started Guide](getting-started.md)** - Setup and installation
- **[Architecture Overview](architecture.md)** - System design and data flow
- **[Deployment Guide](deployment.md)** - Production deployment

## Features & Usage

- **[Service Worker Compatibility](service-workers.md)** - How we achieve 99.9% tracking reliability
- **[Site Management](site-management.md)** - Creating and managing sites
- **[Roadmap](roadmap.md)** - Future features and timeline

## Component Documentation

- **[API Documentation](../api/README.md)** - Go tracking API
- **[Tracker Documentation](../tracker/README.md)** - JavaScript snippet
- **[Dashboard Documentation](../dashboard/README.md)** - Analytics dashboard
- **[Database Schema](../database/README.md)** - PostgreSQL schema
- **[CLI Tools](../tools/README.md)** - Command-line utilities

## Quick Links

### For Developers
- [Architecture](architecture.md) - Understand the system
- [Getting Started](getting-started.md) - Set up locally
- [API README](../api/README.md) - API development
- [API Commands](../api/docs/commands.md) - Quick reference

### For Operations
- [Deployment](deployment.md) - Deploy to production
- [Site Management](site-management.md) - Create sites
- [Service Workers](service-workers.md) - Troubleshooting

### For Planning
- [Roadmap](roadmap.md) - Future features
- [Phase 2 Status](phase2-complete.md) - Dashboard progress

## Project Status

**Phase 1: ✅ Complete** - Core tracking infrastructure ready

Currently tracking:
- ✅ Page views with full metadata
- ✅ Unique visitors (browser fingerprinting)
- ✅ Sessions (30-minute timeout)
- ✅ Browser/device information
- ✅ **99.9% success rate** (service worker compatible)

**Phase 2: 🚧 In Progress** - Dashboard UI with PHP + HTMX

## Support

Having issues? Check:

1. **Tracking not working?** → [Service Workers Guide](service-workers.md)
2. **Deployment issues?** → [Deployment Guide](deployment.md)
3. **Need to create a site?** → [Site Management](site-management.md)
4. **General questions?** → [Getting Started](getting-started.md)

## Contributing

This is currently a personal project. Documentation improvements welcome!

---

**Quick Start:**
```bash
# 1. Set up database
psql -h your-host -U user -d trackveil -f database/migrations/001_initial_schema.sql

# 2. Start API
cd api && make run

# 3. Add tracker to website
<script async src="https://cdn.trackveil.net/tracker.js" 
        data-site-id="YOUR_SITE_ID"></script>
```

For detailed instructions, see [Getting Started](getting-started.md).

