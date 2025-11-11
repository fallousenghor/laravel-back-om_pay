.PHONY: help build push deploy clean test lint

# Default registry and image settings
REGISTRY ?= ompay
IMAGE_NAME ?= ompay-backend
VERSION ?= $(shell git describe --tags --exact-match 2>/dev/null || git rev-parse --short HEAD 2>/dev/null || echo "latest")

# Colors for output
RED=\033[0;31m
GREEN=\033[0;32m
YELLOW=\033[1;33m
BLUE=\033[0;34m
NC=\033[0m # No Color

help: ## Show this help message
	@echo "$(BLUE)OMPay Backend Docker Management$(NC)"
	@echo ""
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-15s$(NC) %s\n", $$1, $$2}'

build: ## Build Docker image with versioning
	@echo "$(BLUE)Building OMPay Backend Docker Image$(NC)"
	@echo "Version: $(VERSION)"
	@echo "Registry: $(REGISTRY)"
	@echo "Image: $(IMAGE_NAME)"
	@./build-docker.sh $(VERSION) $(REGISTRY)

push: ## Push Docker image to registry
	@echo "$(YELLOW)Pushing OMPay Backend Docker Image$(NC)"
	@docker push $(REGISTRY)/$(IMAGE_NAME):$(VERSION)
	@if [ "$(VERSION)" != "latest" ] && [ "$(VERSION)" != dev-* ]; then \
		echo "Also pushing latest tag..."; \
		docker push $(REGISTRY)/$(IMAGE_NAME):latest; \
	fi

deploy: build push ## Build and push Docker image

test: ## Run tests
	@echo "$(BLUE)Running tests...$(NC)"
	@docker-compose exec app php artisan test

lint: ## Run code linting
	@echo "$(BLUE)Running code linting...$(NC)"
	@docker-compose exec app ./vendor/bin/phpcs

clean: ## Clean up Docker images and containers
	@echo "$(RED)Cleaning up Docker resources...$(NC)"
	@docker-compose down -v
	@docker system prune -f
	@docker rmi $(REGISTRY)/$(IMAGE_NAME):$(VERSION) 2>/dev/null || true

logs: ## Show application logs
	@docker-compose logs -f app

shell: ## Access container shell
	@docker-compose exec app bash

restart: ## Restart the application
	@docker-compose restart app

status: ## Show container status
	@docker-compose ps

version: ## Show current version
	@echo "$(GREEN)Current version: $(VERSION)$(NC)"

# Development commands
dev-build: ## Build for development
	@echo "$(BLUE)Building development image...$(NC)"
	@docker-compose build --no-cache

dev-up: ## Start development environment
	@echo "$(BLUE)Starting development environment...$(NC)"
	@docker-compose up -d

dev-down: ## Stop development environment
	@echo "$(RED)Stopping development environment...$(NC)"
	@docker-compose down

# Production commands
prod-build: ## Build for production
	@echo "$(BLUE)Building production image...$(NC)"
	@docker build -t $(REGISTRY)/$(IMAGE_NAME):$(VERSION) -f Dockerfile .

prod-deploy: prod-build push ## Build and deploy to production

# Utility commands
docker-login: ## Login to Docker registry
	@echo "$(YELLOW)Logging into Docker registry...$(NC)"
	@docker login $(REGISTRY)

tag: ## Create a git tag
	@echo "Current tags:"
	@git tag -l | tail -5
	@echo ""
	@read -p "Enter new version tag (e.g., v1.0.0): " TAG; \
	git tag $$TAG; \
	git push origin $$TAG; \
	echo "$(GREEN)Tag $$TAG created and pushed!$(NC)"