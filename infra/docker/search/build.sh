#!/bin/bash

typeset -i errorcount
let errorcount=0

blankline() {
    if [ "$logfile" == "" ]; then
        echo -e ""
    else
        echo -e "" >> $logfile
    fi
}

singleline() {
    if [ "$logfile" == "" ]; then
        echo -e "----------------------------------------------------------------------------------------------------"
    else
        echo -e "----------------------------------------------------------------------------------------------------" >> $logfile
    fi
}

doubleline() {
    if [ "$logfile" == "" ]; then
        echo -e "===================================================================================================="
    else
        echo -e "====================================================================================================" >> $logfile
    fi
}

logInfo() {
    if [ "$logfile" == "" ]; then
        echo -e $(date) "INFO:   " $1
    else
        echo $(date) "INFO:   " >> $logfile
        echo -e $1 >> $logfile
    fi

    if [ "$2" == "true" ]; then
         logger -i -p user.info -t ESbuild -- "INFO:   $1"
    fi
}

logError() {
    if [ "$logfile" == "" ]; then
        echo -e $(date) "ERROR:  " $1
    else
        echo $(date) "ERROR:  " >> $logfile
        echo -e $1 >> $logfile
    fi
    
    if [ "$2" == "true" ]; then
        logger -i -p user.notice -t ESbuild -- "ERROR:  $1"
    fi
}

logWarning() {
    if [ "$logfile" == "" ]; then
        echo -e $(date) "WARNING:" $1
    else
        echo $(date) "WARNING:" >> $logfile
        echo -e $1 >> $logfile
    fi
    
    if [ "$2" == "true" ]; then
        logger -i -p user.warning -t ESbuild -- "WARNING:$1"
    fi
}

function enableReplicas()
{
    index="${1}" 
    newVersion="${2}"
    syslogEnabled="${3}"
    elasticHost="${4}"

    if [[ "${elastichost}" == *"dev-dvsacloud"* ]]; then 
        logInfo "No need for replicas for [${index}_v${newVersion}] index in [${elastichost}] account." ${syslogEnabled}
        curl -s -XPUT "https://$ELASTIC_HOST/${index}_v${newVersion}/_settings" -H 'Content-Type: application/json' -d '{"index": {"number_of_replicas": 0,"auto_expand_replicas": false}}'
        return 0
    else
        logInfo "Enable replicas for [${index}_v${newVersion}] index." ${syslogEnabled}
        response=$(curl -s -XPUT "https://$ELASTIC_HOST/${index}_v${newVersion}/_settings" -H 'Content-Type: application/json' -d '{"index": {"number_of_replicas": 1}}')
        if [[ ${response} != "{\"acknowledged\":true}" ]]; then
            logError "Failed to enable replicas for [${index}_v${newVersion}] - error code is [${response}]." ${syslogEnabled}
            return 1
        else
            logInfo "Successfully configured replicas for [${index}_v${newVersion}] index." ${syslogEnabled}
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

    syslogEnabled="$4"
    elasticHost="$5"

    while true
    do
        singleline
        logInfo "Deleting indexes matching [${index}] which have no alias." ${syslogEnabled}

        indexsWithoutAlias=$(curl -s -XGET https://${elasticHost}/_aliases | python3 ./py/indexWithoutAlias.py ${index} })

        if [ ! -z $indexsWithoutAlias ]; then
            logInfo "Matching indexes without aliases are [${indexsWithoutAlias}]." ${syslogEnabled}
            response=$(curl -XDELETE -s https://${elasticHost}/$indexsWithoutAlias)

            if [[ "$response" != "{\"acknowledged\":true}" ]]; then
                let retryCount=$((retryCount + 1))
                logError "One or more matching indexes without an alias was not deleted: [${indexsWithoutAlias}] - error code [${response}]." ${syslogEnabled}

                if (( ${retryCount} < ${retryLimit} )); then
                    logInfo "Backing off for [${sleepTime}] seconds." ${syslogEnabled}
                    sleep ${sleepTime}
                else
                    logError "One or more matching indexes without an alias could not be deleted: [${indexsWithoutAlias}] - error code [${response}]." ${syslogEnabled}
                    return 1
                fi
            else
                logInfo "The following matching indexes without aliases were deleted: [${indexsWithoutAlias}]." ${syslogEnabled}
                return 0
            fi
        else
            logInfo "No indexes matching [${index}] were found without aliases" ${syslogEnabled}
            return 0
        fi
    
        blankline
    done
}

delay=70 # seconds
newVersion=$(date +%Y%m%d%H%M%S) #timestamp
confDir='/usr/share/logstash/config/'
promoteNewIndex=true
syslogEnabled=true

while getopts "c:lps" opt; do
  case $opt in
    c)
        if [ ! -f $OPTARG ]; then
          usage "Config file $OPTARG doesn't exist";
        fi
        source $OPTARG    
    \?)
      usage "Invalid option: -$OPTARG";
      ;;
    :)
      usage "Option -$OPTARG requires an argument.";
      ;;
  esac
done

if [ -z "${ELASTIC_HOST}" ]
then
    usage "ELASTIC_HOST must be specified in config file"
    exit;
fi

