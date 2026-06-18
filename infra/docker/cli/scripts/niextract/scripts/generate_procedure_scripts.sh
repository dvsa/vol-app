#!/bin/env bash

CONNECTION=$1
DB=$2

# Configuration: Maximum parallel database connections to protect Aurora ACU scaling
MAX_PARALLEL_JOBS=3

# Mappings of generator scripts to output files
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

# Loop through tasks and execute with parallel throttling
for task in "${TASKS[@]}"; do
    # Split the mapping key/value
    GEN_FILE="${task%%:*}"
    OUT_FILE="${task#*:}"

    # Execute mysql generation in the background
    (
        mysql $CONNECTION -e "USE $DB;\. $GEN_FILE" | tr -d '/-/' > "$OUT_FILE"
    ) &

    # Throttling gate: If we hit the max jobs, wait for at least one to finish
    while [ "$(jobs -r | wc -l)" -ge "$MAX_PARALLEL_JOBS" ]; do
        sleep 0.1
    done
done

# Wait for all remaining background jobs to finish cleanly
wait

echo "All SP maintenance files compiled successfully inside AWS Batch."