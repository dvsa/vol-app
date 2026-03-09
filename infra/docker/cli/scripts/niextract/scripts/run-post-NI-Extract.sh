#!/bin/env bash

CONNECTION=$1
DB=$2

# drop constraints

mysql $CONNECTION -vve "use $DB;CALL sp_drop_constraints;"

# add indices removed for extract

mysql $CONNECTION -vve "use $DB;CALL sp_add_indices;"

# add all constraints

mysql $CONNECTION -vve "use $DB;CALL sp_add_original_constraints;"
