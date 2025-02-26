#!/bin/bash

# Load environment variables
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
else
    echo "⚠️  WARNING: .env file not found!"
    SERVER_USER="your_user"
    SERVER_HOST="your_server"
    SERVER_PATH="/var/www/your_project/"
fi

REPO_ROOT="$(dirname "$0")"
SRC_DIR="$REPO_ROOT/src"

if [ ! -d "$SRC_DIR" ]; then
    echo "❌ ERROR: 'src/' directory not found. Run build.sh first!"
    exit 1
fi

echo "🚀 Uploading all files from src/ to $SERVER_USER@$SERVER_HOST:$SERVER_PATH..."

rsync -avz --progress "$SRC_DIR/" "${SERVER_USER}@${SERVER_HOST}:${SERVER_PATH}"

echo "✅ Upload complete!"

# Test if UserDetails.php exists on the server
echo "🔍 Verifying UserDetails.php on the server..."
ssh "${SERVER_USER}@${SERVER_HOST}" "ls -lah ${SERVER_PATH} | grep UserDetails.php"

echo "✅ Verification complete!"
