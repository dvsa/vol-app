#!/usr/bin/env bash

if [ -z "$1" ] || [ -z "$2" ]; then
    echo "ERROR: Missing parameters."
    echo "Usage: $(basename "$0") <connection_string> <database_name>"
    exit 1
fi

CONNECTION=$1
DB=$2

MAX_PARALLEL_JOBS=3

declare -a TASKS=(
    "generate-sp_drop_constraints.sql:sp_drop_constraints.sql"
    "generate-sp_add_original_constraints.sql:sp_add_original_constraints.sql"
    "generate-sp_add_NI_Extract_constraints.sql:sp_add_NI_Extract_constraints.sql"
    "generate-sp_NI_Extract_save_table_counts.sql:sp_NI_Extract_save_table_counts.sql"
    "generate-sp_NI_Extract_update_table_counts.sql:sp_NI_Extract_update_table_counts.sql"
    "generate-sp_drop_triggers.sql:sp_drop_triggers.sql"
    "generate-sp_drop_hist_tables.sql:sp_drop_hist_tables.sql"
    "generate-sp_drop_indices.sql:sp_drop_indices.sql"
    "generate-sp_add_indices.sql:sp_add_indices.sql"
)

echo "Starting optimized metadata compilation against $DB..."

declare -a CURRENT_PIDS=()

for task in "${TASKS[@]}"; do
    GEN_FILE="${task%%:*}"
    OUT_FILE="${task#*:}"

    while [ "${#CURRENT_PIDS[@]}" -ge "$MAX_PARALLEL_JOBS" ]; do
        TEMP_PIDS=()
        for pid in "${CURRENT_PIDS[@]}"; do
            if kill -0 "$pid" 2>/dev/null; then
                TEMP_PIDS+=("$pid")
            fi
        done
        CURRENT_PIDS=("${TEMP_PIDS[@]}")
        
        if [ "${#CURRENT_PIDS[@]}" -ge "$MAX_PARALLEL_JOBS" ]; then
            sleep 0.5
        fi
    done

    mysql $CONNECTION -NB "$DB" < "$GEN_FILE" > "$OUT_FILE" &
    CURRENT_PIDS+=($!)
done

wait

echo "All SP maintenance files compiled successfully inside AWS Batch."