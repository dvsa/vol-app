#!/bin/env bash

CONNECTION=$1
DB=$2

# NI_Extract table

mysql $CONNECTION -e "use $DB;\. NI_Extract_table.sql"

# NI_Extract table maintenance procs

mysql $CONNECTION -e "use $DB;\. sp_NI_Extract_save_table_counts.sql"
mysql $CONNECTION -e "use $DB;\. sp_NI_Extract_update_table_counts.sql"

# constraints

mysql $CONNECTION -e "use $DB;\. sp_drop_constraints.sql"
mysql $CONNECTION -e "use $DB;\. sp_add_NI_Extract_constraints.sql"
mysql $CONNECTION -e "use $DB;\. sp_add_original_constraints.sql"

# indices
mysql $CONNECTION -e "use $DB;\. sp_drop_indices.sql"
mysql $CONNECTION -e "use $DB;\. sp_add_indices.sql"

# drop history tables

mysql $CONNECTION -e "use $DB;\. sp_drop_hist_tables.sql"

# drop triggers

mysql $CONNECTION -e "use $DB;\. sp_drop_triggers.sql"

# main NI Extract procedure

mysql $CONNECTION -e "use $DB;\. sp_NI_Extract.sql"

# validate NI Extract procedure

mysql $CONNECTION -e "use $DB;\. sp_validate_NI_Extract.sql"

# create delete procedures

cd delete_procs

./create_delete_procs.sh "$CONNECTION" $DB

cd ..
