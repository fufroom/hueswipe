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
    "fix_permissions.sh"  # Include the fix_permissions script
)

# Build rsync include parameters
INCLUDE_ARGS=()
for ITEM in "${INCLUDE_LIST[@]}"; do
    INCLUDE_ARGS+=("--include=${ITEM}")
done

# Run rsync, exclude the .env, README.md, .sh files, and the uploads folder contents
rsync -avz --progress \
    "${INCLUDE_ARGS[@]}" \
    --exclude=".env" \
    --exclude="README.md" \
    --exclude="*.sh" \
    --exclude="uploads/*" \
    --exclude="*/" \
    "$LOCAL_PATH" "${SERVER_USER}@${SERVER_HOST}:${SERVER_PATH}"

# SSH into the server and run the fix_permissions.sh script
ssh ${SERVER_USER}@${SERVER_HOST} << EOF
  echo "Running fix_permissions.sh..."

  # Change to the directory where the files are uploaded
  cd ${SERVER_PATH}

  # Make sure fix_permissions.sh has execute permissions
  sudo chmod +x fix_permissions.sh

  # Run the fix_permissions.sh script
  sudo ./fix_permissions.sh

  echo "Permissions fixed on the server."
EOF

# Now proceed with file upload functionality, ensuring the file is uploaded and permissions are checked
echo "✅ Starting file upload process..."

# Files to upload
UPLOAD_URL="http://${SERVER_HOST}/upload.php"  # Replace with correct upload URL

# Use curl to upload a file for testing purposes (replace with real file for your use)
# Adjust for the correct path and content type
curl -X POST -F "file=@$LOCAL_PATH/testfile.png" "$UPLOAD_URL"

echo "✅ Upload complete!"
