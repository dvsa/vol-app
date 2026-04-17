#!/bin/env bash

CONNECTION=$1
DB=$2

# NI_Extract table

mysql $CONNECTION -vve "use $DB;DROP TABLE IF EXISTS NI_Extract;"

# NI_Extract table maintenance procs

mysql $CONNECTION -vve "use $DB;DROP PROCEDURE IF EXISTS sp_NI_Extract_save_table_counts;"
mysql $CONNECTION -vve "use $DB;DROP PROCEDURE IF EXISTS sp_NI_Extract_update_table_counts;"

# constraints

mysql $CONNECTION -vve "use $DB;DROP PROCEDURE IF EXISTS sp_drop_constraints;"
mysql $CONNECTION -vve "use $DB;DROP PROCEDURE IF EXISTS sp_add_NI_Extract_constraints;"
mysql $CONNECTION -vve "use $DB;DROP PROCEDURE IF EXISTS sp_add_original_constraints;"

# indices
mysql $CONNECTION -vve "use $DB;DROP PROCEDURE IF EXISTS sp_drop_indices;"
mysql $CONNECTION -vve "use $DB;DROP PROCEDURE IF EXISTS sp_add_indices;"

# drop history tables

mysql $CONNECTION -vve "use $DB;DROP PROCEDURE IF EXISTS sp_drop_hist_tables;"

# drop triggers

mysql $CONNECTION -vve "use $DB;DROP PROCEDURE IF EXISTS sp_drop_triggers;"

# main NI Extract procedure

mysql $CONNECTION -vve "use $DB;DROP PROCEDURE IF EXISTS sp_NI_Extract;"

#drop misc tables
mysql $CONNECTION -vve "use $DB;DROP TABLE IF EXISTS DATABASECHANGELOG;"
mysql $CONNECTION -vve "use $DB;DROP TABLE IF EXISTS DATABASECHANGELOGLOCK;"

#validation

mysql $CONNECTION -vve "use $DB;DROP PROCEDURE IF EXISTS sp_validate_NI_Extract;"

#drop delete procedures

cd delete_procs

./drop_delete_procs.sh "$CONNECTION" $DB

cd ..

