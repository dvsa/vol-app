#!/usr/bin/env bash

if [ -z "$1" ] || [ -z "$2" ]; then
    echo "ERROR: Missing parameters."
    echo "Usage: $(basename "$0") <connection_string> <database_name>"
    exit 1
fi

CONNECTION=$1
DB=$2
PROC="sp_validate_NI_Extract"

mysql $CONNECTION -NB -e "CALL \`$DB\`.\`$PROC\`;"