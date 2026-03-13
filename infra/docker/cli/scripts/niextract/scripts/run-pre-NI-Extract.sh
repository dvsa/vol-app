#!/bin/env bash

CONNECTION=$1
DB=$2

# drop constraints  and indices not required for extract

mysql $CONNECTION -e "use $DB;CALL sp_drop_constraints;"

mysql $CONNECTION -e "use $DB;CALL sp_drop_indices;"

# add required contraints for the extract

mysql $CONNECTION -e "use $DB;CALL sp_add_NI_Extract_constraints;"

# drop triggers

mysql $CONNECTION -e "use $DB;CALL sp_drop_triggers;"

# drop _hist tables

mysql $CONNECTION -e "use $DB;CALL sp_drop_hist_tables;"

