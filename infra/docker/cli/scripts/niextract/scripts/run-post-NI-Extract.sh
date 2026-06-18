#!/bin/env bash

CONNECTION=$1
DB=$2

# drop constraints
# add indices removed for extract
# add all constraints
# Unable to run in parallel due to foreign key constraints, so all in one call to avoid deadlocks

mysql $CONNECTION -vve "USE $DB; CALL sp_drop_constraints; CALL sp_add_indices; CALL sp_add_original_constraints;"