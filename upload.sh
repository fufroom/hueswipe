#!/bin/bash

# Load environment variables from .env if it exists
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
else
    echo "⚠️  WARNING: .env file not found!"
    echo "   Create a .env file with SERVER_USER, SERVER_HOST, and SERVER_PATH."
    echo "   Using default placeholders instead."
    SERVER_USER="your_user"
    SERVER_HOST="your_server"
    SERVER_PATH="/var/www/your_project/"
fi

REPO_ROOT="$(dirname "$0")"
OUTPUT_DIR="$REPO_ROOT/src"

if [ ! -d "$OUTPUT_DIR" ]; then
    echo "❌ ERROR: 'src/' directory not found. Run build.sh first!"
    exit 1
fi

echo "🚀 Uploading files from output/ to $SERVER_USER@$SERVER_HOST:$SERVER_PATH..."

# Sync files to the server
rsync -avz --progress \
    --exclude=".env" \
    --exclude="README.md" \
    "$OUTPUT_DIR/" "${SERVER_USER}@${SERVER_HOST}:${SERVER_PATH}"

echo "✅ Upload complete! Remember to run fix_permissions.sh manually on the server if needed."

# Test file upload using `curl`
UPLOAD_URL="http://${SERVER_HOST}/upload.php"
TEST_FILE="$OUTPUT_DIR/testfile.png"

if [ -f "$TEST_FILE" ]; then
    echo "📤 Uploading test file..."
    curl -X POST -F "file=@$TEST_FILE" "$UPLOAD_URL"
    echo "✅ Test upload complete!"
else
    echo "⚠️  No test file found, skipping test upload."
fi

echo "✅ Upload process finished!"
