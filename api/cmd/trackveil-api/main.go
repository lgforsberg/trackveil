package main

import (
	"fmt"
	"log"
	"os"
	"os/signal"
	"syscall"

	"trackveilapi/internal/config"
	"trackveilapi/internal/database"
	"trackveilapi/internal/handlers"
	"trackveilapi/internal/middleware"

	"github.com/gin-gonic/gin"
)

var (
	Version   = "dev"
	BuildTime = "unknown"
)

func main() {
	log.Printf("Trackveil API %s (built: %s)", Version, BuildTime)

	// Load configuration
	cfg, err := config.Load()
	if err != nil {
		log.Fatalf("Failed to load configuration: %v", err)
	}

	// Connect to database
	db, err := database.Connect(cfg.ConnectionString())
	if err != nil {
		log.Fatalf("Failed to connect to database: %v", err)
	}
	defer db.Close()

	log.Println("Successfully connected to database")

	// Set Gin mode
	if cfg.API.Env == "production" {
		gin.SetMode(gin.ReleaseMode)
	}

	// Initialize router
	router := gin.Default()

	// Add CORS middleware
	router.Use(middleware.CORS(cfg.CORS.AllowedOrigins))

	// Initialize handlers
	trackHandler := handlers.NewTrackHandler(db)

	// Routes
	router.GET("/health", trackHandler.Health)
	router.POST("/track", trackHandler.Track)
	router.GET("/track", trackHandler.Track) // Support GET for image pixel fallback

	// Start server
	addr := fmt.Sprintf(":%d", cfg.API.Port)
	log.Printf("Starting Trackveil API on %s", addr)
	log.Printf("Environment: %s", cfg.API.Env)

	// Graceful shutdown
	go func() {
		if err := router.Run(addr); err != nil {
			log.Fatalf("Failed to start server: %v", err)
		}
	}()

	// Wait for interrupt signal
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	<-quit

	log.Println("Shutting down server...")
}
