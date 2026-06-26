#!/usr/bin/env bash

if [ -z "$1" ] || [ -z "$2" ]; then
    echo "ERROR: Missing parameters."
    echo "Usage: $(basename "$0") <connection_string> <database_name>"
    exit 1
fi

CONNECTION=$1
DB=$2

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR" || exit 1

mysql $CONNECTION "$DB" < NI_Extract_table.sql

mysql $CONNECTION "$DB" < sp_NI_Extract_save_table_counts.sql
mysql $CONNECTION "$DB" < sp_NI_Extract_update_table_counts.sql

mysql $CONNECTION "$DB" < sp_drop_constraints.sql
mysql $CONNECTION "$DB" < sp_add_NI_Extract_constraints.sql
mysql $CONNECTION "$DB" < sp_add_original_constraints.sql

mysql $CONNECTION "$DB" < sp_drop_indices.sql
mysql $CONNECTION "$DB" < sp_add_indices.sql

mysql $CONNECTION "$DB" < sp_drop_hist_tables.sql

mysql $CONNECTION "$DB" < sp_drop_triggers.sql

mysql $CONNECTION "$DB" < sp_NI_Extract.sql

mysql $CONNECTION "$DB" < sp_validate_NI_Extract.sql

if [ -d "delete_procs" ]; then
    cd delete_procs || exit 1
    if [ -x "./create_delete_procs.sh" ]; then
        ./create_delete_procs.sh "$CONNECTION" "$DB"
    else
        bash ./create_delete_procs.sh "$CONNECTION" "$DB"
    fi
    cd ..
else
    echo "WARNING: delete_procs directory not found relative to script location."
fi