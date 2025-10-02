# Trackveil Tools

Command-line tools for managing Trackveil.

## create-site

Tool for creating new sites and accounts.

### Usage

```bash
cd tools/create-site

# Build
go build -o create-site

# List all accounts
./create-site -list-accounts

# Create a site for a new account
./create-site -account "Acme Corp" -name "Acme Main Site" -domain "acme.com"

# Create a site for an existing account (by ID)
./create-site -account-id "12345678-1234-1234-1234-123456789abc" \
              -name "Acme Blog" \
              -domain "blog.acme.com"

# Create a site for an existing account (by name - will find or create)
./create-site -account "Acme Corp" -name "Acme Store" -domain "store.acme.com"
```

### Configuration

The tool reads database credentials from `../../api/.env`:

```env
DB_HOST=pg1.trackveil.net
DB_PORT=5432
DB_USER=markedo
DB_PASSWORD=your-password
DB_NAME=trackveil
DB_SSLMODE=require
```

### Examples

**Create first site for a new company:**
```bash
./create-site -account "My Company" -name "Main Website" -domain "mycompany.com"
```

Output:
```
✓ Created new account: My Company (ID: abc-123-def-456)
============================================================
✓ Site created successfully!
============================================================

Site ID: kJ8mN2pQ5rT9vW3xY7zA4bC6dE1fG0hI
Name: Main Website
Domain: mycompany.com
Account ID: abc-123-def-456

Add this snippet to your website:
----------------------------------------
<script async src="https://cdn.trackveil.net/tracker.js" 
        data-site-id="kJ8mN2pQ5rT9vW3xY7zA4bC6dE1fG0hI"></script>
============================================================
```

**Add another site to the same account:**
```bash
./create-site -account "My Company" -name "Company Blog" -domain "blog.mycompany.com"
```

Output:
```
✓ Using existing account: My Company (ID: abc-123-def-456)
============================================================
✓ Site created successfully!
...
```

**List all accounts:**
```bash
./create-site -list-accounts
```

Output:
```
Accounts:
----------------------------------------
ID: 12345678-1234-1234-1234-123456789abc
Name: My Company
Sites: 2

ID: 87654321-4321-4321-4321-cba987654321
Name: Test Account  
Sites: 1
```

