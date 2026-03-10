#!/bin/env bash

CONNECTION=$1
DB=$2

# drop NI Extract delete procedure

for file in `ls -1 sp_delete*.sql`
do
    procedure=$(echo $file | sed -e 's/\.sql//')
    mysql $CONNECTION -e "DROP PROCEDURE IF EXISTS $DB.$procedure;"
done
