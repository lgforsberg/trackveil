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
UPDATE users 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
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