if [ -z "${INDEXES}" ]
then
    INDEXES=( "irfo" "busreg" "case" "application" "user" "licence" "psv_disc" "address" "person" "vehicle_current" "publication"  "vehicle_removed" )
fi

doubleline
logInfo "ES REBUILD WITH THE FOLLOWING CONFIGURATION" ${syslogEnabled}
blankline
logInfo "ES Rebuild Config Path Dir: ${confDir}" ${syslogEnabled}
logInfo "ES Rebuild Target indexes:  ${INDEXES[*]}" ${syslogEnabled}
logInfo "ES Rebuild Delay:           ${delay}" ${syslogEnabled}
logInfo "ES Rebuild New version:     ${newVersion}" ${syslogEnabled}
logInfo "ES Rebuild Promote Index:   ${promoteNewIndex}" ${syslogEnabled}
blankline

singleline
logInfo "INDEX STATS BEFORE" ${syslogEnabled}
blankline

for index in "${INDEXES[@]}"; do curl -XGET -ss https://$ELASTIC_HOST/_cat/indices?pretty | grep $index | sort ; done
if [ "${syslogEnabled}" == "true" ]; then
    for index in "${INDEXES[@]}"; do curl -XGET -ss https://$ELASTIC_HOST/_cat/indices?pretty | grep $index | sort ; done | sed "s/^/INFO:   /" | while read oneLine; do logger -i -p user.warning -t ESbuild -- "$oneLine"; done
fi

blankline

for index in "${INDEXES[@]}"
do
    singleline
    logInfo "DELETING MATCHING INDEXES WITHOUT AN ALIAS" ${syslogEnabled}
    blankline
     
    purgeOldAliases ${index} 30 6 ${syslogEnabled} ${ELASTIC_HOST}
    ret=$?
    if [[ $ret != 0 ]]; then
        let errorcount=$((errorcount + 1))
    fi

    blankline
    singleline
    logInfo "CREATING NEW INDEX [${index}]" ${syslogEnabled}
    blankline
    logInfo "Updating config file for [${index}] index and new version [${index}_v${newVersion}]." ${syslogEnabled}

    sed "s/index => \"${index}_v[0-9]*\"/index => \"${index}_v${newVersion}\"/" -i $confDir/$index.conf

done

# Start logstash in the background
logstash &

# Store the PID of the logstash process
LOGSTASH_PID=$!

for index in "${INDEXES[@]}"
do
    logInfo "Populate Index [${index}]." ${syslogEnabled}

    lastSize=0
    while true; do
        # wait X seconds before checking
        sleep $delay

        size=$(curl -XGET -s "https://$ELASTIC_HOST/${index}_v${newVersion}/_stats" | python3 ./py/getIndexSize.py)
        logInfo "Loading data to [${index}_v${newVersion}] document count is $size" ${syslogEnabled}
        if [ "$size" -lt 10 ]; then
            continue
        fi

        if [ "$lastSize" == "$size" ]; then
            logInfo "Document count of [${index}_v${newVersion}] index has not changed in the last ${delay} secs, it may be fully populated." ${syslogEnabled}
            if [ -f /etc/logstash/lastrun/${index}.lastrun ]; then
                logInfo "Lastrun file exists, so assuming [${index}_v${newVersion}] is fully populated." ${syslogEnabled}
                break;
            fi
        fi

        lastSize=$size
    done

    logInfo "Moving the alias [${index}] to the new index [${index}_v${newVersion}]." ${syslogEnabled}
    modifyBody=$(curl -s -XGET https://$ELASTIC_HOST/_aliases?pretty | python3 ./py/modifyAliases.py $newVersion $index)
    response=$(curl -XPOST -s "https://$ELASTIC_HOST/_aliases" -H 'Content-Type: application/json' -d "$modifyBody")
    if [[ "${response}" != "{\"acknowledged\":true}" ]]; then
        logError "Alias [${index}] not moved to [${index}_v${newVersion}] - error code is [${response}]." ${syslogEnabled}
        let errorcount=$((errorcount + 1))
    else
        logInfo "Successfully moved alias [${index}] to the new index [${index}_v${newVersion}]." ${syslogEnabled}
    fi
    
    enableReplicas "${index}" "${newVersion}" "${syslogEnabled}" "${ELASTIC_HOST}"
    if [[ $? != 0 ]]; then
        logError "Failed to enable replicas for [${index}_v${newVersion}] - error code is [${response}]." ${syslogEnabled}
        let errorcount=$((errorcount + 1))
    fi

    blankline
done

singleline
logInfo "INDEX STATS AFTER" ${syslogEnabled}
blankline

for index in "${INDEXES[@]}"; do curl -XGET -ss https://$ELASTIC_HOST/_cat/indices?pretty | grep $index | sort ; done

if [ "${syslogEnabled}" == "true" ]; then
    for index in "${INDEXES[@]}"; do curl -XGET -ss https://$ELASTIC_HOST/_cat/indices?pretty | grep $index | sort ; done | sed "s/^/INFO:   /" | while read oneLine; do logger -i -p user.warning -t ESbuild -- "$oneLine"; done
fi

blankline

# Kill the first logstash process
kill $LOGSTASH_PID

# Wait for the logstash process to terminate
wait $LOGSTASH_PID

# Replace the current script with the new logstash process
exec logstash