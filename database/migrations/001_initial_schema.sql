-- Trackveil Database Schema
-- Phase 1: Initial schema for data collection and future dashboard

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Accounts table
-- Represents the main organizational entity (company, individual, etc.)
CREATE TABLE accounts (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Users table
-- People who can access an account (Phase 2 will add authentication)
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    account_id UUID NOT NULL REFERENCES accounts(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Sites table
-- Websites being tracked under an account
CREATE TABLE sites (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    account_id UUID NOT NULL REFERENCES accounts(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(account_id, domain)
);

-- Visitors table
-- Unique visitors identified by a hashed fingerprint
CREATE TABLE visitors (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    site_id UUID NOT NULL REFERENCES sites(id) ON DELETE CASCADE,
    fingerprint_hash VARCHAR(64) NOT NULL, -- SHA-256 hash of browser fingerprint
    first_seen_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    total_visits INTEGER DEFAULT 1,
    UNIQUE(site_id, fingerprint_hash)
);

-- Sessions table
-- Visitor sessions (30 min timeout typical)
CREATE TABLE sessions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    visitor_id UUID NOT NULL REFERENCES visitors(id) ON DELETE CASCADE,
    site_id UUID NOT NULL REFERENCES sites(id) ON DELETE CASCADE,
    started_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    last_activity_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP WITH TIME ZONE
);

-- Page views table
-- Individual page view events
CREATE TABLE page_views (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    site_id UUID NOT NULL REFERENCES sites(id) ON DELETE CASCADE,
    visitor_id UUID NOT NULL REFERENCES visitors(id) ON DELETE CASCADE,
    session_id UUID NOT NULL REFERENCES sessions(id) ON DELETE CASCADE,
    
    -- Page information
    page_url TEXT NOT NULL,
    page_title VARCHAR(500),
    referrer TEXT,
    
    -- Technical information
    user_agent TEXT,
    ip_address INET,
    country_code VARCHAR(2), -- ISO 3166-1 alpha-2
    
    -- Browser/device information (parsed from user agent)
    browser_name VARCHAR(50),
    browser_version VARCHAR(50),
    os_name VARCHAR(50),
    os_version VARCHAR(50),
    device_type VARCHAR(20), -- desktop, mobile, tablet
    
    -- Screen information
    screen_width INTEGER,
    screen_height INTEGER,
    
    -- Timing
    viewed_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    
    -- Performance metrics (optional, can be null)
    page_load_time INTEGER -- milliseconds
);

-- Indexes for query performance

-- Accounts
CREATE INDEX idx_accounts_created_at ON accounts(created_at);

-- Users
CREATE INDEX idx_users_account_id ON users(account_id);
CREATE INDEX idx_users_email ON users(email);

-- Sites
CREATE INDEX idx_sites_account_id ON sites(account_id);
CREATE INDEX idx_sites_domain ON sites(domain);

-- Visitors
CREATE INDEX idx_visitors_site_id ON visitors(site_id);
CREATE INDEX idx_visitors_fingerprint_hash ON visitors(fingerprint_hash);
CREATE INDEX idx_visitors_last_seen_at ON visitors(last_seen_at);

-- Sessions
CREATE INDEX idx_sessions_visitor_id ON sessions(visitor_id);
CREATE INDEX idx_sessions_site_id ON sessions(site_id);
CREATE INDEX idx_sessions_started_at ON sessions(started_at);

-- Page views (most queried table, needs good indexes)
CREATE INDEX idx_page_views_site_id ON page_views(site_id);
CREATE INDEX idx_page_views_visitor_id ON page_views(visitor_id);
CREATE INDEX idx_page_views_session_id ON page_views(session_id);
CREATE INDEX idx_page_views_viewed_at ON page_views(viewed_at DESC);
CREATE INDEX idx_page_views_site_viewed_at ON page_views(site_id, viewed_at DESC);
CREATE INDEX idx_page_views_page_url ON page_views(site_id, page_url);
CREATE INDEX idx_page_views_country_code ON page_views(site_id, country_code);

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers for updated_at
CREATE TRIGGER update_accounts_updated_at BEFORE UPDATE ON accounts
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_sites_updated_at BEFORE UPDATE ON sites
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Function to update visitor last_seen_at
CREATE OR REPLACE FUNCTION update_visitor_last_seen()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE visitors 
    SET last_seen_at = NEW.viewed_at,
        total_visits = total_visits + 1
    WHERE id = NEW.visitor_id;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_visitor_on_page_view AFTER INSERT ON page_views
    FOR EACH ROW EXECUTE FUNCTION update_visitor_last_seen();

-- Function to update session last_activity_at
CREATE OR REPLACE FUNCTION update_session_last_activity()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE sessions 
    SET last_activity_at = NEW.viewed_at
    WHERE id = NEW.session_id;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_session_on_page_view AFTER INSERT ON page_views
    FOR EACH ROW EXECUTE FUNCTION update_session_last_activity();

