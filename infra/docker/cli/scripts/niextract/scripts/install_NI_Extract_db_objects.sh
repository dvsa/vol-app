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

run_sql() {
    local file="$1"
    local filename
    filename=$(basename "$file")
    echo "Running $file..."
    echo "File size: $(wc -c < "$file") bytes, lines: $(wc -l < "$file") lines"
    aws s3 cp "$file" "s3://devapp-shd-pri-olcsci-build-s3/anondata/debug/$filename" 2>&1 || echo "S3 upload failed for $filename"
    grep -v '^DELIMITER' "$file" | mysql $CONNECTION "$DB" --delimiter='$$' 2>&1 || { echo "ERROR: Failed to execute $file"; exit 1; }
}

run_sql "$SCRIPT_DIR/NI_Extract_table.sql"
run_sql "$SCRIPT_DIR/sp_NI_Extract_save_table_counts.sql"
run_sql "$SCRIPT_DIR/sp_NI_Extract_update_table_counts.sql"
run_sql "$SCRIPT_DIR/sp_drop_constraints.sql"
run_sql "$SCRIPT_DIR/sp_add_NI_Extract_constraints.sql"
run_sql "$SCRIPT_DIR/sp_add_original_constraints.sql"
run_sql "$SCRIPT_DIR/sp_drop_indices.sql"
run_sql "$SCRIPT_DIR/sp_add_indices.sql"
run_sql "$SCRIPT_DIR/sp_drop_hist_tables.sql"
run_sql "$SCRIPT_DIR/sp_drop_triggers.sql"
run_sql "$SCRIPT_DIR/sp_NI_Extract.sql"
run_sql "$SCRIPT_DIR/sp_validate_NI_Extract.sql"

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