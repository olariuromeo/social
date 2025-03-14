# Installation Guide for the Application using Docker and Composer

This guide outlines the steps required to install and run the application using Docker and Composer.

## 1. Cloning the Repository

Begin by cloning the application repository using the following command:

```bash
gh repo clone unacms/una una

```

Navigate to the cloned repository:

```bash

cd una

```

Checkout the desired branch (e.g., 14.0.0-B1):

```bash

git checkout 14.0.0-RC4

```

## 2. Installing Composer

Begin by downloading the Docker image for Composer using the following command:

```bash
docker pull composer/composer

```

## 3. Installing PHP Dependencies using Composer
After downloading the Docker image for Composer, run the following command to install the PHP dependencies of the application:

```bash

docker run --rm -it -v "$(pwd):/app" composer/composer:2.2.9 install

```

This command will run Composer in a Docker container, and the PHP dependencies will be installed in the current directory.


## 5. Setting Permissions
Before running the Docker containers, ensure that proper permissions are set for directories and files. Follow the instructions below to set permissions:

Create a file:

```bash

vim set_permissions.sh

```

Copy the instructions below into the file:

```sh

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

```

Make sure you are in the root directory of the application.

Make the set_permissions.sh script executable using the command:

```bash

chmod +x set_permissions.sh

```

Run the set_permissions.sh script using the command:

```bash

sudo ./set_permissions.sh

```

This will automatically apply the appropriate permissions for all directories and files specified in the script.

## 6 Add your .env variable

Setup your variable in .env file

## 7. Running Docker Containers using docker-compose

After setting the permissions, build the Docker images for the necessary services:
 
```bash

docker-compose build cron

```

```bash

docker-compose build php

```

To run the application, use docker-compose. Make sure you have a properly configured docker-compose.yml file for your application.

```bash

docker-compose up

```

This command will start the Docker containers according to the specifications in the docker-compose.yml file, allowing you to run the application.

or

```bash

docker cvompose up -d --build

```
This command will nuild the images and start the Docker containers according to the specifications in the docker-compose.yaml file, allowing you to run the applications.

## 8. Accessing the Application
Open your web browser and navigate to www.exemple.com to access the application. Follow the on-screen instructions to complete the installation process.

This guide details the steps to download Composer using Docker, install PHP dependencies using Composer, and run the application using Docker Compose. The `INSTALL.md` file should serve as a helpful guide for someone looking to install and run your application using Docker and Composer.



