# Accounts and Sites Architecture

## Overview

Trackveil uses a hierarchical structure to organize tracking data:

```
Account (Organization/Company)
    ↓
    ├── Users (People who access the dashboard - Phase 2)
    └── Sites (Websites being tracked)
            ↓
            └── Tracking Data (page_views, visitors, sessions)
```

## Database Schema

### accounts
The top-level organizational entity (company, team, individual).

```sql
CREATE TABLE accounts (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE,
    updated_at TIMESTAMP WITH TIME ZONE
);
```

**Example:**
- "Acme Corporation"
- "John's Portfolio Sites"
- "Marketing Agency Inc"

### users
People who have access to an account's data (for Phase 2 dashboard).

```sql
CREATE TABLE users (
    id UUID PRIMARY KEY,
    account_id UUID REFERENCES accounts(id),
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE,
    updated_at TIMESTAMP WITH TIME ZONE
);
```

**In Phase 2:**
- Users log in with email/password
- Can view all sites under their account
- Can invite other users to the account

### sites
Individual websites being tracked.

```sql
CREATE TABLE sites (
    id VARCHAR(32) PRIMARY KEY,  -- 32-char alphanumeric
    account_id UUID REFERENCES accounts(id),
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE,
    updated_at TIMESTAMP WITH TIME ZONE
);
```

**Example:**
Account: "Acme Corporation"
- Site 1: "Acme Main Site" (acme.com)
- Site 2: "Acme Blog" (blog.acme.com)
- Site 3: "Acme Store" (store.acme.com)

## Use Cases

### Single Site Owner
```
Account: "John's Sites"
  └── Site: "John's Blog" (johnblog.com)
```

### Small Business
```
Account: "Bob's Coffee Shop"
  ├── Site: "Main Website" (bobscoffee.com)
  └── Site: "Online Store" (shop.bobscoffee.com)
```

### Agency Managing Multiple Clients
```
Account: "Marketing Agency"
  ├── Site: "Client A Website" (clienta.com)
  ├── Site: "Client B Website" (clientb.com)
  └── Site: "Client C Website" (clientc.com)
```

### Large Company with Multiple Properties
```
Account: "TechCorp Inc"
  ├── Site: "Main Website" (techcorp.com)
  ├── Site: "Product Docs" (docs.techcorp.com)
  ├── Site: "Blog" (blog.techcorp.com)
  ├── Site: "Support Portal" (support.techcorp.com)
  └── Site: "Customer Dashboard" (app.techcorp.com)
```

## Creating Accounts and Sites

### Phase 1: CLI Tool

Use the `create-site` tool:

```bash
cd tools/create-site
go build -o create-site

# List existing accounts
./create-site -list-accounts

# Create site (creates account if it doesn't exist)
./create-site -account "My Company" \
              -name "Main Website" \
              -domain "mycompany.com"

# Add another site to same account
./create-site -account "My Company" \
              -name "Blog" \
              -domain "blog.mycompany.com"

# Create site with existing account ID
./create-site -account-id "12345678-1234-1234-1234-123456789abc" \
              -name "Store" \
              -domain "store.mycompany.com"
```

### Phase 2: Dashboard (Planned)

Web interface for managing accounts and sites:

1. User creates account and logs in
2. Dashboard shows "Add New Site" button
3. User enters site name and domain
4. System generates site ID automatically
5. User copies tracking snippet
6. User pastes snippet on website

## Querying Analytics by Account

### All page views for an account
```sql
SELECT 
    s.name as site_name,
    COUNT(pv.id) as total_views
FROM accounts a
JOIN sites s ON a.id = s.account_id
JOIN page_views pv ON s.id = pv.site_id
WHERE a.id = 'account-uuid-here'
GROUP BY s.id, s.name
ORDER BY total_views DESC;
```

### Unique visitors across all sites in account
```sql
SELECT 
    s.name as site_name,
    COUNT(DISTINCT v.id) as unique_visitors
FROM accounts a
JOIN sites s ON a.id = s.account_id
JOIN visitors v ON s.id = v.site_id
WHERE a.id = 'account-uuid-here'
GROUP BY s.id, s.name
ORDER BY unique_visitors DESC;
```

### Account summary
```sql
SELECT 
    a.name as account_name,
    COUNT(DISTINCT s.id) as total_sites,
    COUNT(DISTINCT v.id) as total_unique_visitors,
    COUNT(pv.id) as total_page_views
FROM accounts a
LEFT JOIN sites s ON a.id = s.account_id
LEFT JOIN visitors v ON s.id = v.site_id
LEFT JOIN page_views pv ON s.id = pv.site_id
WHERE a.id = 'account-uuid-here'
GROUP BY a.id, a.name;
```

## Security and Access Control

### Phase 1 (Current)
- No authentication required
- Site ID acts as access token
- Anyone with site ID can view data (Phase 2 will add auth)

### Phase 2 (Planned)
- Users belong to accounts
- Users can only see data for their account's sites
- JWT or session-based authentication
- Invite system for adding team members

### Phase 3 (Future)
- Role-based access control
  - Admin: Full access
  - Editor: Can add/edit sites
  - Viewer: Read-only access
- Multi-account support (users can belong to multiple accounts)
- API keys for programmatic access

## Best Practices

### Account Naming
- Use your company/organization name
- Be specific if you have multiple entities
- Examples:
  - ✓ "Acme Corporation"
  - ✓ "John's Personal Projects"
  - ✗ "Sites" (too generic)
  - ✗ "Test" (not descriptive)

### Site Naming
- Use descriptive names
- Include purpose or subdomain
- Examples:
  - ✓ "Main Website"
  - ✓ "Company Blog"
  - ✓ "E-commerce Store"
  - ✗ "Site 1" (not descriptive)

### Domain Format
- Use the actual domain name
- Include subdomain if applicable
- Don't include protocol or path
- Examples:
  - ✓ "mycompany.com"
  - ✓ "blog.mycompany.com"
  - ✗ "https://mycompany.com" (no protocol)
  - ✗ "mycompany.com/blog" (no path)

## Migration Path

If you start with multiple accounts and want to consolidate:

```sql
-- Move all sites from Account B to Account A
UPDATE sites 
SET account_id = 'account-a-uuid'
WHERE account_id = 'account-b-uuid';

-- Delete old account
DELETE FROM accounts WHERE id = 'account-b-uuid';
```

## Future Enhancements

Phase 2+:
- Account settings (timezone, currency, etc.)
- Team member management
- Billing/subscription per account
- Account-level API keys
- White-label branding per account
- Data retention policies per account
- Export data per account

