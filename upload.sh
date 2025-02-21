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

LOCAL_PATH="$(dirname "$0")/"

# Files and directories to sync
INCLUDE_LIST=(
    "index.html"
    "upload.php"
    "process.php"
    "stats.php"
    "styles.css"
    "script.js"
    "images/"
    "images/**"
    "js/"
    "js/**"
    "assets/"
    "assets/**"
)

# Build rsync include parameters
INCLUDE_ARGS=()
for ITEM in "${INCLUDE_LIST[@]}"; do
    INCLUDE_ARGS+=("--include=${ITEM}")
done

# Run rsync but prevent deletion of `uploads/` while syncing its contents
rsync -avz --progress  \
    "${INCLUDE_ARGS[@]}" \
    --exclude="*/" \
    --exclude="uploads/*" \
    "$LOCAL_PATH" "${SERVER_USER}@${SERVER_HOST}:${SERVER_PATH}"

echo "✅ Upload complete!"
