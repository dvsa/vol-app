#!/bin/env bash

CONNECTION=$1
DB=$2

# install NI Extract delete procedure

for file in `ls -1 sp_delete*.sql`
do
    mysql $CONNECTION -e "use $DB;\. $file"
done
