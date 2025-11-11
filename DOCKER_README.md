# OMPay Backend - Docker Build and Deployment

This document explains how to build, version, and deploy the OMPay backend application using Docker.

## Prerequisites

- Docker installed and running
- Docker Compose (for local development)
- Git (for version tagging)
- Access to a Docker registry (Docker Hub, AWS ECR, etc.)

## Quick Start

### Using Make (Recommended)

```bash
# Build and push with automatic versioning
make deploy

# Build only
make build

# Push only
make push

# View available commands
make help
```

### Using Build Script

```bash
# Build with automatic versioning
./build-docker.sh

# Build with specific version
./build-docker.sh v1.0.0

# Build with custom registry
./build-docker.sh v1.0.0 myregistry.com
```

## Versioning

The build system supports multiple versioning strategies:

1. **Git Tags**: If you have git tags, they will be used automatically
   ```bash
   git tag v1.0.0
   git push origin v1.0.0
   make build  # Will use v1.0.0
   ```

2. **VERSION File**: Create/update a VERSION file with your desired version
   ```bash
   echo "1.0.0" > VERSION
   make build  # Will use 1.0.0
   ```

3. **Command Line**: Specify version when building
   ```bash
   make build VERSION=v1.0.0
   ./build-docker.sh v1.0.0
   ```

4. **Git Commit Hash**: Falls back to short commit hash with "dev-" prefix
   ```bash
   make build  # Will use dev-a1b2c3d
   ```

## Image Naming

Images are tagged with the following pattern:
- `ompay/ompay-backend:{version}`
- `ompay/ompay-backend:latest` (for non-development versions)

## Development Workflow

### Local Development

```bash
# Start development environment
make dev-up

# View logs
make logs

# Access container shell
make shell

# Run tests
make test

# Stop development
make dev-down
```

### Production Deployment

```bash
# Build and deploy
make prod-deploy

# Or step by step
make prod-build
make push
```

## Configuration

### Environment Variables

The Docker image expects the following environment variables:

```bash
APP_ENV=production
APP_KEY=your-app-key
DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password
DB_SSLMODE=require
```

### Custom Registry

To use a custom Docker registry:

```bash
# Login to registry
make docker-login REGISTRY=myregistry.com

# Build and push
make deploy REGISTRY=myregistry.com
```

## Available Make Commands

| Command | Description |
|---------|-------------|
| `make help` | Show all available commands |
| `make build` | Build Docker image with versioning |
| `make push` | Push image to registry |
| `make deploy` | Build and push image |
| `make clean` | Clean up Docker resources |
| `make test` | Run application tests |
| `make lint` | Run code linting |
| `make logs` | Show application logs |
| `make shell` | Access container shell |
| `make restart` | Restart application |
| `make status` | Show container status |
| `make version` | Show current version |
| `make tag` | Create and push git tag |

## Development Commands

| Command | Description |
|---------|-------------|
| `make dev-build` | Build development image |
| `make dev-up` | Start development environment |
| `make dev-down` | Stop development environment |

## Production Commands

| Command | Description |
|---------|-------------|
| `make prod-build` | Build production image |
| `make prod-deploy` | Build and deploy to production |

## Troubleshooting

### Build Issues

1. **Permission denied**: Make sure the build script is executable
   ```bash
   chmod +x build-docker.sh
   ```

2. **Registry login failed**: Check your credentials
   ```bash
   docker login your-registry.com
   ```

3. **Build context too large**: Check `.dockerignore` file

### Runtime Issues

1. **Database connection failed**: Verify environment variables
2. **Permission issues**: Check file permissions in container
3. **Health check failed**: Ensure the application starts correctly

## CI/CD Integration

For automated builds, you can use the build script in your CI pipeline:

```yaml
# GitHub Actions example
- name: Build and Push Docker Image
  run: ./build-docker.sh ${{ github.ref_name }}
```

## File Structure

```
ompay-backend/
├── Dockerfile              # Main Docker build file
├── docker-compose.yml      # Local development setup
├── build-docker.sh         # Build and push script
├── Makefile               # Make commands
├── VERSION                # Version tracking file
├── .dockerignore          # Docker ignore rules
└── DOCKER_README.md       # This documentation