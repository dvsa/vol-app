#!/bin/env bash

CONNECTION=$1
DB=$2

# Create a temporary container script for the batch
TMP_SQL=$(mktemp)

# Initialise the file with the target database context
echo "USE $DB;" > "$TMP_SQL"

# Safely loop through the matching files using native globbing
for file in sp_delete*.sql; do
    # Check if files actually exist to handle empty directory edge cases safely
    [ -e "$file" ] || continue
    
    # Strip the .sql extension using native Bash parameter expansion
    procedure="${file%.sql}"
    
    # Append the drop directive to our batch file
    echo "DROP PROCEDURE IF EXISTS $procedure;" >> "$TMP_SQL"
done

echo "Dropping deletion procedures from $DB over a single connection..."

# Execute all drops sequentially over one single connection block
mysql $CONNECTION < "$TMP_SQL"

# Clean up the temporary execution script
rm -f "$TMP_SQL"

echo "All specified procedures dropped."