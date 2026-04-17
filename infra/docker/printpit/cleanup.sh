#!/bin/bash
# PrintPit PDF cleanup script

# Get cleanup period from environment variable (default 6 hours)
HOURS=${CLEANUP_HOURS:-6}
MINUTES=$((HOURS * 60))

echo "$(date '+%Y-%m-%d %H:%M:%S'): Starting PDF cleanup (removing files older than ${HOURS} hours)..."

# Count files before cleanup
BEFORE_COUNT=$(find /var/spool/cups-pdf -name "*.pdf" -type f 2>/dev/null | wc -l)

# Remove PDF files older than specified hours
find /var/spool/cups-pdf -name "*.pdf" -type f -mmin +${MINUTES} -delete 2>/dev/null

# Count files after cleanup
AFTER_COUNT=$(find /var/spool/cups-pdf -name "*.pdf" -type f 2>/dev/null | wc -l)

# Calculate deleted files
DELETED=$((BEFORE_COUNT - AFTER_COUNT))

echo "$(date '+%Y-%m-%d %H:%M:%S'): Cleanup complete. Deleted ${DELETED} files. ${AFTER_COUNT} files remaining."