#!/bin/bash
set -e

echo "Starting PrintPit virtual print server..."

# Create lpadmin group if it doesn't exist (Alpine specific)
addgroup -S lpadmin 2>/dev/null || true

# Create admin user if specified
if [ -n "$CUPS_ADMIN_USER" ] && [ -n "$CUPS_ADMIN_PASSWORD" ]; then
    echo "Creating CUPS admin user: $CUPS_ADMIN_USER"
    adduser -S -G lpadmin -h /var/spool/cups -s /bin/false "$CUPS_ADMIN_USER" 2>/dev/null || true
    echo "$CUPS_ADMIN_USER:$CUPS_ADMIN_PASSWORD" | chpasswd
fi

# Setup cron with configurable cleanup period
HOURS=${CLEANUP_HOURS:-6}
echo "Setting up cleanup schedule: every ${HOURS} hours"
echo "0 */${HOURS} * * * /usr/local/bin/cleanup.sh" | crontab -

# Start CUPS temporarily for configuration
echo "Starting CUPS for initial configuration..."
cupsd

# Wait for CUPS to be ready
echo "Waiting for CUPS to be ready..."
for i in {1..30}; do
    if curl -s http://localhost:631/ > /dev/null; then
        echo "CUPS is ready"
        break
    fi
    sleep 1
done

# Add virtual PDF printer
echo "Adding PrintPit virtual printer..."
# CUPS-PDF PPD file location in Alpine
PPD_FILE="/usr/share/ppd/cups-pdf/cups-pdf.ppd"

if [ -f "$PPD_FILE" ]; then
    echo "Using PPD file: $PPD_FILE"
    lpadmin -p PrintPit -E -v cups-pdf:/ -P "$PPD_FILE" \
        -D "PrintPit Virtual PDF Printer" \
        -L "VOL Test Environment" \
        -o printer-is-shared=true
else
    echo "Error: CUPS-PDF PPD file not found at $PPD_FILE"
    exit 1
fi

# Set as default printer
echo "Setting PrintPit as default printer..."
lpadmin -d PrintPit

# Configure CUPS to accept jobs from any host
echo "Configuring CUPS for remote access..."
# Note: Remote access is already configured in cupsd.conf
# cupsctl requires authentication and isn't needed with our cupsd.conf setup

# Enable the printer
echo "Enabling PrintPit printer..."
cupsenable PrintPit
cupsaccept PrintPit

# Show printer status
echo "Printer status:"
lpstat -p -d

# Stop CUPS before supervisor starts it
echo "Stopping temporary CUPS instance..."
killall cupsd 2>/dev/null || true
sleep 1

# Start crond (Alpine's cron daemon)
echo "Starting cron daemon..."
crond

# Start supervisor (which will manage CUPS and FileBrowser)
echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf