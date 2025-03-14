#!/bin/bash

# Consult vol-puppet/puppet/modules/profile/manifests/olcs/searchdatav6.pp for original build steps

# Install and configure search data

# Templates
declare -a templates=("address" "application" "busreg" "case" "irfo" "licence" "person" "psv_disc" "publication" "user" "vehicle_current" "vehicle_removed")

# To do: update settings file settings.sh using env vars

# To do: install mysql plugin

#    command => "/bin/bash -c '/usr/local/bin/aws s3 cp s3://${rpmmgmtbucket}/mysql-connector-java-${mysql_connector_version}.jar /opt/dvsa/olcs/mysql-connector-java-${mysql_connector_version}.jar && chmod 644 /opt/dvsa/olcs/mysql-connector-java-${mysql_connector_version}.jar'",
#    creates => "/opt/dvsa/olcs/mysql-connector-java-${mysql_connector_version}.jar",

# Install logstash pipeline configs

for i in "${templates[@]}"
do
    # Create /usr/share/logstash/pipeline/$i.conf
    /usr/share/logstash/config/generate-conf.sh -c /usr/share/logstash/config/settings.sh -i $i
    
    # Add to pipelines.yaml
    echo -e '- pipeline.id: $i\n  path.config: \"/usr/share/logstash/pipline/${i}.conf\"' >> "/usr/share/logstash/config/pipelines.yml"


    /usr/share/logstash/build.sh -c /usr/share/logstash/config/settings.sh -d 60 -l $i
done
    






