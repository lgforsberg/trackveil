package main

import (
	"fmt"
	"log"
	"os"
	"os/signal"
	"syscall"

	"github.com/gin-gonic/gin"
	"github.com/lgforsberg/trackveil/api/config"
	"github.com/lgforsberg/trackveil/api/database"
	"github.com/lgforsberg/trackveil/api/handlers"
	"github.com/lgforsberg/trackveil/api/middleware"
)

func main() {
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
