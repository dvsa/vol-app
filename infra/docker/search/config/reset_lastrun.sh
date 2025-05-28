#!/bin/bash

# Templates
declare -a templates=("address" "application" "busreg" "case" "irfo" "licence" "person" "psv_disc" "publication" "user" "vehicle_current" "vehicle_removed")

for i in "${templates[@]}"
do
    echo "---" > /usr/share/logstash/config/lastrun/$i.lastrun
    echo "sql_last_value: 1970-01-01 00:00:00.000000000 +00:00" >> /usr/share/logstash/config/lastrun/$i.lastrun

done