#!/bin/env bash

CONNECTION=$1
DB=$2

# Create a temporary container script for the batch
TMP_SQL=$(mktemp)

# Initialise the file with the target database context
echo "USE \`$DB\`;" > "$TMP_SQL"

# Safely loop through the matching files using native globbing
for file in sp_delete*.sql; do
    # Check if files actually exist to handle empty directory edge cases safely
    [ -e "$file" ] || continue
    sed -i 's/\r$//' "$file"
    echo "\. $file" >> "$TMP_SQL"
    echo "" >> "$TMP_SQL"
done

echo "Registering deletion procedures into $DB over a single connection..."

# Stream all source directives through one single connection block
mysql $CONNECTION < "$TMP_SQL"

# Clean up the temporary execution script
rm -f "$TMP_SQL"

echo "All procedures successfully registered."