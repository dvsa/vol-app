#!/bin/bash

# Consult vol-puppet/puppet/modules/profile/manifests/olcs/searchdatav6.pp for original build steps

function enableReplicas()
{
    index="${1}" 
    newVersion="${2}"
    awsAccount="${4}"

    if [[ "${awsAccount}" == "dev-dvsacloud" ]]; then 
        echo "No need for replicas for [${index}_v${newVersion}] index in [${awsAccount}] account." 
        curl -s -XPUT "https://$ELASTIC_HOST/${index}_v${newVersion}/_settings" -H 'Content-Type: application/json' -d '{"index": {"number_of_replicas": 0,"auto_expand_replicas": false}}'
        return 0
    else
        echo "Enable replicas for [${index}_v${newVersion}] index." 
        response=$(curl -s -XPUT "https://$ELASTIC_HOST/${index}_v${newVersion}/_settings" -H 'Content-Type: application/json' -d '{"index": {"number_of_replicas": 1}}')
        if [[ ${response} != "{\"acknowledged\":true}" ]]; then
            echo "Failed to enable replicas for [${index}_v${newVersion}] - error code is [${response}]." 
            return 1
        else
            echo "Successfully configured replicas for [${index}_v${newVersion}] index." 
        fi
    fi

    return 0
}

function purgeOldAliases()
{
    typeset -i retryCount=0
    typeset -i sleepTime=20
    typeset -i retryLimit=6

    index="$1"

    if [[ $2 =~ ^[0-9]+$ ]] ; then
        let sleepTime=$((10#$2))
    fi

    if [[ $3 =~ ^[0-9]+$ ]] ; then
        let retryLimit=$((10#$3))
    fi

    elasticHost="$5"

    while true
    do
        echo "Deleting indexes matching [${index}] which have no alias." 

        indexsWithoutAlias=$(curl -s -XGET https://${elasticHost}/_aliases | python3 ./config/py/indexWithoutAlias.py ${index} })

        if [ ! -z $indexsWithoutAlias ]; then
            echo "Matching indexes without aliases are [${indexsWithoutAlias}]." 
            response=$(curl -XDELETE -s https://${elasticHost}/$indexsWithoutAlias)

            if [[ "$response" != "{\"acknowledged\":true}" ]]; then
                let retryCount=$((retryCount + 1))
                echo "One or more matching indexes without an alias was not deleted: [${indexsWithoutAlias}] - error code [${response}]." 

                if (( ${retryCount} < ${retryLimit} )); then
                    echo "Backing off for [${sleepTime}] seconds." 
                    sleep ${sleepTime}
                else
                    echo "One or more matching indexes without an alias could not be deleted: [${indexsWithoutAlias}] - error code [${response}]." 
                    return 1
                fi
            else
                echo "The following matching indexes without aliases were deleted: [${indexsWithoutAlias}]." 
                return 0
            fi
        else
            echo "No indexes matching [${index}] were found without aliases" 
            return 0
        fi
     
    done
}

delay=70 # seconds
newVersion=$(date +%Y%m%d%H%M%S) #timestamp
confDir='/usr/share/logstash/config'
awsAccount=$(uname -n | cut -d'.' -f4)

INDEXES=( "irfo" "busreg" "case" "application" "user" "licence" "psv_disc" "address" "person" "vehicle_current" "publication"  "vehicle_removed" )

echo "ES REBUILD WITH THE FOLLOWING CONFIGURATION" 
echo "ES Rebuild Config Path Dir: ${confDir}" 
echo "ES Rebuild Target indexes:  ${INDEXES[*]}" 
echo "ES Rebuild Delay:           ${delay}" 
echo "ES Rebuild New version:     ${newVersion}" 
echo "ES Rebuild Promote Index:   ${promoteNewIndex}" 

echo "INDEX STATS BEFORE" 


for index in "${INDEXES[@]}"; do curl -XGET -ss https://$ELASTIC_HOST/_cat/indices?pretty | grep $index | sort ; done

echo "DELETING MATCHING INDEXES WITHOUT AN ALIAS." 
    
for index in "${INDEXES[@]}"
do
    purgeOldAliases ${index} 30 6  ${ELASTIC_HOST}
done
    
echo "CREATING NEW INDEXES" 

for index in "${INDEXES[@]}"
do

    echo "Updating config file for [${index}] index and new version [${index}_v${newVersion}]." 

    # Create index config
    /usr/share/logstash/config/generate-conf.sh -c /usr/share/logstash/config/settings.sh -i $index -n ${newVersion}
    
    # Add to logstash pipeline
    echo -e "- pipeline.id: $i\n  path./config: \"/usr/share/logstash/pipeline/${i}.conf\"" >> "/usr/share/logstash/config/pipelines.yml"
        
done
    
echo "Starting Logstash service in background" 

logstash > /dev/null 2>&1 &
SERVICE_PID=$!

echo "POPULATING NEW INDEXES" 

for index in "${INDEXES[@]}"
do
    echo "Populate Index [${index}]." 

    lastSize=0
    while true; do
        # wait X seconds before checking
        sleep $delay

        size=$(curl -XGET -s "https://$ELASTIC_HOST/${index}_v${newVersion}/_stats" | python3 ./config/py/getIndexSize.py)
        echo "Loading data to [${index}_v${newVersion}] document count is $size" 
        if [ "$size" -lt 10 ]; then
            continue
        fi

        if [ "$lastSize" == "$size" ]; then
            echo "Document count of [${index}_v${newVersion}] index has not changed in the last ${delay} secs, it may be fully populated." 
            if [ -f /etc/logstash/lastrun/${index}.lastrun ]; then
                echo "Lastrun file exists, so assuming [${index}_v${newVersion}] is fully populated." 
                break;
            fi
        fi

        lastSize=$size
    done

    echo "Moving the alias [${index}] to the new index [${index}_v${newVersion}]." 
    modifyBody=$(curl -s -XGET https://$ELASTIC_HOST/_aliases?pretty | python3 ./config/py/modifyAliases.py $newVersion $index)
    response=$(curl -XPOST -s "https://$ELASTIC_HOST/_aliases" -H 'Content-Type: application/json' -d "$modifyBody")
    if [[ "${response}" != "{\"acknowledged\":true}" ]]; then
        echo "Alias [${index}] not moved to [${index}_v${newVersion}] - error code is [${response}]." 
        let errorcount=$((errorcount + 1))
    else
        echo "Successfully moved alias [${index}] to the new index [${index}_v${newVersion}]." 
    fi
    
    enableReplicas "${index}" "${newVersion}" "" "${awsAccount}"
    if [[ $? != 0 ]]; then
        echo "Failed to enable replicas for [${index}_v${newVersion}] - error code is [${response}]." 
        let errorcount=$((errorcount + 1))
    fi

    
done

echo "INDEX STATS AFTER" 

for index in "${INDEXES[@]}"; do curl -XGET -ss https://$ELASTIC_HOST/_cat/indices?pretty | grep $index | sort ; done

echo "STOPPING LOGSTASH IN BACKGROUND"

kill $SERVICE_PID
wait $SERVICE_PID 2>/dev/null

echo "STARTING LOGSTASH IN FORGROUND"

exec logstash