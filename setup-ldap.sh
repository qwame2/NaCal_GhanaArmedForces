#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

echo "=========================================="
echo "  NACOC LDAP Extension Setup for Ubuntu   "
echo "=========================================="

# Check if running as root
if [ "$EUID" -ne 0 ]; then
  echo "Error: Please run this script with sudo:"
  echo "sudo ./setup-ldap.sh"
  exit 1
fi

# Detect running PHP version
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in PATH."
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "Detected PHP Version: $PHP_VERSION"

echo "Updating package lists..."
apt-get update -y

echo "Installing php${PHP_VERSION}-ldap..."
apt-get install -y "php${PHP_VERSION}-ldap"

echo "Enabling LDAP extension..."
phpenmod ldap

# Restart services
echo "------------------------------------------"
echo "Select your web server / PHP processor to restart:"
echo "1) Nginx with PHP-FPM"
echo "2) Apache"
echo "3) Skip restart (restart manually later)"
echo "------------------------------------------"
read -p "Enter choice [1-3]: " choice

case $choice in
  1)
    echo "Restarting PHP-FPM..."
    # Attempt to restart FPM for the detected PHP version
    if systemctl list-units --type=service --state=running | grep -q "php${PHP_VERSION}-fpm"; then
      systemctl restart "php${PHP_VERSION}-fpm"
      echo "php${PHP_VERSION}-fpm restarted successfully."
    else
      # General fallback
      systemctl restart php-fpm || echo "Could not restart php-fpm automatically. Please restart it manually."
    fi
    ;;
  2)
    echo "Restarting Apache..."
    systemctl restart apache2
    echo "Apache restarted successfully."
    ;;
  *)
    echo "Skipping service restart. Make sure to restart your web server / PHP-FPM manually."
    ;;
esac

echo "=========================================="
echo "LDAP extension installation completed!"
echo "Verify using: php -m | grep ldap"
echo "=========================================="
