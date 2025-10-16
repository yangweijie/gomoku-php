#!/bin/bash

# PHP Gomoku Build Script for Unix-like systems (macOS, Linux)

set -e  # Exit immediately if a command exits with a non-zero status

echo "Building PHP Gomoku PHAR package..."

# Check if Box is installed
if ! command -v box &> /dev/null
then
    echo "Box is not installed. Installing Box..."
    composer require --dev humbug/box
fi

# Check if composer.json exists
if [ ! -f "composer.json" ]; then
    echo "Error: composer.json not found"
    exit 1
fi

# Install or update dependencies
echo "Installing/Updating dependencies..."
composer install --no-dev --optimize-autoloader

# Run Box to build the PHAR
echo "Creating PHAR package..."
vendor/bin/box compile

# Check if the PHAR was created successfully
if [ -f "gomoku.phar" ]; then
    echo "Build successful! PHAR package created: gomoku.phar"
    
    # Make the PHAR executable
    chmod +x gomoku.phar
    
    echo "You can now run the application with: ./gomoku.phar"
else
    echo "Error: Failed to create gomoku.phar"
    exit 1
fi