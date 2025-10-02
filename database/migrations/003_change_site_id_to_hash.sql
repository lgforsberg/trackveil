-- Migration: Change site IDs from UUID to 32-character alphanumeric hash
-- This makes the site IDs shorter and more user-friendly

BEGIN;

-- Drop the test site (we'll recreate it with the new format)
DELETE FROM sites WHERE id = '00000000-0000-0000-0000-000000000003';

-- Alter sites table to use VARCHAR(32) for ID
ALTER TABLE sites DROP CONSTRAINT sites_pkey CASCADE;
ALTER TABLE sites ALTER COLUMN id TYPE VARCHAR(32);
ALTER TABLE sites ADD PRIMARY KEY (id);

-- Update foreign key in visitors table
ALTER TABLE visitors ALTER COLUMN site_id TYPE VARCHAR(32);
ALTER TABLE visitors ADD CONSTRAINT visitors_site_id_fkey 
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE;

-- Update foreign key in sessions table
ALTER TABLE sessions ALTER COLUMN site_id TYPE VARCHAR(32);
ALTER TABLE sessions ADD CONSTRAINT sessions_site_id_fkey 
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE;

-- Update foreign key in page_views table
ALTER TABLE page_views ALTER COLUMN site_id TYPE VARCHAR(32);
ALTER TABLE page_views ADD CONSTRAINT page_views_site_id_fkey 
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE;

-- Recreate test site with new ID format
INSERT INTO sites (id, account_id, name, domain, created_at, updated_at) 
VALUES (
    'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6',
    '00000000-0000-0000-0000-000000000001',
    'Test Site',
    'example.com',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
);

-- Display the new test site ID
SELECT 
    'Use this Site ID in your tracker snippet:' as note,
    id as site_id,
    name,
    domain
FROM sites 
WHERE id = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6';

COMMIT;

