#!/bin/bash

# Consult vol-puppet/puppet/modules/profile/manifests/olcs/searchdatav6.pp for original build steps

# Templates
declare -a templates=("address" "application" "busreg" "case" "irfo" "licence" "person" "psv_disc" "publication" "user" "vehicle_current" "vehicle_removed")

# Install logstash pipeline configs

for i in "${templates[@]}"
do
    # Create /usr/share/logstash/pipeline/$i.conf
    /usr/share/logstash/config/generate-conf.sh -c /usr/share/logstash/config/settings.sh -i $i 
    
    # Add to pipelines.yaml
    echo -e "- pipeline.id: $i\n  path.config: \"/usr/share/logstash/pipline/${i}.conf\"" >> "/usr/share/logstash/pipeline/pipelines.yml"

done

# build indexes
#/usr/share/logstash/build.sh -c /usr/share/logstash/config/settings.sh

# Start logstash
exec logstash

# keep container running
#tail -f /dev/null
    






