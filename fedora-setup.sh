#!/bin/bash

# QA Evaluation App - Fedora Development Setup
# Quick setup script for Fedora with httpd

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=== QA Evaluation App - Fedora Dev Setup ===${NC}"

# Copy virtual host configuration
echo -e "${YELLOW}Setting up virtual host...${NC}"
sudo cp vhost-fedora-dev.conf /etc/httpd/conf.d/qa-eval.conf

# Add to hosts file
if ! grep -q "qa-eval.local" /etc/hosts; then
    echo -e "${YELLOW}Adding qa-eval.local to hosts file...${NC}"
    echo "127.0.0.1    qa-eval.local www.qa-eval.local" | sudo tee -a /etc/hosts
fi

# Set SELinux context (Fedora specific)
echo -e "${YELLOW}Setting SELinux context...${NC}"
sudo setsebool -P httpd_can_network_connect 1
sudo semanage fcontext -a -t httpd_exec_t "/var/www/html/eval(/.*)?" 2>/dev/null || true
sudo restorecon -R /var/www/html/eval

# Create uploads directory and set permissions
echo -e "${YELLOW}Setting up uploads directory...${NC}"
sudo mkdir -p /var/www/html/eval/uploads/{audio,transcripts}
sudo chown -R apache:apache /var/www/html/eval/uploads
sudo chmod -R 775 /var/www/html/eval/uploads

# Restart httpd
echo -e "${YELLOW}Restarting httpd...${NC}"
sudo systemctl restart httpd

echo -e "${GREEN}Setup complete!${NC}"
echo -e "${GREEN}Access your app at: http://qa-eval.local${NC}"
echo
echo "Next steps:"
echo "1. Update database config in app/Config/config.php"
echo "2. Run: composer install"
echo "3. Import database from sql/ directory"
