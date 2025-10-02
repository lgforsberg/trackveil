package config

import (
	"fmt"
	"os"
	"strconv"
	"strings"

	"github.com/joho/godotenv"
)

type Config struct {
	Database  DatabaseConfig
	API       APIConfig
	CORS      CORSConfig
	RateLimit RateLimitConfig
}

type DatabaseConfig struct {
	Host     string
	Port     int
	User     string
	Password string
	DBName   string
	SSLMode  string
}

type APIConfig struct {
	Port int
	Env  string
}

type CORSConfig struct {
	AllowedOrigins []string
}

type RateLimitConfig struct {
	Requests      int
	WindowSeconds int
}

// Load loads configuration from environment variables
func Load() (*Config, error) {
	// Load .env file if it exists (for development)
	_ = godotenv.Load()

	// Parse database port
	dbPort, err := strconv.Atoi(getEnv("DB_PORT", "5432"))
	if err != nil {
		return nil, fmt.Errorf("invalid DB_PORT: %w", err)
	}

	// Parse API port
	apiPort, err := strconv.Atoi(getEnv("API_PORT", "8080"))
	if err != nil {
		return nil, fmt.Errorf("invalid API_PORT: %w", err)
	}

	// Parse rate limit
	rateLimitRequests, err := strconv.Atoi(getEnv("RATE_LIMIT_REQUESTS", "1000"))
	if err != nil {
		return nil, fmt.Errorf("invalid RATE_LIMIT_REQUESTS: %w", err)
	}

	rateLimitWindow, err := strconv.Atoi(getEnv("RATE_LIMIT_WINDOW_SECONDS", "60"))
	if err != nil {
		return nil, fmt.Errorf("invalid RATE_LIMIT_WINDOW_SECONDS: %w", err)
	}

	// Parse CORS origins
	originsStr := getEnv("ALLOWED_ORIGINS", "*")
	origins := strings.Split(originsStr, ",")
	for i, origin := range origins {
		origins[i] = strings.TrimSpace(origin)
	}

	return &Config{
		Database: DatabaseConfig{
			Host:     getEnv("DB_HOST", "localhost"),
			Port:     dbPort,
			User:     getEnv("DB_USER", "postgres"),
			Password: getEnv("DB_PASSWORD", ""),
			DBName:   getEnv("DB_NAME", "trackveil"),
			SSLMode:  getEnv("DB_SSLMODE", "require"),
		},
		API: APIConfig{
			Port: apiPort,
			Env:  getEnv("API_ENV", "development"),
		},
		CORS: CORSConfig{
			AllowedOrigins: origins,
		},
		RateLimit: RateLimitConfig{
			Requests:      rateLimitRequests,
			WindowSeconds: rateLimitWindow,
		},
	}, nil
}

// ConnectionString builds a PostgreSQL connection string
func (c *Config) ConnectionString() string {
	return fmt.Sprintf(
		"host=%s port=%d user=%s password=%s dbname=%s sslmode=%s",
		c.Database.Host,
		c.Database.Port,
		c.Database.User,
		c.Database.Password,
		c.Database.DBName,
		c.Database.SSLMode,
	)
}

func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}
