-- Seed data for development/testing
-- This creates a test account, user, and site for initial development

-- Test account
INSERT INTO accounts (id, name) VALUES 
    ('00000000-0000-0000-0000-000000000001', 'Test Account');

-- Test user
INSERT INTO users (id, account_id, email, name) VALUES 
    ('00000000-0000-0000-0000-000000000002', '00000000-0000-0000-0000-000000000001', 'test@example.com', 'Test User');

-- Test site
INSERT INTO sites (id, account_id, name, domain) VALUES 
    ('00000000-0000-0000-0000-000000000003', '00000000-0000-0000-0000-000000000001', 'Test Site', 'example.com');

-- Display the test site ID for easy reference
SELECT 
    'Use this Site ID in your tracker snippet:' as note,
    id as site_id,
    name,
    domain
FROM sites 
WHERE id = '00000000-0000-0000-0000-000000000003';

