#!/bin/bash

# OMPay Backend Docker Build and Push Script with Versioning
# Usage: ./build-docker.sh [version] [registry]
# Example: ./build-docker.sh v1.0.0 myregistry.com
# If no version provided, uses git tag or commit hash

set -e

# Default values
REGISTRY=${2:-"ompay"}
IMAGE_NAME="ompay-backend"
DEFAULT_VERSION="latest"

# Get version from argument, git tag, VERSION file, or commit hash
if [ -n "$1" ]; then
    VERSION=$1
elif git describe --tags --exact-match >/dev/null 2>&1; then
    VERSION=$(git describe --tags --exact-match)
elif [ -f "VERSION" ]; then
    VERSION=$(cat VERSION)
elif git rev-parse --short HEAD >/dev/null 2>&1; then
    VERSION="dev-$(git rev-parse --short HEAD)"
else
    VERSION=$DEFAULT_VERSION
fi

# Clean version (remove 'v' prefix if present and sanitize)
VERSION=$(echo "$VERSION" | sed 's/^v//' | sed 's/[^a-zA-Z0-9._-]/-/g')

echo "Building OMPay Backend Docker Image"
echo "===================================="
echo "Registry: $REGISTRY"
echo "Image: $IMAGE_NAME"
echo "Version: $VERSION"
echo "Full tag: $REGISTRY/$IMAGE_NAME:$VERSION"
echo ""

# Build the Docker image
echo "Building Docker image..."
docker build -t "$REGISTRY/$IMAGE_NAME:$VERSION" -t "$REGISTRY/$IMAGE_NAME:latest" .

# Show image details
echo ""
echo "Image built successfully!"
docker images "$REGISTRY/$IMAGE_NAME"

# Ask for confirmation before pushing
echo ""
read -p "Do you want to push the image to the registry? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Pushing image to registry..."

    # Push version tag
    echo "Pushing $REGISTRY/$IMAGE_NAME:$VERSION..."
    docker push "$REGISTRY/$IMAGE_NAME:$VERSION"

    # Push latest tag if this is not a development version
    if [[ "$VERSION" != dev-* ]]; then
        echo "Pushing $REGISTRY/$IMAGE_NAME:latest..."
        docker push "$REGISTRY/$IMAGE_NAME:latest"
    fi

    echo ""
    echo "Image pushed successfully!"
    echo "You can now deploy using: $REGISTRY/$IMAGE_NAME:$VERSION"
else
    echo "Image not pushed. You can push manually later with:"
    echo "docker push $REGISTRY/$IMAGE_NAME:$VERSION"
    if [[ "$VERSION" != dev-* ]]; then
        echo "docker push $REGISTRY/$IMAGE_NAME:latest"
    fi
fi

echo ""
echo "Build complete!"