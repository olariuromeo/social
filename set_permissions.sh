#!/bin/bash
# sudo set_permissions.sh
sudo chown -R www-data:www-data .
sudo find ./ -type d -exec chmod 755 {} \;

# Set permissions for the specified directories
sudo find ./storage -type d -exec chmod 775 {} \;
sudo find ./tmp -type d -exec chmod 775 {} \;

sudo chmod 775 ./cache
sudo chmod 775 ./cache_public
sudo chmod 775 ./logs
sudo chmod 775 ./periodic
sudo chmod 775 ./storage
sudo chmod 775 ./tmp

# Set permissions for ./inc  directories only before instalkation
# sudo chmod 777 ./inc
sudo chmod 755 ./inc

# Guthub Folders
sudo chown -R root:root ./.github
sudo chown -R root:root ./.git
sudo chmod -R 755 ./.github
sudo chmod -R 755 ./.git

# Set permissions for the all files
sudo find ./ -type f -exec chmod 644 {} \;

# Set execute permissions for ffmpeg.exe
sudo chmod +x ./plugins/ffmpeg/ffmpeg.exe
sudo chmod +x ./periodic/cron.php
sudo chmod +x ./image_transcoder.php

# Github Files
sudo chown root:root ./.gitignore

# Set permissions for my files
sudo chown root:root ./docker-compose.yaml
sudo chown root:root ./.env
sudo chown root:root ./INSTALL.md
sudo chown root:root ./set_permissions.sh

# Set execute permissions for files
sudo chmod +x ./docker-compose.yaml
sudo chmod +x ./set_permissions.sh

