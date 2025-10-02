.PHONY: help build run stop restart test clean dev install deps

# Application name
APP_NAME=trackveil-api
BUILD_DIR=bin
MAIN_PATH=./cmd/trackveil-api

# Go parameters
GOCMD=go
GOBUILD=$(GOCMD) build
GORUN=$(GOCMD) run
GOTEST=$(GOCMD) test
GOGET=$(GOCMD) get
GOMOD=$(GOCMD) mod
GOCLEAN=$(GOCMD) clean

# Build variables
VERSION?=$(shell git describe --tags --always --dirty 2>/dev/null || echo "dev")
BUILD_TIME=$(shell date -u '+%Y-%m-%d_%H:%M:%S')
LDFLAGS=-ldflags "-X main.Version=$(VERSION) -X main.BuildTime=$(BUILD_TIME)"

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-15s %s\n", $$1, $$2}'

deps: ## Download dependencies
	@echo "Downloading dependencies..."
	$(GOMOD) download
	$(GOMOD) tidy

install: deps ## Install dependencies (alias for deps)

build: ## Build the application binary
	@echo "Building $(APP_NAME)..."
	@mkdir -p $(BUILD_DIR)
	$(GOBUILD) $(LDFLAGS) -o $(BUILD_DIR)/$(APP_NAME) $(MAIN_PATH)
	@echo "Build complete: $(BUILD_DIR)/$(APP_NAME)"

build-linux: ## Build for Linux (for deployment)
	@echo "Building $(APP_NAME) for Linux..."
	@mkdir -p $(BUILD_DIR)
	CGO_ENABLED=0 GOOS=linux GOARCH=amd64 $(GOBUILD) $(LDFLAGS) -o $(BUILD_DIR)/$(APP_NAME)-linux $(MAIN_PATH)
	@echo "Build complete: $(BUILD_DIR)/$(APP_NAME)-linux"

run: ## Run the application (development)
	@echo "Starting $(APP_NAME)..."
	$(GORUN) $(MAIN_PATH)/main.go

dev: ## Run with auto-reload (requires air: go install github.com/cosmtrek/air@latest)
	@command -v air >/dev/null 2>&1 || { echo "air not found. Install with: go install github.com/cosmtrek/air@latest"; exit 1; }
	@echo "Starting $(APP_NAME) with auto-reload..."
	air

start: build ## Build and start the application in background
	@echo "Starting $(APP_NAME) in background..."
	@$(BUILD_DIR)/$(APP_NAME) > logs/api.log 2>&1 & echo $$! > $(BUILD_DIR)/$(APP_NAME).pid
	@echo "$(APP_NAME) started with PID $$(cat $(BUILD_DIR)/$(APP_NAME).pid)"
	@echo "Logs: tail -f logs/api.log"

stop: ## Stop the running application
	@if [ -f $(BUILD_DIR)/$(APP_NAME).pid ]; then \
		echo "Stopping $(APP_NAME)..."; \
		kill $$(cat $(BUILD_DIR)/$(APP_NAME).pid) 2>/dev/null || true; \
		rm -f $(BUILD_DIR)/$(APP_NAME).pid; \
		echo "$(APP_NAME) stopped"; \
	else \
		echo "No PID file found. Trying to kill by process name..."; \
		pkill -f "$(BUILD_DIR)/$(APP_NAME)" || echo "No running process found"; \
	fi

restart: stop start ## Restart the application

status: ## Check if the application is running
	@if [ -f $(BUILD_DIR)/$(APP_NAME).pid ]; then \
		PID=$$(cat $(BUILD_DIR)/$(APP_NAME).pid); \
		if ps -p $$PID > /dev/null 2>&1; then \
			echo "$(APP_NAME) is running (PID: $$PID)"; \
			curl -s http://localhost:8080/health | grep -q "healthy" && echo "Health check: OK" || echo "Health check: FAILED"; \
		else \
			echo "$(APP_NAME) is not running (stale PID file)"; \
			rm -f $(BUILD_DIR)/$(APP_NAME).pid; \
		fi \
	else \
		echo "$(APP_NAME) is not running"; \
	fi

test: ## Run tests
	@echo "Running tests..."
	$(GOTEST) -v -race ./...

test-coverage: ## Run tests with coverage
	@echo "Running tests with coverage..."
	$(GOTEST) -v -race -coverprofile=coverage.out ./...
	$(GOCMD) tool cover -html=coverage.out -o coverage.html
	@echo "Coverage report: coverage.html"

clean: ## Clean build artifacts
	@echo "Cleaning..."
	$(GOCLEAN)
	rm -rf $(BUILD_DIR)
	rm -f coverage.out coverage.html
	@echo "Clean complete"

logs: ## Tail the application logs
	@tail -f logs/api.log

deploy-build: clean build-linux ## Build for deployment to production
	@echo "Binary ready for deployment: $(BUILD_DIR)/$(APP_NAME)-linux"

check: ## Run go vet and go fmt
	@echo "Running go fmt..."
	@gofmt -l -w .
	@echo "Running go vet..."
	@$(GOCMD) vet ./...
	@echo "Code check complete"

.DEFAULT_GOAL := help

