# Creating Sites

In Phase 1, sites must be created manually via SQL. Phase 2 will add a web interface for this.

## Creating a New Site

### Step 1: Generate a Site ID

Site IDs are 32-character alphanumeric strings (a-zA-Z0-9). You can generate one using:

**Online:**
```bash
# Using openssl (macOS/Linux)
openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 32

# Using /dev/urandom (macOS/Linux)
cat /dev/urandom | tr -dc 'a-zA-Z0-9' | head -c 32
```

**Or use this Go snippet:**
```go
package main

import (
    "fmt"
    "github.com/lgforsberg/trackveil/api/internal/models"
)

func main() {
    siteID, _ := models.GenerateSiteID()
    fmt.Println(siteID)
}
```

### Step 2: Insert into Database

```sql
-- First, make sure you have an account
INSERT INTO accounts (id, name) 
VALUES (
    uuid_generate_v4(),
    'My Company Name'
);

-- Get the account ID you just created
SELECT id, name FROM accounts ORDER BY created_at DESC LIMIT 1;

-- Insert the site with your generated site ID
INSERT INTO sites (id, account_id, name, domain) 
VALUES (
    'YOUR_32_CHAR_SITE_ID_HERE',  -- e.g., 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6'
    'YOUR_ACCOUNT_ID_HERE',       -- UUID from previous query
    'My Website',
    'mywebsite.com'
);

-- Verify the site was created
SELECT id, name, domain FROM sites WHERE id = 'YOUR_32_CHAR_SITE_ID_HERE';
```

### Step 3: Install Tracker

Add this snippet to your website (replace with your actual site ID):

```html
<script async 
        src="https://cdn.trackveil.net/tracker.js" 
        data-site-id="YOUR_32_CHAR_SITE_ID_HERE">
</script>
```

## Complete Example

```sql
-- Create account
INSERT INTO accounts (id, name) 
VALUES (
    '12345678-1234-1234-1234-123456789abc',
    'Acme Corporation'
);

-- Create site with a generated 32-character ID
INSERT INTO sites (id, account_id, name, domain) 
VALUES (
    'kJ8mN2pQ5rT9vW3xY7zA4bC6dE1fG0hI',
    '12345678-1234-1234-1234-123456789abc',
    'Acme Main Site',
    'acme.com'
);

-- Verify
SELECT 
    s.id as site_id,
    s.name as site_name,
    s.domain,
    a.name as account_name
FROM sites s
JOIN accounts a ON s.account_id = a.id
WHERE s.id = 'kJ8mN2pQ5rT9vW3xY7zA4bC6dE1fG0hI';
```

## Site ID Format Rules

- **Length:** Exactly 32 characters
- **Characters:** Only a-z, A-Z, and 0-9 (no special characters)
- **Case sensitive:** `abc123` is different from `ABC123`
- **Unique:** Each site must have a unique ID

## Checking Your Sites

```sql
-- List all your sites
SELECT 
    s.id as site_id,
    s.name,
    s.domain,
    s.created_at,
    COUNT(pv.id) as total_page_views
FROM sites s
LEFT JOIN page_views pv ON s.id = pv.site_id
GROUP BY s.id, s.name, s.domain, s.created_at
ORDER BY s.created_at DESC;
```

## Future (Phase 2)

In Phase 2, you'll be able to create sites through a web dashboard:

1. Log in to dashboard.trackveil.net
2. Click "Add New Site"
3. Enter site name and domain
4. System generates site ID automatically
5. Copy the tracking snippet
6. Paste into your website

For now, manual SQL creation ensures maximum flexibility and control during development.

