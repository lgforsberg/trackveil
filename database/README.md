# Database Schema

PostgreSQL database schema and migrations for Trackveil.

## Running Migrations

```bash
psql -h your-rds-endpoint.rds.amazonaws.com -U your-user -d trackveil -f migrations/001_initial_schema.sql
```

## Schema Overview

### Accounts
Main billing/organizational entity. Each account can have multiple users and sites.

### Users
People who can access an account's data. No role levels in Phase 1.

### Sites
Websites being tracked. Each site belongs to one account.

### Page Views
Individual page view events with full metadata.

### Visitors
Unique visitors identified by hashed fingerprint. Used for unique visitor counting.

### Sessions
Visitor sessions for grouping page views together.

