#!/usr/bin/env bash

if [ -z "$1" ] || [ -z "$2" ]; then
    echo "ERROR: Missing parameters."
    echo "Usage: $(basename "$0") <connection_string> <database_name>"
    exit 1
fi

CONNECTION=$1
DB=$2

mysql $CONNECTION "$DB" <<EOF
CALL sp_drop_constraints;
CALL sp_drop_indices;
CALL sp_add_NI_Extract_constraints;
CALL sp_drop_triggers;
CALL sp_drop_hist_tables;
EOF