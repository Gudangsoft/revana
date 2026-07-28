#!/bin/bash

# Script untuk fix permission di server produksi
# Jalankan di server: bash fix-permission-server.sh

echo "Fixing permissions for storage and bootstrap/cache..."

# Get current directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Fix storage permissions
echo "Setting storage permissions..."
chmod -R 775 storage
find storage -type f -exec chmod 664 {} \;
find storage -type d -exec chmod 775 {} \;

# Fix bootstrap/cache permissions
echo "Setting bootstrap/cache permissions..."
chmod -R 775 bootstrap/cache
find bootstrap/cache -type f -exec chmod 664 {} \;

# Try to set ownership if running as root
if [ "$EUID" -eq 0 ]; then
    echo "Running as root, setting ownership..."
    
    # Detect web server user
    if id "www-data" &>/dev/null; then
        WEB_USER="www-data"
    elif id "apache" &>/dev/null; then
        WEB_USER="apache"
    elif id "nginx" &>/dev/null; then
        WEB_USER="nginx"
    else
        WEB_USER="www-data"
    fi
    
    echo "Setting owner to: $WEB_USER"
    chown -R $WEB_USER:$WEB_USER storage
    chown -R $WEB_USER:$WEB_USER bootstrap/cache
else
    echo "Not running as root, skipping ownership change"
    echo "If permission issues persist, run: sudo bash fix-permission-server.sh"
fi

# Ensure laravel.log exists and is writable
if [ ! -f "storage/logs/laravel.log" ]; then
    echo "Creating laravel.log..."
    touch storage/logs/laravel.log
    chmod 664 storage/logs/laravel.log
    
    if [ "$EUID" -eq 0 ]; then
        chown $WEB_USER:$WEB_USER storage/logs/laravel.log
    fi
fi

echo ""
echo "✓ Permissions fixed!"
echo ""
echo "Directory permissions:"
ls -la storage/
echo ""
echo "Log file permissions:"
ls -la storage/logs/
echo ""
echo "If error persists, try:"
echo "  sudo bash fix-permission-server.sh"
echo "  or"
echo "  chmod -R 777 storage"
echo ""
