#!/bin/sh

if ! pgrep -f "queue:scheduler" > /dev/null; then
    echo "ERROR: Queue scheduler process not running"
    exit 1
fi

if [ ! -f /tmp/scheduler-health.json ]; then
    echo "ERROR: Health check file missing"
    exit 1
fi

file_age=$(($(date +%s) - $(stat -c %Y /tmp/scheduler-health.json)))
if [ $file_age -gt 120 ]; then
    echo "ERROR: Health check file too old ($file_age seconds)"
    exit 1
fi

if ! grep -q '"status":"healthy"' /tmp/scheduler-health.json; then
    echo "ERROR: Health check reports unhealthy status"
    exit 1
fi

echo "OK: All health checks passed"
exit 0
