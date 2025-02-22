#!/bin/bash

echo "Fixing permissions and ownership..."

# Ensure that the directories have write permissions
sudo chown -R www-data:www-data /var/www/hueswipe/uploads
sudo chmod -R 775 /var/www/hueswipe/uploads

# Ensure that the files inside uploads also have the right permissions
sudo chmod -R u+w /var/www/hueswipe/uploads

# Set proper permissions for the log files
sudo chmod 644 /var/www/hueswipe/upload_log.csv /var/www/hueswipe/upload_errors.log /var/www/hueswipe/unique_ips.log

echo "Permissions set correctly!"
