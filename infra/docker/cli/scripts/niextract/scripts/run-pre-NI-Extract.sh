#!/bin/env bash

CONNECTION=$1
DB=$2

# drop constraints  and indices not required for extract
# add required contraints for the extract
# drop triggers
# drop _hist tables
# Unable to run in parallel due to foreign key constraints, so all in one call to avoid deadlocks

mysql $CONNECTION -e "USE $DB; CALL sp_drop_constraints; CALL sp_drop_indices; CALL sp_add_NI_Extract_constraints; CALL sp_drop_triggers; CALL sp_drop_hist_tables;"