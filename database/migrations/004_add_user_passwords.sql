-- Add password authentication to users table

BEGIN;

-- Add password column to users table
ALTER TABLE users ADD COLUMN password_hash VARCHAR(255);

-- Add email verification columns (for Phase 2)
ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN verification_token VARCHAR(64);

-- Add last login tracking
ALTER TABLE users ADD COLUMN last_login_at TIMESTAMP WITH TIME ZONE;

-- Update test user with a password (password: "password123")
-- Hash generated with: password_hash('password123', PASSWORD_DEFAULT)
UPDATE users 
SET password_hash = '$2y$12$kLXtqjDycOWPBQY1Z5gqdedJ173eRLWZFOKilDtSf62X7KvW/9PkC'
WHERE email = 'test@example.com';

-- Create an index on email for faster login queries
CREATE INDEX IF NOT EXISTS idx_users_email_lookup ON users(email) WHERE password_hash IS NOT NULL;

COMMIT;

-- Display test credentials
SELECT 
    'Test login credentials:' as note,
    email,
    'password123' as password
FROM users 
WHERE email = 'test@example.com';

