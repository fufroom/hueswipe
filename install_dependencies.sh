#!/bin/bash

# Get the latest version from GitHub API
LATEST_VERSION=$(curl -s https://api.github.com/repos/sass/dart-sass/releases/latest | grep '"tag_name":' | cut -d '"' -f 4)

if [[ -z "$LATEST_VERSION" ]]; then
    echo "Error: Unable to fetch the latest Dart Sass version."
    exit 1
fi

# Set architecture
ARCH="linux-x64" # Change to "linux-arm64" for ARM devices

# Define download URL
URL="https://github.com/sass/dart-sass/releases/download/$LATEST_VERSION/dart-sass-$LATEST_VERSION-$ARCH.tar.gz"

# Define install directory
INSTALL_DIR="/usr/local/bin/sass"

# Download and extract Sass
echo "Downloading Dart Sass $LATEST_VERSION for $ARCH..."
wget -q --show-progress "$URL" -O dart-sass.tar.gz

echo "Extracting Sass..."
sudo mkdir -p "$INSTALL_DIR"
sudo tar -xzf dart-sass.tar.gz -C "$INSTALL_DIR" --strip-components=1
rm dart-sass.tar.gz

# Create a symbolic link so Sass is accessible system-wide
sudo ln -sf "$INSTALL_DIR/sass" /usr/local/bin/sass

# Verify installation
echo "Sass installed successfully!"
sass --version
