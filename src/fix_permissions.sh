#!/bin/bash

echo "🔧 Fixing permissions and ownership..."

PROJECT_PATH="/var/www/hueswipe.click"
LOG_FILES=("upload_log.csv" "upload_errors.log" "unique_ips.log" "total_uploads.log" "php_errors.log")
UPLOADS_DIR="$PROJECT_PATH/uploads"

# Ensure fufroom owns the main project, but www-data has group access
sudo chown -R fufroom:www-data "$PROJECT_PATH"
sudo chmod -R 775 "$PROJECT_PATH"

# Ensure logs and uploads are fully writable by www-data
sudo chown -R www-data:www-data "$UPLOADS_DIR"
sudo chmod -R 775 "$UPLOADS_DIR"

# Ensure existing files inside uploads/ are owned by www-data
sudo chown -R www-data:www-data "$UPLOADS_DIR"/*
sudo chmod -R 664 "$UPLOADS_DIR"/*

# Set group sticky bit so future files inherit correct permissions
sudo chmod g+s "$UPLOADS_DIR"

# Ensure all log files exist, are owned by www-data, and have correct permissions
for file in "${LOG_FILES[@]}"; do
    LOG_PATH="$PROJECT_PATH/$file"
    sudo touch "$LOG_PATH"
    sudo chown www-data:www-data "$LOG_PATH"
    sudo chmod 664 "$LOG_PATH"
done

# Ensure userDetails.php is readable by www-data
USER_DETAILS="$PROJECT_PATH/userDetails.php"
if [ -f "$USER_DETAILS" ]; then
    sudo chown fufroom:www-data "$USER_DETAILS"
    sudo chmod 644 "$USER_DETAILS"
fi

echo "✅ Permissions set correctly!"
