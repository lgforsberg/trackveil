package handlers

import (
	"crypto/sha256"
	"database/sql"
	"encoding/hex"
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
	"github.com/lgforsberg/trackveil/api/internal/database"
	"github.com/lgforsberg/trackveil/api/internal/models"
	"github.com/mssola/user_agent"
)

// TrackHandler handles incoming tracking requests
type TrackHandler struct {
	db *database.DB
}

// NewTrackHandler creates a new track handler
func NewTrackHandler(db *database.DB) *TrackHandler {
	return &TrackHandler{db: db}
}

// Track handles POST /track requests
func (h *TrackHandler) Track(c *gin.Context) {
	var req models.TrackRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid request body"})
		return
	}

	// Validate site_id format
	if !models.ValidateSiteID(req.SiteID) {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Invalid site_id format"})
		return
	}

	siteID := req.SiteID

	// Verify site exists
	var siteExists bool
	err := h.db.QueryRow("SELECT EXISTS(SELECT 1 FROM sites WHERE id = $1)", siteID).Scan(&siteExists)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Database error"})
		return
	}
	if !siteExists {
		c.JSON(http.StatusNotFound, gin.H{"error": "Site not found"})
		return
	}

	// Get client IP
	clientIP := c.ClientIP()

	// Get user agent
	userAgentStr := c.GetHeader("User-Agent")

	// Hash the fingerprint
	fingerprintHash := hashFingerprint(req.Fingerprint)

	// Parse user agent
	browserInfo := parseUserAgent(userAgentStr)

	// Get or create visitor
	visitorID, err := h.getOrCreateVisitor(siteID, fingerprintHash)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to get/create visitor"})
		return
	}

	// Get or create session (30 min timeout)
	sessionID, err := h.getOrCreateSession(siteID, visitorID)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to get/create session"})
		return
	}

	// Create page view
	err = h.createPageView(models.PageView{
		SiteID:         siteID,
		VisitorID:      visitorID,
		SessionID:      sessionID,
		PageURL:        req.PageURL,
		PageTitle:      nullString(req.PageTitle),
		Referrer:       nullString(req.Referrer),
		UserAgent:      nullString(userAgentStr),
		IPAddress:      clientIP,
		CountryCode:    nil, // TODO: GeoIP lookup in Phase 2
		BrowserName:    nullString(browserInfo.BrowserName),
		BrowserVersion: nullString(browserInfo.BrowserVersion),
		OSName:         nullString(browserInfo.OSName),
		OSVersion:      nullString(browserInfo.OSVersion),
		DeviceType:     nullString(browserInfo.DeviceType),
		ScreenWidth:    nullInt(req.ScreenWidth),
		ScreenHeight:   nullInt(req.ScreenHeight),
		ViewedAt:       time.Now(),
		PageLoadTime:   req.LoadTime,
	})

	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create page view"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"status": "success"})
}

// Health check endpoint
func (h *TrackHandler) Health(c *gin.Context) {
	// Check database connection
	if err := h.db.Ping(); err != nil {
		c.JSON(http.StatusServiceUnavailable, gin.H{
			"status": "unhealthy",
			"error":  "database connection failed",
		})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"status": "healthy",
		"time":   time.Now().UTC(),
	})
}

// getOrCreateVisitor gets an existing visitor or creates a new one
func (h *TrackHandler) getOrCreateVisitor(siteID string, fingerprintHash string) (uuid.UUID, error) {
	var visitorID uuid.UUID

	// Try to get existing visitor
	err := h.db.QueryRow(`
		SELECT id FROM visitors 
		WHERE site_id = $1 AND fingerprint_hash = $2
	`, siteID, fingerprintHash).Scan(&visitorID)

	if err == sql.ErrNoRows {
		// Create new visitor
		visitorID = uuid.New()
		_, err = h.db.Exec(`
			INSERT INTO visitors (id, site_id, fingerprint_hash, first_seen_at, last_seen_at, total_visits)
			VALUES ($1, $2, $3, $4, $5, 0)
		`, visitorID, siteID, fingerprintHash, time.Now(), time.Now())
		if err != nil {
			return uuid.Nil, err
		}
	} else if err != nil {
		return uuid.Nil, err
	}

	return visitorID, nil
}

// getOrCreateSession gets an active session or creates a new one
func (h *TrackHandler) getOrCreateSession(siteID string, visitorID uuid.UUID) (uuid.UUID, error) {
	var sessionID uuid.UUID

	// Try to get active session (within last 30 minutes)
	thirtyMinsAgo := time.Now().Add(-30 * time.Minute)
	err := h.db.QueryRow(`
		SELECT id FROM sessions 
		WHERE visitor_id = $1 
		AND site_id = $2
		AND last_activity_at > $3
		AND ended_at IS NULL
		ORDER BY started_at DESC
		LIMIT 1
	`, visitorID, siteID, thirtyMinsAgo).Scan(&sessionID)

	if err == sql.ErrNoRows {
		// Create new session
		sessionID = uuid.New()
		_, err = h.db.Exec(`
			INSERT INTO sessions (id, visitor_id, site_id, started_at, last_activity_at)
			VALUES ($1, $2, $3, $4, $5)
		`, sessionID, visitorID, siteID, time.Now(), time.Now())
		if err != nil {
			return uuid.Nil, err
		}
	} else if err != nil {
		return uuid.Nil, err
	}

	return sessionID, nil
}

// createPageView inserts a new page view record
func (h *TrackHandler) createPageView(pv models.PageView) error {
	pv.ID = uuid.New()

	_, err := h.db.Exec(`
		INSERT INTO page_views (
			id, site_id, visitor_id, session_id, page_url, page_title, referrer,
			user_agent, ip_address, country_code, browser_name, browser_version,
			os_name, os_version, device_type, screen_width, screen_height,
			viewed_at, page_load_time
		) VALUES (
			$1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19
		)
	`,
		pv.ID, pv.SiteID, pv.VisitorID, pv.SessionID, pv.PageURL, pv.PageTitle,
		pv.Referrer, pv.UserAgent, pv.IPAddress, pv.CountryCode, pv.BrowserName,
		pv.BrowserVersion, pv.OSName, pv.OSVersion, pv.DeviceType, pv.ScreenWidth,
		pv.ScreenHeight, pv.ViewedAt, pv.PageLoadTime,
	)

	return err
}

// hashFingerprint creates a SHA-256 hash of the fingerprint
func hashFingerprint(fingerprint string) string {
	hash := sha256.Sum256([]byte(fingerprint))
	return hex.EncodeToString(hash[:])
}

// parseUserAgent parses the user agent string
func parseUserAgent(uaString string) models.BrowserInfo {
	ua := user_agent.New(uaString)

	browserName, browserVersion := ua.Browser()
	osInfo := ua.OS()

	deviceType := "desktop"
	if ua.Mobile() {
		deviceType = "mobile"
	}
	// Note: user_agent library doesn't detect tablets well, could enhance in Phase 2

	return models.BrowserInfo{
		BrowserName:    browserName,
		BrowserVersion: browserVersion,
		OSName:         osInfo,
		OSVersion:      "", // Library doesn't provide OS version easily
		DeviceType:     deviceType,
	}
}

// Helper functions for nullable fields
func nullString(s string) *string {
	if s == "" {
		return nil
	}
	return &s
}

func nullInt(i int) *int {
	if i == 0 {
		return nil
	}
	return &i
}
