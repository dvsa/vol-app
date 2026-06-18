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

mysql $CONNECTION "$DB" -vv <<EOF
DROP TABLE IF EXISTS NI_Extract;
DROP PROCEDURE IF EXISTS sp_NI_Extract_save_table_counts;
DROP PROCEDURE IF EXISTS sp_NI_Extract_update_table_counts;
DROP PROCEDURE IF EXISTS sp_drop_constraints;
DROP PROCEDURE IF EXISTS sp_add_NI_Extract_constraints;
DROP PROCEDURE IF EXISTS sp_add_original_constraints;
DROP PROCEDURE IF EXISTS sp_drop_indices;
DROP PROCEDURE IF EXISTS sp_add_indices;
DROP PROCEDURE IF EXISTS sp_drop_hist_tables;
DROP PROCEDURE IF EXISTS sp_drop_triggers;
DROP PROCEDURE IF EXISTS sp_NI_Extract;
DROP TABLE IF EXISTS DATABASECHANGELOG;
DROP TABLE IF EXISTS DATABASECHANGELOGLOCK;
DROP PROCEDURE IF EXISTS sp_validate_NI_Extract;
EOF

if [ -d "delete_procs" ]; then
    cd delete_procs || exit 1
    if [ -x "./drop_delete_procs.sh" ]; then
        ./drop_delete_procs.sh "$CONNECTION" "$DB"
    else
        bash ./drop_delete_procs.sh "$CONNECTION" "$DB"
    fi
    cd ..
else
    echo "WARNING: delete_procs directory not found relative to script location."
fi