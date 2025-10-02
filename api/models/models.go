package models

import (
	"time"

	"github.com/google/uuid"
)

// TrackRequest represents the incoming tracking data from the JS snippet
type TrackRequest struct {
	SiteID       string `json:"site_id" binding:"required"`
	PageURL      string `json:"page_url" binding:"required"`
	PageTitle    string `json:"page_title"`
	Referrer     string `json:"referrer"`
	ScreenWidth  int    `json:"screen_width"`
	ScreenHeight int    `json:"screen_height"`
	Fingerprint  string `json:"fingerprint" binding:"required"` // Client-side generated fingerprint
	LoadTime     *int   `json:"load_time"`                      // Optional page load time in ms
}

// Visitor represents a unique visitor
type Visitor struct {
	ID              uuid.UUID
	SiteID          uuid.UUID
	FingerprintHash string
	FirstSeenAt     time.Time
	LastSeenAt      time.Time
	TotalVisits     int
}

// Session represents a visitor session
type Session struct {
	ID             uuid.UUID
	VisitorID      uuid.UUID
	SiteID         uuid.UUID
	StartedAt      time.Time
	LastActivityAt time.Time
	EndedAt        *time.Time
}

// PageView represents a single page view event
type PageView struct {
	ID             uuid.UUID
	SiteID         uuid.UUID
	VisitorID      uuid.UUID
	SessionID      uuid.UUID
	PageURL        string
	PageTitle      *string
	Referrer       *string
	UserAgent      *string
	IPAddress      string
	CountryCode    *string
	BrowserName    *string
	BrowserVersion *string
	OSName         *string
	OSVersion      *string
	DeviceType     *string
	ScreenWidth    *int
	ScreenHeight   *int
	ViewedAt       time.Time
	PageLoadTime   *int
}

// BrowserInfo contains parsed user agent information
type BrowserInfo struct {
	BrowserName    string
	BrowserVersion string
	OSName         string
	OSVersion      string
	DeviceType     string
}

// Site represents a tracked website
type Site struct {
	ID        uuid.UUID
	AccountID uuid.UUID
	Name      string
	Domain    string
	CreatedAt time.Time
	UpdatedAt time.Time
}

// Account represents an account (for future dashboard use)
type Account struct {
	ID        uuid.UUID
	Name      string
	CreatedAt time.Time
	UpdatedAt time.Time
}

// User represents a user (for future dashboard use)
type User struct {
	ID        uuid.UUID
	AccountID uuid.UUID
	Email     string
	Name      *string
	CreatedAt time.Time
	UpdatedAt time.Time
}
