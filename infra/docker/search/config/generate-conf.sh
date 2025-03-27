#!/bin/bash -e

usage() {
    if [ -n "$1" ]; then
        echo;
        echo Error : $1
    fi

    echo;
    echo Usage: generate-conf.sh [options];
    echo;
    echo "-c <file>       : bash file containing config";
    echo "-i <index>      : Generate config for named index. Config files will be generated as '<INDEX_NAME>.conf'"
    echo
    exit;
}

log() {
    if [ "$logfile" == "" ]; then
        echo -e $(date) $1
    else
        echo $(date) >> $logfile
        echo -e $1 >> $logfile
    fi
}

while getopts "c:i:" opt; do
  case $opt in
    c)
        if [ ! -f $OPTARG ]; then
          usage "Config file $OPTARG doesn't exist";
        fi
        source $OPTARG
      ;;
    i)
        INDEXES=(${INDEXES[@]} "${OPTARG}")
      ;;
    \?)
      usage "Invalid option: -$OPTARG";
      ;;
    :)
      usage "Option -$OPTARG requires an argument.";
      ;;
  esac
done

if [ -z "$ELASTIC_HOST" ]
then
    usage "-e parameter must be set"
    exit;
fi

if [ -z "$DBHOST" ]
then
    usage "-h parameter must be set"
    exit;
fi

if [ -z "$DBNAME" ]
then
    usage "-m parameter must be set"
    exit;
fi

if [ -z "$DBUSER" ]
then
    usage "-u parameter must be set"
    exit;
fi

if [ -z "$DBPASSWORD" ]
then
    usage "-p parameter must be set"
    exit;
fi

if [ -z "$DIRPATH" ]
then
    DIRPATH="/usr/share/logstash/pipeline/"
fi

if [ -z "$INDEXES" ]
then
    INDEXES=( "irfo" "busreg" "case" "application" "user" "licence" "psv_disc" "address" "person" "vehicle_current" "publication"  "vehicle_removed" )
fi

newVersion=$(date +%s) #timestamp

JDBC_LIBRARY=$(basename "`ls /usr/share/logstash/logstash-core/lib/jars/mysql-connector-java.jar`")
log "Replace placeholders in logstash config file(s)"
BASEDIR=$(dirname $(readlink -m $0))
for INDEX in "${INDEXES[@]}"; do
  CONFFILE="${INDEX}.conf"
  cp /usr/share/logstash/config/populate_indices.conf.dist $DIRPATH/$CONFFILE
  sed "s/<JDBC_LIBRARY>/$JDBC_LIBRARY/" -i $DIRPATH/$CONFFILE
  sed "s/<DB_HOST>/$DBHOST/" -i $DIRPATH/$CONFFILE
  sed "s/<DB_NAME>/$DBNAME/" -i $DIRPATH/$CONFFILE
  sed "s/<DB_USER>/$DBUSER/" -i $DIRPATH/$CONFFILE
  sed "s/<DB_PASSWORD>/$DBPASSWORD/" -i $DIRPATH/$CONFFILE
  sed "s/<ELASTIC_HOST>/$ELASTIC_HOST/" -i $DIRPATH/$CONFFILE
  sed "s/<INDEX_VERSION>/$newVersion/" -i $DIRPATH/$CONFFILE
  sed "s#<LOGSTASH_PATH>#$LOGSTASH_PATH#" -i $DIRPATH/$CONFFILE
  sed "s#<BASEDIR>#$BASEDIR#" -i $DIRPATH/$CONFFILE
  sed "s/<INDEX_NAME>/$INDEX/" -i $DIRPATH/$CONFFILE
  chmod 644 $DIRPATH/$CONFFILE
done

log "Done"