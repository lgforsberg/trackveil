package models

import (
	"crypto/rand"
	"math/big"
)

const (
	// SiteIDLength is the length of generated site IDs
	SiteIDLength = 32
	// SiteIDChars are the characters used in site IDs (alphanumeric)
	SiteIDChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
)

// GenerateSiteID generates a random 32-character alphanumeric site ID
func GenerateSiteID() (string, error) {
	result := make([]byte, SiteIDLength)
	charsLen := big.NewInt(int64(len(SiteIDChars)))

	for i := 0; i < SiteIDLength; i++ {
		num, err := rand.Int(rand.Reader, charsLen)
		if err != nil {
			return "", err
		}
		result[i] = SiteIDChars[num.Int64()]
	}

	return string(result), nil
}

// ValidateSiteID checks if a site ID is valid format
func ValidateSiteID(id string) bool {
	if len(id) != SiteIDLength {
		return false
	}

	for _, char := range id {
		valid := false
		for _, validChar := range SiteIDChars {
			if char == validChar {
				valid = true
				break
			}
		}
		if !valid {
			return false
		}
	}

	return true
}

