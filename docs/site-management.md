# Site Management

## Account & Site Structure

Trackveil uses a hierarchical structure:

```
Account (Organization/Company)
    ↓
    ├── Users (People who access the dashboard)
    └── Sites (Websites being tracked)
            ↓
            └── Tracking Data (page_views, visitors, sessions)
```

**Examples:**

**Single site owner:**
```
Account: "John's Sites"
  └── Site: "John's Blog" (johnblog.com)
```

**Small business:**
```
Account: "Bob's Coffee Shop"
  ├── Site: "Main Website" (bobscoffee.com)
  └── Site: "Online Store" (shop.bobscoffee.com)
```

**Agency managing clients:**
```
Account: "Marketing Agency"
  ├── Site: "Client A" (clienta.com)
  ├── Site: "Client B" (clientb.com)
  └── Site: "Client C" (clientc.com)
```

## Creating Sites (Phase 1)

### Using the CLI Tool (Recommended)

```bash
cd tools/create-site
go build -o create-site

# List existing accounts
./create-site -list-accounts

# Create site (creates account if doesn't exist)
./create-site -account "My Company" \
              -name "Main Website" \
              -domain "mycompany.com"

# Add another site to same account
./create-site -account "My Company" \
              -name "Blog" \
              -domain "blog.mycompany.com"
```

The tool outputs a ready-to-use tracking snippet:

```html
<script async src="https://cdn.trackveil.net/tracker.js" 
        data-site-id="a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"></script>
```

### Manual Creation (SQL)

If you prefer SQL directly:

```sql
-- 1. Create account (if needed)
INSERT INTO accounts (id, name) 
VALUES (uuid_generate_v4(), 'My Company');

-- 2. Generate site ID (32 alphanumeric characters)
-- Use: openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 32

-- 3. Create site
INSERT INTO sites (id, account_id, name, domain) 
VALUES (
    'YOUR_32_CHAR_SITE_ID',
    (SELECT id FROM accounts WHERE name = 'My Company'),
    'Main Website',
    'mycompany.com'
);
```

### Site ID Format

- **Length:** Exactly 32 characters
- **Characters:** a-z, A-Z, 0-9 only (no special characters)
- **Case sensitive:** `abc123` ≠ `ABC123`
- **Example:** `a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6`

## Viewing Your Sites

```sql
-- List all sites with stats
SELECT 
    s.id as site_id,
    s.name,
    s.domain,
    s.created_at,
    COUNT(pv.id) as total_page_views,
    COUNT(DISTINCT pv.visitor_id) as unique_visitors
FROM sites s
LEFT JOIN page_views pv ON s.id = pv.site_id
GROUP BY s.id
ORDER BY s.created_at DESC;

-- Account summary
SELECT 
    a.name as account_name,
    COUNT(DISTINCT s.id) as total_sites,
    COUNT(DISTINCT v.id) as unique_visitors,
    COUNT(pv.id) as total_page_views
FROM accounts a
LEFT JOIN sites s ON a.id = s.account_id
LEFT JOIN visitors v ON s.id = v.site_id
LEFT JOIN page_views pv ON s.id = pv.site_id
WHERE a.name = 'My Company'
GROUP BY a.id, a.name;
```

## Best Practices

### Naming Conventions

**Accounts:**
- ✅ "Acme Corporation"
- ✅ "John's Personal Projects"
- ❌ "Sites" (too generic)
- ❌ "Test" (not descriptive)

**Sites:**
- ✅ "Main Website"
- ✅ "Company Blog"
- ✅ "E-commerce Store"
- ❌ "Site 1" (not descriptive)

**Domains:**
- ✅ `mycompany.com`
- ✅ `blog.mycompany.com`
- ❌ `https://mycompany.com` (no protocol)
- ❌ `mycompany.com/blog` (no path)

## Phase 2: Web Dashboard

In Phase 2, you'll be able to manage sites through a web interface:

1. Log in to dashboard.trackveil.net
2. Click "Add New Site"
3. Enter site name and domain
4. System generates site ID automatically
5. Copy the tracking snippet
6. Paste into your website

### Future Features

- User invitation system
- Role-based access (Admin, Editor, Viewer)
- Multi-account support
- API keys for programmatic access
- Site verification
- Team management
- Account-level settings (timezone, currency)

## Security & Access Control

### Phase 1 (Current)
- No authentication required
- Site ID acts as access token
- Anyone with site ID can track (Phase 2 will add auth)

### Phase 2 (Planned)
- Users belong to accounts
- Users can only see their account's sites
- JWT/session-based authentication
- Invite system for team members

## Migration & Maintenance

### Moving sites between accounts

```sql
-- Move all sites from Account B to Account A
UPDATE sites 
SET account_id = (SELECT id FROM accounts WHERE name = 'Account A')
WHERE account_id = (SELECT id FROM accounts WHERE name = 'Account B');

-- Delete old account
DELETE FROM accounts WHERE name = 'Account B';
```

### Deleting sites

```sql
-- Warning: This will delete all associated tracking data!
DELETE FROM sites WHERE id = 'site-id-here';
```

---

**For now:** Use the CLI tool or SQL for site management. Phase 2 will add a full web interface for all these operations.

