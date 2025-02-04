#!/bin/bash

# Start Apache in the background
apache2-foreground &

# Start PHP built-in server to listen on all IPs (0.0.0.0) on port 8001
php -S 0.0.0.0:8001 -t /var/www/html &

# Wait forever to keep the container running
tail -f /dev/null

docker compose up -d --build --pull always
